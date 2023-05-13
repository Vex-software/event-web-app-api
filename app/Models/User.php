<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'phone_number',
        'email',
        'password',
        'role',
        'profile_photo_path',
        'address',
        'city_id',
        'email_verified_at',
        'remember_token',
        'google_id',
        'github_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
        'email',
        'phone_number',
        'address',
        'city_id',
        'email_verified_at',
        'google_id',
        'github_id',
        'pivot',
        'role_id',
    ];

    public static function getAllProperties()
    {
        if(auth()->user()->role_id === Role::where('slug', 'admin')->first()->id){
            return self::$hidden;
        }else{
            return "You are not authorized to see this information.";
        }
    }

    public function isUser()
    {
        return $this->role_id === Role::where('slug', 'user')->first()->id;
    }


    // public static function getAllProperties()
    // {
    //     return (new static)->hidden;
    // }






    public function isClubManager()
    {
        return $this->role_id === Role::where('slug', 'club_manager')->first()->id;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function clubs()
    {
        return $this->belongsToMany(Club::class);
    }

    public function events()
    {
        return $this->belongsToMany(Event::class);
    }

    public function tokens()
    {
        return $this->hasMany(\Laravel\Passport\Token::class);
    }

    public function managerOfClub()
    {
        return $this->hasOne(Club::class, 'manager_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function socialMediaLinks()
    {
        return $this->hasMany(SocialMediaLink::class);
    }
}
