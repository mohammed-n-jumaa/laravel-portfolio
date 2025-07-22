<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Experience extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company',
        'position',
        'start_date',
        'end_date',
        'description',
        'skills',
        'logo',
    ];

    protected $dates = ['deleted_at'];
}