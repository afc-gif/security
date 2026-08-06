<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markRead(Request $request, AdminNotification $notification)
    {
        if ((int) $notification->user_id !== (int) $request->user()->id) {
            abort(403);
        }

        $notification->update([
            'read_at' => now(),
        ]);

        return back()->with('success', 'Notification marked as read.');
    }
}
