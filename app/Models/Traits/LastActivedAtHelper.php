<?php

namespace App\Models\Traits;

use Carbon\Carbon;
use Redis;

trait LastActivedAtHelper
{
    protected $hash_prefix = "last_actived_at:";
    protected $field_prefix = 'user:';

    public function recordLastActivedAt()
    {
        // 获取今天的日期
        //$date = Carbon::now()->toDateString();

        $hash = $this->getHashFromDateString(Carbon::now()->toDateString());

        //dd(Redis::hGetAll($hash));
        // 字段名称，如：user:1
        $field = $this->getHashField();

        // 当前时间，如：2017-10-21 08:35:15
        $now = Carbon::now()->toDateTimeString();

        // 数据写入 Redis ，字段已存在会被更新
        Redis::hSet($hash, $field, $now);
    }

    // 同步到数据库中
    public function syncUserActivedAt()
    {
        // 获取昨天的日期
        $yesterday_date = Carbon::now()->toDateString(); // test
        //$yesterday_date = Carbon::yesterday()->toDateString();

        // 取出数据
        $hash = $this->getHashFromDateString($yesterday_date);
        $data = Redis::hGetAll($hash);

        // 遍历，并同步到数据库中
        foreach ($data as $user_id => $actived_at) {
            // 获取用户id
            $user_id = str_replace($this->field_prefix, '', $user_id);

            // 当用户存在时候更新到数据库
            if ($user = $this->find($user_id)) {
                $user->update([
                    'last_actived_at' => $actived_at,
                ]);
            }
        }

        // 以数据库为中心的存储，既已同步，即可删除
        Redis::del($hash);
    }

    // 访问器 last_actived_at
    public function getLastActivedAtAttribute($value)
    {
        // 获取今天的日期
        $date = Carbon::now()->toDateString();

        // Redis 哈希表的命名 vasarbbs:last_actived_at:2017-10-21
        $hash = $this->getHashFromDateString($date);

        //dd(Redis::hGetAll($hash));
        // 字段名称，如：user:1
        $field = $this->getHashField();

        $datetime = Redis::hGet($hash, $field) ?: $value;

        if ($datetime) {
            return new Carbon($datetime);
        } else {
            // 否则使用用户注册时间
            return $this->created_at;
        }
    }

    public function getHashFromDateString($date)
    {
        // Redis 哈希表的命名 vasarbbs:last_actived_at:2017-10-21
        return $this->hash_prefix . $date;
    }

    public function getHashField()
    {
        // 字段名称，如：user:1
        return $this->field_prefix . $this->id;
    }
}