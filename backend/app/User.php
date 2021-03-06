<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'slug', 'email', 'password', 'description', 'avatar', 'joined_at', 'privilege'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function category()
    {
        return $this->hasOne('App\Category', 'moderator');
    }

    public function posts()
    {
        return $this->hasMany('App\Post', 'author_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Comment');
    }

    public function votes()
    {
        return $this->belongsToMany('App\Comment', 'votes', 'user_id', 'comment_id');
    }

    public function recommendations()
    {
        return $this->belongsToMany(
            'App\Post', 'recommendations', 'user_id', 'post_id'
        );
    }

    # 0 banned user    # 1 regular user     # 2 author
    # 3 moderator      # 4 admin
    public function role()
    {
        switch ($this->privilege) {
            case 0:
                return 'banned user';
            case 1:
                return 'regular user';
            case 2:
                return 'author';
            case 3:
                return 'moderator';
            case 4:
                return 'admin';
            default:
                # AFAIK: this couldn't be reached.
                return 'unknown';
        }
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function isAdmin()
    {
        return $this->privilege == 4;
    }

    public function isBanned()
    {
        return $this->privilege == 0;
    }

    public function isModerator($category_id)
    {
        if ($this->privilege != 3 || $category_id == null) {
            return false;
        }

        $category = Category::find($category_id);
        if (empty($category) || $category->moderator != $this->id) {
            return false;
        }

        return true;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
