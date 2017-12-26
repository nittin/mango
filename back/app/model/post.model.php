<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    const CREATED_AT = 'date';
    const UPDATED_AT = 'date';
    protected $table = 'group_post';
    protected $dateFormat = 'U000';
    protected $fillable = [
        'user',
        'description',
        'lat',
        'lng',
        'expire'
    ];
    public $timestamps = true;
}
