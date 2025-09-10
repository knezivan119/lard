<?php

namespace Tests;

use App\Models\User;
use App\Models\Account;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

use Laravel\Sanctum\Sanctum;
use Laravel\Scout\EngineManager;

abstract class TestCase extends BaseTestCase
{
    protected function enableScout(): void
    {
        config( [ 'scout.driver' => 'meilisearch' ] );
    }


    protected function disableScout(): void
    {
        config( [ 'scout.driver' => 'null' ] );
    }


    protected function loadJsonFixture( string $table, string $file, array $merger = [] ): void
    {
        $path = base_path( "tests/Fixtures/{$file}" );
        $data = collect( json_decode( file_get_contents( $path ), true ) );

        $data->each( fn( $x ) => DB::table( $table )->insert( [ ...$merger, ...$x ] ) );
    }


    protected function url( ?int $id = null, string $path = null ): string
    {
        return implode( '/', array_filter( [ $this->baseUrl, $id, $path ] ) );
    }


    protected function signIn( string $role='admin' ): User
    {
        $accounts = Account::factory( 1 )->create();

        $user = User::factory()->create();

        $user->assignRole( $role );
        $user->accounts()->attach( $accounts->first()->id );

        Sanctum::actingAs( $user );

        return $user;
    }

}
