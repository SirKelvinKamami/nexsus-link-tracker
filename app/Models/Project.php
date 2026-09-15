<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'settings',
        'is_default',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_default' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name);
                $slug = $project->slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $project->slug . '-' . $count;
                    $count++;
                }
                $project->slug = $slug;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function links()
    {
        return $this->hasMany(Link::class);
    }

    public function forms()
    {
        return $this->hasMany(Form::class);
    }

    public function landingPages()
    {
        return $this->hasMany(LandingPage::class);
    }

    public function clicks()
    {
        return $this->hasMany(LinkClick::class);
    }

    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function getGa4Id()
    {
        return $this->getSetting('ga4_id');
    }

    public function getGtmId()
    {
        return $this->getSetting('gtm_id');
    }
}
