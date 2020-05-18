/*
	select * from v_work_order_search
*/
CREATE OR REPLACE VIEW  v_work_order_search
as
select
	work_order.id,
	work_order.wo_number,
	work_order.required_days,
	work_order.work_specs,
    
	major_account.id as major_account_id,
    major_account.code as major_account_code,
    major_account.name as major_account_name,
    
	work_order.sap_description,
	work_order.video_guid,
	work_order.video_name,
	work_order.start_date,
	work_order.end_date,
	work_order.created_by_user,
	work_order.created_at,
	work_order.updated_by_user,
	work_order.updated_at,	
	work_order.maintenance_id,

	work_order_status.id as work_order_status_id,
	work_order_status.name as work_order_status_name,

	branch_location.id as branch_location_id,
	branch_location.name as branch_location_name,
    branch_location.address as branch_location_address,

	branch.branch_id,
	branch.name as branch_name,
    
    ticket.id as ticket_id,
    ticket.ticket_number
from work_order
left join work_order_status on work_order.work_order_status_id = work_order_status.id
left join branch_location on work_order.branch_location_id = branch_location.id
left join branch on branch_location.branch_branch_id = branch.branch_id
left join ticket on work_order.ticket_id = ticket.id
left join major_account on work_order.major_account_id = major_account.id




