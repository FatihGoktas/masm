<?php

namespace App\Http\Middleware;

use App\Register;
use Closure;

class ClientTokenAuth
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
        if (is_null($request->get("client_token")) || empty($request->get("client_token"))) {
            return response()->json(
                array(
                    "result" => "false",
                    "message" => "missing client_token parameter",
                ), 400
            );
        } else {
            if (!Register::checkByClientToken($request->get("client_token"))) {
                return response()->json(
                    array(
                        "result" => "false",
                        "message" => "unauthorized access",
                    ), 401
                );
            }
        }
        return $next($request);
    }
}
