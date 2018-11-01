<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'group_event';
    protected $fillable = [
        'group',
        'owner',
        'name',
        'type',
        'lat',
        'lng',
        'expire',
        'address'
    ];
    public $timestamps = true;
}
