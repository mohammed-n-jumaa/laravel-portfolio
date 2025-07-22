<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'icon',
        'technologies',
    ];

    protected $dates = ['deleted_at'];
    
    // Cast technologies to array when retrieving from database
    protected $casts = [
        'technologies' => 'array',
    ];
    
    // This will automatically convert the technologies array to JSON when saving
    public function setTechnologiesAttribute($value)
    {
        $this->attributes['technologies'] = is_array($value) ? json_encode($value) : $value;
    }
}