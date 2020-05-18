<?php namespace App\Models\Checklist;


use App\Enums\AccessTypeEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\StringHelper;
use App\Models\Branch;
use App\Models\UserStoreBranch;
use App\Models\Views\CheckListSearch;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class Checklist extends Model
{
    protected $table = 'checklist';
    protected $guarded = ['checklist_id'];

    public function branch() {
        return $this ->hasOne(Branch::class, 'branch_id', 'branch_id');
    }

    public static function GenerateNumber(){
        $ini_counter = 1000;
        $max = Checklist::max('checklist_number');
        if(!$max)
            $max = $ini_counter;
        $max = ((int)$max + 1);
        return $max;
    }

    public static function GetImageURL($checklist_id, $photo_guid){
        $url = Config::get('app.web_checklist_photo_path');
        $url = str_replace('{id}', $checklist_id, $url);
        $url = str_replace('{guid}', $photo_guid, $url);

        if($photo_guid == null){
            $url = Config::get('app.web_url') . '/images/no-image.png';
        }

        return $url;
    }

    public static function GetView($number)
    {
        $user = Auth::user();
        $role = $user->role->name;
        $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);

        $data = CheckListSearch::where('checklist_number', $number)
            ->filterByRole($role, $usb_allowed)
            ->first();

        return $data;
    }

    public static function Search($accessType, $filterParams, $sortByParams = null)
    {
        // -------------------------------------
        // Set Columns
        // -------------------------------------
        $columns = null;

        if($accessType == AccessTypeEnum::Api) {
            $columns = '
                id,
                checklist_number,
                status,
                status_name,
                disagreement,
                store_id,
                store_name,
                branch_id,
                branch_name,
                UNIX_TIMESTAMP(CONVERT_TZ(created_at, \'+00:00\', @@global.time_zone)) created_at,
                total_points
            ';
        }else if($accessType == AccessTypeEnum::Web) {
            $columns = '
                checklist_number,
                created_at,
                total_points,
                status_name,
                branch_name
            ';
        }


        $columns = ltrim(rtrim($columns));

        $query = CheckListSearch::select(DB::raw($columns));

        // -------------------------------------
        // Set Filters
        // -------------------------------------
        self::SetFilterForSearchAndExport($filterParams, $accessType, $query);

        // -------------------------------------
        // Set Paginate
        // -------------------------------------
        $per_page = null;

        if ($accessType == AccessTypeEnum::Api) {
            $page = $filterParams['page'];
            $per_page = $filterParams['per_page'];

            Paginator::currentPageResolver(function () use ($page) {
                return $page;
            });
        } else {
            $per_page = Config::get('app.paginate_default_per_page');
        }

        // -------------------------------------
        // Set OrderBy
        // -------------------------------------
        $columnsAllowedForSortBy = self::columnsAllowedForSortBy();

        $direction = !StringHelper::IsNullOrEmptyString($sortByParams['direction']) ? $sortByParams['direction'] : 'desc';
        $sort = 'checklist_number'; //use default sort

        if (in_array($sortByParams['sort'], $columnsAllowedForSortBy)) {
            $sort = $sortByParams['sort'];
        }

        $query->orderBy($sort, $direction);

        // -------------------------------------
        // Return Data
        // -------------------------------------
        return $query->paginate($per_page);
    }

    public static function Export($filterParams, $sortByParams = null)
    {
        // -------------------------------------
        // Set Columns
        // -------------------------------------
        $columns = "
            v_checklist_search.checklist_number,
            v_checklist_search.total_points,
            v_checklist_search.status_name,
            v_checklist_search.branch_name,

            types.name as type_name,
            checklist_item.name as subtype_name,

            checklist_item_type.name as item_name,
            (case when checklist_item_details.id is null then '' when checklist_item_details.disagreement = 1 then 'No' else 'Si' end) as item_confirmed,
            checklist_item_details.disagreement_reason as item_reason,
            (case when checklist_item_details.id is null then '' when checklist_item_details.disagreement_generate_ticket = 1 then 'Si' else 'No' end) as item_generate_ticket,

            v_checklist_search.created_by_user,
            v_checklist_search.created_at,
            v_checklist_search.updated_by_user,
            v_checklist_search.updated_at,
            v_checklist_search.approved_by_user,
            v_checklist_search.approved_at,
            v_checklist_search.rejected_by_user,
            v_checklist_search.rejected_at,
            v_checklist_search.status_reason
        ";

        $columns = ltrim(rtrim($columns));

        $query = CheckListSearch::select(DB::raw($columns));
        $query->leftJoin('checklist_item_details', 'v_checklist_search.id', 'checklist_item_details.checklist_id');
        $query->leftJoin('checklist_item', 'checklist_item_details.checklist_item_id', 'checklist_item.id');
        $query->leftJoin('checklist_item_type', 'checklist_item.type', 'checklist_item_type.id');
        $query->leftJoin('checklist_item_type as types', 'checklist_item_type.parent_id', 'types.id');

        // -------------------------------------
        // Set Filters
        // -------------------------------------
        self::SetFilterForSearchAndExport($filterParams, AccessTypeEnum::Web, $query);

        // -------------------------------------
        // Set OrderBy
        // -------------------------------------
        $columnsAllowedForSortBy = self::columnsAllowedForSortBy();

        $direction = !StringHelper::IsNullOrEmptyString($sortByParams['direction']) ? $sortByParams['direction'] : 'desc';
        $sort = 'checklist_number'; //use default sort

        if (in_array($sortByParams['sort'], $columnsAllowedForSortBy)) {
            $sort = $sortByParams['sort'];
        }

        $query->orderBy($sort, $direction)
            ->orderBy('checklist_item.id', 'asc')
            ->orderBy('checklist_item.type', 'asc');

        // -------------------------------------
        // Return Data
        // -------------------------------------
        return $query->get();
    }

    private static function SetFilterForSearchAndExport($filterParams, $accessType, &$query){

        if (isset($filterParams['checklist_number']) && !StringHelper::IsNullOrEmptyString($filterParams['checklist_number'])) {
            $query->where('checklist_number', $filterParams['checklist_number']);
        }

        if (isset($filterParams['store_id']) && !StringHelper::IsNullOrEmptyString($filterParams['store_id'])) {
            $query->where('store_id', $filterParams['store_id']);
        }

        if (isset($filterParams['branch_id']) && !StringHelper::IsNullOrEmptyString($filterParams['branch_id'])) {
            $query->where('branch_id', $filterParams['branch_id']);
        }

        if (isset($filterParams['status']) && !StringHelper::IsNullOrEmptyString($filterParams['status'])) {
            $query->where('status', $filterParams['status']);
        }

        if (isset($filterParams['disagreement']) && !StringHelper::IsNullOrEmptyString($filterParams['disagreement'])) {
            $query->where('disagreement', $filterParams['disagreement']);
        }

        if (isset($filterParams['confirmed']) && !StringHelper::IsNullOrEmptyString($filterParams['confirmed'])) {
            $query->where('confirmed', $filterParams['confirmed']);
        }

        if($accessType == AccessTypeEnum::Api) {

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

        } else{

            if (isset($filterParams['date_from']) and trim($filterParams['date_from']) != '') {
                $date_from = Carbon::createFromFormat('d/m/Y', $filterParams['date_from'])->toDateString();
                $date_from = date('Y-m-d 00:00:00', strtotime($date_from));
                $w_raw = sprintf("CONVERT_TZ(created_at, '+00:00', '%s') >= '%s'", Config::get('app.utc_offset'), $date_from);
                $query->whereRaw($w_raw);
            }

            if (isset($filterParams['date_to']) and trim($filterParams['date_to']) != '') {
                $date_to = Carbon::createFromFormat('d/m/Y', $filterParams['date_to'])->toDateString();
                $date_to = date('Y-m-d 23:59:59', strtotime($date_to));
                $w_raw = sprintf("CONVERT_TZ(created_at, '+00:00', '%s') <= '%s'", Config::get('app.utc_offset'), $date_to);
                $query->whereRaw($w_raw);
            }

        }

        $query->filterByRole($filterParams['role'], $filterParams['usb_allowed']);
    }

    private static function columnsAllowedForSortBy()
    {
        return [
            'checklist_number',
            'created_at',
            'total_points',
            'status_name'
        ];
    }

}
