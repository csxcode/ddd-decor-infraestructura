<?php namespace App\Http\Controllers;

use App\Enums\AccessTypeEnum;
use App\Enums\UserRoleEnum;
use App\Helpers\UserHelper;
use App\Models\Role;
use App\Models\User;
use App\Models\UserStoreBranch;
use App\Models\Views\StoreBranchList;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class UserController extends Controller{

    protected $request;
    protected $niceNames;
    protected $user_type_allowed_sb;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->user_type_allowed_sb = UserRoleEnum::ROLES_IDS_ALLOWED_TO_ADD_SB;

        $this->niceNames = [
            'username' => 'Nombre de Usuario',
            'email' => 'Email',
            'password' => 'Contraseña',
            'first_name' => 'Nombre',
            'last_name' => 'Apellido',
            'role_id' => 'Rol'
        ];
    }

    public function index()
    {                           
        if(!Auth::user()->hasRole('admin')) {
            return view('errors.403');
        }

        $name = $this->request->get('name');
        $role = $this->request->get('role');
        $enabled = $this->request->get('enabled');

        $sort = $this->request->get('sort');
        $direction = $this->request->get('direction');

        //get data
        $users = User::Search(
            compact('name', 'role', 'enabled'),
            compact('sort', 'direction')
        );

        $roles = Role::orderBy('display_name')->pluck('display_name', 'role_id');
        $roles->prepend(trans('global.all_select'), '');

        $conditions = array(
            '' => trans('global.all_select'),
            '1' => 'Activo',
            '0' => 'Deshabilitado'
        );

        return view('users.index')->with(compact('users', 'roles', 'conditions'));
    }

    public function create()
    {
        if(!Auth::user()->hasRole('admin')) {
            return view('errors.403');
        }

        $user = new User();
        $user->enabled = 1;
        $roles = Role::pluck('display_name', 'role_id');
        $user_type_allowed_sb = $this->user_type_allowed_sb;
        $sb_list = StoreBranchList::GetAllByUser($user->user_id);

        return view('users.create')->with(compact('user', 'roles', 'sb_list', 'user_type_allowed_sb'));
    }

    public function store()
    {
        // --------------------------------------------------------
        // Validations
        // --------------------------------------------------------
        if(!Auth::user()->hasRole('admin')) {
            return view('errors.403');
        }

        $rules = [
            'username' => 'required|unique:user,username',
            'email' => 'email|unique:user,email',
            'password' => 'required|confirmed',
            'first_name' => 'required',
            'last_name' => 'required',
            'role_id' => 'required|integer'
        ];

        $this->validate($this->request, $rules, [], $this->niceNames);

        // Add Store and branches by user
        if(UserHelper::CheckUserTypeCanSeeStoresAndBranches($this->request->get('role_id'))){

            // Insert news branches
            $branches = $this->request->get('branches');

            if(!$branches) {
                return redirect()->back()->withInput()->withErrors(['branch_required' => trans('validation.user_required_branch')]);
            }
        }

        // --------------------------------------------------------
        // Save Data
        // --------------------------------------------------------
        try {

            DB::beginTransaction();

            $user = User::create(array(
                'username' => $this->request->get('username'),
                'email' => $this->request->get('email'),
                'password' => crypt($this->request->get('password'), null),
                'first_name' => $this->request->get('first_name'),
                'last_name' =>  $this->request->get('last_name'),
                'role_id' =>  $this->request->get('role_id'),
                'enabled' =>  ($this->request->get('enabled') == 'on' ? 1 : 0),
                'multiple_sessions' =>  ($this->request->get('multiple_sessions') == 'on' ? 1 : 0)
            ));

            //add a role_user
            DB::table('role_user')->insert(
                [
                    'user_id' => $user->user_id,
                    'role_id' => $this->request->get('role_id')
                ]
            );

            // Add Store and branches by user
            if(UserHelper::CheckUserTypeCanSeeStoresAndBranches($user->role_id)){

                // Insert news branches
                $branches = $this->request->get('branches');

                if($branches) {
                    $sb_data = [];
                    foreach ($branches as $branch_id) {
                        $sb_item = [];
                        $sb_item['user_id'] = $user->user_id;
                        $sb_item['branch_id'] = $branch_id;
                        array_push($sb_data, $sb_item);
                    }
                    UserStoreBranch::insert($sb_data);
                }
            }

            DB::commit();

            Session::flash('flash_message', '&nbsp;&nbsp;Se agregó correctamente el usuario <strong>'.$this->request->get('username').'</strong>');

            return Redirect::route('users.index');

        } catch (\Exception $e) {
            DB::rollBack();
            MailService::SendErrorMail($e, AccessTypeEnum::Web);
            return redirect()->back()->withInput()->withErrors(['internal_error' => 'Ha ocurrido un error interno']);
        }

    }

    public function show($id)
    {
        if(!Auth::user()->hasRole('admin')) {
            return view('errors.403');
        }

        $user = User::find($id);        
        $roles = Role::pluck('display_name', 'role_id');
        $user_type_allowed_sb = $this->user_type_allowed_sb;
        $sb_list = StoreBranchList::GetAllByUser($user->user_id);

        return view('users.show')->with(compact('user', 'roles', 'sb_list', 'user_type_allowed_sb'));
    }

    public function edit($id)
    {
        if(!Auth::user()->hasRole('admin')) {
            return view('errors.403');
        }

        $user = User::find($id);        
        $roles = Role::pluck('display_name', 'role_id');
        $user_type_allowed_sb = $this->user_type_allowed_sb;
        $sb_list = StoreBranchList::GetAllByUser($user->user_id);

        return view('users.edit')->with(compact('user', 'roles', 'sb_list', 'user_type_allowed_sb'));
    }

    public function update($id)
    {
        // --------------------------------------------------------
        // Validations
        // --------------------------------------------------------
        if(!Auth::user()->hasRole('admin')) {
            return view('errors.403');
        }

        $user = User::findOrFail($id);

        /*check if the user want to change its password */
        $password = trim($this->request->get('password'));
        $passwordWasEntered = (isset($password) and $password!='' ? true : false);

        $rules = [
            'username' => 'required|unique:user,username,'.$id.','.'user_id',
            'email' => 'email|unique:user,email,'.$id.','.'user_id',
            'password' => ($passwordWasEntered ? 'required|confirmed' : ''),
            'first_name' => 'required',
            'last_name' => 'required',
            'role_id' => 'required|integer'
        ];

        $this->validate($this->request, $rules, [], $this->niceNames);

        if(UserHelper::CheckUserTypeCanSeeStoresAndBranches($user->role_id)){

            $branches = $this->request->get('branches');

            if(!$branches) {
                return redirect()->back()->withInput()->withErrors(['branch_required' => trans('validation.user_required_branch')]);
            }
        }


        // --------------------------------------------------------
        // Save Data
        // --------------------------------------------------------
        try {

            DB::beginTransaction();

            $user->fill(array(
                'username' => $this->request->get('username'),
                'email' => $this->request->get('email'),
                'password' => ($passwordWasEntered ? crypt($this->request->get('password'), null) : $user->password),
                'first_name' => $this->request->get('first_name'),
                'last_name' =>  $this->request->get('last_name'),
                'role_id' =>  $this->request->get('role_id'),
                'enabled' =>  ($this->request->get('enabled') == 'on' ? 1 : 0),
                'multiple_sessions' =>  ($this->request->get('multiple_sessions') == 'on' ? 1 : 0)
            ))->save();

            //Update role_user
            DB::table('role_user')
                ->where('user_id', $id)
                ->update(['role_id' => $this->request->get('role_id')]);


            // Remove old branches (always)
            UserStoreBranch::where('user_id', $id)->forceDelete();

            // Add Store and branches by user
            if(UserHelper::CheckUserTypeCanSeeStoresAndBranches($user->role_id)){

                // Insert news branches
                $branches = $this->request->get('branches');

                if($branches) {
                    $sb_data = [];
                    foreach ($branches as $branch_id) {
                        $sb_item = [];
                        $sb_item['user_id'] = $id;
                        $sb_item['branch_id'] = $branch_id;
                        array_push($sb_data, $sb_item);
                    }
                    UserStoreBranch::insert($sb_data);
                }
            }

            DB::commit();

            // Return
            Session::flash('flash_message', 'El usuario '.$this->request->get('username').' fue actualizado correctamente.');

            return Redirect::route('users.index');

        } catch (\Exception $e) {
            DB::rollBack();
            MailService::SendErrorMail($e, AccessTypeEnum::Web);
            return redirect()->back()->withInput()->withErrors(['internal_error' => 'Ha ocurrido un error interno']);
        }
    }

    public function destroy($id)
    {
        if(!Auth::user()->hasRole('admin')) {
            return view('errors.403');
        }

        $user = User::find($id);

        if(is_null($user)){
            return response()->json('data not found', 404);
        }

        $user->delete();

        $msg = 'El usuario fue eliminado correctamente!';
        return response(['message' => $msg], Response::HTTP_OK);
    }
}