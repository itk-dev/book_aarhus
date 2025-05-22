<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521113644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE holiday_opening_hours DROP FOREIGN KEY FK_7F8827F889329D25');
        $this->addSql('ALTER TABLE holiday_opening_hours ADD CONSTRAINT FK_7F8827F889329D25 FOREIGN KEY (resource_id) REFERENCES resource (id)');
        $this->addSql('ALTER TABLE opening_hours DROP FOREIGN KEY FK_2640C10B89329D25');
        $this->addSql('ALTER TABLE opening_hours ADD CONSTRAINT FK_2640C10B89329D25 FOREIGN KEY (resource_id) REFERENCES resource (id)');
        $this->addSql('ALTER TABLE resource DROP display_name');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE opening_hours DROP FOREIGN KEY FK_2640C10B89329D25');
        $this->addSql('ALTER TABLE opening_hours ADD CONSTRAINT FK_2640C10B89329D25 FOREIGN KEY (resource_id) REFERENCES resource (source_id)');
        $this->addSql('ALTER TABLE holiday_opening_hours DROP FOREIGN KEY FK_7F8827F889329D25');
        $this->addSql('ALTER TABLE holiday_opening_hours ADD CONSTRAINT FK_7F8827F889329D25 FOREIGN KEY (resource_id) REFERENCES resource (source_id)');
        $this->addSql('ALTER TABLE resource ADD display_name VARCHAR(128) DEFAULT NULL');
    }
}
