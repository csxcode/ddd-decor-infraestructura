<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class GlobalController extends Controller
{
    public function clear()
    {
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        return "clear successfully!";
    }


    /* --------------------------------------------- */
    /* AJAX METHODS */
    /* --------------------------------------------- */
    public static function GetBranchesByStore(Request $request){

        $user = Auth::user();

        if (!$request->store) {
            $html = '<option value="">'.trans('global.all_select').'</option>';
        } else {
            $html = '';
            $branches = Branch::GetUserBranches($user->user_id, $request->store);

            $html .= '<option value="">'.trans('global.all_select').'</option>';

            foreach ($branches as $item) {
                $html .= '<option value="'.$item->branch_id.'">'.$item->branch_name.'</option>';
            }
        }

        return response()->json(['html' => $html]);
    }

}
