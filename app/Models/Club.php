<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
        'title',
        'description',
        'email',
        'phone_number',
        'website',
        'founded_year',
        'manager_id',
    ];

    protected $casts = [
        'founded_year' => 'integer',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'email',
        'phone_number',
        'pivot',
    ];



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


    public function socialMediaLink()
    {
        return $this->hasOne(SocialMediaLink::class);
    }

    public function getClubEventsDataForUser(int $id, int $paginate)
    {
        return $this->findOrFail($id)->events()->paginate($paginate);
    }

    public function getLogoAttribute($value)
    {
        // if (filter_var($value, FILTER_VALIDATE_URL))
        //     return route('getClubPhoto', ['id' => $this->id]);
        // else {
        //     return asset('storage/' . $value);
        // }
        return route('getClubPhoto', ['id' => $this->id]);
    }

   

    // public function getWebsiteAttribute($value)
    // {
    //     return 'https://' . $value;
    // }

    // public function setWebsiteAttribute($value)
    // {
    //     $this->attributes['website'] = str_replace('https://', '', $value);
    // }

    public function getPhoneNumberAttribute($value)
    {
        return '+90' . $value;
    }

    // public function setPhoneNumberAttribute($value)
    // {
    //     $this->attributes['phone_number'] = str_replace('+90', '', $value);
    // }

    public function getCreatedAtAttribute($value)
    {
        return date('d.m.Y H:i:s', strtotime($value));
    }

    public function getUpdatedAtAttribute($value)
    {
        return date('d.m.Y H:i:s', strtotime($value));
    }

    public function getDeletedAtAttribute($value)
    {
        return date('d.m.Y H:i:s', strtotime($value));
    }

    public function getFoundedYearAttribute($value)
    {
        return date('Y', strtotime($value));
    }

    // public function setFoundedYearAttribute($value)
    // {
    //     $this->attributes['founded_year'] = date('Y-m-d H:i:s', strtotime($value));
    // }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', '%' . $search . '%')
            ->orWhere('title', 'like', '%' . $search . '%')
            ->orWhere('description', 'like', '%' . $search . '%')
            ->orWhere('email', 'like', '%' . $search . '%')
            ->orWhere('phone_number', 'like', '%' . $search . '%')
            ->orWhere('website', 'like', '%' . $search . '%')
            ->orWhere('founded_year', 'like', '%' . $search . '%');
    }

    public function scopeFilter($query, $filters)
    {
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['title'])) {
            $query->where('title', 'like', '%' . $filters['title'] . '%');
        }

        if (isset($filters['description'])) {
            $query->where('description', 'like', '%' . $filters['description'] . '%');
        }

        if (isset($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }

        if (isset($filters['phone_number'])) {
            $query->where('phone_number', 'like', '%' . $filters['phone_number'] . '%');
        }

        if (isset($filters['website'])) {
            $query->where('website', 'like', '%' . $filters['website'] . '%');
        }

        if (isset($filters['founded_year'])) {
            $query->where('founded_year', 'like', '%' . $filters['founded_year'] . '%');
        }
    }

    public function scopeSort($query, $sort)
    {
        if (isset($sort['name'])) {
            $query->orderBy('name', $sort['name']);
        }

        if (isset($sort['title'])) {
            $query->orderBy('title', $sort['title']);
        }

        if (isset($sort['description'])) {
            $query->orderBy('description', $sort['description']);
        }

        if (isset($sort['email'])) {
            $query->orderBy('email', $sort['email']);
        }

        if (isset($sort['phone_number'])) {
            $query->orderBy('phone_number', $sort['phone_number']);
        }

        if (isset($sort['website'])) {
            $query->orderBy('website', $sort['website']);
        }

        if (isset($sort['founded_year'])) {
            $query->orderBy('founded_year', $sort['founded_year']);
        }
    }

    public function scopePaginate($query, $paginate)
    {
        return $query->paginate($paginate);
    }

    public function scopeCount($query)
    {
        return $query->count();
    }

    public function scopeCountAll($query)
    {
        return $query->withTrashed()->count();
    }

    public function scopeCountActive($query)
    {
        return $query->whereNull('deleted_at')->count();
    }

    public function scopeCountInactive($query)
    {
        return $query->onlyTrashed()->count();
    }

    public function scopeCountWithFilters($query, $filters)
    {
        return $query->filter($filters)->count();
    }

    public function scopeCountAllWithFilters($query, $filters)
    {
        return $query->withTrashed()->filter($filters)->count();
    }

    public function scopeCountActiveWithFilters($query, $filters)
    {
        return $query->whereNull('deleted_at')->filter($filters)->count();
    }

    public function scopeCountInactiveWithFilters($query, $filters)
    {
        return $query->onlyTrashed()->filter($filters)->count();
    }
}
