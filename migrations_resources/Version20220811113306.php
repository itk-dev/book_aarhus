<?php

declare(strict_types=1);

namespace DoctrineMigrationsResources;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220811113306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA ExtBooking');
        $this->addSql('CREATE TABLE ExtBooking.AAKResources (ID INT IDENTITY NOT NULL, ResourceMail NVARCHAR(128) NOT NULL, ResourceName NVARCHAR(128) NOT NULL, ResourceImage VARCHAR(MAX), ResourceEmailText VARCHAR(MAX) NOT NULL, Location NVARCHAR(128) NOT NULL, LocationType VARCHAR(MAX) NOT NULL, GeoCordinates NVARCHAR(128), Capacity BIGINT, Type VARCHAR(MAX) NOT NULL, ResourceDescription VARCHAR(MAX), WheelChairAccessible BIT NOT NULL, VideoConferenceEquipment BIT NOT NULL, MonitorEquipment BIT NOT NULL, BookingRights VARCHAR(MAX) NOT NULL, AcceptanceFlow BIT NOT NULL, Opening_Hours VARCHAR(MAX), Holiday_Opening_Hours VARCHAR(MAX), Whitelist VARCHAR(MAX), Catering BIT NOT NULL, FormID VARCHAR(MAX), UpdateTimeStamp DATETIME2(6) NOT NULL, PRIMARY KEY (ID))');
        $this->addSql('CREATE TABLE ExtBooking.BookingRight (ID INT IDENTITY NOT NULL, bookingrightName NVARCHAR(56) NOT NULL, UpdateTimeStamp DATETIME2(6) NOT NULL, PRIMARY KEY (ID))');
        $this->addSql('CREATE TABLE ExtBooking.OpenHours (ID INT IDENTITY NOT NULL, resourceID INT NOT NULL, weekday INT NOT NULL, [open] TIME(0) NOT NULL, [close] TIME(0) NOT NULL, UpdateTimeStamp DATETIME2(6) NOT NULL, PRIMARY KEY (ID))');
        $this->addSql('CREATE TABLE ExtBooking.cvrWhiteList (ID INT IDENTITY NOT NULL, resourceID INT NOT NULL, cvr INT NOT NULL, UpdateTimeStamp DATETIME2(6) NOT NULL, PRIMARY KEY (ID))');
        $this->addSql('CREATE TABLE ExtBooking.holidayOpenHours (ID INT IDENTITY NOT NULL, resourceID INT NOT NULL, holidayopen TIME(0) NOT NULL, holidayclose TIME(0) NOT NULL, UpdateTimeStamp DATETIME2(6) NOT NULL, PRIMARY KEY (ID))');
        $this->addSql('CREATE TABLE ExtBooking.locationType (ID INT IDENTITY NOT NULL, resourceID INT NOT NULL, locationType NVARCHAR(512) NOT NULL, UpdateTimeStamp DATETIME2(6) NOT NULL, PRIMARY KEY (ID))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE ExtBooking.AAKResources');
        $this->addSql('DROP TABLE ExtBooking.BookingRight');
        $this->addSql('DROP TABLE ExtBooking.OpenHours');
        $this->addSql('DROP TABLE ExtBooking.cvrWhiteList');
        $this->addSql('DROP TABLE ExtBooking.holidayOpenHours');
        $this->addSql('DROP TABLE ExtBooking.locationType');
    }
}
