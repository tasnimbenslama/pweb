<?php

namespace App\Controller;


use App\Entity\Formation;
use App\Form\FormationFormType;
use App\Repository\FormationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
        $r=$this->getDoctrine()->getRepository(Formation::class);
        $c=$r->findAll();
        return $this->render('formation/afficheF.html.twig', [
            'formation' => $c,
        ]);
    }
    /**
     * @Route("/affiche", name="affiche")
     */
    public function affiche(): Response
    {
        //recuperer le repository //
        $r=$this->getDoctrine()->getRepository(Formation::class);
        $c=$r->findAll();
        return $this->render('formation/affiche.html.twig', [
            'for' => $c,
        ]);
    }
    /**
     * @Route("/supprimerC/{id}", name="suppC")
     */
    public function supprimerC($id,FormationRepository $repository, ManagerRegistry $doctrine): Response
    {
        //recuperer classroom a supprimer
        $formation=$repository->find($id);
        //action de suppression via Entity manager
        $em=$doctrine->getManager();
        $em->remove($formation);
        $em->flush();
        return $this->redirectToRoute('afficheF');

    }
    /**
     * @Route("/update/{id}", name="modifC")
     */
    public function updateClassroom( ManagerRegistry $doctrine,Request $request,$id,FormationRepository $r )
    {

        $formation=$r->find($id);
        $form=$this->createForm(FormationFormType::class,$formation);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em=$doctrine->getManager();
            $em->flush();
            return $this->redirectToRoute('afficheF');}
        return $this->renderForm("formation/add.html.twig",
            array("f"=>$form));}

    /**
     * @Route("/add", name="add")
     */
    public function ajouter( ManagerRegistry $doctrine,Request $request )
    {
        $formation=new Formation();
        $form=$this->createForm(FormationFormType::class,$formation);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()) {
            $em=$doctrine->getManager();
            $em->persist($formation);
            $em->flush();
            return $this->redirectToRoute('afficheF');}
        return $this->renderForm("formation/add.html.twig",
            array("f"=>$form));}

}
