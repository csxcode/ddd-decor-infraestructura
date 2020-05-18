/*
	select * from v_checklist_search
*/
alter view v_checklist_search
as
select 
	checklist.id,
	checklist.checklist_number,
   checklist.checklist_status_id as status,
   checklist_status.name as status_name,	
	(
		case when (select count(*) from checklist_item_details where checklist_item_details.checklist_id = checklist.id and disagreement = 1) > 0
        then 
			1 
        else 
			0
		end 
	) as disagreement,   
    (
		case when (select count(*) from checklist_item_details where checklist_item_details.checklist_id = checklist.id and disagreement = 1) > 0
        then 
			0 
        else 
			1
		end 
	) as confirmed,   
	store.store_id,
	store.name as store_name,    
	branch.branch_id,
	branch.name as branch_name,
	checklist.status_reason,
	checklist.created_at,  
	checklist.created_by_user,  
	checklist.updated_at,  
	checklist.updated_by_user,  
	checklist.approved_at,  
	checklist.approved_by_user,  
	checklist.rejected_at,  
	checklist.rejected_by_user,
	checklist.total_points,
	checklist.edit_status
from checklist
left join branch on checklist.branch_id = branch.branch_id
left join store on branch.store_id = store.store_id
left join checklist_status on checklist.checklist_status_id = checklist_status.id

