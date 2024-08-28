<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240821005857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add uuid column to files table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE files ADD uuid UUID NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE files DROP uuid');
    }
}
