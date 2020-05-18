
/*	ROLE */
/*==================================================================================================*/
INSERT INTO role VALUES (1, 'admin', 'Administrator Sistema', null);
INSERT INTO role VALUES (4, 'gestor', 'Gestor de Infraestructura', null);
INSERT INTO role VALUES (5, 'sede', 'Responsable de Sede', null);
INSERT INTO role VALUES (6, 'prov', 'Proveedor', null);

/*	WORK_ORDER_STATUS */
/*==================================================================================================*/
INSERT INTO work_order_status VALUES (1, 'Cotizando', 1);
INSERT INTO work_order_status VALUES (2, 'Pendiente Iniciar', 2);
INSERT INTO work_order_status VALUES (3, 'Iniciado ', 3);
INSERT INTO work_order_status VALUES (4, 'Pausado', 4);
INSERT INTO work_order_status VALUES (5, 'Terminado', 5);
INSERT INTO work_order_status VALUES (6, 'Confirmado', 6);
INSERT INTO work_order_status VALUES (7, 'Cerrado', 7);
INSERT INTO work_order_status VALUES (8, 'Anulado', 8);
INSERT INTO work_order_status VALUES (9, 'Reapertura', 9);


/*	QUOTE_STATUS */
/*==================================================================================================*/
INSERT INTO quote_status VALUES (1, 'Pendiente', 1);
INSERT INTO quote_status VALUES (2, 'Cotizado', 2);
INSERT INTO quote_status VALUES (3, 'Aceptado', 3);
INSERT INTO quote_status VALUES (4, 'Denegado', 4);


/*	TICKET_STATUS */
/*==================================================================================================*/
INSERT INTO ticket_status VALUES (1, 'Nuevo', 1);
INSERT INTO ticket_status VALUES (2, 'Confirmado', 2);
INSERT INTO ticket_status VALUES (3, 'Completado', 3);
INSERT INTO ticket_status VALUES (4, 'Anulado', 4);
INSERT INTO ticket_status VALUES (6, 'Cotizando', 6);
INSERT INTO ticket_status VALUES (7, 'Ejecutando', 7);


/* USER */
/*==================================================================================================*/
INSERT INTO `user` (`user_id`, `username`, `first_name`, `last_name`, `password`, `role_id`, `enabled`, `multiple_sessions`, `remember_token`) VALUES (1, 'sysadmin', 'Developer', 'Team', '$1$CfNLAZ2v$H8wbvOd7M3UP./dwV7qVR.', 1, 1, 1, null);
INSERT INTO role_user VALUES (1, 1);


/* STORE */
/*==================================================================================================*/
INSERT INTO `store` (`store_id`, `name`, `enabled`) VALUES (17, 'Decorcenter', 1);


/* BRANCH */
/*==================================================================================================*/
INSERT INTO `branch` (`branch_id`, `name`, `store_id`, `enabled`) VALUES (1, 'Miraflores', 17, 1);
INSERT INTO `branch` (`branch_id`, `name`, `store_id`, `enabled`) VALUES (2, 'San Isidro', 17, 1);
INSERT INTO `branch` (`branch_id`, `name`, `store_id`, `enabled`) VALUES (3, 'Huaral', 17, 1);
INSERT INTO `branch` (`branch_id`, `name`, `store_id`, `enabled`) VALUES (4, 'Chancay', 17, 1);


/* BRANCH LOCATION */
/*==================================================================================================*/
INSERT INTO `branch_location` (`branch_branch_id`, `name`, `address`) VALUES (4, 'Tottus de Chancay', 'Av. Suarez #1235');
INSERT INTO `branch_location` (`branch_branch_id`, `name`, `address`) VALUES (4, 'Galeria Villa de Arnedo', 'Av. Lopez de Zuñiga Piso 2 #895');


/* MODULE */
/*==================================================================================================*/
INSERT INTO `module` (`module_id`, `name`) VALUES (1, 'Work Order');    	
INSERT INTO `module` (`module_id`, `name`) VALUES (2, 'Checklist');    	
INSERT INTO `module` (`module_id`, `name`) VALUES (3, 'Ticket');    	

/* MODULE */
/*==================================================================================================*/
INSERT INTO `maintenance_status` (`id`, `name`, `display_order`) VALUES (1, 'Pendiente Iniciar', 1);   
INSERT INTO `maintenance_status` (`id`, `name`, `display_order`) VALUES (2, 'Iniciado', 2);   
INSERT INTO `maintenance_status` (`id`, `name`, `display_order`) VALUES (3, 'Completado', 3);   


