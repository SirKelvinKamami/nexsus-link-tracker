<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LinkClick extends Model
{
    protected $fillable = [
        'link_id',
        'user_id',
        'project_id',
        'session_id',
        'ip_hash',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'user_agent',
        'device_type',
        'browser',
        'browser_version',
        'os',
        'country',
        'language',
    ];

    public function link()
    {
        return $this->belongsTo(Link::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}