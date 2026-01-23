<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Rate limit para navegação geral da bilheteria (60/min)
        RateLimiter::for('bilheteria', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limit específico para criação de vendas (15/min)
        RateLimiter::for('bilheteria-sales', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();

            return Limit::perMinute(15)
                ->by($key)
                ->response(function (Request $request, array $headers) use ($key) {
                    Log::warning('Rate limit exceeded for ticket sales', [
                        'user_id' => $request->user()?->id,
                        'ip' => $request->ip(),
                        'key' => $key,
                        'retry_after' => $headers['Retry-After'] ?? null,
                    ]);

                    return response()->json([
                        'message' => 'Muitas vendas em pouco tempo. Aguarde alguns segundos e tente novamente.',
                    ], 429, $headers);
                });
        });
    }
}
