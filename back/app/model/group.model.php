<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $table = 'group';

    public $timestamps = false;

    public function members()
    {
        return $this->belongsToMany('App\Models\User', 'user_group', 'group', 'user')
            ->withPivot('role', 'status', 'date');
    }

}
