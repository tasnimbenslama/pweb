<?php

namespace App\Controller;

use App\Entity\Formation;
use App\services\pdfservice;
use App\Form\FormationFormType;
use App\Repository\FormationRepository;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;


class FormationController extends AbstractController
{
    /**
     * @Route("/formation", name="app_formation")
     */
    public function index(): Response
    {
        return $this->render('formation/index.html.twig', [
            'controller_name' => 'FormationController',
        ]);
    }

    /**
     * @Route("/afficheF", name="afficheF")
     */
    public function afficheC(): Response
    {
        //recuperer le repository //
        $r = $this->getDoctrine()->getRepository(Formation::class);
        $c = $r->findAll();
        return $this->render('formation/afficheF.html.twig', [
            'formation' => $c,
        ]);
    }

    /**
     * @Route("affiche", name="affiche")
     */
    public function affiche(): Response
    {
        //recuperer le repository //
        $r = $this->getDoctrine()->getRepository(Formation::class);
        $c = $r->findAll();
        return $this->render('formation/affiche.html.twig', [
            'for' => $c,
        ]);

    }

    /**
     * @Route("/supprimerC/{id}", name="suppC")
     */
    public function supprimerC($id, FormationRepository $repository, ManagerRegistry $doctrine): Response
    {
        $formation = $repository->find($id);
        //action de suppression via Entity manager
        $em = $doctrine->getManager();
        $em->remove($formation);
        $em->flush();
        return $this->redirectToRoute('afficheF');

    }

    /**
     * @Route("/update/{id}", name="modifC")
     */
    public function updateClassroom(ManagerRegistry $doctrine, Request $request, $id, FormationRepository $r, SluggerInterface $slugger)
    {

        $formation = $r->find($id);
        $form = $this->createForm(FormationFormType::class, $formation);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $em = $doctrine->getManager();
            $brochureFile = $form->get('detail')->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $formation->setDetail($newFilename);
            }
            $em->flush();
            return $this->redirectToRoute('afficheF');
        }
        return $this->renderForm("formation/update.html.twig",
            array("f" => $form));
    }

    /**
     * @Route("/add", name="add")
     */
    public function ajouter(ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger)
    {
        $formation = new Formation();
        $form = $this->createForm(FormationFormType::class, $formation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->persist($formation);
            $brochureFile = $form->get('detail')->getData();
            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                try {
                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $formation->setDetail($newFilename);
            }

            $em->flush();
            $this->addFlash(
                'notice',
                'Formation ajouteé avec success!');
        }
        return $this->renderForm("formation/add.html.twig",
            array("f" => $form));
    }


    /**
     * @Route("/pdf/{id}", name="formation.pdf")
     */
    public function generatePdf(Formation $formation = null, PdfService $pdf)
    {
        $html = $this->render('formation/detail.html.twig', ['formation' => $formation]);
        $pdf->showPdfFile($html);
    }

    /**
     * @Route("/test", name="test")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function statAction()
    {
        $items = $this->getDoctrine()->getRepository(formation::class)->findAll();
        $prix = 0;
        $nbplaces = 0;
        foreach ($items as $item) {
            $prix += $item->getPrix();
            $nbplaces += $item->getNbplaces();
        }
        $pieChart = new PieChart();

        $pieChart->getData()->setArrayToDataTable(array(
            ['Task', 'Hours per Day'],
            ['prix', $prix],
            ['nbplaces', $nbplaces],
        ));
        $pieChart->getOptions()->setTitle('les details');
        $pieChart->getOptions()->setHeight(400);
        $pieChart->getOptions()->setWidth(400);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#07600');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(25);


        return $this->render('formation/statrec.html.twig', array(
                'piechart' => $pieChart,
            )

        );

    }

    /**
     * @param $id
     * @param FormationRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/likepost/{id}", name="likepost")
     */
    public function likepost(FormationRepository $repository, $id)
    {
        $forum = $repository->find($id);
        $new = $forum->getJaime() + 1;
        $forum->setJaime($new);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('affiche');
    }

    /**
     * @param $id
     * @param FormationRepository $repository
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/dislikepost/{id}", name="dislikepost")
     */
    public function dislikePost(FormationRepository $repository, $id)
    {
        $forum = $repository->find($id);
        $new = $forum->getJaimepas() + 1;
        $forum->setJaimepas($new);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('affiche');
    }

    /**
     * @Route("/stat/{id}", name="stat")
     */
    public function statAction1($id): Response
    {
        $pieChart = new PieChart();

        $entityManager = $this->getDoctrine()->getManager();
        $objet = $entityManager->getRepository(Formation::class)->find($id);
        $pieChart = new PieChart();
        $pieChart->getData()->setArrayToDataTable(array(
            ['post', 'Nombre de jaime'],
            ['Jaime', $objet->getJaime()],
            ['Jaime pas', $objet->getJaimepas()],
        ));

        $pieChart->getOptions()->setTitle('Statistique like/dislike Formation');
        $pieChart->getOptions()->setHeight(400);
        $pieChart->getOptions()->setWidth(400);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#07600');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(25);


        return $this->render('formation/statrec.html.twig', array(
                'piechart' => $pieChart,
            )

        );
    }
}

