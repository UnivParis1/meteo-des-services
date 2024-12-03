<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240612141016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE maintenance_history ADD maintenance_id INT DEFAULT NULL, ADD date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE maintenance_history ADD CONSTRAINT FK_5E4C35D9F6C202BC FOREIGN KEY (maintenance_id) REFERENCES maintenance (id)');
        $this->addSql('CREATE INDEX IDX_5E4C35D9F6C202BC ON maintenance_history (maintenance_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE maintenance_history DROP FOREIGN KEY FK_5E4C35D9F6C202BC');
        $this->addSql('DROP INDEX IDX_5E4C35D9F6C202BC ON maintenance_history');
        $this->addSql('ALTER TABLE maintenance_history DROP maintenance_id, DROP date');
    }
}
