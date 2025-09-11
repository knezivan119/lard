<?php

namespace App\Models;

use App\Traits\ServedAtTrait;
use App\Enums\AccountStatusEnum;
// use App\Models\Scopes\AccountScope;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Account extends Model
{
    use HasFactory;
    use ServedAtTrait;
    // use SoftDeletes;

    protected $fillable = [
        'name',
        'status',
        'description',
        'comment',
        'data',
        'extra',
    ];

    protected $appends = [
        'served_at',
    ];

    protected $hidden = [
        // 'created_at',
        'updated_at',
        'deleted_at',
    ];

    # CASTS ==================================================================
    protected function casts()
    {
        return [
            'status' => AccountStatusEnum::class,
            'data' => 'array',
            'extra' => 'array',
        ];
    }

    # BOOT ===================================================================
    protected static function boot() : void
    {
        parent::boot();

        static::updating( function ( $query ) {
            $query->checkServedAt();
        });
    }

    # RELATIONS ==============================================================

    public function users(): Relations\BelongsToMany
    {
        return $this->belongsToMany( User::class );
    }

    // public function counter(): Relations\HasOne
    // {
    //     return $this->hasOne( QuoteCounter::class );
    // }

    // public function customers(): Relations\HasMany
    // {
    //     return $this->hasMany( Customer::class );
    // }

    # BOOT ===================================================================
    // protected static function booted(): void
    // {
    //     static::addGlobalScope( new AccountScope );
    // }

    # SCOPES =================================================================
    public function scopeActive( Builder $query ): Builder
    {
        return $query->where('status', AccountStatusEnum::Active );
    }

    # ACCESSORS ==============================================================
    // public function getAddressAttribute()
    // {
    //     $data = $this->data;

    //     return implode(', ', array_filter([
    //         data_get( $data, 'address' ),
    //         implode(' ', array_filter([
    //             data_get( $data, 'suburb' ),
    //             data_get( $data, 'state' ),
    //             data_get( $data, 'postcode' ),
    //         ])),
    //         data_get($data, 'country' ),
    //     ]));
    // }


    # METHODS ================================================================
    public function logoName(): String
    {
        $name = ( 100207 + $this->id ) * 3;
        return dechex( $name );
    }

}
