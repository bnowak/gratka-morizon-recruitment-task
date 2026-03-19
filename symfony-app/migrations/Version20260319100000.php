<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260319100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add phoenix_photo_id to photos table with partial unique index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE photos ADD phoenix_photo_id INT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_photo_phoenix ON photos (user_id, phoenix_photo_id) WHERE phoenix_photo_id IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_photo_phoenix');
        $this->addSql('ALTER TABLE photos DROP COLUMN phoenix_photo_id');
    }
}
