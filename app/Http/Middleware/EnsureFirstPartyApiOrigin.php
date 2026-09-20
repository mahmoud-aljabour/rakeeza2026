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
        $origin = $request->headers->get('Origin');
        $referer = $request->headers->get('Referer');

        if (! is_string($origin) || $origin === '') {
            $origin = $this->originFromReferer(is_string($referer) ? $referer : null);
        }

        if ($origin === null || ! $this->isTrustedOrigin($origin, $request)) {
            return response()->json([
                'message' => 'Invalid request origin.',
            ], 403);
        }

        $request->headers->set('Origin', $origin);

        return $next($request);
    }

    private function originFromReferer(?string $referer): ?string
    {
        if ($referer === null || $referer === '') {
            return null;
        }

        $parts = parse_url($referer);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $origin = $parts['scheme'].'://'.$parts['host'];

        if (isset($parts['port'])) {
            $origin .= ':'.$parts['port'];
        }

        return $origin;
    }

    private function isTrustedOrigin(string $origin, Request $request): bool
    {
        $parts = parse_url($origin);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return false;
        }

        if (! in_array(strtolower((string) $parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower((string) $parts['host']);

        if (isset($parts['port'])) {
            $host .= ':'.$parts['port'];
        }

        $allowedHosts = collect(config('sanctum.stateful', []))
            ->push($request->getHttpHost())
            ->filter(static fn (mixed $value): bool => is_string($value) && $value !== '')
            ->map(static fn (string $value): string => strtolower($value))
            ->unique()
            ->values()
            ->all();

        return in_array($host, $allowedHosts, true);
    }
}
