<?php

namespace App\Handlers;

// Laravel 5.5 自带了 扩展包发现 ，所以不需要手动添加 Provider。
use Image;

/**
 * 图片上传处理器
 * Class ImageUploadHandler
 * @package App\Handlers
 */
class ImageUploadHandler
{
    //
    protected $allow_ext = ['png', 'jpg', 'jpeg', 'gif'];

    public function save($file, $folder, $file_prefix, $max_width = false)
    {

        // 值如：uploads/images/avatars/201709/21/
        $folder_name = "uploads/images/{$folder}/" . date("Ym/d", time());

        $upload_path = public_path() . '/' . $folder_name;

        $extension = strtolower($file->getClientOriginalExtension()) ?: 'png';

        // 值如：1_1493521050_7BVc9v9ujP.png
        $filename = $file_prefix . '_' . time() . '_' . str_random(10) . '.' . $extension;

        if (!in_array($extension, $this->allow_ext)) {
            return false;
        }

        $file->move($upload_path, $filename);

        if ($max_width && $extension !== 'gif') {
            $this->reduceSize($upload_path . '/' .$filename, $max_width);
        }

        return [
            'path' => config('app.url') . '/' .$folder_name . '/' . $filename,
        ];

    }


    public function reduceSize($file_path, $max_width)
    {

        $image = Image::make($file_path);


        $image->resize($max_width, null, function ($constraint) {
            // 设定宽度是 $max_width，高度等比例双方缩放
            $constraint->aspectRatio();

            // 防止裁图时图片尺寸变大
            $constraint->upsize();
        });

        $image->save();
    }

}
