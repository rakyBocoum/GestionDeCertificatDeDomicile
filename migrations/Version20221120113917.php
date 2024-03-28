<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221120113917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE personne_quartier (personne_id INT NOT NULL, quartier_id INT NOT NULL,etat VARCHAR(30) NOT NULL,datededebut DATE NOT NULL, datedefin DATETIME DEFAULT NULL, INDEX IDX_3D3A9768A21BD112 (personne_id), INDEX IDX_3D3A9768DF1E57AB (quartier_id), PRIMARY KEY(personne_id, quartier_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE personne_commune (personne_id INT NOT NULL, commune_id INT NOT NULL,etat VARCHAR(30) NOT NULL,datededebut DATE NOT NULL, datedefin DATETIME DEFAULT NULL, INDEX IDX_3C6D3513A21BD112 (personne_id), INDEX IDX_3C6D3513131A4F72 (commune_id), PRIMARY KEY(personne_id, commune_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE personne_quartier ADD CONSTRAINT FK_3D3A9768A21BD112 FOREIGN KEY (personne_id) REFERENCES personne (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE personne_quartier ADD CONSTRAINT FK_3D3A9768DF1E57AB FOREIGN KEY (quartier_id) REFERENCES quartier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE personne_commune ADD CONSTRAINT FK_3C6D3513A21BD112 FOREIGN KEY (personne_id) REFERENCES personne (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE personne_commune ADD CONSTRAINT FK_3C6D3513131A4F72 FOREIGN KEY (commune_id) REFERENCES commune (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE commune ADD departement_id INT NOT NULL');
        $this->addSql('ALTER TABLE commune ADD CONSTRAINT FK_E2E2D1EECCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('CREATE INDEX IDX_E2E2D1EECCF9E01E ON commune (departement_id)');
        $this->addSql('ALTER TABLE demandecretificat ADD habitant_id INT NOT NULL, ADD quartier_id INT NOT NULL, ADD delegue_id INT NOT NULL');
        $this->addSql('ALTER TABLE demandecretificat ADD CONSTRAINT FK_A12BC3358254716F FOREIGN KEY (habitant_id) REFERENCES personne (id)');
        $this->addSql('ALTER TABLE demandecretificat ADD CONSTRAINT FK_A12BC335DF1E57AB FOREIGN KEY (quartier_id) REFERENCES quartier (id)');
        $this->addSql('ALTER TABLE demandecretificat ADD CONSTRAINT FK_A12BC335C283956F FOREIGN KEY (delegue_id) REFERENCES personne (id)');
        $this->addSql('CREATE INDEX IDX_A12BC3358254716F ON demandecretificat (habitant_id)');
        $this->addSql('CREATE INDEX IDX_A12BC335DF1E57AB ON demandecretificat (quartier_id)');
        $this->addSql('CREATE INDEX IDX_A12BC335C283956F ON demandecretificat (delegue_id)');
        $this->addSql('ALTER TABLE demandeinscription ADD habitant_id INT NOT NULL, ADD quartier_id INT NOT NULL, ADD delegue_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE demandeinscription ADD CONSTRAINT FK_CB170C0F8254716F FOREIGN KEY (habitant_id) REFERENCES personne (id)');
        $this->addSql('ALTER TABLE demandeinscription ADD CONSTRAINT FK_CB170C0FDF1E57AB FOREIGN KEY (quartier_id) REFERENCES quartier (id)');
        $this->addSql('ALTER TABLE demandeinscription ADD CONSTRAINT FK_CB170C0FC283956F FOREIGN KEY (delegue_id) REFERENCES personne (id)');
        $this->addSql('CREATE INDEX IDX_CB170C0F8254716F ON demandeinscription (habitant_id)');
        $this->addSql('CREATE INDEX IDX_CB170C0FDF1E57AB ON demandeinscription (quartier_id)');
        $this->addSql('CREATE INDEX IDX_CB170C0FC283956F ON demandeinscription (delegue_id)');
        $this->addSql('ALTER TABLE departement ADD region_id INT NOT NULL');
        $this->addSql('ALTER TABLE departement ADD CONSTRAINT FK_C1765B6398260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('CREATE INDEX IDX_C1765B6398260155 ON departement (region_id)');
        $this->addSql('ALTER TABLE personne ADD deleguenommeur_id INT DEFAULT NULL, DROP fonction');
        $this->addSql('ALTER TABLE personne ADD CONSTRAINT FK_FCEC9EFB703ABAC FOREIGN KEY (deleguenommeur_id) REFERENCES personne (id)');
        $this->addSql('CREATE INDEX IDX_FCEC9EFB703ABAC ON personne (deleguenommeur_id)');
        $this->addSql('ALTER TABLE quartier ADD commune_id INT NOT NULL');
        $this->addSql('ALTER TABLE quartier ADD CONSTRAINT FK_FEE8962D131A4F72 FOREIGN KEY (commune_id) REFERENCES commune (id)');
        $this->addSql('CREATE INDEX IDX_FEE8962D131A4F72 ON quartier (commune_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE personne_quartier DROP FOREIGN KEY FK_3D3A9768A21BD112');
        $this->addSql('ALTER TABLE personne_quartier DROP FOREIGN KEY FK_3D3A9768DF1E57AB');
        $this->addSql('ALTER TABLE personne_commune DROP FOREIGN KEY FK_3C6D3513A21BD112');
        $this->addSql('ALTER TABLE personne_commune DROP FOREIGN KEY FK_3C6D3513131A4F72');
        $this->addSql('DROP TABLE personne_quartier');
        $this->addSql('DROP TABLE personne_commune');
        $this->addSql('ALTER TABLE commune DROP FOREIGN KEY FK_E2E2D1EECCF9E01E');
        $this->addSql('DROP INDEX IDX_E2E2D1EECCF9E01E ON commune');
        $this->addSql('ALTER TABLE commune DROP departement_id');
        $this->addSql('ALTER TABLE demandecretificat DROP FOREIGN KEY FK_A12BC3358254716F');
        $this->addSql('ALTER TABLE demandecretificat DROP FOREIGN KEY FK_A12BC335DF1E57AB');
        $this->addSql('ALTER TABLE demandecretificat DROP FOREIGN KEY FK_A12BC335C283956F');
        $this->addSql('DROP INDEX IDX_A12BC3358254716F ON demandecretificat');
        $this->addSql('DROP INDEX IDX_A12BC335DF1E57AB ON demandecretificat');
        $this->addSql('DROP INDEX IDX_A12BC335C283956F ON demandecretificat');
        $this->addSql('ALTER TABLE demandecretificat DROP habitant_id, DROP quartier_id, DROP delegue_id');
        $this->addSql('ALTER TABLE demandeinscription DROP FOREIGN KEY FK_CB170C0F8254716F');
        $this->addSql('ALTER TABLE demandeinscription DROP FOREIGN KEY FK_CB170C0FDF1E57AB');
        $this->addSql('ALTER TABLE demandeinscription DROP FOREIGN KEY FK_CB170C0FC283956F');
        $this->addSql('DROP INDEX IDX_CB170C0F8254716F ON demandeinscription');
        $this->addSql('DROP INDEX IDX_CB170C0FDF1E57AB ON demandeinscription');
        $this->addSql('DROP INDEX IDX_CB170C0FC283956F ON demandeinscription');
        $this->addSql('ALTER TABLE demandeinscription DROP habitant_id, DROP quartier_id, DROP delegue_id');
        $this->addSql('ALTER TABLE departement DROP FOREIGN KEY FK_C1765B6398260155');
        $this->addSql('DROP INDEX IDX_C1765B6398260155 ON departement');
        $this->addSql('ALTER TABLE departement DROP region_id');
        $this->addSql('ALTER TABLE personne DROP FOREIGN KEY FK_FCEC9EFB703ABAC');
        $this->addSql('DROP INDEX IDX_FCEC9EFB703ABAC ON personne');
        $this->addSql('ALTER TABLE personne ADD fonction VARCHAR(100) NOT NULL, DROP deleguenommeur_id');
        $this->addSql('ALTER TABLE quartier DROP FOREIGN KEY FK_FEE8962D131A4F72');
        $this->addSql('DROP INDEX IDX_FEE8962D131A4F72 ON quartier');
        $this->addSql('ALTER TABLE quartier DROP commune_id');
    }
}
