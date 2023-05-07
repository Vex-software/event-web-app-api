<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;


    protected $fillable =
    [
        'name',
        'description',
        'start_time',
        'end_time',
        'club_id'
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
