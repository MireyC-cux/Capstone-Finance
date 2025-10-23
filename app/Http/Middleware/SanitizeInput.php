<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        $reserved = ['_token', '_method'];
        $sanitize = function (&$value, $key) use ($reserved) {
            if (in_array($key, $reserved, true)) {
                return;
            }
            if (is_string($value)) {
                $value = trim($value);
                $value = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
            }
        };

        array_walk_recursive($input, $sanitize);

        // Merge sanitized input back into the request
        $request->merge($input);

        return $next($request);
    }
}
