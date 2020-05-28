<?php

namespace App\UseCases\Ticket;

use App\Helpers\PaginateHelper;
use App\Helpers\StringHelper;
use App\Models\Views\TicketSearch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Search
{
    public function execute($filterParams, $sortByParams = null)
    {
        $query = TicketSearch::select(DB::raw($this->columns()));

        $this->setFilters($filterParams, $query);

        // Set Paginate
        $per_page = $filterParams['per_page'];
        PaginateHelper::SetPaginateDefaultValues($page, $per_page);

        // Set OrderBy
        $sortByDefault = 'ticket_number';
        $direction = !StringHelper::IsNullOrEmptyString($sortByParams['direction']) ? $sortByParams['direction'] : 'desc';
        $sort = PaginateHelper::getEquivalenceSort($sortByParams['sort'], $this->equilaventColumns(), $sortByDefault);
        $query->orderBy($sort, $direction);

        // Return Data
        return $query->paginate($per_page);
    }

    public function equilaventColumns()
    {
        return [
            [
                'name' => 'id',
                'equilavence' => 'id',
            ],
            [
                'name' => 'ticket_number',
                'equilavence' => 'ticket_number',
            ],
            [
                'name' => 'created_at',
                'equilavence' => 'created_at',
            ],
            [
                'name' => 'type_name',
                'equilavence' => 'type_name',
            ],
            [
                'name' => 'store_name',
                'equilavence' => 'store_name',
            ],
            [
                'name' => 'status_name',
                'equilavence' => 'status_name',
            ],
        ];
    }

    public function setFilters($filterParams, &$query)
    {
        if (isset($filterParams['ticket_number']) && !StringHelper::IsNullOrEmptyString($filterParams['ticket_number'])) {
            $query->where('ticket_number', $filterParams['ticket_number']);
        }

        if (isset($filterParams['store_id']) && !StringHelper::IsNullOrEmptyString($filterParams['store_id'])) {
            $query->where('store_id', $filterParams['store_id']);
        }

        if (isset($filterParams['branch_id']) && !StringHelper::IsNullOrEmptyString($filterParams['branch_id'])) {
            $query->where('branch_id', $filterParams['branch_id']);
        }

        if (isset($filterParams['status_id']) && !StringHelper::IsNullOrEmptyString($filterParams['status_id'])) {
            $query->where('status_id', $filterParams['status_id']);
        }

        if (isset($filterParams['type_id']) && !StringHelper::IsNullOrEmptyString($filterParams['type_id'])) {
            $query->where('type_id', $filterParams['type_id']);
        }

        if (isset($filterParams['date_from']) && !StringHelper::IsNullOrEmptyString($filterParams['date_from'])) {
            $date_from = Carbon::createFromTimestamp($filterParams['date_from'])->toDateString();
            $date_from = date('Y-m-d 00:00:00', strtotime($date_from));
            $query->where('created_at', '>=', $date_from);
        }

        if (isset($filterParams['date_to']) && !StringHelper::IsNullOrEmptyString($filterParams['date_to'])) {
            $date_to = Carbon::createFromTimestamp($filterParams['date_to'])->toDateString();
            $date_to = date('Y-m-d 23:59:59', strtotime($date_to));
            $query->where('created_at', '<=', $date_to);
        }

        $query->filterByRole($filterParams['user']);
    }

    private function columns()
    {
        return ltrim(rtrim('
            id,
            ticket_number,
            status_id,
            status_name,
            type_id,
            type_name,
            store_id,
            store_name,
            branch_id,
            branch_name,
            subtype_id,
            subtype_name,
            priority_id,
            priority_name,
            reference_doc,
            UNIX_TIMESTAMP(CONVERT_TZ(created_at, \'+00:00\', @@global.time_zone)) created_at
        '));
    }
}
