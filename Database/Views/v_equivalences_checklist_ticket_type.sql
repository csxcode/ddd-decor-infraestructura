/*
	-- Listando que tickets voy a generar
	select * from v_equivalences_checklist_ticket_type
    where checklist_item_id in (2, 4, 9)
    	
*/
create view v_equivalences_checklist_ticket_type
as
	select 
		checklist_item_type.id as checklist_type_id,
		checklist_item_type.name as checklist_type_name,
        
        checklist_item.id as checklist_item_id,
        checklist_item.name as checklist_item_name,        
        
        ticket_type.id as ticket_type_id,
        ticket_type.name as ticket_type_name,		
        
        ticket_type_sub.id as ticket_type_sub_id,
        ticket_type_sub.name as ticket_type_sub_name
        
    from equivalency_checklist_ticket_type ectt
	inner join checklist_item on ectt.checklist_item_id = checklist_item.id
	inner join ticket_type_sub on ectt.ticket_type_sub_id = ticket_type_sub.id
    inner join checklist_item_type on checklist_item.type = checklist_item_type.id
    inner join ticket_type on ticket_type_sub.ticket_type_id = ticket_type.id
    
    order by checklist_item.id;