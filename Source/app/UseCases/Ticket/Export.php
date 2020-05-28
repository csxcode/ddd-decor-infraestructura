<?php
namespace App\UseCases\Ticket;

use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\Views\TicketSearch;
use Illuminate\Support\Facades\DB;

class Export
{
    public function execute($filterParams, $sortByParams = null)
    {
        $searchAction = new Search();
        $columns = '*, TicketSearch.type_name as ticket_type_name';

        $columns = ltrim(rtrim($columns));
        $query = TicketSearch::select(DB::raw($columns));
        $query->leftJoin('TicketComponents', 'TicketSearch.id', DB::raw('TicketComponents.ticket_id and ifnull(TicketComponents.action_id, 0) <> 1'));

        $searchAction->setFilters($filterParams, $query);
        $query->whereRaw('ifnull(TicketComponents.action_id, 0) <> 1'); //<> Mantener componente
       
        // Set OrderBy
        $sortByDefault = 'TicketSearch.ticket_number';
        $direction = !StringHelper::IsNullOrEmptyString($sortByParams['direction']) ? $sortByParams['direction'] : 'desc';
        $sort = PaginateHelper::getEquivalenceSort($sortByParams['sort'], $searchAction->equilaventColumns(), $sortByDefault);                
        
        $query->orderBy($sort, $direction)
        ->orderBy('TicketComponents.type_id', 'asc')
        ->orderBy('TicketComponents.name', 'asc')
        ->orderBy('TicketComponents.action_name', 'asc');
        
        // Return Data    
        return $query->get();
    }

}
