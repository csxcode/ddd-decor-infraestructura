<?php namespace App\Models;

use App\Enums\AccessTypeEnum;
use App\Enums\SessionStateEnum;
use App\Helpers\CookieHelper;
use App\Helpers\CryptHelper;
use App\Helpers\FunctionHelper;
use App\Helpers\TokenHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Session extends Model{

    protected $table = 'session';
    protected $primaryKey = 'session_id';
    protected $hidden = [];
    protected $fillable = [
        'fullname_user', 'email', 'user_id', 'ip_address', 'user_agent',
        'access_type', 'token', 'login', 'last_Activity', 'logout', 'status'
    ];

    public static function LogFailedLoginAttempts($user, $type){
        Session::create(array(
            'fullname_user' => self::GetNiceUserName($user),
            'user_id' => $user->user_id,
            'ip_address' => FunctionHelper::get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'access_type' => $type,
            'last_Activity' => null,
            'logout' => null,
            'login' => Carbon::now(),
            'status' => SessionStateEnum::Fallo
        ));
    }

    public static function LogSuccessLogin($user, $type, $token = null){


        // The cookie is created only for web access
        if($type == AccessTypeEnum::Web){
            $token = TokenHelper::CreateToken();
            $num_of_minutes_until_expire = null;
            $encrypted_value = CryptHelper::Encrypt($token);
            setcookie(Config::get('app.cookie_token_name'), $encrypted_value, $num_of_minutes_until_expire, '/', FunctionHelper::getDomain());
        }

        // Cerrar las sesiones abiertas
        if($user->multiple_sessions == false) {
            Session::where('user_id', $user->user_id)
                ->where('status', SessionStateEnum::Abierto)
                ->where('access_type', $type)
                ->update([
                        'status' => SessionStateEnum::Cerrado,
                        'logout' => Carbon::now()
                    ]
                );
        }

        Session::create(array(
            'fullname_user' => self::GetNiceUserName($user),
            'user_id' => $user->user_id,
            'ip_address' => FunctionHelper::get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'access_type' => $type,
            'token' => $token,
            'login' => Carbon::now(),
            'last_Activity' => Carbon::now(),
            'logout' => null,
            'status' => SessionStateEnum::Abierto
        ));
    }

    public static function LogLogout($type, $session = null){

        if($type == AccessTypeEnum::Web){
            if (isset($_COOKIE[Config::get('app.cookie_token_name')])) {
                $token = CookieHelper::GetCookieToken();
                if ($token != null){
                    $session = Session::where('token', $token)->first();
                }
            }

            // Remove token cookie
            CookieHelper::DeleteCookieToken();
        }

        if($session != null) {
            $session->status = SessionStateEnum::Cerrado;
            $session->logout = Carbon::now();
            $session->save();
        }
    }

    public static function TrackingLastActivity($type, $token = null){

        if($type == AccessTypeEnum::Web) {
            if (isset($_COOKIE[Config::get('app.cookie_token_name')])) {
                $token = CookieHelper::GetCookieToken();
            }
        }

        $session = Session::where('token', $token)
            ->where('status', SessionStateEnum::Abierto)
            ->first();

        if($session != null){
            $session->last_activity = Carbon::now();
            $session->save();
        }
    }

    public static function Search($filterParams, $sortByParams)
    {
        $query = Session::select()->leftJoin('user', 'session.user_id', 'user.user_id');

        //Set Filters
        if (isset($filterParams['name']) and trim($filterParams['name']) != '') {
            $name = $filterParams['name'];
            $query->where(function($query) use ($name){
                $query->where('session.fullname_user', 'like', '%'.$name.'%');
                $query->orWhere('user.email', 'like', '%'.$name.'%');
                $query->orWhere('user.username', 'like', '%'.$name.'%');
            });
        }

        if (isset($filterParams['access_type']) and trim($filterParams['access_type']) != '') {
            $query->where('session.access_type', '=', $filterParams['access_type']);
        }

        if (isset($filterParams['status']) and trim($filterParams['status']) != '') {
            $query->where('session.status', '=', $filterParams['status']);
        }

        if (isset($filterParams['ip_address']) and trim($filterParams['ip_address']) != '') {
            $query->where('session.ip_address', '=', $filterParams['ip_address']);
        }

        if (isset($filterParams['login_from']) and trim($filterParams['login_from']) != '') {
            $login_from = Carbon::createFromFormat('d/m/Y', $filterParams['login_from'])->toDateString();
            $login_from = date('Y-m-d 00:00:00', strtotime($login_from));
            $w_raw = sprintf("CONVERT_TZ(session.login, '+00:00', '%s') >= '%s'", Config::get('app.utc_offset'), $login_from);
            $query->whereRaw($w_raw);
        }

        if (isset($filterParams['login_to']) and trim($filterParams['login_to']) != '') {
            $login_to = Carbon::createFromFormat('d/m/Y', $filterParams['login_to'])->toDateString();
            $login_to = date('Y-m-d 23:59:59', strtotime($login_to));
            $w_raw = sprintf("CONVERT_TZ(session.login, '+00:00', '%s') <= '%s'", Config::get('app.utc_offset'), $login_to);
            $query->whereRaw($w_raw);
        }

        if (isset($filterParams['last_activity_from']) and trim($filterParams['last_activity_from']) != '') {
            $last_activity_from = Carbon::createFromFormat('d/m/Y', $filterParams['last_activity_from'])->toDateString();
            $last_activity_from = date('Y-m-d 00:00:00', strtotime($last_activity_from));
            $w_raw = sprintf("CONVERT_TZ(session.last_activity, '+00:00', '%s') >= '%s'", Config::get('app.utc_offset'), $last_activity_from);
            $query->whereRaw($w_raw);
        }

        if (isset($filterParams['last_activity_to']) and trim($filterParams['last_activity_to']) != '') {
            $last_activity_to = Carbon::createFromFormat('d/m/Y', $filterParams['last_activity_to'])->toDateString();
            $last_activity_to = date('Y-m-d 23:59:59', strtotime($last_activity_to));
            $w_raw = sprintf("CONVERT_TZ(session.last_activity, '+00:00', '%s') <= '%s'", Config::get('app.utc_offset'), $last_activity_to);
            $query->whereRaw($w_raw);
        }

        if (isset($filterParams['logout_from']) and trim($filterParams['logout_from']) != '') {
            $logout_from = Carbon::createFromFormat('d/m/Y', $filterParams['logout_from'])->toDateString();
            $logout_from = date('Y-m-d 00:00:00', strtotime($logout_from));
            $w_raw = sprintf("CONVERT_TZ(session.logout, '+00:00', '%s') >= '%s'", Config::get('app.utc_offset'), $logout_from);
            $query->whereRaw($w_raw);
        }

        if (isset($filterParams['logout_to']) and trim($filterParams['logout_to']) != '') {
            $logout_to = Carbon::createFromFormat('d/m/Y', $filterParams['logout_to'])->toDateString();
            $logout_to = date('Y-m-d 23:59:59', strtotime($logout_to));
            $w_raw = sprintf("CONVERT_TZ(session.logout, '+00:00', '%s') <= '%s'", Config::get('app.utc_offset'), $logout_to);
            $query->whereRaw($w_raw);
        }

        //Set Order by
        $columnsAllowedForSortBy = self::columnsAllowedForSortBy();

        $direction = isset($sortByParams['direction']) ? $sortByParams['direction'] : 'desc';
        $sort = 'session_id'; //use default sort

        if (in_array($sortByParams['sort'], $columnsAllowedForSortBy)) {
            $sort = $sortByParams['sort'];
        }

        $query->orderBy($sort, $direction);

        //Get data and return
        return $query->paginate(Config::get('app.paginate_default_per_page'));
    }

    public static function getStatusName($status){
        $return = null;

        switch($status){
            case SessionStateEnum::Abierto:
                $return = 'Abierto';
                break;
            case SessionStateEnum::Cerrado:
                $return = 'Cerrado';
                break;
            case SessionStateEnum::Expirado:
                $return = 'Expirado';
                break;
            case SessionStateEnum::Expulsado:
                $return = 'Expulsado';
                break;
            case SessionStateEnum::Fallo:
                $return = 'Fallo';
                break;
        }

        return $return;
    }

    public static function GetNiceUserName($user){
        return $user->first_name.' '.$user->last_name.' ('.$user->username.')';
    }

    public static function getAccessTypeName($access_type){
        $return = null;

        switch($access_type){
            case AccessTypeEnum::Api:
                $return = 'API';
                break;
            case AccessTypeEnum::Web:
                $return = 'Web';
                break;
        }

        return $return;
    }

    private static function columnsAllowedForSortBy(){
        return [
            'name',
            'access_type',
            'status',
            'ip_address',
            'login_from',
            'login_to',
            'last_activity_from',
            'last_activity_to',
            'logout_from',
            'logout_to',
            'fullname_user'
        ];
    }

}