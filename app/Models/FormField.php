<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $fillable = [
        'form_id',
        'label',
        'type',
        'options',
        'is_required',
        'placeholder',
        'default_value',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public static function getValidTypes()
    {
        return [
            'text',
            'email',
            'textarea',
            'select',
            'radio',
            'checkbox',
            'date',
            'number',
            'file',
            'phone',
            'url',
            'hidden',
        ];
    }
}
