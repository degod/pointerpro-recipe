<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'cuisine_type',
        'ingredients',
        'steps',
        'picture'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
