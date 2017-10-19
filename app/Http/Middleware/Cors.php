<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class Cors
{
    public function handle($request, Closure $next)
    {
        // @todo: Check if it's safe to remove this conditional statement
        if ($request->isMethod('OPTIONS')) {
            $response = new Response("", 200);
        }
        else {
            $response = $next($request);
        }

        // only set headers if response is not audio
        if(strpos($response->headers->get('content-type'), 'audio') === false) {
            header('Access-Control-Allow-Origin: *');
            //->header('Access-Control-Max-Age', (60 * 60 * 24))
            header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }


        return $response;
    }
}
