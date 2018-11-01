<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notification';
    protected $dateFormat = 'U';
    protected $fillable = [
        'user',
        'channel',
        'template',
        'mention',
        'meaning',
        'status'
    ];
    protected $hidden = ['user'];
    public $timestamps = true;
}
