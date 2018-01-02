<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'user_token';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'token',
        'scope',
        'expire',
        'environment'
    ];
}
