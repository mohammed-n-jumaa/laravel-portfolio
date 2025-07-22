<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceSection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'subtitle',
        'info_text',
    ];

    protected $dates = ['deleted_at'];
}