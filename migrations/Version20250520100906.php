<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250520100906 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F4165E9E89CB');
        $this->addSql('DROP INDEX IDX_BC91F4165E9E89CB ON resource');
        $this->addSql('ALTER TABLE resource ADD location_id INT DEFAULT NULL, DROP location');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F41664D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('CREATE INDEX IDX_BC91F41664D218E ON resource (location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resource DROP FOREIGN KEY FK_BC91F41664D218E');
        $this->addSql('DROP INDEX IDX_BC91F41664D218E ON resource');
        $this->addSql('ALTER TABLE resource ADD location VARCHAR(255) DEFAULT NULL, DROP location_id');
        $this->addSql('ALTER TABLE resource ADD CONSTRAINT FK_BC91F4165E9E89CB FOREIGN KEY (location) REFERENCES location (location)');
        $this->addSql('CREATE INDEX IDX_BC91F4165E9E89CB ON resource (location)');
    }
}
