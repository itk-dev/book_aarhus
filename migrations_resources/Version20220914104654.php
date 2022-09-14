<?php

declare(strict_types=1);

namespace DoctrineMigrationsResources;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220914104654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ExtBooking.OpenHours ALTER COLUMN resourceID INT');
        $this->addSql('ALTER TABLE ExtBooking.OpenHours ADD CONSTRAINT FK_E206D38FB79B5C79 FOREIGN KEY (resourceID) REFERENCES ExtBooking.AAKResources (ID)');
        $this->addSql('CREATE INDEX IDX_E206D38FB79B5C79 ON ExtBooking.OpenHours (resourceID)');
        $this->addSql('ALTER TABLE ExtBooking.holidayOpenHours ALTER COLUMN resourceID INT');
        $this->addSql('ALTER TABLE ExtBooking.holidayOpenHours ADD CONSTRAINT FK_CFAE645EB79B5C79 FOREIGN KEY (resourceID) REFERENCES ExtBooking.AAKResources (ID)');
        $this->addSql('CREATE INDEX IDX_CFAE645EB79B5C79 ON ExtBooking.holidayOpenHours (resourceID)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ExtBooking.OpenHours DROP CONSTRAINT FK_E206D38FB79B5C79');
        $this->addSql('DROP INDEX IDX_E206D38FB79B5C79 ON ExtBooking.OpenHours');
        $this->addSql('ALTER TABLE ExtBooking.OpenHours ALTER COLUMN resourceID INT NOT NULL');
        $this->addSql('ALTER TABLE ExtBooking.holidayOpenHours DROP CONSTRAINT FK_CFAE645EB79B5C79');
        $this->addSql('DROP INDEX IDX_CFAE645EB79B5C79 ON ExtBooking.holidayOpenHours');
        $this->addSql('ALTER TABLE ExtBooking.holidayOpenHours ALTER COLUMN resourceID INT NOT NULL');
    }
}
