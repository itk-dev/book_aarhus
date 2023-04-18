<?php

declare(strict_types=1);

namespace DoctrineMigrationsResources;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230418104739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD ResourceDisplayName NVARCHAR(128)');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD LocationDisplayName NVARCHAR(128)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN ResourceDisplayName');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN LocationDisplayName');
    }
}
