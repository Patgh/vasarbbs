<?php

namespace App\Observers;

use App\Models\Topic;

// creating, created, updating, updated, saving,
// saved,  deleting, deleted, restoring, restored

class TopicObserver
{
    public function saving(Topic $topic)
    {
        // xss 过滤
        $topic->body = clean($topic->body, 'user_topic_body');

        // 摘要
        $topic->excerpt = make_excerpt($topic->body);
    }

    public function creating(Topic $topic)
    {
        //
    }

    public function updating(Topic $topic)
    {
        //
    }
}