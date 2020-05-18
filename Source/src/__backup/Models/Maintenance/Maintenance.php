<?php namespace App\Models\Maintenance;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $table = 'maintenance';
    protected $guarded = [];
    public $timestamps = false;

    public function generateNumber(){
        $ini_counter = 1000;
        $max = $this->max('maintenance_number');
        if(!$max)
            $max = $ini_counter;
        $max = ((int)$max + 1);
        return $max;
    }

    // ================================================================
    // ===================== Local Scopes =============================
    // ================================================================
    public function scopeFilterByRole($query, $user)
    {
        $role = strtolower($user->role->name);


        if ($role == UserRoleEnum::GESTOR_INFRAESTRUCTURA) {

            $all = true;

        }  else {

            // not access to data
            $query->where('maintenance.id', 0);

        }

    }

}
