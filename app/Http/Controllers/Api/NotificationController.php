<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    
    public function index(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'notifications' => $user->notifications()->latest()->get(),
            'unreadCount' => $user->unreadNotifications()->count()
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All marked as read']);
    }
}
