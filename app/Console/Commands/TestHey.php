<?php

namespace App\Console\Commands;

use App\Traits\JsonTrait;
use Illuminate\Console\Command;

class TestHey extends Command
{
    use JsonTrait;

    protected $signature = 'test:hey {file} {test?}';
    protected $description = 'Command description';

    protected array $args;
    protected array $rows = [];


    private function args(): void
    {
        $this->args = [
            'test' => $this->argument( 'test' ) ?? 'Hey!',
            'file' => $this->argument( 'file' ),
        ];
    }


    public function handle(): int
    {
        $this->args();

        if ( !$this->check()    ) return self::FAILURE;
        if ( !$this->readFile() ) return self::FAILURE;

        $this->process();

        $this->info( 'Command completed.' );
        return self::SUCCESS;
    }


    protected function process(): void
    {
        $this->info( $this->args[ 'test' ] );

        // foreach ( $this->rows as $row ) {
        //     dump( $row );
        // }
    }


    private function readFile(): bool
    {
        try {
            $this->rows = $this->readJsonFile( $this->args['file'] );
        }
        catch( \Throwable $e ) {
            $this->error( "Invalid JSON file" );
            return false;
        }

        return true;
    }


    private function check(): bool
    {
        // if ( !file_exists( $this->args['file'] ) ) {
        //     $this->error( 'File not found!' );
        //     return false;
        // }

        return true;
    }

}