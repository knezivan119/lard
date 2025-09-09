<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Route;

use Tests\TestCase;

class AuthorisationApiTest extends TestCase
{
    public function test_endpointsFailIfUnauthorised(): void
    {
        // $this->markTestSkipped('Temporary skipped till figuring out Record update');

        $routes = Route::getRoutes();
        $skip = [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ];

        // $this->assertTrue( true );
        // return;
        // dd( $routes);

        foreach ( $routes as $route ) {
            $only_api = preg_match( '/^api\//', $route->uri() );
            $has_methods = array_intersect( $skip, $route->methods() );
            $uses_sanctum = in_array( 'auth:sanctum', $route->middleware() );

            if ( !( $only_api && $has_methods && $uses_sanctum ) ) {
                continue;
            }

            $method = strtolower( $route->methods()[0] );

            $response = $this->$method( '/' . $route->uri() );

            try {
                $response->assertStatus( 401 );
            }
            catch ( \Exception $e ) {
                $this->fail( "Failed: {$method} /{$route->uri()}" );
            }

        }
    }
}
