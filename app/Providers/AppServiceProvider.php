<?php

namespace App\Providers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        if ($this->app->environment('production') || str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Admin-only: full CRUD over Qorboshi/Uzgolon/Literature/Video/Region/Period/
        // MapMarker/TimelineEvent/HistoricalMapLayer/User — enforced in each entity's Policy,
        // this Gate is the coarse-grained check used by admin route middleware.
        Gate::define('manage-content', fn (User $user) => $user->role === Role::Admin);

        // Editor + Admin: blog/comment moderation (approve/reject).
        Gate::define('moderate', fn (User $user) => in_array($user->role, [Role::Editor, Role::Admin], strict: true));
    }
}
