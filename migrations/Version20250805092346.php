<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250805092346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cvr_whitelist (id INT AUTO_INCREMENT NOT NULL, resource_id INT DEFAULT NULL, cvr INT NOT NULL, source_id INT NOT NULL, UNIQUE INDEX UNIQ_B4646E3B953C1C61 (source_id), INDEX IDX_B4646E3B89329D25 (resource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE holiday_opening_hours (id INT AUTO_INCREMENT NOT NULL, resource_id INT DEFAULT NULL, holiday_open DATETIME NOT NULL, holiday_close DATETIME NOT NULL, source_id INT NOT NULL, UNIQUE INDEX UNIQ_7F8827F8953C1C61 (source_id), INDEX IDX_7F8827F889329D25 (resource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, location VARCHAR(255) NOT NULL, display_name VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, postal_code VARCHAR(255) NOT NULL, geo_coordinates VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_5E9E89CB5E9E89CB (location), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE opening_hours (id INT AUTO_INCREMENT NOT NULL, resource_id INT DEFAULT NULL, weekday INT NOT NULL, open DATETIME NOT NULL, close DATETIME NOT NULL, source_id INT NOT NULL, UNIQUE INDEX UNIQ_2640C10B953C1C61 (source_id), INDEX IDX_2640C10B89329D25 (resource_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE resource (id INT AUTO_INCREMENT NOT NULL, location_id INT DEFAULT NULL, resource_mail VARCHAR(128) NOT NULL, resource_name VARCHAR(128) NOT NULL, resource_image LONGTEXT DEFAULT NULL, resource_email_text LONGTEXT DEFAULT NULL, geo_coordinates VARCHAR(128) DEFAULT NULL, capacity BIGINT DEFAULT NULL, resource_description LONGTEXT DEFAULT NULL, wheelchair_accessible TINYINT(1) NOT NULL, video_conference_equipment TINYINT(1) NOT NULL, monitor_equipment TINYINT(1) NOT NULL, acceptance_flow TINYINT(1) NOT NULL, catering TINYINT(1) NOT NULL, form_id LONGTEXT DEFAULT NULL, has_holiday_open TINYINT(1) DEFAULT NULL, has_open TINYINT(1) DEFAULT NULL, has_whitelist TINYINT(1) DEFAULT NULL, permission_employee TINYINT(1) DEFAULT NULL, permission_citizen TINYINT(1) DEFAULT NULL, permission_business_partner TINYINT(1) DEFAULT NULL, city VARCHAR(128) DEFAULT NULL, street_name VARCHAR(128) DEFAULT NULL, postal_code INT DEFAULT NULL, resource_category VARCHAR(128) DEFAULT NULL, resource_display_name VARCHAR(128) DEFAULT NULL, location_display_name VARCHAR(128) DEFAULT NULL, accept_conflict TINYINT(1) DEFAULT NULL, include_in_ui TINYINT(1) DEFAULT NULL, source_id INT NOT NULL, UNIQUE INDEX UNIQ_BC91F416953C1C61 (source_id), INDEX IDX_BC91F41664D218E (location_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cvr_whitelist ADD CONSTRAINT FK_B4646E3B89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id)');
        $this->addSql('ALTER TABLE holiday_opening_hours ADD CONSTRAINT FK_7F8827F889329D25 FOREIGN KEY (resource_id) REFERENCES resource (id)');
        $this->addSql('ALTER TABLE opening_hours ADD CONSTRAINT FK_2640C10B89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id)');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F41664D218E FOREIGN KEY (location_id) REFERENCES location (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cvr_whitelist DROP FOREIGN KEY FK_B4646E3B89329D25');
        $this->addSql('ALTER TABLE holiday_opening_hours DROP FOREIGN KEY FK_7F8827F889329D25');
        $this->addSql('ALTER TABLE opening_hours DROP FOREIGN KEY FK_2640C10B89329D25');
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F41664D218E');
        $this->addSql('DROP TABLE cvr_whitelist');
        $this->addSql('DROP TABLE holiday_opening_hours');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE opening_hours');
        $this->addSql('DROP TABLE resource');
    }
}
