<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\AccountStatusEnum;
use App\Models\User;
use App\Models\Account;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;

use Tests\TestCase;

class AccountApiTest extends TestCase
{
    use RefreshDatabase;

    public string $baseUrl = '/api/v1/accounts';


    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan( 'db:seed', [ '--class' => 'Database\Seeders\TestingSeeder' ] );
    }


    private function payload( array $overrides = [] ): array
    {
        $base = [
            'name' => 'Demo',
            'status' => AccountStatusEnum::Draft,
            // 'status' => 'draft',
            'data' => [ 'flags' => [ 'published' => true ], 'counts' => [ 'views' => 3 ] ],
            'extra' => [ 'tags' => [ 'php', 'laravel' ], 'style' => [ 'color' => 'red' ] ],
        ];

        return array_replace_recursive( $base, $overrides );
    }


    public function test_guestIsUnauthorised()
    {
        // $this->markTestIncomplete( 'Not implemnted yet' );
        // $this->markTestSkipped( 'Not implemnted yet' );
        //
        $this->getJson( $this->url() )->assertStatus( 401 );
        $this->postJson( $this->url(), $this->payload() )->assertStatus( 401 );
    }

    public function test_getAccountForCurrentUser(): void
    {
        $this->signIn();
        $response = $this->get( 'api/v1/account' );

        $response->assertStatus( 200 );
        $response->assertJsonStructure( [ 'id', 'name', 'users', 'served_at' ] );
    }


    public function test_list()
    {
        // $this->markTestSkipped( 'Not ready yet' );

        $this->signIn();

        $params = [
            'rowsPerPage' => 2,
        ];

        Account::factory()->create( [ 'name' => 'One' ] );
        Account::factory()->create( [ 'name' => 'Two' ] );
        Account::factory()->create( [ 'name' => 'Three' ] );

        $this->getJson( $this->url(), $params )
            ->assertOk()
            ->assertJsonFragment( [ 'name' => 'One' ] )
            ->assertJsonFragment( [ 'name' => 'Two' ] )
            ->assertJsonFragment( [ 'name' => 'Three' ] )
        ;
    }


    public function test_create()
    {
        // $this->markTestSkipped( 'Not ready yet' );

        $this->signIn();
        $payload = $this->payload();

        $response = $this->postJson( $this->url(), $payload )
            ->assertCreated()
            ->assertJsonStructure( [ 'id', 'name', 'status', 'data', 'extra' ] )
        ;

        $id = $response->json( 'id' );
        $account = Account::findOrFail( $id );

        $this->assertSame( 'Demo', $account->name );
        $this->assertSame( 'red', $account->extra[ 'style' ][ 'color' ] );
        $this->assertTrue( $account->data[ 'flags' ][ 'published' ] );
        $this->assertSame( 3, $account->data[ 'counts' ][ 'views' ] );

        $this->assertDatabaseHas( 'accounts', [ 'id' => $id, 'name' => 'Demo' ] );

        if ( Schema::getConnection()->getDriverName() === 'pgsql' ) {
            $types = DB::table( 'information_schema.columns' )
                ->select( 'column_name', 'data_type' )
                ->where( 'table_name', 'accounts' )
                ->whereIn( 'column_name', [ 'extra', 'data' ] )
                ->pluck( 'data_type', 'column_name' )
            ;

            $this->assertSame( 'jsonb', $types[ 'extra' ] ?? null );
            $this->assertSame( 'jsonb', $types[ 'data' ] ?? null );
        }
    }


    public function test_show()
    {
        // $this->markTestSkipped( 'Not ready yet' );

        $this->signIn();
        $account = Account::factory()->create( [ 'name' => 'Show Me' ] );

        $this->getJson( $this->url( $account->id ) )
            ->assertOk()
            ->assertJsonFragment( [ 'id' => $account->id, 'name' => 'Show Me' ] )
        ;
    }


    public function test_updateOutdatedProtection()
    {
        // $this->markTestSkipped( 'Not ready yet' );

        $this->signIn();
        $account = Account::factory()->create( $this->payload() );

        $updated = $this->payload( [
            'name'       => 'Updated',
            'extra'  => [ 'style' => [ 'color' => 'blue' ], 'tags' => [ 'json' ] ],
            'data' => [ 'flags' => [ 'published' => false ], 'counts' => [ 'views' => 10 ] ],
            'served_at' => Carbon::now()->subMinutes( 10 ),
        ] );

        $this->putJson( $this->url( $account->id ), $updated )
            ->assertStatus( 500 )
        ;
    }


    public function test_update()
    {
        // $this->markTestSkipped( 'Not ready yet' );

        $this->signIn();
        $account = Account::factory()->create( $this->payload() );

        $updated = $this->payload( [
            'name'       => 'Updated',
            'extra'  => [ 'style' => [ 'color' => 'blue' ], 'tags' => [ 'json' ] ],
            'data' => [ 'flags' => [ 'published' => false ], 'counts' => [ 'views' => 10 ] ],
            'served_at' => Carbon::now(),
        ] );

        $this->putJson( $this->url( $account->id ), $updated )
            ->assertOk()
            ->assertJsonFragment( [ 'name' => 'Updated' ] )
        ;

        $account->refresh();
        $this->assertSame( 'blue', $account->extra[ 'style' ][ 'color' ] );
        $this->assertFalse( $account->data[ 'flags' ][ 'published' ] );
        $this->assertSame( 10, $account->data[ 'counts' ][ 'views' ] );
    }


    public function test_destroy()
    {
        // $this->markTestSkipped( 'Not ready yet' );

        $this->signIn();
        $account = Account::factory()->create();

        $this->deleteJson( $this->url( $account->id ) )
            ->assertStatus( 204 )
        ;

        $this->assertModelMissing( $account );
        $this->assertDatabaseMissing( 'accounts', [ 'id' => $account->id ] );
    }


    public function test_storeLogo(): void
    {
        $this->signIn();

        $formats = [ '.png', '.png', '.jpg', '.gif' ];

        $account = Account::factory()->create();

        Storage::fake('public');

        foreach ( $formats as $format ) {
            $data = [
                'logo' => UploadedFile::fake()->image('logo' . $format ),
            ];

            $response = $this->postJson( $this->url( $account->id ) . '/logo', $data );

            $response->assertStatus( 200 );

            Storage::disk( 'public' )
                ->assertExists( 'logos/' . $account->logoName() . $format )
            ;
            $this->assertDatabaseHas( 'accounts', [
                'id' => $account->id,
                'data->logo' => 'logos/' . $account->logoName() . $format,
            ]);
        }
    }
}