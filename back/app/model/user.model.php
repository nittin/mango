<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $table = 'user';
    protected $fillable = [
        'id',
        'name',
        'first_name',
        'status',
        'device'
    ];
    protected $hidden = ['pivot', 'created_at', 'updated_at'];
    protected $appends = ['photo', 'pin'];
    protected $casts = [
        'id' => 'string',
        'photo' => 'string',
    ];
    public $timestamps = true;
    public function getPhotoAttribute()
    {
        return "https://".$_SERVER['HTTP_HOST']."/back/assets/users/origin/{$this->id}.jpg";
    }
    public function getPinAttribute()
    {
        return $this->pins()
            ->where(['type' => 'normal', 'deleted' => '0'])
            ->latest('updated_at')
            ->first();
    }
    public function notifications()
    {
        return $this->hasMany('App\Models\Notification', 'user', 'id');
    }
    public function pins()
    {
        return $this->hasMany('App\Models\Pin', 'user', 'id');
    }
    public function friends()
    {
        return $this->belongsToMany('App\Models\User', 'user_friend', 'user1', 'user2')
            ->withTimestamps()
            ->withPivot('status');
    }
    public function trueFriends()
    {
        return $this->friends()->wherePivot('status', 1);
    }
    public function isWaitForFriend($id)
    {
        return $this->belongsToMany('App\Models\User', 'user_friend', 'user1', 'user2')
            ->where('user2', $id)
            ->wherePivot('status', 2)
            ->count() > 0;
    }
}
