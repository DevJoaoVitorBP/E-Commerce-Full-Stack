<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConvertMethodInFormData
{
    /**
     * Lida uma solicitação HTTP e converte o método se necessário.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // PHP não parseia multipart/form-data em PUT/PATCH/DELETE
        // Quando chegar POST com _method, simular o método para o FormRequest
        if ($request->isMethod('post') && $request->has('_method')) {
            $method = strtoupper($request->input('_method'));
            $request->setMethod($method);
        }

        return $next($request);
    }
}
