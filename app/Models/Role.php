<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    // public function users() : BelongsToMany ilk
    // {
    //     return $this->belongsToMany(User::class, 'user_roles');
    // }

    /**
     * Get the users that belong to the role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // public function users($slug) en son
    // {
    //     return User::whereHas('roles', function ($query) use ($slug) {
    //         $query->where('slug', $slug);
    //     })->get();
    // }

    // public function permissions() : \Illuminate\Database\Eloquent\Relations\BelongsToMany
    // {
    //     return $this->belongsToMany(Permission::class, 'role_permissions');
    // }
}
