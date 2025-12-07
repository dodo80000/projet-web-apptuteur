<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127155149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE etudiant (id INT AUTO_INCREMENT NOT NULL, tuteur_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, formation VARCHAR(255) NOT NULL, INDEX IDX_717E22E386EC68D8 (tuteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tuteur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE visite (id INT AUTO_INCREMENT NOT NULL, tuteur_id INT DEFAULT NULL, etudiant_id INT DEFAULT NULL, date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', commentaire VARCHAR(255) NOT NULL, compte_rendu LONGTEXT DEFAULT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_B09C8CBB86EC68D8 (tuteur_id), INDEX IDX_B09C8CBBDDEAB1A3 (etudiant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E386EC68D8 FOREIGN KEY (tuteur_id) REFERENCES tuteur (id)');
        $this->addSql('ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBB86EC68D8 FOREIGN KEY (tuteur_id) REFERENCES tuteur (id)');
        $this->addSql('ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBBDDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E386EC68D8');
        $this->addSql('ALTER TABLE visite DROP FOREIGN KEY FK_B09C8CBB86EC68D8');
        $this->addSql('ALTER TABLE visite DROP FOREIGN KEY FK_B09C8CBBDDEAB1A3');
        $this->addSql('DROP TABLE etudiant');
        $this->addSql('DROP TABLE tuteur');
        $this->addSql('DROP TABLE visite');
    }
}
