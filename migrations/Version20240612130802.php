<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240612130802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application_history DROP FOREIGN KEY FK_CC0475783E030ACD');
        $this->addSql('ALTER TABLE application_history CHANGE application_id application_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE application_history ADD CONSTRAINT FK_CC0475783E030ACD FOREIGN KEY (application_id) REFERENCES application (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application_history DROP FOREIGN KEY FK_CC0475783E030ACD');
        $this->addSql('ALTER TABLE application_history CHANGE application_id application_id INT NOT NULL');
        $this->addSql('ALTER TABLE application_history ADD CONSTRAINT FK_CC0475783E030ACD FOREIGN KEY (application_id) REFERENCES application (id)');
    }
}
