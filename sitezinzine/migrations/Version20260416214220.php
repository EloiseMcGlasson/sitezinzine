<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416214220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emission ADD is_auto_generated TINYINT(1) DEFAULT 0 NOT NULL, ADD is_pending_completion TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('DROP INDEX uniq_programmation_rule_category_number ON programmation_rule');
        $this->addSql('ALTER TABLE programmation_rule RENAME INDEX idx_4ba758f412469de2 TO idx_programmation_rule_category');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emission DROP is_auto_generated, DROP is_pending_completion');
        $this->addSql('CREATE UNIQUE INDEX uniq_programmation_rule_category_number ON programmation_rule (category_id, rule_number)');
        $this->addSql('ALTER TABLE programmation_rule RENAME INDEX idx_programmation_rule_category TO IDX_4BA758F412469DE2');
    }
}
