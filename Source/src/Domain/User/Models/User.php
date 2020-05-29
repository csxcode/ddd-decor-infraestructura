<?php 

namespace Domain\User\Models;

use Support\Enums\UserRoleEnum;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'user_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'first_name', 'last_name', 'email', 'password', 'role_id', 'enabled', 'multiple_sessions'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

     /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /* -----------------------------------
    |  Relationships
    |  -----------------------------------*/

    public function role()
    {
        return $this->hasOne('App\Models\Role', 'role_id', 'role_id');
    }

    /* -----------------------------------
    |  Functions
    |  -----------------------------------*/
    public function getStatusAttribute()
    {
        return $this->enabled == 1 ? 'Activo' : 'Deshabilitado';
    }

    public function getMultipleSessionsNameAttribute()
    {
        return $this->multiple_sessions == 1 ? 'Si' : 'No';
    }

    public static function GetByToken($token)
    {
        return User::join("session", "user.user_id", "session.user_id")
            ->where("token", $token)
            ->first();
    }

    public static function GetCreatedByUser($user)
    {
        return ($user == null ? null : $user->first_name.' '.$user->last_name.' ('.$user->username.')');
    }

    public static function Search($filterParams, $sortByParams)
    {
        $query = User::select();

        //Set Filters
        if (isset($filterParams['name']) and trim($filterParams['name']) != '') {
            $name = $filterParams['name'];
            $query->where(function ($q) use ($name){
                $q->where('email', 'like', '%'.$name.'%');
                $q->orWhere('first_name', 'like', '%'.$name.'%');
                $q->orWhere('last_name', 'like', '%'.$name.'%');
                $q->orWhere('username', 'like', '%'.$name.'%');
            });
        }

        if (isset($filterParams['role']) and trim($filterParams['role']) != '') {
            $query->where('role_id', '=', $filterParams['role']);
        }

        if (isset($filterParams['enabled']) and trim($filterParams['enabled']) != '') {
            $query->where('enabled', '=', $filterParams['enabled']);
        }

        //eager loading
        $query->with('role');

        //Set Order by
        $columnsAllowedForSortBy = self::columnsAllowedForSortBy();

        $direction = isset($sortByParams['direction']) ? $sortByParams['direction'] : 'asc';
        $sort = 'first_name'; //use default sort by users

        if (in_array($sortByParams['sort'], $columnsAllowedForSortBy)) {
            $sort = $sortByParams['sort'];
        }

        $query->orderBy($sort, $direction);

        //Get data and return
        return $query->paginate(Config::get('app.paginate_default_per_page'));
    }

    private static function columnsAllowedForSortBy(){
        return [
            'first_name',
            'last_name',
            'email',
            'type'
        ];
    }


    // ================================================================
    // ===================== Local Scopes =============================
    // ================================================================
    public function scopeFilterByRoleForContacts($query, $user)
    {
        $role = strtolower($user->role->name);

        if ($role == UserRoleEnum::GESTOR_INFRAESTRUCTURA) {

            $all = true;

        } else {

            // not access to data
            $query->where('user_id', 0);

        }
    }
}
