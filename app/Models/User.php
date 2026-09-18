<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Models\UserPrivacy;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'image',
        'password',
        'provider',
        'provider_id',
        'email_verified_at',
        'handle',
        'role',
        'block',
        'description',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEditor(): bool
    {
        return in_array($this->role, ['admin', 'editor']);
    }

    public function isBlocked(): bool
    {
        return $this->block === 'yes';
    }

    public function canComment(): bool
    {
        return in_array($this->role, ['admin', 'editor', 'commenter']);
    }

    public function visits()
    {
        return visits($this)->relation();
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function privacy()
    {
        return $this->hasOne(UserPrivacy::class);
    }

    public function getPrivacy(): UserPrivacy
    {
        return $this->privacy ?? UserPrivacy::create(array_merge(
            UserPrivacy::defaults(),
            ['user_id' => $this->id]
        ));
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (config('Nexsus Tracker.disable_random_user_ids') != 'true') {
                if (is_null(User::first())) {
                    $user->id = 1;
                } else {
                    $numberOfDigits = config('Nexsus Tracker.user_id_length') ?? 6;
    
                    $minIdValue = 10**($numberOfDigits - 1);
                    $maxIdValue = 10**$numberOfDigits - 1;
    
                    do {
                        $randomId = rand($minIdValue, $maxIdValue);
                    } while (User::find($randomId));
    
                    $user->id = $randomId;
                }
            }
        });
    }
}
