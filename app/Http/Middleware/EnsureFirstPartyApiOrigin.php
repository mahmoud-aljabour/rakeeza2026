<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureFirstPartyApiOrigin
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->headers->get('Origin') && ! $request->headers->get('Referer')) {
            $request->headers->set('Origin', $request->getSchemeAndHttpHost());
        }

        return $next($request);
    }
}
