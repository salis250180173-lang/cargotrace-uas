<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request): View
    {
        $notifications = $this->notificationService->getForUser($request->user());

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }

        $this->notificationService->markAsRead($notification);

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }
}

