/*
	select fn_get_username_from_inside_parentheses('Carlos Silva (developer)') as returnx;
*/
drop function if exists fn_get_username_from_inside_parentheses;
DELIMITER //
create function fn_get_username_from_inside_parentheses(
	param_value varchar(100)
) 
returns varchar(50)
begin

	declare v_return varchar(50);
    declare v_start_parenthesis int;
    declare v_end_parenthesis int;
    declare v_len_to_extract int;
    declare v_len int;
        
	set v_return = null;
    
    set param_value = lower(param_value);
    set v_start_parenthesis = (select position('(' in param_value));
    set v_end_parenthesis = (select position(')' in param_value));
    set v_len_to_extract = (v_end_parenthesis - v_start_parenthesis);
    
    if(v_start_parenthesis <> 0 && v_end_parenthesis <> 0) then
		if(v_end_parenthesis > v_start_parenthesis) then
			set v_return = substring(param_value, (v_start_parenthesis + 1), (v_len_to_extract -1));
        end if;
    end if;
    		
	return v_return;
end; //
delimiter ;	
