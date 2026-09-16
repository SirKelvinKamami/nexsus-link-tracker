<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'rule_type',
        'rule_config',
        'is_active',
    ];

    protected $casts = [
        'rule_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get rules for a specific user.
     */
    public static function forUser($userId)
    {
        return self::where('user_id', $userId)->where('is_active', true)->get();
    }
}
