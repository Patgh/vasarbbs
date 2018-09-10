<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Reply;

class ReplyPolicy extends Policy
{
    public function update(User $user, Reply $reply)
    {
        // return $reply->user_id == $user->id;
        return $user->isAuthorOf($reply);
    }

    public function destroy(User $user, Reply $reply)
    {
        // 回复人可以删除或者 话题作者也可以删除
        return $user->isAuthorOf($reply) || $user->isAuthorOf($reply->topic);
    }
}
