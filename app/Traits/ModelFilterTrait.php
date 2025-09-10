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
                    // $value = $search[ $key ] != 'true' ? 1 : 0;
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


    /**
     * Search for customers using keywords
     *
     * If search.customer is present in JSON object from request()->search
     * the search will be applied using customerKeyword model to extend search
     */
    // public function filterCustomer( Builder $query, array $request ) : Builder
    // {
    //     $search = $this->decodeSearch( $request );
    //     $customer_id_field = $query->from === 'customers' ? 'id' : 'customer_id';

    //     # SEARCH BY CUSTOMER KEYWORDS
    //     if ( !empty( $search['customer'] ) ) {

    //         $options = [
    //             'mode' => 'boolean',
    //             'expanded' => false,
    //         ];
    //         $value = $search['customer'] . '*';

    //         $customerKeywords = CustomerKeyword
    //             ::whereFullText( 'haystack', $value, $options )
    //         ;

    //         $query->whereIntegerInRaw( $customer_id_field, $customerKeywords->pluck( 'customer_id' ) );
    //     }

    //     return $query;
    // }


    /**
     * Search for products using keywords
     *
     * If search.product is present in JSON object from request()->search
     * the search will be applied using productKeyword model to extend search
     */
    // public function filterProduct( Builder $query, array $request ) : Builder
    // {
    //     $search = $this->decodeSearch( $request );
    //     $product_id_field = $query->from === 'products' ? 'id' : 'product_id';

    //     # SEARCH BY PRODUCT KEYWORDS
    //     if ( !empty( $search['product'] ) ) {

    //         $options = [
    //             'mode' => 'boolean',
    //             'expanded' => false,
    //         ];
    //         $value = $search['product'] . '*';

    //         $productKeywords = ProductKeyword
    //             ::whereFullText( 'haystack', $value, $options )
    //         ;

    //         $query->whereIntegerInRaw( $product_id_field, $productKeywords->pluck( 'product_id' ) );
    //     }

    //     return $query;
    // }


    /**
     *
     *
     */
    public function filterSort( Builder $query, Request $request ) : Builder
    {
        $req = $request->pagination ?? $request->all();

        // dd( $req, $request->all() );

        $defaults = [
            'sortBy' => 'id',
            'descending' => false,
        ];

        if ( !empty( $this->defaultSort ) ) {
            $defaults = [ ...$defaults, ...$this->defaultSort ];
        }

        $opts = array_merge( $defaults, array_intersect_key( $req, $defaults ) );


        // $sortBy = empty( $req['sortBy'] ) ? $defaults['sortBy'] : $req['sortBy'];

        // $default_sort = empty( $this->defaults['sort'] ) ? 'ASC' : $this->defaults['sort'];
        $sort = filter_var( $opts['descending'], FILTER_VALIDATE_BOOLEAN ) ? 'desc' : 'asc';
        // dd( $opts, $sort );

        // $query->reorder()->orderBy( $opts['sortBy'], $sort );
        // $query->reorder( $opts['sortBy'], $sort );
        $query->orderBy( $opts['sortBy'], $sort );
        // $query->orderBy( $opts['sortBy'], 'asc' );
        // $query->orderBy( $opts['sortBy'], 'desc' );

        return $query;
    }


    /**
     *
     */
    // public function filterOnlyUsed( Builder $query, array $request ) : Builder
    // {
    //     $search = $this->decodeSearch( $request );
    //     return empty( $search['only_used'] ) ? $query : $query->has('quantity');
    // }

    /**
     *
     */
    // public function applyFilter( Builder $query, array $request ): Builder
    // {
    //     $filter = $request['filter'] ?? null;

    //     return !empty( $this->validScopes ) && in_array( $filter, $this->validScopes )
    //         ? $query->$filter()
    //         : $query;
    // }


    /**
     *
     */
    // public function handleRelationSorting( Builder $query, ?string &$sort_by ): Builder
    // {
    //     if ( empty( $this->sortSafeRelations ) || !in_array( $sort_by, $this->sortSafeRelations ) ) {
    //         return $query;
    //     }

    //     // [ $relation, $attribute ] = explode( '.', $sort_by );

    //     if ( preg_match( '/(.+)\.(\w+)$/', $sort_by, $rx ) ) {
    //         list( , $relation, $attribute ) = $rx;

    //         $table = Str::plural( $relation );
    //         $query->join( $table, "{$this->table}.{$relation}_id", '=', "{$table}.id" );
    //         $sort_by = $table . '.' . $attribute;
    //     }

    //     return $query;
    // }


}
