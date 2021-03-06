<?php

use Illuminate\Database\Seeder;
use App\Models\Topic;

class TopicsTableSeeder extends Seeder
{
    public function run()
    {
        // 获取用户ID
        $user_ids = \App\Models\User::all()->pluck('id')->toArray();

        // 分类ID
        $category_ids = \App\Models\Category::all()->pluck('id')->toArray();

        $faker = app(Faker\Generator::class);

        $topics = factory(Topic::class)->times(100)->make()->each(function ($topic, $index) use ($faker, $user_ids, $category_ids) {
            $topic->user_id = $faker->randomElement($user_ids);
            $topic->category_id = $faker->randomElement($category_ids);
        });

        Topic::insert($topics->toArray());
    }

}

