<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240819000713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert dropbox record in hosting table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO hosting (name, slug) VALUES ('Dropbox', 'dropbox');");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM hosting WHERE slug = 'dropbox'");
    }
}
