<?php namespace App\Http\Controllers;

use App\Enums\AccessTypeEnum;
use App\Enums\SessionStateEnum;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller{

    protected $request;

    public function __construct(Request $request){
        $this->request = $request;
    }

    public function index()
    {
        if(!Auth::user()->hasRole('admin')) {
            return view('errors.403');
        }

        $name = $this->request->get('name');
        $access_type = $this->request->get('access_type');
        $status = $this->request->get('status');
        $login_from = $this->request->get('login_from');
        $login_to = $this->request->get('login_to');
        $last_activity_from = $this->request->get('last_activity_from');
        $last_activity_to = $this->request->get('last_activity_to');
        $logout_from = $this->request->get('logout_from');
        $logout_to = $this->request->get('logout_to');
        $ip_address = $this->request->get('ip_address');

        $sort = $this->request->get('sort');
        $direction = $this->request->get('direction');

        //get data
        $data = Session::Search(
            compact('name', 'access_type', 'status', 'login_from', 'login_to', 'last_activity_from', 'last_activity_to', 'logout_from', 'logout_to', 'ip_address'),
            compact('sort', 'direction')
        );

        $status = SessionStateEnum::getAllSessionState();
        asort($status);
        $status = ['' => trans('global.all_select')] + $status;

        $access_type = AccessTypeEnum::GetAll();
        asort($access_type);
        $access_type = ['' => trans('global.all_select')] + $access_type;

        return view('sessions.index')->with(compact('data', 'status', 'access_type'));
    }

    public function show($id)
    {
        if(!Auth::user()->hasRole('admin')) {
            return view('errors.403');
        }

        $data = Session::leftJoin('user', 'user.user_id', 'session.user_id')->where('session_id', $id)->first();

        // hidden token only for web
        if($data->access_type == AccessTypeEnum::Web){
            $data->token = null;
        }

        return view('sessions.show')->with(compact('data'));
    }

    public function eject($id)
    {
        if(!Auth::user()->hasRole('admin')) {
            return view('errors.403');
        }

        $data = Session::find($id);
        $data->status = SessionStateEnum::Expulsado;
        $data->logout = Carbon::now();
        $data->save();

        $msg = 'La sesión fue expulsada correctamente!';
        return response(['message' => $msg], Response::HTTP_OK);
    }

}