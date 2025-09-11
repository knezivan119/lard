<?php

namespace Tests\Feature\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Testing\PendingCommand;

use Tests\TestCase;

class TestHeyTest extends TestCase
{
    public function test_validJson(): void
    {
        $path = tempnam( sys_get_temp_dir(), 'hey_' );
        file_put_contents( $path, json_encode( [ [ 'id' => 1 ], [ 'id' => 2 ] ] ) );

        $pc = $this->artisan( 'test:hey ' . $path . ' Hello' );
        $this->assertInstanceOf( PendingCommand::class, $pc );
        $pc->expectsOutput( 'Hello' )
            ->expectsOutput( 'Command completed.' )
            ->assertExitCode( Command::SUCCESS )
        ;

        @unlink( $path );
    }


    public function test_invalidJson(): void
    {
        $path = tempnam( sys_get_temp_dir(), 'hey_' );
        file_put_contents( $path, "[ [ 'id' => 1 ], [ 'id' => 2 ], ]," );

        $pc = $this->artisan( 'test:hey ' . $path . ' Hello' );
        $this->assertInstanceOf( PendingCommand::class, $pc );
        $pc->expectsOutput( 'Invalid JSON file' )
            ->assertExitCode( Command::FAILURE )
        ;

        @unlink( $path );
    }


    public function test_missingJsonFile(): void
    {
        $missing = sys_get_temp_dir() . '/does-not-exist-' . uniqid( '' ) . '.json';

        $pc = $this->artisan( 'test:hey ' . $missing );
        $this->assertInstanceOf( PendingCommand::class, $pc );
        $pc->expectsOutput( 'File not found!' )
            ->assertExitCode( Command::FAILURE )
        ;
    }

}
