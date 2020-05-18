/*
	call sp_get_data_dashboard(138)	
*/
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_get_data_dashboard`$$

create procedure sp_get_data_dashboard
(
	param_user_id int	
)
begin
	
	declare ___usb_allowed VARCHAR(1000);       
    declare ___tickets_new int;
    declare ___tickets_in_process int;    
    declare ___checklists_new int;
    
    declare ___user_id int;    
    declare ___username varchar(50);   
    declare ___role_id int;
    declare ___role_admin int;
    declare ___role_visual int;
    declare ___role_user int;
    declare ___role_store_manager int;
    declare ___checklist_status_new int;
    declare ___ticket_status_new int;
    declare ___ticket_status_process int;
    
	set ___role_admin = 1;
    set ___role_visual = 2;
    set ___role_store_manager = 4;
    set ___role_user = 5;    
    set ___checklist_status_new = 1;
    set ___ticket_status_new = 1;
    set ___ticket_status_process = 2;
	
    select user_id, role_id, username    
	into ___user_id, ___role_id, ___username from user where user_id = param_user_id;	    
    
    if (not ___user_id is null) then
					
		set ___usb_allowed = REPLACE((select fn_get_store_branch_ids_by_user(param_user_id)), ' ', '');					
		
		/*----------------------------------------*/
		/* Get data for tickets */
		/*----------------------------------------*/  
        if(___role_id = ___role_admin || ___role_id = ___role_visual) then          
			set ___tickets_new = (select count(*) from ticket where status_id = ___ticket_status_new); 
			set ___tickets_in_process =  (select count(*) from ticket where status_id = ___ticket_status_process);             
            
		elseif (___role_id = ___role_store_manager) then        
			set ___tickets_new = (select count(*) from ticket where status_id = ___ticket_status_new and FIND_IN_SET(branch_id, ___usb_allowed)); 
			set ___tickets_in_process = (select count(*) from ticket where status_id = ___ticket_status_process and FIND_IN_SET(branch_id, ___usb_allowed));                
            
        else        
			set ___tickets_new = (select count(*) from v_ticket_search where status_id = ___ticket_status_new and FIND_IN_SET(branch_id, ___usb_allowed) and ticket_created_by_username = ___username); 
			set ___tickets_in_process = (select count(*) from v_ticket_search where status_id = ___ticket_status_process and FIND_IN_SET(branch_id, ___usb_allowed) and ticket_created_by_username = ___username); 			
            
        end if;
        		
		
		/*----------------------------------------*/
		/* Get data for checklist */
		/*----------------------------------------*/          
        if(___role_id = ___role_user || ___role_id = ___role_store_manager) then        
			set ___checklists_new = (select count(*) from checklist where FIND_IN_SET(branch_id, ___usb_allowed) and edit_status = 1 and checklist_status_id = ___checklist_status_new); 					         
		else         
			set ___checklists_new = (select count(*) from checklist where checklist_status_id = ___checklist_status_new); 					         
        end if;			
        
	end if;
	
    
	/*----------------------------------------*/
    /* Return Data */
    /*----------------------------------------*/ 
	select 		
		ifnull(___tickets_new, 0) as tickets_new,
		ifnull(___tickets_in_process, 0) as tickets_in_process, 
		ifnull(___checklists_new, 0) as checklists_new;
    
END$$

DELIMITER ;