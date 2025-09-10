<?php

namespace App\Traits;

use App\Models\Customer\CustomerKeyword;
use App\Models\Product\ProductKeyword;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

trait ModelFilterTrait
{
    /**
     *
     */
    public function decodeSearch( Request $request ) : array
    {
        $req = $request->all();

        $search = empty( $req['search'] )
            ? []
            : ( is_array( $req['search'] )
                ? $req['search']
                : json_decode( $req['search'], true )
            )
        ;

        $req['search'] = $search;
        return array_merge( $req, $search );
    }


    /**
     * Search through various fields set in $fieldsToSearch from request()->search
     *
     */
    public function filterSearch( Builder $query, Request $request ) : Builder
    {
        if ( !$this->fieldsToSearch ) {
            return $query;
        }

        if ( $search = $this->decodeSearch( $request ) ) {

            # SEARCH BY ARRAY OF ARGS
            foreach ( $this->fieldsToSearch as $key => $field_data ) {

                if ( empty( $search[ $key ] ) ) {
                    continue;
                }

                $type = '';
                extract( $field_data );

                if ( $type == 'bool' ) {
                    $value = in_array( $search[ $key ], ['false', '0', 'no'] ) ? 0 : 1;
                }
                else {
                    $value = $search[ $key ];
                }

                if ( preg_match( '/^date\_/', $key ) ) {
                    try {
                        $value = Carbon::parse( $value )->toDateTimeString();
                    }
                    catch ( \Exception $e ) {
                        continue;
                    }
                }

                if ( is_array( $value ) ) {
                    if ( preg_match( '/(.+)\.(\w+)$/', $field, $rx ) ) {
                        list( , $relation, $attribute ) = $rx;
                        $query->whereHas( $relation, fn( $q ) => $q->whereIn( $attribute, $value ) );
                    }
                    else {
                        $query->whereIn( $field, $value );
                    }
                }
                else {
                    $pattern = str_replace( '?', $value, $pattern );

                    if ( preg_match( '/(.+)\.(\w+)$/', $field, $rx ) ) {
                        list( , $relation, $attribute ) = $rx;
                        $query->whereHas( $relation, fn( $q ) => $q->where( $attribute, $compare, $pattern ) );
                    }
                    else {
                        $query->where( $field, $compare, $pattern );
                    }
                }
            }
        }

        return $query;
    }


    public function filterSort( Builder $query, Request $request ) : Builder
    {
        $req = $request->pagination ?? $request->all();

        $defaults = [
            'sortBy' => 'id',
            'descending' => false,
        ];

        if ( !empty( $this->defaultSort ) ) {
            $defaults = [ ...$defaults, ...$this->defaultSort ];
        }

        $opts = array_merge( $defaults, array_intersect_key( $req, $defaults ) );
        $sort = filter_var( $opts['descending'], FILTER_VALIDATE_BOOLEAN ) ? 'desc' : 'asc';

        $query->orderBy( $opts['sortBy'], $sort );

        return $query;
    }

}
