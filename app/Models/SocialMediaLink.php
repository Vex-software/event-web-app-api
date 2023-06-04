<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMediaLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'type', 'link',
    ];

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
