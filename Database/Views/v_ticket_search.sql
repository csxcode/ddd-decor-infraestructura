/*
	select * from v_ticket_search
*/
alter view v_ticket_search
as
select
	ticket.id,
	ticket.ticket_number,
    ticket.status_id,
    ticket_status.name as status_name,
    ticket.type_id,
    ticket_type.name as type_name,
    ticket_type_sub.id as subtype_id,
	ticket_type_sub.name as subtype_name,
    branch.branch_id,
    branch.name as branch_name,
    branch.store_id,
    store.name as store_name,  
    ticket.description,
    ticket.delivery_date,
    ticket.status_reason,
    priority.id as priority_id,
    priority.name as priority_name,
    checklist.id as checklist_id,
    ticket.created_at,
    ticket.created_by_user,
    ticket.updated_at,
    ticket.updated_by_user,
    ticket.approved_at,
    ticket.approved_by_user,
	ticket.rejected_at,
    ticket.rejected_by_user,
    substr(ticket.created_by_user, instr(ticket.created_by_user, "(") + 1, instr(ticket.created_by_user,")") - instr(ticket.created_by_user,"(") - 1) as ticket_created_by_username
from ticket
left join ticket_status on ticket.status_id = ticket_status.id
left join ticket_type on ticket.type_id = ticket_type.id
left join ticket_type_sub on ticket.subtype_id = ticket_type_sub.id
left join branch on ticket.branch_id = branch.branch_id
left join store on branch.store_id = store.store_id
left join priority on ticket.priority_id = priority.id
left join checklist on ticket.checklist_id = checklist.id




