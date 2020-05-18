<?php

namespace App\Models\Views;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class vEquivalencesChecklistTicketType extends Model
{
    protected $table = 'v_equivalences_checklist_ticket_type';
    public $timestamps = false;

    public static function GetEquivalences($checklist_item_ids){

        // This function is grouped by "checklist_type_id" and then by "ticket_type_id"

        $equivalences = DB::table('v_equivalences_checklist_ticket_type')
            ->whereIn('checklist_item_id', $checklist_item_ids)
            ->get()
            ->groupBy('checklist_type_id')
            ->transform(function($item, $k) {
                return $item->groupBy('ticket_type_id');
            });

        return $equivalences;
    }

}