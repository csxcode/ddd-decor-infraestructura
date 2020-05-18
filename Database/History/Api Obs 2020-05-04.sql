alter table work_order_file
drop column `order`;

ALTER TABLE `user` ADD `contact_enabled` TINYINT(0) NOT NULL DEFAULT '0' AFTER `vendor_id`;

ALTER TABLE `vendor` ADD `vendor_status` TINYINT(1) NULL DEFAULT '1' COMMENT '0 = Inactivo, 1 = Activo' AFTER `email`;
