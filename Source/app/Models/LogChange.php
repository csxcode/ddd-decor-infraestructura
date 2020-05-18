<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/15/2019
 * Time: 2:54 PM
 */

namespace App\Models;


use App\Helpers\StringHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class LogChange extends Model
{
    protected $table = 'log_changes';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function changes() {
        return $this->hasMany(LogChangeDetail::class, 'log_change_id', 'id');
    }

    public static function Search($accessType, $filterParams, $sortByParams = null)
    {
        // -------------------------------------
        // Set Columns
        // -------------------------------------
        $columns = "
            log_changes.id,            
            UNIX_TIMESTAMP(CONVERT_TZ(log_changes.created_at, '+00:00', @@global.time_zone)) created_at,  
            log_changes.user,
            log_changes.reason,
            log_changes.description             
        ";
        $columns = ltrim(rtrim($columns));

        $query = LogChange::select(DB::raw($columns));

        // -------------------------------------
        // Set Filters
        // -------------------------------------
        $query->where('log_changes.module_id', $filterParams['module_id']);
        $query->where('log_changes.record_id', $filterParams['record_id']);

        // -------------------------------------
        // Set Paginate
        // -------------------------------------
        $page = $filterParams['page'];
        $per_page = $filterParams['per_page'];

        Paginator::currentPageResolver(function() use ($page) {
            return $page;
        });

        // -------------------------------------
        // Set OrderBy
        // -------------------------------------
        $columnsAllowedForSortBy = self::columnsAllowedForSortBy();

        $direction = !StringHelper::IsNullOrEmptyString($sortByParams['direction']) ? $sortByParams['direction'] : 'desc';
        $sort = 'created_at'; //use default sort

        if (in_array($sortByParams['sort'], $columnsAllowedForSortBy)) {
            $sort = $sortByParams['sort'];
        }

        $query->orderBy($sort, $direction);

        // -------------------------------------
        // Return Data
        // -------------------------------------
        return $query->paginate($per_page);
    }

    private static function columnsAllowedForSortBy(){
        return [
            'created_at'
        ];
    }

}