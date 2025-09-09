<?php

namespace Tests\Feature;

use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

use Laravel\Sanctum\Sanctum;

use Tests\TestCase;

class AuthorisationApiTest extends TestCase
{
    use RefreshDatabase;

    /** Adjust these if your routes differ */
    private string $issueTokenUrl = '/api/v1/auth/login';
    private string $currentUrl    = '/api/v1/user';

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


    public function test_issueTokenValidatesRequiredFields( )
    {
        $response = $this->postJson( $this->issueTokenUrl, [ ] );

        $response->assertStatus( 422 )
                 ->assertJsonValidationErrors( [ 'email', 'password', 'device_name' ] );
    }

    public function test_issueTokenRejectsInvalidCredentials( )
    {
        $user = User::factory( )->create( [
            'email'    => 'jane@example.com',
            'password' => Hash::make( 'correct-horse' ),
        ] );

        $payload = [
            'email'       => 'jane@example.com',
            'password'    => 'wrong-battery',
            'device_name' => 'macbook-pro',
        ];

        $this->postJson( $this->issueTokenUrl, $payload )
             ->assertStatus( 422 )
             ->assertJson( [ 'message' => 'Invalid credentials' ] );
    }

    public function test_issueTokenReturnsTokenAndStoresPersonalAccessToken( )
    {
        $user = User::factory( )->create( [
            'email'    => 'jane@example.com',
            'password' => Hash::make( 'secret-123' ),
        ] );

        $payload = [
            'email'       => 'jane@example.com',
            'password'    => 'secret-123',
            'device_name' => 'macbook-pro',
        ];

        $response = $this->postJson( $this->issueTokenUrl, $payload )
                         ->assertOk( )
                         ->assertJsonStructure( [ 'token' ] );

        $token = $response->json( 'token' );

        $this->assertIsString( $token );
        $this->assertStringContainsString( '|', $token, 'Sanctum tokens are "id|plaintext".' );

        $this->assertDatabaseHas( 'personal_access_tokens', [
            'tokenable_id'   => $user->id,
            'tokenable_type' => User::class,
            'name'           => 'macbook-pro',
        ] );
    }

    public function test_currentRequiresAuthentication( )
    {
        $this->getJson( $this->currentUrl )->assertStatus( 401 );
    }

    public function test_currentReturnsAuthenticatedUserResource( )
    {
        $user = User::factory( )->create( [
            'email'    => 'jane@example.com',
            'password' => Hash::make( 'secret-123' ),
        ] );

        Sanctum::actingAs( $user );   /** or attach a token via Authorization header */

        $this->getJson( $this->currentUrl )
             ->assertOk( )
             ->assertJsonFragment( [
                 'id'    => $user->id,
                 'email' => 'jane@example.com',
             ] );  /** Adjust fields to match your UserCurrentResource */
    }
}
