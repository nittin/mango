<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    const CREATED_AT = 'date';
    const UPDATED_AT = 'date';
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
