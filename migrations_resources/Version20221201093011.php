<?php

declare(strict_types=1);

namespace DoctrineMigrationsResources;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221201093011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD DisplayName NVARCHAR(128)');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD City NVARCHAR(128)');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD StreetName NVARCHAR(128)');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD PostalCode INT');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD RessourceCategory NVARCHAR(128)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN DisplayName');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN City');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN StreetName');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN PostalCode');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN RessourceCategory');
    }
}
