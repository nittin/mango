<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pin extends Model
{
    protected $table = 'user_pin';
    protected $fillable = [
        'user',
        'type',
        'lat',
        'lng',
        'deleted'
    ];
    public $timestamps = true;
    protected $hidden = ['created_at', 'deleted'];
    protected $appends = ['photo'];
    protected $casts = [
        'id' => 'string',
        'photo' => 'string',
        'updated_at' => 'datetime',
        'lat' => 'float',
        'lng' => 'float'
    ];
    public function getPhotoAttribute()
    {
        return "https://".$_SERVER['HTTP_HOST']."/back/assets/users/origin/{$this->user}.jpg";
    }
}
