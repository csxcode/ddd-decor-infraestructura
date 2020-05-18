/*
	CREATE DATABASE decor_infra CHARACTER SET utf8 COLLATE utf8_unicode_ci;
	use decor_infra;
*/

-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Table `store`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `store` (
  `store_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `enabled` TINYINT(1) NOT NULL,
  PRIMARY KEY (`store_id`));


-- -----------------------------------------------------
-- Table `branch`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `branch` (
  `branch_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `store_id` INT(10) UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `enabled` TINYINT(1) NOT NULL,
  PRIMARY KEY (`branch_id`),
  UNIQUE INDEX `unique_branch_name_and_store` (`name` ASC, `store_id` ASC),
  INDEX `fk_branch_store` (`store_id` ASC),
  CONSTRAINT `fk_branch_store`
    FOREIGN KEY (`store_id`)
    REFERENCES `store` (`store_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `checklist_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `checklist_status` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id`));


-- -----------------------------------------------------
-- Table `checklist`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `checklist` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `checklist_number` VARCHAR(15) NOT NULL,
  `checklist_status_id` INT(10) UNSIGNED NOT NULL,
  `branch_id` INT(10) UNSIGNED NOT NULL,
  `status_reason` VARCHAR(255) NULL DEFAULT NULL,
  `created_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `approved_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `rejected_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `rejected_at` TIMESTAMP NULL DEFAULT NULL,
  `created_by_user_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `checklist_number_unique` (`checklist_number` ASC),
  INDEX `fk_checklist_status_id` (`checklist_status_id` ASC),
  INDEX `fk_checklist_branch1_idx` (`branch_id` ASC),
  CONSTRAINT `fk_checklist_status_id`
    FOREIGN KEY (`checklist_status_id`)
    REFERENCES `checklist_status` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_checklist_branch1`
    FOREIGN KEY (`branch_id`)
    REFERENCES `branch` (`branch_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `checklist_item_type`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `checklist_item_type` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `parent_id` INT(10) NULL,
  `display_order` INT(10) NULL,
  `type_status` TINYINT(1) NULL,
  PRIMARY KEY (`id`));


-- -----------------------------------------------------
-- Table `checklist_item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `checklist_item` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `type` INT(10) UNSIGNED NOT NULL,
  `description` VARCHAR(1000) NULL,
  `display_order` INT(10) NULL,
  `item_status` TINYINT(1) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_checklist_item_type` (`type` ASC),
  CONSTRAINT `fk_checklist_item_type`
    FOREIGN KEY (`type`)
    REFERENCES `checklist_item_type` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `checklist_item_details`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `checklist_item_details` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `checklist_id` INT(10) UNSIGNED NOT NULL,
  `checklist_item_id` INT(10) UNSIGNED NOT NULL,
  `disagreement` TINYINT(1) NOT NULL,
  `disagreement_reason` VARCHAR(255) NULL DEFAULT NULL,
  `disagreement_generate_ticket` TINYINT(1) NULL DEFAULT NULL,
  `photo1_guid` VARCHAR(50) NULL DEFAULT NULL,
  `photo1_name` VARCHAR(150) NULL DEFAULT NULL,
  `photo2_guid` VARCHAR(50) NULL,
  `photo2_name` VARCHAR(150) NULL,
  `photo3_guid` VARCHAR(50) NULL,
  `photo3_name` VARCHAR(150) NULL,
  `video_guid` VARCHAR(50) NULL,
  `video_name` VARCHAR(150) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_checklist_id` (`checklist_id` ASC),
  INDEX `fk_checklist_item_id` (`checklist_item_id` ASC),
  CONSTRAINT `fk_checklist_id`
    FOREIGN KEY (`checklist_id`)
    REFERENCES `checklist` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_checklist_item_id`
    FOREIGN KEY (`checklist_item_id`)
    REFERENCES `checklist_item` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `module`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `module` (
  `module_id` TINYINT(3) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`module_id`));


-- -----------------------------------------------------
-- Table `log_changes`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `log_changes` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user` VARCHAR(100) NOT NULL,
  `module_id` TINYINT(3) UNSIGNED NOT NULL,
  `record_id` INT(11) NOT NULL,
  `reason` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `fk_log_changes_module` (`module_id` ASC),
  CONSTRAINT `fk_log_changes_module`
    FOREIGN KEY (`module_id`)
    REFERENCES `module` (`module_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `log_change_details`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `log_change_details` (
  `log_change_id` INT(10) UNSIGNED NOT NULL,
  `field_name` VARCHAR(50) NOT NULL,
  `old_value` LONGTEXT NULL DEFAULT NULL,
  `new_value` LONGTEXT NULL DEFAULT NULL,
  INDEX `fk_log_change_details_id` (`log_change_id` ASC),
  CONSTRAINT `fk_log_change_details_id`
    FOREIGN KEY (`log_change_id`)
    REFERENCES `log_changes` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
);


-- -----------------------------------------------------
-- Table `role`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `role` (
  `role_id` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `display_name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE INDEX `role_name_unique` (`name` ASC)
);


-- -----------------------------------------------------
-- Table `vendor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `vendor` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `contact_name` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` VARCHAR(100) NULL,
  `ruc` VARCHAR(11) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`));


-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `user_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NULL DEFAULT NULL,
  `role_id` SMALLINT(5) UNSIGNED NOT NULL,
  `enabled` TINYINT(1) NOT NULL,
  `multiple_sessions` TINYINT(1) NOT NULL DEFAULT '0',
  `remember_token` VARCHAR(100) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `phone` VARCHAR(20) NULL,
  `vendor_id` INT(10) UNSIGNED NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `user_username_unique` (`username` ASC),
  UNIQUE INDEX `user_email_unique` (`email` ASC),
  INDEX `fk_user_role` (`role_id` ASC),
  INDEX `fk_user_vendor1_idx` (`vendor_id` ASC),
  CONSTRAINT `fk_user_role`
    FOREIGN KEY (`role_id`)
    REFERENCES `role` (`role_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_vendor1`
    FOREIGN KEY (`vendor_id`)
    REFERENCES `vendor` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);


-- -----------------------------------------------------
-- Table `role_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `role_user` (
  `user_id` INT(10) UNSIGNED NOT NULL,
  `role_id` SMALLINT(5) UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  INDEX `role_user_role_id_foreign` (`role_id` ASC),
  CONSTRAINT `role_user_role_id_foreign`
    FOREIGN KEY (`role_id`)
    REFERENCES `role` (`role_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `role_user_user_id_foreign`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `session`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `session` (
  `session_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullname_user` VARCHAR(100) NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `ip_address` VARCHAR(100) NULL DEFAULT NULL,
  `user_agent` VARCHAR(255) NULL DEFAULT NULL,
  `access_type` TINYINT(4) NOT NULL,
  `token` VARCHAR(255) NULL DEFAULT NULL,
  `login` DATETIME NULL DEFAULT NULL,
  `last_activity` DATETIME NULL DEFAULT NULL,
  `logout` DATETIME NULL DEFAULT NULL,
  `status` TINYINT(4) NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  UNIQUE INDEX `sessions_token_unique` (`token` ASC),
  INDEX `sessions_token_index` (`token` ASC),
  INDEX `fk_session_user` (`user_id` ASC),
  CONSTRAINT `fk_session_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
);


-- -----------------------------------------------------
-- Table `ticket_type`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_type` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`)
);


-- -----------------------------------------------------
-- Table `ticket_type_sub`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_type_sub` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `ticket_type_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_ticket_type_id` (`ticket_type_id` ASC),
  CONSTRAINT `fk_ticket_type_id`
    FOREIGN KEY (`ticket_type_id`)
    REFERENCES `ticket_type` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
);


-- -----------------------------------------------------
-- Table `ticket_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_status` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `display_order` INT(10) NOT NULL,
  PRIMARY KEY (`id`)
);


-- -----------------------------------------------------
-- Table `priority`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `priority` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `display_order` INT(10) NOT NULL,
  PRIMARY KEY (`id`)
);


-- -----------------------------------------------------
-- Table `ticket`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_number` VARCHAR(15) NOT NULL,
  `status_id` INT(10) UNSIGNED NOT NULL,
  `type_id` INT(10) UNSIGNED NOT NULL,
  `branch_id` INT(10) UNSIGNED NOT NULL,
  `description` LONGTEXT NULL DEFAULT NULL,
  `status_reason` VARCHAR(255) NULL DEFAULT NULL,
  `created_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `delivery_date` DATETIME NULL DEFAULT NULL,
  `subtype_id` INT(10) UNSIGNED NOT NULL,
  `approved_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `rejected_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `rejected_at` TIMESTAMP NULL DEFAULT NULL,
  `priority_id` INT(10) UNSIGNED NOT NULL,
  `video_guid` VARCHAR(50) NULL,
  `video_name` VARCHAR(255) NULL,
  `location` VARCHAR(255) NULL,
  `reference_doc` VARCHAR(50) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `ticket_number_unique` (`ticket_number` ASC),
  INDEX `fk_ticket_status` (`status_id` ASC),
  INDEX `fk_ticket_type` (`type_id` ASC),
  INDEX `fk_ticket_branch` (`branch_id` ASC),
  INDEX `fk_subtype_id` (`subtype_id` ASC),
  INDEX `fk_ticket_priority1_idx` (`priority_id` ASC),
  CONSTRAINT `fk_subtype_id`
    FOREIGN KEY (`subtype_id`)
    REFERENCES `ticket_type_sub` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ticket_branch`
    FOREIGN KEY (`branch_id`)
    REFERENCES `branch` (`branch_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ticket_status`
    FOREIGN KEY (`status_id`)
    REFERENCES `ticket_status` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ticket_type`
    FOREIGN KEY (`type_id`)
    REFERENCES `ticket_type` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ticket_priority1`
    FOREIGN KEY (`priority_id`)
    REFERENCES `priority` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);


-- -----------------------------------------------------
-- Table `ticket_photo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_photo` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT(10) UNSIGNED NOT NULL,
  `guid` VARCHAR(50) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `order` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_ticket_photo_ticket` (`ticket_id` ASC),
  CONSTRAINT `fk_ticket_photo_ticket`
    FOREIGN KEY (`ticket_id`)
    REFERENCES `ticket` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
);


