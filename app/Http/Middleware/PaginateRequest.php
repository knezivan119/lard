<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaginateRequest
{
    public function handle( Request $request, Closure $next ): Response
    {
        $pagination = $request->input( 'pagination', [] );
        $new_request = $request->except( 'pagination' );

        $request->replace( $new_request );
        $request->merge( $pagination );

        return $next( $request );
    }
}
