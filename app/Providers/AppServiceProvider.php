<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\OpnameSession;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            $globalSessions = [];
            
            // Check if table exists to prevent crash on migrate/fresh install
            if (Schema::hasTable('opname_sessions')) {
                // Tampilkan semua sesi (termasuk yang CLOSED) agar user bisa melihat histori/tracking sesi lama
                $globalSessions = OpnameSession::orderBy('created_at', 'desc')->take(20)->get();
            }
            
            $activeSessionId = session('active_opname_session_id');

            $view->with('globalSessions', $globalSessions)
                 ->with('activeSessionId', $activeSessionId);
        });
    }
}
