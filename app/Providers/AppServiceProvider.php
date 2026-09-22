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
        // Only force https for the canonical production domain, which sits behind
        // an external proxy that terminates TLS (no cert on this box — see
        // DEPLOYMENT.md). Requests that reach nginx directly over plain HTTP
        // (e.g. the internal 192.168.40.112 vhost) must keep their real scheme,
        // otherwise asset/canonical URLs point at a https:// port nothing listens on.
        $productionHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        if ($productionHost && request()->getHost() === $productionHost) {
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
