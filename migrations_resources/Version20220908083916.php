<?php

declare(strict_types=1);

namespace DoctrineMigrationsResources;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220908083916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE ExtBooking.BookingRight');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD HasHolidayOpen BIT');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD HasOpen BIT');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD HasWhiteList BIT');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD PermissionEmployee BIT');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD PermissionCitizen BIT');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD PermissionBusinessPartner BIT');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN LocationType');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN Type');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN BookingRights');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN Opening_Hours');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN Holiday_Opening_Hours');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN Whitelist');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ALTER COLUMN ResourceEmailText VARCHAR(MAX)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA db_accessadmin');
        $this->addSql('CREATE SCHEMA db_backupoperator');
        $this->addSql('CREATE SCHEMA db_datareader');
        $this->addSql('CREATE SCHEMA db_datawriter');
        $this->addSql('CREATE SCHEMA db_ddladmin');
        $this->addSql('CREATE SCHEMA db_denydatareader');
        $this->addSql('CREATE SCHEMA db_denydatawriter');
        $this->addSql('CREATE SCHEMA db_owner');
        $this->addSql('CREATE SCHEMA db_securityadmin');
        $this->addSql('CREATE SCHEMA dbo');
        $this->addSql('CREATE TABLE ExtBooking.BookingRight (ID INT IDENTITY NOT NULL, bookingrightName NVARCHAR(56) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL, UpdateTimeStamp DATETIME2(6) NOT NULL, PRIMARY KEY (ID))');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD LocationType VARCHAR(MAX) NOT NULL');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD Type VARCHAR(MAX) NOT NULL');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD BookingRights VARCHAR(MAX) NOT NULL');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD Opening_Hours VARCHAR(MAX)');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD Holiday_Opening_Hours VARCHAR(MAX)');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ADD Whitelist VARCHAR(MAX)');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN HasHolidayOpen');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN HasOpen');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN HasWhiteList');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN PermissionEmployee');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN PermissionCitizen');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources DROP COLUMN PermissionBusinessPartner');
        $this->addSql('ALTER TABLE ExtBooking.AAKResources ALTER COLUMN ResourceEmailText VARCHAR(MAX) NOT NULL');
    }
}
