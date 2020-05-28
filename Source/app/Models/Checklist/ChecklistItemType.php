<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/27/2019
 * Time: 4:36 PM
 */

namespace App\Models\Checklist;


use Illuminate\Database\Eloquent\Model;

class ChecklistItemType extends Model
{
    protected $table = 'checklist_item_type';
    protected $fillable = [
        'name', 'parent_id', 'display_order', 'type_status'
    ];
    public $timestamps = false;

    public function sub_types() {
        return $this->hasMany(ChecklistItemType::class, 'parent_id', 'id');
    }

    public function items() {
        return $this->hasMany(ChecklistItem::class, 'type', 'id');
    }
}