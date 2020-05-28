<?php

namespace App\Models\Views;

use App\Enums\UserRoleEnum;
use App\Models\UserStoreBranch;
use App\Models\WorkOrder\WorkOrderContact;
use App\Models\WorkOrder\WorkOrderQuoteStatus;
use App\Models\WorkOrder\WorkOrderStatus;
use Illuminate\Database\Eloquent\Model;

class WorkOrderSearch extends Model
{
    protected $table = 'v_work_order_search';
    public $timestamps = false;

    public function wo_contacts()
    {
        return $this->hasMany(WorkOrderContact::class, 'work_order_id', 'id');
    }

    // ================================================================
    // ===================== Local Scopes =============================
    // ================================================================

    public function scopeFilterByRole($query, $user)
    {
        $role = strtolower($user->role->name);

        if ($role == UserRoleEnum::ADMIN || $role == UserRoleEnum::GESTOR_INFRAESTRUCTURA) {

            
        } else if ($role == UserRoleEnum::RESPONSABLE_SEDE) {

            // puede ver sólo las WO de las tiendas que tiene asignado dicho usuario
            $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
            $query->whereIn('branch_id', $usb_allowed);
        } else if ($role == UserRoleEnum::PROVEEDOR) {

            $query->where(function ($q) use ($user) {

                // El estado de la WO sea “cotizando” (1) y el proveedor participe de la cotización (tabla work_order_quote, campo vendor_id)
                $q->where(function ($q1) use ($user) {
                    $q1->where('v_work_order_search.work_order_status_id', WorkOrderStatus::STATUS_COTIZANDO);
                    $q1->whereExists(function ($q1) use ($user) {
                        $q1->select(\DB::raw(1))
                            ->from('work_order_quote')
                            ->whereRaw('work_order_quote.work_order_id = v_work_order_search.id')
                            ->where('work_order_quote.vendor_id', $user->vendor_id);
                    });
                });

                // La WO haya sido asignada al proveedor (tabla work_order_quote, campo vendor_id, donde quote_status_id = 3).
                $q->orWhere(function ($q2) use ($user) {
                    $q2->whereExists(function ($q2) use ($user) {
                        $q2->select(\DB::raw(1))
                            ->from('work_order_quote')
                            ->whereRaw('work_order_quote.work_order_id = v_work_order_search.id')
                            ->where('work_order_quote.vendor_id', $user->vendor_id)
                            ->where('work_order_quote.quote_status_id', WorkOrderQuoteStatus::STATUS_ACEPTADO);
                    });
                });
            });
        }
    }

    public function scopeFilterByRoleForWOH($query, $user)
    {
        $role = strtolower($user->role->name);

        if ($role == UserRoleEnum::ADMIN || $role == UserRoleEnum::GESTOR_INFRAESTRUCTURA) {

            
        } else if ($role == UserRoleEnum::RESPONSABLE_SEDE) {

            // puede ver sólo las WO de las tiendas que tiene asignado dicho usuario
            $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
            $query->whereIn('branch_id', $usb_allowed);
            
        } else if ($role == UserRoleEnum::PROVEEDOR) {

            $query->where(function ($q) use ($user) {

                // La WO haya sido asignada al proveedor (tabla work_order_quote, campo vendor_id, donde quote_status_id = 3).
                $q->whereExists(function ($q1) use ($user) {
                    $q1->select(\DB::raw(1))
                        ->from('work_order_quote')
                        ->whereRaw('work_order_quote.work_order_id = v_work_order_search.id')
                        ->where('work_order_quote.vendor_id', $user->vendor_id)
                        ->where('work_order_quote.quote_status_id', WorkOrderQuoteStatus::STATUS_ACEPTADO);                      
                });

            });
        }
    }
}
