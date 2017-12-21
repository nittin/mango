<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'user';
    protected $fillable = [
        'id',
        'name',
        'lat',
        'lng',
        'status',
        'date',
        'device',
        'friends'
    ];
    public $timestamps = false;
}
