<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221204204617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE test (id INT AUTO_INCREMENT NOT NULL, sup DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE personne ADD is_verified TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE personne_quartier DROP etat, DROP datededebut, DROP datedefin');
        $this->addSql('ALTER TABLE personne_commune DROP etat, DROP datededebut, DROP datedefin');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE test');
        $this->addSql('ALTER TABLE personne DROP is_verified');
        $this->addSql('ALTER TABLE personne_commune ADD etat VARCHAR(30) NOT NULL, ADD datededebut DATE NOT NULL, ADD datedefin DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE personne_quartier ADD etat VARCHAR(30) NOT NULL, ADD datededebut DATE NOT NULL, ADD datedefin DATETIME DEFAULT NULL');
    }
}
