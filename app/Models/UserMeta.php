<?php

namespace App\Models;

// use App\Traits\ModelFilterTrait;
use App\Traits\ServedAtTrait;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;

class UserMeta extends Model
{
    use HasFactory;
    // use ModelFilterTrait;
    use ServedAtTrait;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'middle_name',
        'phones',
        'addresses',
        'notes',
        'extra',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        // 'deleted_at',
    ];

    protected $appends = [
        'served_at',
    ];

    protected function casts()
    {
        return [
            'phones' => 'array',
            'addresses' => 'array',
            'notes' => 'array',
            'extra' => 'array',
        ];
    }

    # SEARCH =================================================================

    // public $fieldsToSearch = [
    //     'id' => [
    //         'field' => 'id',
    //         'compare' => '=',
    //         'pattern' => '?',
    //     ],
    // ];

    // public $defaultSort = [
    //     'sort_by' => 'id',
    //     // 'sort' => 'ASC',
    // ];


    # BOOT ===================================================================

    protected static function boot() : void
    {
        parent::boot();

        static::updating( function ( $query ) {
            $query->checkServedAt();
        });
    }


    # RELATIONS ==============================================================

    public function user(): Relations\BelongsTo
    {
        return $this->belongsTo( User::class );
    }


    # MAGIC SCOPES ===========================================================

    // public function scopeFilter( Builder $query, array $request ) : Builder
    // {
    //     $this->filterSearch( $query, $request );
    //     $this->filterSort( $query, $request );

    //     return $query;
    // }


    # METHODS ================================================================

}

