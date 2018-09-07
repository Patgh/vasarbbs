<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadHandler;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function __construct()
    {
        // except 方法来设定 指定动作 不使用 Auth 中间件进行过滤
        // 首选 except 方法，这样的话，当你新增一个控制器方法时，默认是安全的，此为最佳实践。
        $this->middleware('auth', ['except'=> ['show']]);
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user, ImageUploadHandler $uploadHandler)
    {
        //dd($request->avatar);
        //$user->update($request->all());
        $this->authorize('update', $user);
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
