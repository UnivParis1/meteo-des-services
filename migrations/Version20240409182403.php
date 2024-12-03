<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240409182403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE application (id INT AUTO_INCREMENT NOT NULL, fname VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, is_from_json TINYINT(1) NOT NULL, state VARCHAR(255) NOT NULL, message LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE application_history (id INT AUTO_INCREMENT NOT NULL, application_id INT NOT NULL, type VARCHAR(255) NOT NULL, state VARCHAR(255) NOT NULL, message LONGTEXT DEFAULT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', author VARCHAR(255) NOT NULL, INDEX IDX_CC0475783E030ACD (application_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE maintenance (id INT AUTO_INCREMENT NOT NULL, application_id INT NOT NULL, starting_date DATETIME NOT NULL, ending_date DATETIME NOT NULL, application_state VARCHAR(255) NOT NULL, INDEX IDX_2F84F8E93E030ACD (application_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE maintenance_history (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, starting_date DATETIME NOT NULL, ending_date DATETIME NOT NULL, application_state VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE application_history ADD CONSTRAINT FK_CC0475783E030ACD FOREIGN KEY (application_id) REFERENCES application (id)');
        $this->addSql('ALTER TABLE maintenance ADD CONSTRAINT FK_2F84F8E93E030ACD FOREIGN KEY (application_id) REFERENCES application (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application_history DROP FOREIGN KEY FK_CC0475783E030ACD');
        $this->addSql('ALTER TABLE maintenance DROP FOREIGN KEY FK_2F84F8E93E030ACD');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE application_history');
        $this->addSql('DROP TABLE maintenance');
        $this->addSql('DROP TABLE maintenance_history');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
