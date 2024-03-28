<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230109131516 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX `primary` ON personne_commune');
        $this->addSql('ALTER TABLE personne_commune DROP etat, DROP dateDeDebut, DROP dateDeFin');
        $this->addSql('ALTER TABLE personne_commune ADD PRIMARY KEY (personne_id, commune_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX `PRIMARY` ON personne_commune');
        $this->addSql('ALTER TABLE personne_commune ADD etat VARCHAR(22) NOT NULL, ADD dateDeDebut DATETIME NOT NULL, ADD dateDeFin DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE personne_commune ADD PRIMARY KEY (personne_id, commune_id, dateDeDebut)');
    }
}
