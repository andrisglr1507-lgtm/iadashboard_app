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
                $globalSessions = OpnameSession::whereNotIn('status', ['closed'])->orderBy('created_at', 'desc')->get();
            }
            
            $activeSessionId = session('active_opname_session_id');

            $view->with('globalSessions', $globalSessions)
                 ->with('activeSessionId', $activeSessionId);
        });
    }
}
