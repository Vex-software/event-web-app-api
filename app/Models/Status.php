<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends Model
{
    use HasFactory;
    use SoftDeletes;

    public static function getActiveStatus()
    {
        return self::where('status_slug', 'active')->first();
    }

    public static function getInactiveStatus()
    {
        return self::where('status_slug', 'passive')->first();
    }

    public static function getPendingStatus()
    {
        return self::where('status_slug', 'pending')->first();
    }

    public static function getSuspendedStatus()
    {
        return self::where('status_slug', 'deleted')->first();
    }

    public static function getBannedStatus()
    {
        return self::where('status_slug', 'banned')->first();
    }

    public static function getDraftStatus()
    {
        return self::where('status_slug', 'draft')->first();
    }

    public static function getPublishedStatus()
    {
        return self::where('status_slug', 'published')->first();
    }

    public static function getUnpublishedStatus()
    {
        return self::where('status_slug', 'unpublished')->first();
    }

    public static function getEmailNotVerifiedStatus()
    {
        return self::where('status_slug', 'email-not-verified')->first();
    }

    protected $fillable = [
        'status_name',
        'status_description',
        'status_color',
        'status_icon',
        'status_slug',
        'status_order',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function getStatusNameAttribute($value)
    {
        return ucwords($value);
    }

    public function getStatusDescriptionAttribute($value)
    {
        return ucwords($value);
    }

    public function getStatusSlugAttribute($value)
    {
        return strtolower($value);
    }

    public function getStatusColorAttribute($value)
    {
        return strtolower($value);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
