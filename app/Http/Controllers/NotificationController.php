<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function unread(Request $request): JsonResponse
    {
        $notifications = Notification::query()
            ->where('company_id', $request->user()->company_id)
            ->where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (Notification $notification): array => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'url' => $notification->data['url'] ?? null,
            ]);

        return response()->json(['notifications' => $notifications]);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        abort_unless(
            (int) $notification->user_id === (int) $request->user()->id
                && (int) $notification->company_id === (int) $request->user()->company_id,
            404,
        );

        $notification->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['message' => 'Notification marked as read.']);
    }
}
