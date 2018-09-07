<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user, ImageUploadHandler $uploadHandler)
    {
        //dd($request->avatar);
        //$user->update($request->all());

        $data = $request->all();

        if ($file = $request->avatar) {

            $result = $uploadHandler->save($file, 'avatar', $request->user()->id, 362);

            if ($result) {
                $data['avatar'] = $result['path'];
            }
        }
        $user->update($data);
        return redirect()->route('users.show', $user)->with('success', '个人资料更新成功');
    }
}
