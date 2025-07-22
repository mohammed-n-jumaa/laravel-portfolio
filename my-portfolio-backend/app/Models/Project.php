<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'image',
        'project_url',
        'code_url',
        'technologies',
        'category',
    ];

    protected $dates = ['deleted_at'];
}