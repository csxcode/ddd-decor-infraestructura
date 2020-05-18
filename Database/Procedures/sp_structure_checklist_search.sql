/*
	call sp_structure_checklist_search(
		null,
        null,
        null,
        null,
        null,
        null, 
        null
    );	
	
*/
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_structure_checklist_search`$$

create procedure sp_structure_checklist_search
(
	param_type_name varchar(100),	
    param_type_status tinyint(1),
    param_subtype_name varchar(100),	
    param_subtype_status tinyint(1),
    param_item_name varchar(100),	
    param_item_status tinyint(1),    
    param_export tinyint(1)
)
begin
	
	/*		
		====================== Notes ======================
		[type] column
			> 1 = type
            > 2 = sub_type
            > 3 = item					
    */
    
    
	/* Drop tmp tables */
    /* --------------------------------------*/
    drop temporary table if exists tmp_scs_types;    
    drop temporary table if exists tmp_scs_subtypes;
    drop temporary table if exists tmp_scs_items;
	
	/* Params for filters */	
    set @param_type_name = ltrim(rtrim(ifnull(param_type_name, '')));		
    set @param_type_status = ltrim(rtrim(ifnull(param_type_status, '')));	    
	set @param_subtype_name = ltrim(rtrim(ifnull(param_subtype_name, '')));		
    set @param_subtype_status = ltrim(rtrim(ifnull(param_subtype_status, '')));		
	set @param_item_name = ltrim(rtrim(ifnull(param_item_name, '')));		
    set @param_item_status = ltrim(rtrim(ifnull(param_item_status, '')));		
    set @param_export = IFNULL(param_export, 0);
    
	/* ------------------- Types ---------------------*/
    /* -----------------------------------------------*/	
    
	set @type_filter = ' 1 = 1 ';  
    
    /*--------------------------------------------------------*/
    if(@param_type_name <> '') then		
		set @type_filter = concat(@type_filter, ' and ', "typex.name like '%", @param_type_name, "%'");			
    end if;
        
	if(@param_type_status <> '') then		
		set @type_filter = concat(@type_filter, ' and ', 'typex.type_status =', @param_type_status);			
    end if;	        
    /*--------------------------------------------------------*/
    if(@param_subtype_name <> '') then		
		set @type_filter = concat(@type_filter, ' and ', "sub_type.name like '%", @param_subtype_name, "%'");			
    end if;        
    
	if(@param_subtype_status <> '') then		
		set @type_filter = concat(@type_filter, ' and ', 'sub_type.type_status =', @param_subtype_status);			
    end if;
    /*--------------------------------------------------------*/    	
    if(@param_item_name <> '') then		
		set @type_filter = concat(@type_filter, ' and ', "item.name like '%", @param_item_name, "%'");			
    end if;
    
	if(@param_item_status <> '') then		
		set @type_filter = concat(@type_filter, ' and ', 'item.item_status =', @param_item_status);			
    end if;		    
    /*--------------------------------------------------------*/
    
    set @query = '
		create temporary table tmp_scs_types		
		as
		select 
			typex.id,
			typex.name,    
			typex.type_status as status,    
			cast(typex.display_order as char) as `order`,
			1 as `type`,
			typex.display_order,
			typex.parent_id
		from 
			checklist_item_type as typex
            left join checklist_item_type as sub_type on typex.id = sub_type.parent_id and not sub_type.parent_id is null
            left join checklist_item as item on sub_type.id = item.type 
		where 
			typex.parent_id is null
			and not cast(typex.display_order as char) is null';
		
        set @group_by = '
			group by
				typex.id,
				typex.name,    
				typex.type_status,    
				typex.display_order,
				typex.display_order,
				typex.parent_id';  
       
	set @query = CONCAT(@query, ' and ', @type_filter, @group_by);		
    
	/* Execute query */
	PREPARE stmt FROM @query;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
			
     
	/* --------------- Sub Types ---------------------*/ 
    /* -----------------------------------------------*/
    
	set @subtype_filter = ' 1 = 1 ';  
    
    /*--------------------------------------------------------*/
    if(@param_type_name <> '') then		
		set @subtype_filter = concat(@subtype_filter, ' and ', "typex.name like '%", @param_type_name, "%'");			
    end if;
        
	if(@param_type_status <> '') then		
		set @subtype_filter = concat(@subtype_filter, ' and ', 'typex.status =', @param_type_status);			
    end if;	  
    /*--------------------------------------------------------*/
    if(@param_subtype_name <> '') then		
		set @subtype_filter = concat(@subtype_filter, ' and ', "sub_type.name like '%", @param_subtype_name, "%'");			
    end if;
    
	if(@param_subtype_status <> '') then		
		set @subtype_filter = concat(@subtype_filter, ' and ', 'sub_type.type_status =', @param_subtype_status);			
    end if;		
    /*--------------------------------------------------------*/
	if(@param_item_name <> '') then		
		set @subtype_filter = concat(@subtype_filter, ' and ', "item.name like '%", @param_item_name, "%'");			
    end if;
    
	if(@param_item_status <> '') then		
		set @subtype_filter = concat(@subtype_filter, ' and ', 'item.item_status =', @param_item_status);			
    end if;		
	/*--------------------------------------------------------*/
    
    set @query = "    
		create temporary table tmp_scs_subtypes        
		as
		select 
			sub_type.id,
			sub_type.name,    
			sub_type.type_status as status,    
			cast(concat(typex.display_order, '.', sub_type.display_order) as char) as `order`,
			2 as `type`,
			sub_type.display_order,
			sub_type.parent_id
		from 
			checklist_item_type as sub_type
			left join tmp_scs_types as typex on sub_type.parent_id = typex.id
            left join checklist_item as item on sub_type.id = item.type 
		where 
			not sub_type.parent_id is null
            and not cast(concat(typex.display_order, '.', sub_type.display_order) as char) is null";
            
	set @group_by = "
		group by 			
			sub_type.id,
			sub_type.name,    
			sub_type.type_status,
			cast(concat(typex.display_order, '.', sub_type.display_order) as char),
			sub_type.display_order,
			sub_type.parent_id";
            
	set @query = CONCAT(@query, ' and ', @subtype_filter, @group_by);	

	/* Execute query */
	PREPARE stmt FROM @query;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
		

	/* ---------- CheckList Item (Items) -------------*/
    /* -----------------------------------------------*/
        
    set @item_filter = ' 1 = 1 ';	 
      
	/*--------------------------------------------------------*/
    if(@param_type_name <> '') then		
		set @item_filter = concat(@item_filter, ' and ', "typex.name like '%", @param_type_name, "%'");			
    end if;
        
	if(@param_type_status <> '') then		
		set @item_filter = concat(@item_filter, ' and ', 'typex.status =', @param_type_status);			
    end if;	  
    /*--------------------------------------------------------*/
    if(@param_subtype_name <> '') then		
		set @item_filter = concat(@item_filter, ' and ', "sub_type.name like '%", @param_subtype_name, "%'");			
    end if;
    
	if(@param_subtype_status <> '') then		
		set @item_filter = concat(@item_filter, ' and ', 'sub_type.status =', @param_subtype_status);			
    end if;		
    /*--------------------------------------------------------*/      
    if(@param_item_name <> '') then		
		set @item_filter = concat(@item_filter, ' and ', "item.name like '%", @param_item_name, "%'");			
    end if;
    
	if(@param_item_status <> '') then		
		set @item_filter = concat(@item_filter, ' and ', 'item.item_status =', @param_item_status);			
    end if;	
    /*--------------------------------------------------------*/
        
    set @query = "        
		create temporary table tmp_scs_items        
		as
		select 
			item.id,
			item.name,
			item.item_status as status,
			cast(concat(typex.display_order, '.', sub_type.display_order, '.', item.display_order) as char) as `order`,
			3 as type,
			item.display_order,
			item.type as parent_id
		from 
			checklist_item as item
			left join tmp_scs_subtypes as sub_type on item.type = sub_type.id
			left join tmp_scs_types as typex on sub_type.parent_id = typex.id
		where 
			not cast(concat(typex.display_order, '.', sub_type.display_order, '.', item.display_order) as char) is null";
            
	set @group_by = "
		group by
			item.id,
			item.name,
			item.item_status,
			cast(concat(typex.display_order, '.', sub_type.display_order, '.', item.display_order) as char),			
			item.display_order,
            item.type";
    
    set @query = CONCAT(@query, ' and ', @item_filter, @group_by);	

	/* Execute query */
	PREPARE stmt FROM @query;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;


	/*--------------------------------------------------------*/
    /*						Return Data						  */
    /*--------------------------------------------------------*/
	if(@param_export) then
    
		/* Export */
		select 
			tmp_scs_types.name as type_name,
			(case tmp_scs_types.`status` when 1 then 'Activo' else 'Inactivo' end) as type_status,
			tmp_scs_subtypes.name as subtype_name,
			(case tmp_scs_subtypes.`status` when 1 then 'Activo' else 'Inactivo' end) as subtype_status,
			tmp_scs_items.name as item_name,
			(case tmp_scs_items.`status` when 1 then 'Activo' else 'Inactivo' end) as item_status		
		from tmp_scs_types
			left join tmp_scs_subtypes on tmp_scs_types.id = tmp_scs_subtypes.parent_id
			left join tmp_scs_items on tmp_scs_subtypes.id = tmp_scs_items.parent_id
		order by 
			tmp_scs_types.display_order,
			tmp_scs_subtypes.display_order,
			tmp_scs_items.display_order;
    
    else
    
		/* Search */        
		select * from tmp_scs_types
		union
		select * from tmp_scs_subtypes
		union
		select * from tmp_scs_items
		
		order by INET_ATON(SUBSTRING_INDEX(CONCAT( `order`,'.0.0.0'),'.',4));
    
    end if;
    
    /* Drop tmp tables */
    /* --------------------------------------*/
    drop temporary table if exists tmp_scs_types;    
    drop temporary table if exists tmp_scs_subtypes;
    drop temporary table if exists tmp_scs_items;
    
END$$

DELIMITER ; 

    