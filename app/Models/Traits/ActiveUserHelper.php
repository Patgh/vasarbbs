<?php

namespace App\Models\Traits;

use App\Models\Reply;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait ActiveUserHelper
{
    // 保存用户
    protected $user = [];

    protected $topic_weight = 4;
    protected $reply_weight = 1;
    protected $pass_day = 7; // 统计7天内
    protected $user_number = 6; // 取出多少用户

    // cache
    protected $cache_key = "vasarbbs:active:users";
    protected $cache_expire_in_minutes = 65;

    public function getActiveUsers()
    {
        // 优先缓存获取
        return Cache::remember($this->cache_key, $this->cache_expire_in_minutes, function () {
            return $this->calculateActiveUsers();
        });
    }

    public function calculateAndCacheActiveUsers()
    {
        $active_users = $this->calculateActiveUsers();

        $this->cacheActiveUsers($active_users);
    }

    private function cacheActiveUsers($active_users)
    {
        // 将数据放入缓存中
        Cache::put($this->cache_key, $active_users, $this->cache_expire_in_minutes);
    }

    private function calculateActiveUsers()
    {
        $this->calculateTopicScore();
        $this->calculateReplyScore();

        // 处理取出 user_number
        // 先排序
        $users = array_sort($this->user, function ($user) {
            return $user['score'];
        });

        // 再倒序 保持key不变
        $users = array_reverse($users, true);

        // 数组中取出一段, 保持key不变
        $users = array_slice($users, 0, $this->user_number, true);

        // 新建一个空集合
        $active_users = collect();

        foreach ($users as $user_id => $user) {
            // 寻找是否存在用户
            $user = $this->find($user_id);
            if ($user) {
                $active_users->push($user);
            }
        }

        return $active_users;

    }

    private function calculateTopicScore()
    {
        // 从话题数据表里取出限定时间范围（$pass_days）内，有发表过话题的用户
        // 并且同时取出用户此段时间内发布话题的数量
        $topic_users = Topic::query()->select(DB::raw('user_id', 'count(*) as topic_count'))
            ->where('created_at', '>=', Carbon::now()->subDays($this->pass_day))
            ->groupBy('user_id')
            ->get();
        // 计算得分
        foreach ($topic_users as $value)
        {
            $this->user[$value->user_id]['score'] = $value->topic_count * $this->topic_weight;
        }
    }

    private function calculateReplyScore()
    {
        // 从回复数据表里取出限定时间范围（$pass_days）内，有发表过回复的用户
        // 并且同时取出用户此段时间内发布回复的数量
        $reply_users = Reply::query()->select(DB::raw('user_id', 'count(*) as reply_count'))
            ->where('created_at', '>=', Carbon::now()->subDays($this->pass_day))
            ->groupBy('user_id')
            ->get();

        // 计算得分
        foreach ($reply_users as $value) {
            $reply_score = $value->reply_count * $this->reply_weight;

            if (isset($this->user[$value->user_id])) {
                $this->user[$value->user_id]['score'] += $reply_score;
            } else {
                $this->user[$value->user_id]['score'] = $reply_score;
            }
        }
    }

}