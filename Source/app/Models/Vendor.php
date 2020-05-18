<?php namespace App\Models;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendor';

    // ================================================================
    // ===================== Local Scopes =============================
    // ================================================================
    public function scopeFilterByRole($query, $user)
    {
        $role = strtolower($user->role->name);

        if ($role == UserRoleEnum::GESTOR_INFRAESTRUCTURA) {

            $all = true;

        } else {

            // not access to data
            $query->where('id', 0);

        }
    }
}
