<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250208191920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE activity_log_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE alert_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE family_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE notification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE settings_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE activity_log (id INT NOT NULL, user_id INT NOT NULL, activity_type VARCHAR(255) NOT NULL, metadata JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FD06F647A76ED395 ON activity_log (user_id)');
        $this->addSql('COMMENT ON COLUMN activity_log.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE alert (id INT NOT NULL, user_id INT NOT NULL, alert_type VARCHAR(255) NOT NULL, sent BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_17FD46C1A76ED395 ON alert (user_id)');
        $this->addSql('COMMENT ON COLUMN alert.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE family (id INT NOT NULL, name VARCHAR(255) NOT NULL, active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN family.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE notification (id INT NOT NULL, recipient_id INT NOT NULL, message TEXT NOT NULL, sent BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF5476CAE92F8F78 ON notification (recipient_id)');
        $this->addSql('COMMENT ON COLUMN notification.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE settings (id INT NOT NULL, family_id INT NOT NULL, inactivity_threshold SMALLINT DEFAULT 30 NOT NULL, do_not_disturb BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E545A0C5C35E566A ON settings (family_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, family_id INT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, user_type VARCHAR(255) NOT NULL, active BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE INDEX IDX_8D93D649C35E566A ON "user" (family_id)');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE activity_log ADD CONSTRAINT FK_FD06F647A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE alert ADD CONSTRAINT FK_17FD46C1A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAE92F8F78 FOREIGN KEY (recipient_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE settings ADD CONSTRAINT FK_E545A0C5C35E566A FOREIGN KEY (family_id) REFERENCES family (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649C35E566A FOREIGN KEY (family_id) REFERENCES family (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE activity_log_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE alert_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE family_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE notification_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE settings_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');
        $this->addSql('ALTER TABLE activity_log DROP CONSTRAINT FK_FD06F647A76ED395');
        $this->addSql('ALTER TABLE alert DROP CONSTRAINT FK_17FD46C1A76ED395');
        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CAE92F8F78');
        $this->addSql('ALTER TABLE settings DROP CONSTRAINT FK_E545A0C5C35E566A');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649C35E566A');
        $this->addSql('DROP TABLE activity_log');
        $this->addSql('DROP TABLE alert');
        $this->addSql('DROP TABLE family');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE settings');
        $this->addSql('DROP TABLE "user"');
    }
}
