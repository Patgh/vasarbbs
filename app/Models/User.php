<?php

namespace App\Models;

use App\Models\Traits\ActiveUserHelper;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    use ActiveUserHelper;

    use Notifiable {
        notify as protected laravelNotify;
    }

    public function notify($instance)
    {
        // 作者本人就不需要通知了
        if ($this->id === \Auth::id()) {
            return;
        }

        $this->increment('notification_count');
        $this->laravelNotify($instance);
    }

    // 标记已读
    public function markAsRead()
    {
        $this->notification_count = 0;
        $this->save();

        $this->unreadNotifications->markAsRead();
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'introduction'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function isAuthorOf($model)
    {
        return $this->id === $model->user_id;
    }

    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    // 修改器
    public function setPasswordAttribute($value)
    {
        if (strlen($value) != 60) {
            $value = bcrypt($value);
        }

        $this->attributes['password'] = $value;
    }

    public function setAvatarAttribute($path)
    {
        if (!starts_with($path, 'http')) {
            $path = config('app.url') . "/uploads/images/avatar/{$path}";
        }

        return $this->attributes['avatar'] = $path;
    }
}
