<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240401152952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE product_update_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE product_update (id INT NOT NULL, supplier_id INT NOT NULL, status VARCHAR(25) DEFAULT \'created\' NOT NULL, data JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FA5974DC2ADD6D8C ON product_update (supplier_id)');
        $this->addSql('ALTER TABLE product_update ADD CONSTRAINT FK_FA5974DC2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE mercurial ADD status VARCHAR(25) DEFAULT \'created\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE product_update_id_seq CASCADE');
        $this->addSql('ALTER TABLE product_update DROP CONSTRAINT FK_FA5974DC2ADD6D8C');
        $this->addSql('DROP TABLE product_update');
        $this->addSql('ALTER TABLE mercurial DROP status');
    }
}
