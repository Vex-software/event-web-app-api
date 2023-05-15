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
     * Not : Bu gizli alanlar her kullanıcıya aittir. 
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

    // Bu Gizli Alanlar sadece Authenticated User'in kendi bilgilerini görmesini engellemek için kullanılır.
    protected $hiddenForAuthUser = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
        'email_verified_at',
        'google_id',
        'github_id',
    ];



    public function getAllAttributes()
    {
        $allAttributes  = $this->makeVisible($this->getFillable())->toArray();
        $hiddenForAuthUser = $this->hiddenForAuthUser ?? [];

        if (isset($allAttributes['city_id'])) {
            $city = $this->city;
            unset($allAttributes['city_id']);
            $allAttributes['city'] = $city;
        }

        if (isset($allAttributes['social_media_id'])) {
            $social_media = $this->socialMediaLink;
            unset($allAttributes['social_media_id']);
            $allAttributes['social_media'] = $social_media;
        }

        foreach ($hiddenForAuthUser as $attribute) {
            unset($allAttributes[$attribute]);
        }

        return $allAttributes;
    }




    public function isUser()
    {
        return $this->role_id === Role::where('slug', 'user')->first()->id;
    }


    public function isClubManager()
    {
        return $this->role_id === Role::where('slug', 'club_manager')->first()->id;
    }

    public function isAdmin()
    {
        return $this->role_id === Role::where('slug', 'admin')->first()->id;
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

    public function socialMediaLink()
    {
        return $this->hasOne(SocialMediaLink::class, 'id', 'social_media_id');
    }
    // protected $appends = [
    //     'profile_photo_url',
    // ];

    public function getProfilePhotoPathAttribute($value)
    {
        if (env('IMAGE_PROCESSING_ON_SERVER')) {
            return route('getUserPhoto', ['id' => $this->id]);
        } else {
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                return $value;
            } else {
                return asset('storage/' . $value);
            }
        }
    }
}

//   https://ui-avatars.com/api/?name=' . $this->name . '+' . $this->surname . '&color=7F9CF5&background=EBF4FF