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
    protected $dateFormat = 'U';
    protected $hidden = ['pivot'];
    protected $appends = ['photo'];
    protected $casts = [
        'photo' => 'string',
    ];
    public $timestamps = true;
    public function getPhotoAttribute()
    {
        return "http://".$_SERVER['HTTP_HOST']."/back/assets/users/origin/{$this->id}.jpg";
    }
    public function notifications()
    {
        return $this->hasMany('App\Models\Notification', 'user', 'id');
    }
}
