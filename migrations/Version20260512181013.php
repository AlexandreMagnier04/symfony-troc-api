<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260512181013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE offer (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(50) NOT NULL, owner_id INT NOT NULL, INDEX IDX_29D6873E7E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE troc_proposal (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, requester_id INT NOT NULL, requested_item_id INT NOT NULL, offered_item_id INT NOT NULL, INDEX IDX_339857CAED442CF4 (requester_id), INDEX IDX_339857CAD0A34B1A (requested_item_id), INDEX IDX_339857CA3897C779 (offered_item_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE troc_proposal ADD CONSTRAINT FK_339857CAED442CF4 FOREIGN KEY (requester_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE troc_proposal ADD CONSTRAINT FK_339857CAD0A34B1A FOREIGN KEY (requested_item_id) REFERENCES offer (id)');
        $this->addSql('ALTER TABLE troc_proposal ADD CONSTRAINT FK_339857CA3897C779 FOREIGN KEY (offered_item_id) REFERENCES offer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE offer DROP FOREIGN KEY FK_29D6873E7E3C61F9');
        $this->addSql('ALTER TABLE troc_proposal DROP FOREIGN KEY FK_339857CAED442CF4');
        $this->addSql('ALTER TABLE troc_proposal DROP FOREIGN KEY FK_339857CAD0A34B1A');
        $this->addSql('ALTER TABLE troc_proposal DROP FOREIGN KEY FK_339857CA3897C779');
        $this->addSql('DROP TABLE offer');
        $this->addSql('DROP TABLE troc_proposal');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
