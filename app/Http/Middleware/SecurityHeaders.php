<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. إزالة X-Powered-By قبل إرسال الاستجابة
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        /** @var Response $response */
        $response = $next($request);

        // 2. ضبط سياسة CSP كاملة
        $cspPolicy = "default-src 'self'; script-src 'self'; object-src 'none'; frame-ancestors 'self'; form-action 'self';";

        // 3. إضافة الهيدرز للـ Response مباشرة
        $response->headers->set('Content-Security-Policy', $cspPolicy);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // إزالة الهيدر من كائن الـ Response الخاص بـ Symfony
        $response->headers->remove('X-Powered-By');

        return $response;
    }
}