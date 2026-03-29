<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RateLimitingMiddleware
{
    private const LIMITS = [
        'auth'   => ['maxAttempts' => 10,  'decaySeconds' => 60],
        'api'    => ['maxAttempts' => 60,  'decaySeconds' => 60],
        'public' => ['maxAttempts' => 30,  'decaySeconds' => 60],
    ];

    public function __construct(
        private readonly RateLimiter $limiter,
    ) {}

    /**
     * @param  string  $tier  — 'auth' | 'api' | 'public'
     */
    public function handle(Request $request, Closure $next, string $tier = 'api'): Response
    {
        $config = self::LIMITS[$tier] ?? self::LIMITS['api'];
        $key    = $this->resolveKey($request, $tier);

        if ($this->limiter->tooManyAttempts($key, $config['maxAttempts'])) {
            return $this->buildThrottledResponse($key, $config['maxAttempts']);
        }

        $this->limiter->hit($key, $config['decaySeconds']);

        $response    = $next($request);
        $remaining   = max(0, $config['maxAttempts'] - $this->limiter->attempts($key));
        $resetAt     = $this->limiter->availableIn($key);

        return $response->withHeaders([
            'X-RateLimit-Limit'     => (string) $config['maxAttempts'],
            'X-RateLimit-Remaining' => (string) $remaining,
            'X-RateLimit-Reset'     => (string) (now()->timestamp + $resetAt),
        ]);
    }

    private function resolveKey(Request $request, string $tier): string
    {
        $ip = $request->ip() ?? 'unknown';

        if ($tier === 'api' && $request->user()) {
            return Str::lower("rl:{$tier}:user:{$request->user()->id}");
        }

        return Str::lower("rl:{$tier}:ip:{$ip}");
    }

    private function buildThrottledResponse(string $key, int $maxAttempts): Response
    {
        $retryAfter = $this->limiter->availableIn($key);

        return response()->json([
            'success' => false,
            'message' => 'Terlalu banyak permintaan. Silakan coba lagi dalam ' . $retryAfter . ' detik.',
        ], Response::HTTP_TOO_MANY_REQUESTS, [
            'Retry-After'           => (string) $retryAfter,
            'X-RateLimit-Limit'     => (string) $maxAttempts,
            'X-RateLimit-Remaining' => '0',
            'X-RateLimit-Reset'     => (string) (now()->timestamp + $retryAfter),
        ]);
    }
}
