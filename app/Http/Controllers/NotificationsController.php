<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(20);
        // 标记已读
        $request->user()->markAsRead();
        return view('notifications.index', compact('notifications'));
    }
}
