<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    const CREATED_AT = 'date';
    const UPDATED_AT = 'date';
    protected $table = 'group';

    protected $fillable = [
        'name',
        'description',
        'admin',
        'theme'
    ];
    protected $dateFormat = 'U000';

    public $timestamps = true;

    public function members()
    {
        return $this->belongsToMany('App\Models\User', 'user_group', 'group', 'user')
            ->withPivot('role', 'status', 'date');
    }
    public function posts()
    {
        return $this->hasMany('App\Models\Post', 'group', 'id');
    }

}
