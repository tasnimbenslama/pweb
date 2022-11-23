<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221113175703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE formation_etudiant (formation_id INT NOT NULL, etudiant_id INT NOT NULL, INDEX IDX_B6EC75125200282E (formation_id), INDEX IDX_B6EC7512DDEAB1A3 (etudiant_id), PRIMARY KEY(formation_id, etudiant_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE formation_etudiant ADD CONSTRAINT FK_B6EC75125200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formation_etudiant ADD CONSTRAINT FK_B6EC7512DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation_etudiant DROP FOREIGN KEY FK_B6EC75125200282E');
        $this->addSql('ALTER TABLE formation_etudiant DROP FOREIGN KEY FK_B6EC7512DDEAB1A3');
        $this->addSql('DROP TABLE formation_etudiant');
    }
}
