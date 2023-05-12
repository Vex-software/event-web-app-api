<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }


    public function socialMediaLinks()
    {
        return $this->hasMany(SocialMediaLink::class);
    }

    
}
