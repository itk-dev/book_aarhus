<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430082757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cvr_whitelist (id INT AUTO_INCREMENT NOT NULL, resource_id INT NOT NULL, cvr INT NOT NULL, update_timestamp DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE holiday_open_hours (id INT AUTO_INCREMENT NOT NULL, resource_id INT DEFAULT NULL, holiday_open DATETIME NOT NULL, holiday_close DATETIME NOT NULL, update_timestamp DATETIME NOT NULL, INDEX IDX_BE55A59A89329D25 (resource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE open_hours (id INT AUTO_INCREMENT NOT NULL, resource_id INT DEFAULT NULL, weekday INT NOT NULL, open_time DATETIME NOT NULL, close_time DATETIME NOT NULL, update_timestamp DATETIME NOT NULL, INDEX IDX_C1A79D8B89329D25 (resource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource (id INT AUTO_INCREMENT NOT NULL, resource_mail VARCHAR(128) NOT NULL, resource_name VARCHAR(128) NOT NULL, resource_image TINYTEXT DEFAULT NULL, resource_email_text TINYTEXT DEFAULT NULL, location VARCHAR(128) NOT NULL, geo_coordinates VARCHAR(128) DEFAULT NULL, capacity BIGINT DEFAULT NULL, resource_description TINYTEXT DEFAULT NULL, wheelchair_accessible TINYINT(1) NOT NULL, video_conference_equipment TINYINT(1) NOT NULL, monitor_equipment TINYINT(1) NOT NULL, acceptance_flow TINYINT(1) NOT NULL, catering TINYINT(1) NOT NULL, form_id TINYTEXT DEFAULT NULL, has_holiday_open TINYINT(1) DEFAULT NULL, has_open TINYINT(1) DEFAULT NULL, has_whitelist TINYINT(1) DEFAULT NULL, permission_employee TINYINT(1) DEFAULT NULL, permission_citizen TINYINT(1) DEFAULT NULL, permission_business_partner TINYINT(1) DEFAULT NULL, update_timestamp DATETIME NOT NULL, display_name VARCHAR(128) DEFAULT NULL, city VARCHAR(128) DEFAULT NULL, StreetName VARCHAR(128) DEFAULT NULL, postal_code INT DEFAULT NULL, resource_category VARCHAR(128) DEFAULT NULL, resource_display_name VARCHAR(128) DEFAULT NULL, location_display_name VARCHAR(128) DEFAULT NULL, accept_conflict TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE holiday_open_hours ADD CONSTRAINT FK_BE55A59A89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id)');
        $this->addSql('ALTER TABLE open_hours ADD CONSTRAINT FK_C1A79D8B89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE holiday_open_hours DROP FOREIGN KEY FK_BE55A59A89329D25');
        $this->addSql('ALTER TABLE open_hours DROP FOREIGN KEY FK_C1A79D8B89329D25');
        $this->addSql('DROP TABLE cvr_whitelist');
        $this->addSql('DROP TABLE holiday_open_hours');
        $this->addSql('DROP TABLE open_hours');
        $this->addSql('DROP TABLE resource');
    }
}
