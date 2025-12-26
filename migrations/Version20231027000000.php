<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231027000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration for Xodimlar system: User, Department, AbsenceRequest, PurchaseRequest, Asset';
    }

    public function up(Schema $schema): void
    {
        // Department
        $this->addSql('CREATE TABLE department (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // User
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, department_id INT DEFAULT NULL, parent_id INT DEFAULT NULL, phone VARCHAR(20) NOT NULL, full_name VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, telegram_id BIGINT DEFAULT NULL, lang VARCHAR(2) DEFAULT \'ru\' NOT NULL, UNIQUE INDEX UNIQ_8D93D649444F97DD (phone), UNIQUE INDEX UNIQ_8D93D649CC0B610C (telegram_id), INDEX IDX_8D93D649AE80F5DF (department_id), INDEX IDX_8D93D649727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649727ACA70 FOREIGN KEY (parent_id) REFERENCES `user` (id)');

        // AbsenceRequest
        $this->addSql('CREATE TABLE absence_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, hr_manager_id INT DEFAULT NULL, type VARCHAR(20) NOT NULL, date DATE NOT NULL, time_start TIME DEFAULT NULL, time_end TIME DEFAULT NULL, reason LONGTEXT NOT NULL, status VARCHAR(20) DEFAULT \'pending\' NOT NULL, created_at DATETIME NOT NULL, calculated_hours NUMERIC(5, 2) DEFAULT NULL, INDEX IDX_user_absence (user_id), INDEX IDX_hr_absence (hr_manager_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE absence_request ADD CONSTRAINT FK_absence_user FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE absence_request ADD CONSTRAINT FK_absence_hr FOREIGN KEY (hr_manager_id) REFERENCES `user` (id)');

        // PurchaseRequest
        $this->addSql('CREATE TABLE purchase_request (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, rejected_by_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, category VARCHAR(100) NOT NULL, description LONGTEXT DEFAULT NULL, price NUMERIC(15, 2) DEFAULT NULL, link VARCHAR(500) DEFAULT NULL, photo_path VARCHAR(255) DEFAULT NULL, status VARCHAR(50) DEFAULT \'new\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE purchase_request ADD CONSTRAINT FK_purchase_user FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE purchase_request ADD CONSTRAINT FK_purchase_rejected_by FOREIGN KEY (rejected_by_id) REFERENCES `user` (id)');

        // Asset
        $this->addSql('CREATE TABLE asset (id INT AUTO_INCREMENT NOT NULL, purchase_request_id INT DEFAULT NULL, current_owner_id INT DEFAULT NULL, inventory_number VARCHAR(50) DEFAULT NULL, serial_number VARCHAR(100) DEFAULT NULL, qr_code VARCHAR(255) DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_asset_purchase (purchase_request_id), INDEX IDX_asset_owner (current_owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_asset_purchase FOREIGN KEY (purchase_request_id) REFERENCES purchase_request (id)');
        $this->addSql('ALTER TABLE asset ADD CONSTRAINT FK_asset_owner FOREIGN KEY (current_owner_id) REFERENCES `user` (id)');

        // AssetAssignment
        $this->addSql('CREATE TABLE asset_assignment (id INT AUTO_INCREMENT NOT NULL, asset_id INT NOT NULL, user_id INT NOT NULL, assigned_at DATETIME NOT NULL, returned_at DATETIME DEFAULT NULL, condition_notes LONGTEXT DEFAULT NULL, signed_pdf_path VARCHAR(255) DEFAULT NULL, INDEX IDX_assignment_asset (asset_id), INDEX IDX_assignment_user (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE asset_assignment ADD CONSTRAINT FK_assignment_asset FOREIGN KEY (asset_id) REFERENCES asset (id)');
        $this->addSql('ALTER TABLE asset_assignment ADD CONSTRAINT FK_assignment_user FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_assignment DROP FOREIGN KEY FK_assignment_asset');
        $this->addSql('ALTER TABLE asset_assignment DROP FOREIGN KEY FK_assignment_user');
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_asset_purchase');
        $this->addSql('ALTER TABLE asset DROP FOREIGN KEY FK_asset_owner');
        $this->addSql('ALTER TABLE purchase_request DROP FOREIGN KEY FK_purchase_user');
        $this->addSql('ALTER TABLE purchase_request DROP FOREIGN KEY FK_purchase_rejected_by');
        $this->addSql('ALTER TABLE absence_request DROP FOREIGN KEY FK_absence_user');
        $this->addSql('ALTER TABLE absence_request DROP FOREIGN KEY FK_absence_hr');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649AE80F5DF');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649727ACA70');

        $this->addSql('DROP TABLE asset_assignment');
        $this->addSql('DROP TABLE asset');
        $this->addSql('DROP TABLE purchase_request');
        $this->addSql('DROP TABLE absence_request');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE department');
    }
}
