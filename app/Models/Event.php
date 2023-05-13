<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;


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
