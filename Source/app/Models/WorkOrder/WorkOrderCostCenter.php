<?php namespace App\Models\WorkOrder;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Eloquent\Model;

class WorkOrderCostCenter extends Model {

    protected $table = 'work_order_cost_center';
    protected $guarded = ['id'];
    public $timestamps = false;

    // ================================================================
    // ===================== Local Scopes =============================
    // ================================================================

    public function scopeFilterByRole($query, $user)
    {
        $role = strtolower($user->role->name);

        if ($role == UserRoleEnum::RESPONSABLE_SEDE || $role == UserRoleEnum::GESTOR_INFRAESTRUCTURA) {

            $all = true;

        } else {

            // not access to data
            $query->where('work_order_cost_center.id', 0);

        }

    }
}
