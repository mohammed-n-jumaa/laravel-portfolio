<?php
// app/Models/Skill.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'description'
    ];

    public function skillItems()
    {
        return $this->hasMany(SkillItem::class);
    }
}
