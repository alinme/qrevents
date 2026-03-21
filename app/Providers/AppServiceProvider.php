<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureRateLimiting();
        $this->configureViteHotFile();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Configure application rate limiters for public album traffic.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('public-album-read', function (Request $request): Limit {
            return Limit::perMinute((int) config('events.public_rate_limits.read_per_minute', 120))
                ->by($this->publicAlbumRateLimitKey($request));
        });

        RateLimiter::for('public-album-write', function (Request $request): Limit {
            return Limit::perMinute((int) config('events.public_rate_limits.write_per_minute', 40))
                ->by($this->publicAlbumRateLimitKey($request));
        });

        RateLimiter::for('public-album-upload', function (Request $request): Limit {
            return Limit::perMinute((int) config('events.public_rate_limits.upload_per_minute', 12))
                ->by($this->publicAlbumRateLimitKey($request));
        });
    }

    /**
     * Build a stable limiter key for public album requests.
     */
    protected function publicAlbumRateLimitKey(Request $request): string
    {
        $shareToken = trim((string) $request->route('shareToken', 'public'));
        $guestToken = trim((string) $request->input('guest_token', ''));
        $ipAddress = (string) $request->ip();

        return implode('|', [
            $shareToken !== '' ? $shareToken : 'public',
            $guestToken !== '' ? $guestToken : 'guest',
            $ipAddress !== '' ? $ipAddress : 'ip',
        ]);
    }

    /**
     * Prevent production hosts from switching to the Vite dev server when a hot file exists.
     */
    protected function configureViteHotFile(): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $hotFile = self::shouldUseViteHotFileForHost(request()->getHost())
            ? public_path('hot')
            : storage_path('framework/vite.hot.disabled');

        Vite::useHotFile($hotFile);
    }

    /**
     * Determine whether the current request host should honor the Vite hot file.
     */
    public static function shouldUseViteHotFileForHost(?string $host): bool
    {
        if (! is_string($host)) {
            return false;
        }

        $normalizedHost = strtolower(trim($host, '[] '));

        if ($normalizedHost === '') {
            return false;
        }

        return in_array($normalizedHost, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($normalizedHost, '.test')
            || str_ends_with($normalizedHost, '.localhost')
            || str_ends_with($normalizedHost, '.local');
    }
}
