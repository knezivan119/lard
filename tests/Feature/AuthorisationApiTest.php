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
        // dd( $routes );
        $skip = [ 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ];

        foreach ( $routes->getRoutes() as $route ) {
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


    public function test_issueTokenValidatesRequiredFields(): void
    {
        $response = $this->postJson( $this->issueTokenUrl, [] );

        $response->assertStatus( 422 )
            ->assertJsonValidationErrors( [ 'email', 'password', 'device_name' ] )
        ;
    }


    public function test_issueTokenRejectsInvalidCredentials(): void
    {
        $user = User::factory()->create( [
            'email'    => 'jane@example.com',
            'password' => Hash::make( 'correct-horse' ),
        ] );

        $payload = [
            'email'       => 'jane@example.com',
            'password'    => 'wrong-battery',
            'device_name' => 'tester',
        ];

        $this->postJson( $this->issueTokenUrl, $payload )
            ->assertStatus( 422 )
            ->assertJson( [ 'message' => 'Invalid credentials' ] )
        ;
    }


    public function test_issueTokenReturnsTokenAndStoresPersonalAccessToken(): void
    {
        $user = User::factory()->create( [
            'email'    => 'jane@example.com',
            'password' => Hash::make( 'secret-123' ),
        ] );

        $payload = [
            'email'       => 'jane@example.com',
            'password'    => 'secret-123',
            'device_name' => 'tester',
        ];

        $response = $this->postJson( $this->issueTokenUrl, $payload )
            ->assertOk()
            ->assertJsonStructure( [ 'token' ] )
        ;

        $token = $response->json( 'token' );

        $this->assertIsString( $token );
        $this->assertStringContainsString( '|', $token, 'Sanctum tokens are "id|plaintext".' );

        $this->assertDatabaseHas( 'personal_access_tokens', [
            'tokenable_id'   => $user->id,
            'tokenable_type' => User::class,
            'name'           => 'tester',
        ] );
    }


    public function test_currentRequiresAuthentication(): void
    {
        $this->getJson( $this->currentUrl )->assertStatus( 401 );
    }


    public function test_currentReturnsAuthenticatedUserResource(): void
    {
        $user = User::factory()->create( [
            'email'    => 'jane@example.com',
            'password' => Hash::make( 'secret-123' ),
        ] );

        Sanctum::actingAs( $user );

        $this->getJson( $this->currentUrl )
            ->assertOk()
            ->assertJsonFragment( [
                'id'    => $user->id,
                'email' => 'jane@example.com',
            ] )
        ;
    }
}
