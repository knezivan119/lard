<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use JsonException;

trait JsonTrait
{
    protected function readJsonFile( string $path, bool $assoc = false ): object|array
    {
        $json = $this->loadJsonBytes( $path );

        try {
            return json_decode( $json, $assoc, 512, JSON_THROW_ON_ERROR );
        }
        catch ( JsonException $e ) {
            $this->error( 'Invalid JSON: ' . $e->getMessage() );
            throw $e;
        }
    }


    private function loadJsonBytes( string $path ): string
    {
        // if ( $path === '-' ) {
        //     $data = stream_get_contents( STDIN );

        //     if ( $data === false ) {
        //         throw new \RuntimeException( 'Failed reading STDIN.' );
        //     }
        //     return $data;
        // }

        $full = $this->resolvePath( $path );

        if ( !File::exists( $full ) ) {
            $this->error( 'File not found!' );
            throw new \RuntimeException( 'File not found: ' . $path );
        }

        return File::get( $full );
    }


    private function resolvePath( string $path ): string
    {
        return str_starts_with( $path, DIRECTORY_SEPARATOR )
            ? $path
            : base_path( $path )
        ;
    }
}
