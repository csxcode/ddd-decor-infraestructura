<?php

namespace App\Models\Views;

use App\Enums\UserRoleEnum;
use App\Models\UserStoreBranch;
use Illuminate\Database\Eloquent\Model;

class TicketSearch extends Model
{
    protected $table = 'v_ticket_search';
    public $timestamps = false;

    public function scopeFilterByRole($query, $user)
    {
        $role = strtolower($user->role->name);

        if ($role == strtolower(UserRoleEnum::ADMIN) || $role == strtolower(UserRoleEnum::GESTOR_INFRAESTRUCTURA)) {
        } else if ($role == strtolower(UserRoleEnum::RESPONSABLE_SEDE)) {

            $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
            $query->whereIn('branch_id', $usb_allowed);
        } else {

            // Cualquier otro tipo de usuario, solo si el ticket pertenece a la
            // sucursal que tiene asignado el usuario y el ticket haya sido creado por el usuario
            $usb_allowed = UserStoreBranch::GetStoreBranchIdsByUser($user->user_id, false);
            $username_from_user = strtolower($user->username);

            $query->whereIn('branch_id', $usb_allowed);
            $query->where('ticket_created_by_username', $username_from_user);
        }
    }
}
