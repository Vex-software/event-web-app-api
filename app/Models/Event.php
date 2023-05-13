<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];
    
    protected $fillable =
    [
        'name',
        'title',
        'description',
        'start_time',
        'end_time',
        'club_id',
        'category_id',
        'location',
        'image',
        'quota',
    ];

    protected $casts =
    [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected $hidden =
    [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static $hiddenClubFields = ['phone_number', 'email', 'created_at', 'updated_at', 'deleted_at'];


    /* Bir etkinligin sadece bir tane yaraticisi/sahibi var. 
    *  Ilerde club_event tablosu olusturulup coga cok iliski yaplabilir 
    */
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

}
