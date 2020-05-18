/*
	select fn_get_store_branch_ids_by_user(4) as returnx;
*/
drop function if exists fn_get_store_branch_ids_by_user;
DELIMITER //
create function fn_get_store_branch_ids_by_user(
	param_user_id int
) 
returns varchar(255)
begin

	declare v_return varchar(1000);        
    set v_return = (select GROUP_CONCAT(branch_id separator ',') from user_store_branch where user_id = param_user_id);    
    		
	return v_return;
end; //
delimiter ;	
