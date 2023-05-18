<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventCategory extends Model
{
    use HasFactory, SoftDeletes;


    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function events()
    {
        // return $this->hasMany(Event::class);
        return $this->hasMany(Event::class, 'category_id', 'id');
    }

    public function eventCount()
    {
        return $this->events()->count();
    }

    public function eventCountWithTrashed()
    {
        return $this->events()->withTrashed()->count();
    }

    public function eventCategoryEvents()
    {
        return $this->events()->with('category');
    }



}

