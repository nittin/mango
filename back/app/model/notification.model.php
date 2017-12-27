<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    const CREATED_AT = 'date';
    const UPDATED_AT = 'date';
    protected $table = 'notification';
    protected $dateFormat = 'U000';
    protected $fillable = [
        'user',
        'type',
        'content',
        'from',
        'status'
    ];
    public $timestamps = true;
}
