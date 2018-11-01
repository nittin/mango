<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'group';

    protected $fillable = [
        'name',
        'owner',
        'type'
    ];

    public $timestamps = true;
    protected $casts = [
        'id' => 'string'
    ];
    public function members()
    {
        return $this->belongsToMany('App\Models\User', 'user_group', 'group', 'user')
            ->withPivot('role', 'status');
    }
    public function posts()
    {
        return $this->hasMany('App\Models\Event', 'group', 'id');
    }

}
