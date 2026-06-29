<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class ForceUppercase
{
    public function handle($request, Closure $next)
    {
        $inputs = $request->all();

        array_walk_recursive($inputs, function (&$value, $key) {
            if (is_string($value)) {
                $lowerKey = mb_strtolower($key);
                if (
                    !in_array($lowerKey, ['password', 'password_confirmation', '_token', '_method', 'email', 'correo'], true)
                    && !str_contains($lowerKey, 'correo')
                    && !str_contains($lowerKey, 'email')
                ) {
                    $value = Str::upper($value);
                }
            }
        });

        $request->replace($inputs);

        return $next($request);
    }
}
