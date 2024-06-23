<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function userData()
    {
        return $this->hasOne(UserData::class, 'user_id');
    }

    public function friendships()
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    // public function friends()
    // {
    //     return $this->hasMany(Friendship::class, 'friend_id');
    // }

    public function friends()
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id')
            ->wherePivot('estado', 'aceptado')
            ->orWhere(function ($query) {
                $query->where('friend_id', $this->id)
                    ->where('estado', 'aceptado');
            });
    }
}
