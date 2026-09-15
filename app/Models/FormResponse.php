<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormResponse extends Model
{
    protected $fillable = [
        'form_id',
        'session_id',
        'ip_hash',
        'email',
        'answers',
        'referrer',
        'user_agent',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function getAnswerForField($fieldLabel)
    {
        return $this->answers[$fieldLabel] ?? null;
    }
}
