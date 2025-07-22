<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExperienceSection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'subtitle',
    ];

    protected $dates = ['deleted_at'];
}