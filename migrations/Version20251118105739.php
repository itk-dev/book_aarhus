<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251118105739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F41664D218E');
        $this->addSql('DROP INDEX IDX_BC91F41664D218E ON resource');
        $this->addSql('ALTER TABLE resource CHANGE location_id location_data_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F416D9B0B298 FOREIGN KEY (location_data_id) REFERENCES location (id)');
        $this->addSql('CREATE INDEX IDX_BC91F416D9B0B298 ON resource (location_data_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F416D9B0B298');
        $this->addSql('DROP INDEX IDX_BC91F416D9B0B298 ON resource');
        $this->addSql('ALTER TABLE resource CHANGE location_data_id location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F41664D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('CREATE INDEX IDX_BC91F41664D218E ON resource (location_id)');
    }
}
