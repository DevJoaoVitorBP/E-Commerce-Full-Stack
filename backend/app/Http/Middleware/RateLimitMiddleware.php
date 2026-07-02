<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Instância do limitador de taxa
     */
    protected RateLimiter $limiter;

    /**
     * Máximo de requisições por minuto
     */
    protected const MAX_REQUESTS = 100;

    /**
     * Janela de tempo em segundos (1 minuto)
     */
    protected const TIME_WINDOW = 60;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Lida uma solicitação HTTP e aplica o limite de taxa.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Use o endereço IP como chave para o limite de taxa
        $key = 'rate_limit:'.$request->ip();

        // Incrementa a contagem de requisições
        $requests = $this->limiter->attempts($key);

        // Se for a primeira requisição, define a expiração
        if ($requests === 1) {
            $this->limiter->hit($key, self::TIME_WINDOW);
        }

        // Verifica se o limite foi excedido
        if ($requests > self::MAX_REQUESTS) {
            return response()->json([
                'success' => false,
                'message' => 'Limite de requisições excedido. Máximo de '.self::MAX_REQUESTS.' requisições por minuto.',
            ], 429)->header('Retry-After', self::TIME_WINDOW);
        }

        $response = $next($request);

        // Adiciona os cabeçalhos de limite de taxa
        return $response
            ->header('X-RateLimit-Limit', self::MAX_REQUESTS)
            ->header('X-RateLimit-Remaining', max(0, self::MAX_REQUESTS - $requests))
            ->header('X-RateLimit-Reset', now()->addSeconds(self::TIME_WINDOW)->timestamp);
    }
}
