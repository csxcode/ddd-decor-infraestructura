<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/27/2019
 * Time: 4:36 PM
 */

namespace App\Models\Checklist;

use App\Enums\ChecklistEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ChecklistItem extends Model
{
    protected $table = 'checklist_item';
    protected $guarded =[];
    protected $fillable = [];
    public $timestamps = false;

    public static function CountRecordsRelatedByTypeOrSubtypeOrItem($id, $type){                

        $query = "select count(*) as count from checklist_item_details
            inner join checklist_item on checklist_item_details.checklist_item_id = checklist_item.id
            inner join (select * from checklist_item_type where not parent_id is null) as subtype on checklist_item.type = subtype.id
            inner join (select * from checklist_item_type where parent_id is null) as type on subtype.parent_id = type.id 
        where 1 = 1 ";

        if($type == ChecklistEnum::STRUCTURE_CHECKLIST_T_TYPE){
            $query = $query . " and type.id = " . $id;
        } else if ($type == ChecklistEnum::STRUCTURE_CHECKLIST_T_SUBTYPE){
            $query = $query . " and subtype.id = " . $id;
        } else if ($type == ChecklistEnum::STRUCTURE_CHECKLIST_T_ITEM){
            $query = $query . " and checklist_item_details.checklist_item_id = " . $id;
        }

        return DB::select($query)[0]->count;
    }
}