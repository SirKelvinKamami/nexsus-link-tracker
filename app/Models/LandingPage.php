<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LandingPage extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'content',
        'settings',
        'meta_title',
        'meta_description',
        'og_image',
        'is_published',
        'collect_emails',
        'form_id',
        'published_at',
    ];

    protected $casts = [
        'content' => 'array',
        'settings' => 'array',
        'is_published' => 'boolean',
        'collect_emails' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($page) {
            if (empty($page->slug)) {
                $page->slug = Str::slug($page->title);
                $slug = $page->slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $page->slug . '-' . $count;
                    $count++;
                }
                $page->slug = $slug;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function isPublished()
    {
        return $this->is_published && ($this->published_at === null || $this->published_at->isPast());
    }

    public function getBlocks()
    {
        return $this->content['blocks'] ?? [];
    }

    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }
}
