<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                // Remove extra spaces
                $value = trim($value);
                // Strip HTML tags and encode special characters
                $value = htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
            }
        });

        // Merge sanitized input back into the request
        $request->merge($input);

        return $next($request);
    }
}
