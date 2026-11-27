<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    public function notify(User $user, string $title, string $message, ?int $shipmentId = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'shipment_id' => $shipmentId,
            'title' => $title,
            'message' => $message,
        ]);
    }

    public function getForUser(User $user): Collection
    {
        return $user->notifications()->latest()->get();
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->update(['is_read' => true]);
    }
}

