<?php

namespace App\Traits;

use Illuminate\Support\Carbon;

trait ServedAtTrait
{

    /**
     * Attach attribute to model result so it can be checked against
     */
    public function getServedAtAttribute() : string
    {
        return Carbon::now()->toISOString();
    }


    /**
     * @event saving
     */
    public function checkServedAt() : bool
    {
        static $check = null;

        if ( !request()->served_at || !$this->updated_at ) {
            return true;
        }

        // if ( $check !== null ) {
        //     return $check;
        // }

        $check = $this->updated_at->lessThanOrEqualTo( Carbon::parse( request()->served_at ) );
        if ( !$check ) {
            throw new \Exception( 'Conflict - Outdated data ', 409 );
        }

        return $check;
    }


    // public static function checkServedAtManually( mixed $object, string $served_at )
    // {
    //     $served_at = Carbon::parse( $served_at );
    //     $check = $object->updated_at->lessThanOrEqualTo( $served_at );

    //     if ( !$check ) {
    //         throw new \Exception( 'Conflict - Outdated data (2) ', 409 );
    //     }

    //     return $check;
    // }

}