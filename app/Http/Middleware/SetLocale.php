<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\AppLocale;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale', AppLocale::default());

        if (! is_string($locale) || ! AppLocale::isSupported($locale)) {
            $locale = AppLocale::default();
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
