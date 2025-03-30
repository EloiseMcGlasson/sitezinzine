<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250329194408 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE annonce (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(100) NOT NULL, organisateur VARCHAR(100) NOT NULL, ville VARCHAR(50) NOT NULL, departement VARCHAR(2) NOT NULL, adresse VARCHAR(50) NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, horaire VARCHAR(50) NOT NULL, prix VARCHAR(50) NOT NULL, presentation LONGTEXT DEFAULT NULL, contact VARCHAR(200) NOT NULL, type VARCHAR(50) NOT NULL, valid TINYINT(1) DEFAULT NULL, update_at DATETIME NOT NULL, thumbnail VARCHAR(255) DEFAULT NULL, soft_delete TINYINT(1) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE categories (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(35) NOT NULL, oldid INT DEFAULT NULL, editeur INT NOT NULL, duree INT NOT NULL, descriptif LONGTEXT NOT NULL, thumbnail VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE editeur (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, mail VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, update_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE emission (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(250) NOT NULL, keyword VARCHAR(250) NOT NULL, datepub DATETIME NOT NULL, ref VARCHAR(250) NOT NULL, duree INT NOT NULL, url VARCHAR(250) NOT NULL, descriptif LONGTEXT NOT NULL, thumbnail VARCHAR(255) DEFAULT NULL, thumbnail_mp3 VARCHAR(255) DEFAULT NULL, updatedat DATETIME DEFAULT NULL, categorie_id INT DEFAULT NULL, theme_id INT DEFAULT NULL, user_id INT DEFAULT NULL, editeur_id INT DEFAULT NULL, INDEX IDX_F0225CF4BCF5E72D (categorie_id), INDEX IDX_F0225CF459027487 (theme_id), INDEX IDX_F0225CF4A76ED395 (user_id), INDEX IDX_F0225CF43375BD21 (editeur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE emission_invite_old_animateur (emission_id INT NOT NULL, invite_old_animateur_id INT NOT NULL, INDEX IDX_15730A4E17E24D70 (emission_id), INDEX IDX_15730A4E7F571CED (invite_old_animateur_id), PRIMARY KEY(emission_id, invite_old_animateur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(100) NOT NULL, organisateur VARCHAR(100) DEFAULT NULL, ville VARCHAR(50) DEFAULT NULL, departement VARCHAR(2) DEFAULT NULL, adresse VARCHAR(50) DEFAULT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, horaire VARCHAR(50) DEFAULT NULL, prix VARCHAR(50) DEFAULT NULL, presentation LONGTEXT DEFAULT NULL, contact VARCHAR(200) DEFAULT NULL, type VARCHAR(50) DEFAULT NULL, valid TINYINT(1) DEFAULT NULL, update_at DATETIME NOT NULL, thumbnail VARCHAR(255) DEFAULT NULL, soft_delete TINYINT(1) DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_B26681EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE invite_old_animateur (id INT AUTO_INCREMENT NOT NULL, last_name VARCHAR(255) DEFAULT NULL, first_name VARCHAR(255) NOT NULL, phone_number VARCHAR(10) DEFAULT NULL, mail VARCHAR(255) NOT NULL, ancienanimateur TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE theme (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, thumbnail VARCHAR(255) DEFAULT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE emission ADD CONSTRAINT FK_F0225CF4BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE emission ADD CONSTRAINT FK_F0225CF459027487 FOREIGN KEY (theme_id) REFERENCES theme (id)');
        $this->addSql('ALTER TABLE emission ADD CONSTRAINT FK_F0225CF4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE emission ADD CONSTRAINT FK_F0225CF43375BD21 FOREIGN KEY (editeur_id) REFERENCES editeur (id)');
        $this->addSql('ALTER TABLE emission_invite_old_animateur ADD CONSTRAINT FK_15730A4E17E24D70 FOREIGN KEY (emission_id) REFERENCES emission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE emission_invite_old_animateur ADD CONSTRAINT FK_15730A4E7F571CED FOREIGN KEY (invite_old_animateur_id) REFERENCES invite_old_animateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE emission DROP FOREIGN KEY FK_F0225CF4BCF5E72D');
        $this->addSql('ALTER TABLE emission DROP FOREIGN KEY FK_F0225CF459027487');
        $this->addSql('ALTER TABLE emission DROP FOREIGN KEY FK_F0225CF4A76ED395');
        $this->addSql('ALTER TABLE emission DROP FOREIGN KEY FK_F0225CF43375BD21');
        $this->addSql('ALTER TABLE emission_invite_old_animateur DROP FOREIGN KEY FK_15730A4E17E24D70');
        $this->addSql('ALTER TABLE emission_invite_old_animateur DROP FOREIGN KEY FK_15730A4E7F571CED');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EA76ED395');
        $this->addSql('DROP TABLE annonce');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE editeur');
        $this->addSql('DROP TABLE emission');
        $this->addSql('DROP TABLE emission_invite_old_animateur');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE invite_old_animateur');
        $this->addSql('DROP TABLE theme');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
