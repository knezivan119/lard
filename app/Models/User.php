<?php

namespace App\Models;

use App\Models\Scopes\AccountUserScope;
use App\Traits\ServedAtTrait;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    // use SoftDeletes;
    use HasRoles;
    use HasApiTokens;
    use ServedAtTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'email_verified_at',
        'deleted_at',
    ];

    protected $appends = [
        'served_at',
    ];

    # CASTS ==================================================================
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    # BOOT ===================================================================
    protected static function boot() : void
    {
        parent::boot();

        static::created( function ( $query ) {
            // $query->account_id = $query->account_id
                // ?? Auth::user()->account()->id;
            $query->accounts()->sync( Auth::user()?->account()->id );
        });

        static::updating( function ( $query ) {
            $query->checkServedAt();
        });
    }

    protected static function booted(): void
    {
        static::addGlobalScope( new AccountUserScope );
    }


    # RELATIONS ==============================================================
    // public function meta(): Relations\HasOne
    // {
    //     return $this->hasOne( UserMeta::class );
    // }

    // public function customers(): Relations\HasMany
    // {
    //     return $this->HasMany( Customer::class );
    // }

    public function accounts(): Relations\BelongsToMany
    {
        return $this->belongsToMany( Account::class );
    }

    # METHODS ================================================================
    public function store( array $data ): self
    {
        $this->fill( $data );
        $this->save();

        // dd( $data );

        if( !empty( $data['meta'] ) ) {
            // dd( $data['meta']);
            $this->meta()->create( $data['meta'] );
        }

        return $this;
    }

    public function account(): ?Account
    {
        // $this->loadMissing('accounts');
        return $this->accounts()->first();
    }

}
/*
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
*/