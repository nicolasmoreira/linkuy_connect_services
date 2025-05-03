<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250503201636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE activity_log ALTER user_id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ALTER user_id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notification ALTER family_id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE settings ALTER family_id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER family_id TYPE INT
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE public.settings ALTER family_id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE public."user" ALTER family_id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE public.activity_log ALTER user_id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE public.notification ALTER user_id TYPE INT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE public.notification ALTER family_id TYPE INT
        SQL);
    }
}
