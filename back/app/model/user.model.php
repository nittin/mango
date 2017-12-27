<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    const CREATED_AT = 'date';
    const UPDATED_AT = 'date';

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
    protected $dateFormat = 'U000';
    protected $hidden = ['pivot'];
    public $timestamps = true;

    public function notifications()
    {
        return $this->hasMany('App\Models\Notification', 'user', 'id');
    }
}
