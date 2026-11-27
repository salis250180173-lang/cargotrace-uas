<?php

namespace App\Providers;

use App\Models\Shipment;
use App\Policies\ShipmentPolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::policy(Shipment::class, ShipmentPolicy::class);

        View::composer('layouts.app', function ($view): void {
            $user = Auth::user();
            if ($user) {
                $unreadCount = $user->notifications()->where('is_read', false)->count();
                $recentNotifications = $user->notifications()->latest()->take(5)->get();
            } else {
                $unreadCount = 0;
                $recentNotifications = collect();
            }

            $view->with([
                'headerUnreadCount' => $unreadCount,
                'headerNotifications' => $recentNotifications,
            ]);
        });
    }
}
