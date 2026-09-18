<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPrivacy extends Model
{
    protected $fillable = [
        'user_id',
        'profile_visible',
        'email_visible',
        'links_visible',
        'analytics_visible',
        'allow_comments',
        'default_link_permission',
    ];

    protected $casts = [
        'profile_visible' => 'boolean',
        'email_visible' => 'boolean',
        'links_visible' => 'boolean',
        'analytics_visible' => 'boolean',
        'allow_comments' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function defaults(): array
    {
        return [
            'profile_visible' => true,
            'email_visible' => false,
            'links_visible' => true,
            'analytics_visible' => false,
            'allow_comments' => false,
            'default_link_permission' => 'private',
        ];
    }
}
