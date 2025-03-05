<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250305213511 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE hosting ALTER refreshable_token TYPE VARCHAR(2000)');
        $this->addSql('ALTER TABLE hosting ALTER access_token TYPE VARCHAR(2000)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE hosting ALTER access_token TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE hosting ALTER refreshable_token TYPE VARCHAR(255)');
    }
}