-- -----------------------------------------------------
-- Table `user_store_branch`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_store_branch` (
  `user_id` INT(10) UNSIGNED NOT NULL,
  `branch_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`, `branch_id`),
  INDEX `fk_usb_office` (`branch_id` ASC),
  CONSTRAINT `fk_usb_office`
    FOREIGN KEY (`branch_id`)
    REFERENCES `branch` (`branch_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usb_user`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`user_id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION
);


-- -----------------------------------------------------
-- Table `checklist_template`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `checklist_template` (
  `id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
);


-- -----------------------------------------------------
-- Table `checklist_template_item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `checklist_template_item` (
  `id` INT(10) UNSIGNED NOT NULL,
  `checklist_template_id` INT(10) UNSIGNED NOT NULL,
  `checklist_item_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_checklist_template_item_checklist_template1_idx` (`checklist_template_id` ASC),
  INDEX `fk_checklist_template_item_checklist_item1_idx` (`checklist_item_id` ASC),
  CONSTRAINT `fk_checklist_template_item_checklist_template1`
    FOREIGN KEY (`checklist_template_id`)
    REFERENCES `checklist_template` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_checklist_template_item_checklist_item1`
    FOREIGN KEY (`checklist_item_id`)
    REFERENCES `checklist_item` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);


-- -----------------------------------------------------
-- Table `ticket_comment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `ticket_comment` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` INT(10) UNSIGNED NOT NULL,
  `description` LONGTEXT NULL,
  `created_at` TIMESTAMP NULL,
  `created_by_user` VARCHAR(50) NULL,
  `updated_at` TIMESTAMP NULL,
  `updated_by_user` VARCHAR(50) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_ticket_comment_ticket1_idx` (`ticket_id` ASC),
  CONSTRAINT `fk_ticket_comment_ticket1`
    FOREIGN KEY (`ticket_id`)
    REFERENCES `ticket` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
);


-- -----------------------------------------------------
-- Table `branch_location`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `branch_location` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_branch_id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(50) NULL,
  `address` VARCHAR(100) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_branch_location_branch1_idx` (`branch_branch_id` ASC),
  CONSTRAINT `fk_branch_location_branch1`
    FOREIGN KEY (`branch_branch_id`)
    REFERENCES `branch` (`branch_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `major_account`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `major_account` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `work_order_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `work_order_status` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `display_order` INT(10) NOT NULL,
  PRIMARY KEY (`id`));


-- -----------------------------------------------------
-- Table `maintenance_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maintenance_status` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `display_order` INT(10) NOT NULL,
  PRIMARY KEY (`id`));


-- -----------------------------------------------------
-- Table `maintenance`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `maintenance` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status_id` INT(10) UNSIGNED NOT NULL,
  `branch_location_id` INT(10) UNSIGNED NOT NULL,
  `maintenance_number` VARCHAR(15) NULL,
  `maintenance_title` VARCHAR(50) NULL,
  `maintenance_date` DATETIME NULL,
  `description` VARCHAR(255) NULL,
  `reminder1` DATETIME NULL,
  `reminder2` DATETIME NULL,
  `created_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_maintenance_status1_idx` (`status_id` ASC),
  INDEX `fk_maintenance_branch_location1_idx` (`branch_location_id` ASC),
  CONSTRAINT `fk_maintenance_status1`
    FOREIGN KEY (`status_id`)
    REFERENCES `maintenance_status` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_maintenance_branch_location1`
    FOREIGN KEY (`branch_location_id`)
    REFERENCES `branch_location` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `work_order`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `work_order` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `wo_number` VARCHAR(15) NOT NULL,
  `required_days` INT(10) NULL,
  `work_specs` LONGTEXT NULL,
  `branch_location_id` INT(10) NULL,
  `major_account_id` INT(10) UNSIGNED NULL,
  `sap_description` VARCHAR(40) NULL,
  `video_guid` VARCHAR(50) NULL,
  `video_name` VARCHAR(255) NULL,
  `work_order_status_id` INT(10) UNSIGNED NOT NULL,
  `start_date` DATETIME NULL,
  `end_date` DATETIME NULL,
  `created_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `ticket_id` INT(10) UNSIGNED NULL, 
  `maintenance_id` INT(10) UNSIGNED NULL, 
  PRIMARY KEY (`id`),
  INDEX `fk_work_order_branch_location1_idx` (`branch_location_id` ASC),
  INDEX `fk_work_order_major_account1_idx` (`major_account_id` ASC),
  INDEX `fk_work_order_work_order_status1_idx` (`work_order_status_id` ASC),
  INDEX `fk_work_order_ticket1_idx` (`ticket_id` ASC),
  INDEX `fk_work_order_maintenance1_idx` (`maintenance_id` ASC),
  CONSTRAINT `fk_work_order_major_account1`
    FOREIGN KEY (`major_account_id`)
    REFERENCES `major_account` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_work_order_work_order_status1`
    FOREIGN KEY (`work_order_status_id`)
    REFERENCES `work_order_status` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_work_order_ticket1`
    FOREIGN KEY (`ticket_id`)
    REFERENCES `ticket` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_work_order_maintenance1`
    FOREIGN KEY (`maintenance_id`)
    REFERENCES `maintenance` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `cost_center`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `cost_center` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`));


