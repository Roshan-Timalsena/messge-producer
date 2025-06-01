<?php

namespace App\Http\Controllers;

use App\Jobs\PublishNotificationJob;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function publish(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'message' => 'required|string|max:255'
        ]);

        $notification = Notification::create($data);

        PublishNotificationJob::dispatch($notification);

        return response()->json([
            'message' => 'Notification queued',
            'id' => $notification->id
        ], 201);
    }
}
