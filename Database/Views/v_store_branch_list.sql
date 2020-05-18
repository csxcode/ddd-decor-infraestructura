/*
	select * from v_store_branch_list
*/
create view v_store_branch_list
as
select 
	store.store_id,
    store.name as store_name,
    branch.branch_id,
    branch.name as branch_name
from store
	inner join branch on store.store_id = branch.store_id
where 
	store.enabled = 1
order by 
	store.name
