<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hero extends Model
{
    use HasFactory;
protected $table = 'hero'; 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'title',
        'description',
        'experience_months',
        'tech_stack',
        'profile_image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'experience_months' => 'integer',
    ];

    /**
     * Get the tech stack as an array.
     *
     * @param  string  $value
     * @return array
     */
    public function getTechStackAttribute($value)
    {
        return json_decode($value, true) ?: [];
    }
}