-- -----------------------------------------------------
-- Table `work_order_cost_center`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `work_order_cost_center` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `work_order_id` INT(10) UNSIGNED NOT NULL,
  `cost_center_id` INT(10) UNSIGNED NOT NULL,
  `percent` TINYINT(2) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_work_order_cost_center_cost_center1_idx` (`cost_center_id` ASC),
  INDEX `fk_work_order_cost_center_work_order1_idx` (`work_order_id` ASC),
  CONSTRAINT `fk_work_order_cost_center_cost_center1`
    FOREIGN KEY (`cost_center_id`)
    REFERENCES `cost_center` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_work_order_cost_center_work_order1`
    FOREIGN KEY (`work_order_id`)
    REFERENCES `work_order` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `quote_status`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `quote_status` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `display_order` INT(10) NOT NULL,
  PRIMARY KEY (`id`));


-- -----------------------------------------------------
-- Table `work_order_quote`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `work_order_quote` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `work_order_id` INT(10) UNSIGNED NOT NULL,
  `vendor_id` INT(10) UNSIGNED NOT NULL,
  `quote_status_id` INT(10) UNSIGNED NOT NULL,
  `quote_file_guid` VARCHAR(50) NULL,
  `quote_file_name` VARCHAR(255) NULL,
  `amount` DECIMAL(13,2) NULL,
  `currency` TINYINT(1) NULL COMMENT '1 = Soles, 2 = Dolares',
  `time_days` INT(10) NULL,
  `time_hours` INT(10) NULL,
  `payment_type` TINYINT(1) NULL COMMENT '1 = Factura, 2 = RH',
  `work_terms` VARCHAR(250) NULL,
  `notes` VARCHAR(250) NULL,
  `photo1_guid` VARCHAR(50) NULL,
  `photo1_name` VARCHAR(255) NULL,
  `photo2_guid` VARCHAR(50) NULL,
  `photo2_name` VARCHAR(255) NULL,
  `photo3_guid` VARCHAR(50) NULL,
  `photo3_name` VARCHAR(255) NULL,
  `created_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  `approved_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `approved_at` TIMESTAMP NULL DEFAULT NULL,
  `rejected_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `rejected_at` TIMESTAMP NULL DEFAULT NULL,
  `notification` TINYINT(1) NULL DEFAULT 0 COMMENT '0 = No requiere notificación, 1 = Pendiente notificar, 2 = Notificación enviada',
  PRIMARY KEY (`id`),
  INDEX `fk_work_order_vendor_vendor1_idx` (`vendor_id` ASC),
  INDEX `fk_work_order_vendor_work_order1_idx` (`work_order_id` ASC),
  INDEX `fk_work_order_quote_quote_status1_idx` (`quote_status_id` ASC),
  CONSTRAINT `fk_work_order_vendor_vendor1`
    FOREIGN KEY (`vendor_id`)
    REFERENCES `vendor` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_work_order_vendor_work_order1`
    FOREIGN KEY (`work_order_id`)
    REFERENCES `work_order` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_work_order_quote_quote_status1`
    FOREIGN KEY (`quote_status_id`)
    REFERENCES `quote_status` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `work_order_contact`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `work_order_contact` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `work_order_id` INT(10) UNSIGNED NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_work_order_contact_work_order1_idx` (`work_order_id` ASC),
  INDEX `fk_work_order_contact_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_work_order_contact_work_order1`
    FOREIGN KEY (`work_order_id`)
    REFERENCES `work_order` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_work_order_contact_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `user` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `work_order_file`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `work_order_file` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `work_order_id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `guid` VARCHAR(50) NOT NULL,
  `order` INT(10) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_work_order_file_work_order1_idx` (`work_order_id` ASC),
  CONSTRAINT `fk_work_order_file_work_order1`
    FOREIGN KEY (`work_order_id`)
    REFERENCES `work_order` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `work_order_photo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `work_order_photo` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `work_order_id` INT(10) UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `guid` VARCHAR(50) NOT NULL,
  `order` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_work_order_photo_work_order1_idx` (`work_order_id` ASC),
  CONSTRAINT `fk_work_order_photo_work_order1`
    FOREIGN KEY (`work_order_id`)
    REFERENCES `work_order` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `work_order_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `work_order_history` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `work_order_id` INT(10) UNSIGNED NOT NULL,
  `work_order_status_id` INT(10) UNSIGNED NOT NULL,
  `start_date` DATETIME NULL,
  `end_date` DATETIME NULL,
  `work_report` LONGTEXT NULL,
  `photo1_guid` VARCHAR(50) NULL,
  `photo1_name` VARCHAR(255) NULL,
  `photo2_guid` VARCHAR(50) NULL,
  `photo2_name` VARCHAR(255) NULL,
  `photo3_guid` VARCHAR(50) NULL,
  `photo3_name` VARCHAR(255) NULL,
  `video_guid` VARCHAR(50) NULL,
  `video_name` VARCHAR(255) NULL,
  `approval_file_guid` VARCHAR(50) NULL,
  `approval_file_name` VARCHAR(255) NULL,
  `created_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by_user` VARCHAR(50) NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_work_order_history_work_order1_idx` (`work_order_id` ASC),
  INDEX `fk_work_order_history_work_order_status1_idx` (`work_order_status_id` ASC),
  CONSTRAINT `fk_work_order_history_work_order1`
    FOREIGN KEY (`work_order_id`)
    REFERENCES `work_order` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_work_order_history_work_order_status1`
    FOREIGN KEY (`work_order_status_id`)
    REFERENCES `work_order_status` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
