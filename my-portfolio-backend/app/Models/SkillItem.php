<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillItem extends Model
{
    use HasFactory;

    protected $table = 'skill_items';
    
    protected $fillable = [
        'skill_id',
        'name',
        'category',
        'image',
        'description'
    ];

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}