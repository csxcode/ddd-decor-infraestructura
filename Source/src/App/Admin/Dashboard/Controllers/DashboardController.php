<?php

namespace App\Admin\Dashboard\Controllers;

use App\Models\Ticket\TicketStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController
{
    public function index()
    {        
        $user = Auth::user();
        $data = self::GetDashboardData($user->user_id)[0];                
        $url_tickets_new = route('tickets.index', ['status' => TicketStatus::STATUS_NUEVO]);
        $url_tickets_in_process = route('tickets.index', ['status' => TicketStatus::STATUS_EJECUTANDO]);
        $url_checklists_new = route('checklist.index', ['status' => 1 ]);

        return view('dashboard.index', compact(
            'data',        
            'url_tickets_new',
            'url_tickets_in_process',
            'url_checklists_new'
        ));
    }

    public static function GetDashboardData($user_id)
    {
        $data = DB::select(DB::raw('CALL sp_get_data_dashboard('.$user_id.')'));
        return $data;
    }
}
