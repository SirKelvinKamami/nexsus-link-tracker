<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Form extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'settings',
        'is_active',
        'collect_email',
        'one_response_per_ip',
        'closed_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'collect_email' => 'boolean',
        'one_response_per_ip' => 'boolean',
        'closed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->title);
                $slug = $form->slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $form->slug . '-' . $count;
                    $count++;
                }
                $form->slug = $slug;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('order');
    }

    public function responses()
    {
        return $this->hasMany(FormResponse::class);
    }

    public function getResponseCountAttribute()
    {
        return $this->responses()->count();
    }

    public function isActive()
    {
        return $this->is_active && ($this->closed_at === null || $this->closed_at->isFuture());
    }
}
