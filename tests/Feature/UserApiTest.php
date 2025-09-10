<?php

namespace Tests\Feature;

use App\Models\User;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithDatabase;

    public $baseUrl = '/api/v1/users';

    // protected $user;
    protected $table = 'users';
    // protected $user_id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan( 'db:seed', [ '--class' => 'Database\Seeders\TestingSeeder' ] );
        // $this->user = User::find( 1 );
        // Sanctum::actingAs( $this->user, ['*'] );

        // $this->user_id = User::factory()->create()->id;
    }

    private function payload( array $overrides = [] ): array
    {
        $base = [
            'name' => 'Tandara Mandara',
            'email' => 'test@example.com',
            'password' => 'password',
            'meta' => [
                'first_name' => 'Tandara',
                'last_name' => 'Mandara',
                'middle_name' => 'M',
                'phones' => [ [ 'e164' => '123456789' ] ],
                'addresses' => [ [
                    'address' => '330 Example St',
                    'suburb' => 'Sub',
                    'postcode' => '11000',
                    'state' => 'NSW',
                    'country' => 'AU',
                ] ],
                'notes' => [ 'Testing' ],
                'extra' => [ 'alo' => 'bre' ],
            ]
        ];

        return array_replace_recursive( $base, $overrides );
    }


    public function test_getCurrentUser(): void
    {
        $user = $this->signIn();

        $response = $this->get( 'api/v1/user' );

        $response->assertStatus( 200 );
        $response->assertJsonStructure([ 'data' => [
            'id', 'name', 'email', 'first_name', 'last_name', 'roles',
        ] ] );
    }


    public function test_create(): void
    {
        $this->signIn();

        $request = [
            // override payload
        ];

        $response = $this->postJson( $this->url(), $this->payload( $request ) );
        $response->assertStatus( 201 );

        $expected = [
            'name' => $this->payload()['name'],
            'email' => $this->payload()['email']
        ];

        $this->assertDatabaseHas( $this->table, $expected );

        $response->assertJsonStructure([
            'data' => [
                'name', 'email', 'id', 'served_at',
                'meta' => [ 'user_id', 'first_name', 'last_name', 'phones', 'addresses' ],
            ],
        ]);
    }


    public function test_updateOutdatedProtection(): void
    {
        $this->signIn();

        # CREATE RECORD
        $record = User::factory()->create();

        # MANIPULATE
        $request = [
            'name' => 'Tandara Mandara F',
            'email' => 'test2@example.com',
            'served_at' => Carbon::now()->subMinutes(10),
        ];

        $response = $this->putJson( $this->url( $record->id ), $request );
        $response->assertStatus( 500 );
    }


    public function test_update(): void
    {
        $this->signIn();

        # CREATE RECORD
        $record = User::factory()->create();

        # MANIPULATE
        $request = [
            'name' => 'Tandara Mandara Up',
            'email' => 'test3@example.com',
            // 'password' => bcrypt('password'),
            'served_at' => Carbon::now(),
            'meta' => [
                'user_id' => $record->id,
                'notes' => [ 'updated!' ],
            ]
        ];

        $response = $this->putJson( $this->url( $record->id ), $request );
        $response->assertStatus( 200 );

        $expected = $request;
        unset( $expected['served_at'] );
        unset( $expected['meta'] );

        $this->assertDatabaseHas( $this->table, $expected );
    }



    public function test_show(): void
    {
        $this->signIn();

        # CREATE RECORD
        $record = User::factory()->create();

        # MANIPULATE
        $response = $this->get( $this->url( $record->id ) );

        $response->assertStatus( 200 );
        $response->assertJsonStructure([
            'data' => [
                'id', 'name', 'email', 'served_at',
                'meta' => [],
                'roles' => [],
            ],
        ]);
    }


    public function test_index(): void
    {
        $this->signIn();

        $params = [
            'rowsPerPage' => 5,
            // 'page' => 1,
        ];

        $response = $this->get( $this->url(), $params );

        $response->assertStatus( 200 );
        $response->assertJsonStructure([
            'data' => [
                '*' => [ 'id', 'name', 'email', 'served_at', 'meta', 'roles' ],
            ],
        ]);
    }


    public function test_delete(): void
    {
        $this->signIn();

        # CREATE RECORD
        $record = User::factory()->create();

        # MANIPULATE
        $response = $this->delete( $this->url( $record->id ) );
        $response->assertStatus( 204 );
    }

}
