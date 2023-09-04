<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230904143023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE betrothed (id INT NOT NULL, betrothed_id INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_B9F43CCDE7927C74 (email), UNIQUE INDEX UNIQ_B9F43CCD36158F60 (betrothed_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE gift_registry (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE internal_user (id INT NOT NULL, email VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_61134782E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invitation (alias VARCHAR(255) NOT NULL, uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', rsvp VARCHAR(100) DEFAULT NULL, times_opened INT DEFAULT NULL, invitation_for INT DEFAULT NULL, has_plus_one TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_F11D61A2D17F50A6 (uuid), PRIMARY KEY(alias)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invitation_invitation_detail (invitation_alias VARCHAR(255) NOT NULL, invitation_detail_id INT NOT NULL, INDEX IDX_B25136FE8DA7FFB5 (invitation_alias), INDEX IDX_B25136FE564EF83A (invitation_detail_id), PRIMARY KEY(invitation_alias, invitation_detail_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invitation_detail (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, maximum_distribution INT DEFAULT NULL, event_date DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invitation_group (id INT AUTO_INCREMENT NOT NULL, invitation_alias VARCHAR(255) DEFAULT NULL, invitee_id INT NOT NULL, type INT NOT NULL, INDEX IDX_10BD0E48DA7FFB5 (invitation_alias), UNIQUE INDEX UNIQ_10BD0E47A512022 (invitee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invitee (id INT NOT NULL, internal_user_id INT NOT NULL, seat_placement_id INT DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(50) DEFAULT NULL, title VARCHAR(50) DEFAULT NULL, invitee_from VARCHAR(100) DEFAULT NULL, invitee_lang VARCHAR(100) DEFAULT NULL, INDEX IDX_F7AADF3DBF7692A3 (internal_user_id), INDEX IDX_F7AADF3DCF66DC38 (seat_placement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media (id INT AUTO_INCREMENT NOT NULL, invitation_detail_id INT DEFAULT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', media_type VARCHAR(255) DEFAULT NULL, file_name LONGTEXT DEFAULT NULL, file_type LONGTEXT DEFAULT NULL, position SMALLINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_6A2CA10C564EF83A (invitation_detail_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_user (media_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_4ED4099AEA9FDD75 (media_id), INDEX IDX_4ED4099AA76ED395 (user_id), PRIMARY KEY(media_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', description LONGTEXT DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_E04992AAD17F50A6 (uuid), UNIQUE INDEX UNIQ_E04992AA5E237E06 (name), UNIQUE INDEX UNIQ_E04992AA989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE permission_role (permission_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_6A711CAFED90CCA (permission_id), INDEX IDX_6A711CAD60322AC (role_id), PRIMARY KEY(permission_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', description LONGTEXT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_57698A6AD17F50A6 (uuid), UNIQUE INDEX UNIQ_57698A6A5E237E06 (name), UNIQUE INDEX UNIQ_57698A6A989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE seat_placement (id INT AUTO_INCREMENT NOT NULL, table_detail_id INT DEFAULT NULL, table_number INT DEFAULT NULL, UNIQUE INDEX UNIQ_4FA83B675E936C41 (table_detail_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE table_detail (id INT AUTO_INCREMENT NOT NULL, venue_id INT DEFAULT NULL, capacity INT NOT NULL, alias VARCHAR(255) DEFAULT NULL, number INT NOT NULL, `order` INT NOT NULL, INDEX IDX_70D47A5040A73EBA (venue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', firstname VARCHAR(100) NOT NULL, is_deleted TINYINT(1) DEFAULT 0, is_verified TINYINT(1) NOT NULL, last_logged_in_at DATETIME DEFAULT NULL, lastname VARCHAR(100) DEFAULT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(180) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649D17F50A6 (uuid), UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_role (user_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_2DE8C6A3A76ED395 (user_id), INDEX IDX_2DE8C6A3D60322AC (role_id), PRIMARY KEY(user_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE venue (id INT AUTO_INCREMENT NOT NULL, invitation_detail_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, address LONGTEXT NOT NULL, map_link LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_91911B0D564EF83A (invitation_detail_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE betrothed ADD CONSTRAINT FK_B9F43CCD36158F60 FOREIGN KEY (betrothed_id) REFERENCES betrothed (id)');
        $this->addSql('ALTER TABLE betrothed ADD CONSTRAINT FK_B9F43CCDBF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE internal_user ADD CONSTRAINT FK_61134782BF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invitation_invitation_detail ADD CONSTRAINT FK_B25136FE8DA7FFB5 FOREIGN KEY (invitation_alias) REFERENCES invitation (alias)');
        $this->addSql('ALTER TABLE invitation_invitation_detail ADD CONSTRAINT FK_B25136FE564EF83A FOREIGN KEY (invitation_detail_id) REFERENCES invitation_detail (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invitation_group ADD CONSTRAINT FK_10BD0E48DA7FFB5 FOREIGN KEY (invitation_alias) REFERENCES invitation (alias)');
        $this->addSql('ALTER TABLE invitation_group ADD CONSTRAINT FK_10BD0E47A512022 FOREIGN KEY (invitee_id) REFERENCES invitee (id)');
        $this->addSql('ALTER TABLE invitee ADD CONSTRAINT FK_F7AADF3DBF7692A3 FOREIGN KEY (internal_user_id) REFERENCES internal_user (id)');
        $this->addSql('ALTER TABLE invitee ADD CONSTRAINT FK_F7AADF3DCF66DC38 FOREIGN KEY (seat_placement_id) REFERENCES seat_placement (id)');
        $this->addSql('ALTER TABLE invitee ADD CONSTRAINT FK_F7AADF3DBF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C564EF83A FOREIGN KEY (invitation_detail_id) REFERENCES invitation_detail (id)');
        $this->addSql('ALTER TABLE media_user ADD CONSTRAINT FK_4ED4099AEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media_user ADD CONSTRAINT FK_4ED4099AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE permission_role ADD CONSTRAINT FK_6A711CAFED90CCA FOREIGN KEY (permission_id) REFERENCES permission (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE permission_role ADD CONSTRAINT FK_6A711CAD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE seat_placement ADD CONSTRAINT FK_4FA83B675E936C41 FOREIGN KEY (table_detail_id) REFERENCES table_detail (id)');
        $this->addSql('ALTER TABLE table_detail ADD CONSTRAINT FK_70D47A5040A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_role ADD CONSTRAINT FK_2DE8C6A3D60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE venue ADD CONSTRAINT FK_91911B0D564EF83A FOREIGN KEY (invitation_detail_id) REFERENCES invitation_detail (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE betrothed DROP FOREIGN KEY FK_B9F43CCD36158F60');
        $this->addSql('ALTER TABLE betrothed DROP FOREIGN KEY FK_B9F43CCDBF396750');
        $this->addSql('ALTER TABLE internal_user DROP FOREIGN KEY FK_61134782BF396750');
        $this->addSql('ALTER TABLE invitation_invitation_detail DROP FOREIGN KEY FK_B25136FE8DA7FFB5');
        $this->addSql('ALTER TABLE invitation_invitation_detail DROP FOREIGN KEY FK_B25136FE564EF83A');
        $this->addSql('ALTER TABLE invitation_group DROP FOREIGN KEY FK_10BD0E48DA7FFB5');
        $this->addSql('ALTER TABLE invitation_group DROP FOREIGN KEY FK_10BD0E47A512022');
        $this->addSql('ALTER TABLE invitee DROP FOREIGN KEY FK_F7AADF3DBF7692A3');
        $this->addSql('ALTER TABLE invitee DROP FOREIGN KEY FK_F7AADF3DCF66DC38');
        $this->addSql('ALTER TABLE invitee DROP FOREIGN KEY FK_F7AADF3DBF396750');
        $this->addSql('ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10C564EF83A');
        $this->addSql('ALTER TABLE media_user DROP FOREIGN KEY FK_4ED4099AEA9FDD75');
        $this->addSql('ALTER TABLE media_user DROP FOREIGN KEY FK_4ED4099AA76ED395');
        $this->addSql('ALTER TABLE permission_role DROP FOREIGN KEY FK_6A711CAFED90CCA');
        $this->addSql('ALTER TABLE permission_role DROP FOREIGN KEY FK_6A711CAD60322AC');
        $this->addSql('ALTER TABLE seat_placement DROP FOREIGN KEY FK_4FA83B675E936C41');
        $this->addSql('ALTER TABLE table_detail DROP FOREIGN KEY FK_70D47A5040A73EBA');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3A76ED395');
        $this->addSql('ALTER TABLE user_role DROP FOREIGN KEY FK_2DE8C6A3D60322AC');
        $this->addSql('ALTER TABLE venue DROP FOREIGN KEY FK_91911B0D564EF83A');
        $this->addSql('DROP TABLE betrothed');
        $this->addSql('DROP TABLE gift_registry');
        $this->addSql('DROP TABLE internal_user');
        $this->addSql('DROP TABLE invitation');
        $this->addSql('DROP TABLE invitation_invitation_detail');
        $this->addSql('DROP TABLE invitation_detail');
        $this->addSql('DROP TABLE invitation_group');
        $this->addSql('DROP TABLE invitee');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE media_user');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE permission_role');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE seat_placement');
        $this->addSql('DROP TABLE table_detail');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_role');
        $this->addSql('DROP TABLE venue');
    }
}
