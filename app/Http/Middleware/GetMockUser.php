<?php

namespace App\Http\Middleware;

use Closure;

class GetMockUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $field_name = env('MOCK_USER_FIELDNAME', 'g8_user');
        $mock_user = $_COOKIE[$field_name] ?? "";

        $request->attributes->add(['mock_user' => $mock_user]);

        return $next($request);
    }
}
