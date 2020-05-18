<?php

namespace App\Http\Traits;

use App\Models\Role;

trait HasRoleTrait {   

  public function hasRole( ... $roles ) {

    foreach ($roles as $role) {      
      if ($this->roles->contains('name', $role)) {
        return true;
      }
    }
    return false;
  }

  public function roles() {

    return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'user_id');

  }
}