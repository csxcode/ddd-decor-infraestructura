<?php
/**
 * Created by PhpStorm.
 * User: CSXCODE
 * Date: 5/15/2019
 * Time: 2:54 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class LogChangeDetail extends Model
{
    protected $table = 'log_change_details';
    protected $fillable = ['log_change_id', 'field_name', 'old_value', 'new_value'];
    public $timestamps = false;

    const NAME_NEW_FIELD = '[NUEVO]';
    const NAME_DELETE_FIELD = '[ELIMINADO]';

    public static function AddToArrayList($list, $field, $old_value, $new_value){
        $new_data = [
            'field_name' => $field,
            'old_value' => $old_value,
            'new_value' => $new_value
        ];
        array_push($list, $new_data);
        return $list;
    }

}