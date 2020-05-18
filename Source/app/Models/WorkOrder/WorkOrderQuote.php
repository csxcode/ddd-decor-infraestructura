<?php namespace App\Models\WorkOrder;

use App\Enums\UserRoleEnum;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 */
class WorkOrderQuote extends Model {
    protected $table = 'work_order_quote';
    protected $guarded = [];

    // ================================================================
    // ==================== Relationships =============================
    // ================================================================
    public function status()
    {
        return $this->hasOne(WorkOrderQuoteStatus::class, 'id', 'quote_status_id');
    }

    public function vendor()
    {
        return $this->hasOne(Vendor::class, 'id', 'vendor_id');
    }

    // ================================================================
    // ===================== Local Scopes =============================
    // ================================================================

    public function scopeFilterByRole($query, $user)
    {
        $role = strtolower($user->role->name);

        if ($role == UserRoleEnum::GESTOR_INFRAESTRUCTURA) {

        } elseif ($role == UserRoleEnum::PROVEEDOR) {
            $query->where('vendor_id', $user->vendor_id);
        } else {

            // not show data for other roles
            $query->where('vendor_id', 0);
        }

    }
}
