<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260526000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add offerer field to troc_proposal';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE troc_proposal ADD offerer_id INT NULL');
        $this->addSql('UPDATE troc_proposal tp JOIN offer o ON tp.requested_item_id = o.id SET tp.offerer_id = o.owner_id');
        $this->addSql('ALTER TABLE troc_proposal MODIFY offerer_id INT NOT NULL');
        $this->addSql('ALTER TABLE troc_proposal ADD CONSTRAINT FK_339857CA6C1B2519 FOREIGN KEY (offerer_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_339857CA6C1B2519 ON troc_proposal (offerer_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE troc_proposal DROP FOREIGN KEY FK_339857CA6C1B2519');
        $this->addSql('DROP INDEX IDX_339857CA6C1B2519 ON troc_proposal');
        $this->addSql('ALTER TABLE troc_proposal DROP offerer_id');
    }
}
