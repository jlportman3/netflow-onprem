<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpAddressCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $fromIp = ip2long($request->ip());

        // Docker internal network address space
        $minIp = ip2long("172.30.68.0");
        $maxIp = $minIp + 255;

        if ($fromIp > $minIp && $fromIp < $maxIp) {
            return $next($request);
        }
        // TODO: LW Remove development address
        if ($request->ip() == "192.168.3.18") {
            return $next($request);
        }

        return abort(403, "Unauthorized");
    }
}
