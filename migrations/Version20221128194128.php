<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221128194128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_twitch ADD banned_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_twitch ADD CONSTRAINT FK_17BE609D2CE9C1AD FOREIGN KEY (banned_user_id) REFERENCES banned_user (id)');
        $this->addSql('CREATE INDEX IDX_17BE609D2CE9C1AD ON user_twitch (banned_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_twitch DROP FOREIGN KEY FK_17BE609D2CE9C1AD');
        $this->addSql('DROP INDEX IDX_17BE609D2CE9C1AD ON user_twitch');
        $this->addSql('ALTER TABLE user_twitch DROP banned_user_id');
    }
}
