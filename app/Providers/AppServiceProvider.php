<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use App\Http\Responses\RegisterResponse;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
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
        if (! isset($_SERVER['HTTP_HOST'])) {
            return;
        }

        $hotFilePath = public_path('hot');
        $hotFile = self::shouldUseViteHotFileForRequest(
            request()->getHost(),
            is_file($hotFilePath) ? file_get_contents($hotFilePath) : null,
        )
            ? $hotFilePath
            : storage_path('framework/vite.hot.disabled');

        Vite::useHotFile($hotFile);
    }

    /**
     * Determine whether the current request should honor the Vite hot file contents.
     */
    public static function shouldUseViteHotFileForRequest(?string $requestHost, ?string $hotFileContents): bool
    {
        if (! self::shouldUseViteHotFileForHost($requestHost)) {
            return false;
        }

        $hotFileHost = self::extractViteHotFileHost($hotFileContents);

        if ($hotFileHost === null) {
            return true;
        }

        return self::hostsMatchForVite($requestHost, $hotFileHost);
    }

    /**
     * Determine whether the current request host should honor the Vite hot file.
     */
    public static function shouldUseViteHotFileForHost(?string $host): bool
    {
        $normalizedHost = self::normalizeHost($host);

        if ($normalizedHost === null) {
            return false;
        }

        return in_array($normalizedHost, ['localhost', '127.0.0.1', '::1'], true)
            || str_ends_with($normalizedHost, '.test')
            || str_ends_with($normalizedHost, '.localhost')
            || str_ends_with($normalizedHost, '.local');
    }

    /**
     * Extract the dev server host from a Vite hot file URL when available.
     */
    protected static function extractViteHotFileHost(?string $hotFileContents): ?string
    {
        if (! is_string($hotFileContents)) {
            return null;
        }

        $trimmedContents = trim($hotFileContents);

        if ($trimmedContents === '') {
            return null;
        }

        $host = parse_url($trimmedContents, PHP_URL_HOST);

        return self::normalizeHost(is_string($host) ? $host : null);
    }

    /**
     * Determine whether the request host and Vite hot file host refer to the same local endpoint.
     */
    protected static function hostsMatchForVite(?string $requestHost, ?string $hotFileHost): bool
    {
        $normalizedRequestHost = self::normalizeHost($requestHost);
        $normalizedHotFileHost = self::normalizeHost($hotFileHost);

        if ($normalizedRequestHost === null || $normalizedHotFileHost === null) {
            return false;
        }

        if ($normalizedRequestHost === $normalizedHotFileHost) {
            return true;
        }

        $loopbackHosts = ['localhost', '127.0.0.1', '::1'];

        return in_array($normalizedRequestHost, $loopbackHosts, true)
            && in_array($normalizedHotFileHost, $loopbackHosts, true);
    }

    /**
     * Normalize request and Vite dev server hosts for safe comparison.
     */
    protected static function normalizeHost(?string $host): ?string
    {
        if (! is_string($host)) {
            return null;
        }

        $normalizedHost = strtolower(trim($host, '[] '));

        if ($normalizedHost === '') {
            return null;
        }

        return $normalizedHost;
    }
}
