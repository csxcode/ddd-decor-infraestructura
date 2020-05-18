<?php

namespace App\Models\Views;

use App\Enums\UserRoleEnum;
use App\Helpers\StringHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CheckListSearch extends Model
{
    protected $table = 'v_checklist_search';
    public $timestamps = false;

    // ================================================================
    // ===================== Local Scopes =============================
    // ================================================================

    public function scopeFilterByRole($query, $role, $usb_allowed)
    {
         // Add security filter
         if (isset($role) && !StringHelper::IsNullOrEmptyString($role)) {

            $role = strtolower($role);

            if($role == strtolower(UserRoleEnum::USER) || $role == strtolower(UserRoleEnum::STORE_MANAGER)) {

                $query->where('edit_status', 1);

                if (isset($usb_allowed) && isset($usb_allowed)) {
                    $query->whereIn('branch_id', $usb_allowed);
                }

            }

        }

        return $query;
    }

}
