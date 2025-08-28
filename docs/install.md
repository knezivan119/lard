# Laravel on Sail

## Set up

### Base install
`curl -s https://laravel.build/lard?with=pgsql | bash`

#### Keep env in git
It's not fun to have everything but working env example :)

```bash
mkdir _env
mv .env _env/env.sail
ln -sf _env/env.sail .env
```

#### Start
`sail up`

#### Test
```bash
sail php --version
sail artisan --version
sail artisan db:show --database=pgsql
```


### Database & migrations
```bash
sail artisan install:api --without-migration-prompt
sail artisan migrate:install
```

#### Test
```bash
ls -lah routes | grep api.php
sail composer show laravel/sanctum | head -n 5
sail artisan migrate:status | grep personal_access_tokens || true
sail psql -U sail -h pgsql -d sail -c "\dt public.*"
```

## Adjust Error Response (optional)
```php
/// bootstrap/app.php

// add inside withMiddleware( ... )
$middleware->prependToGroup( 'api', \App\Http\Middleware\ForceJsonResponse::class );
```

```php
/// app/Http/Middleware/ForceJsonResponse.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    public function handle( Request $request, Closure $next )
    {
        $request->headers->set( 'Accept', 'application/json' );
        return $next( $request );
    }
}
```


## Configure

### API endpoint
```php
/// routes/api.php
use Illuminate\Support\Facades\Route;

Route::prefix( 'v1' )->group( function () {
    Route::get( '/ping', fn () => [ 'ok' => true, 'time' => now()->toISOString() ] );
} );
```

#### Test
`curl -i http://localhost/api/v1/ping`

### Auth
```php
/// routes/api.php
use App\Http\Controllers\AuthController;

Route::prefix( 'v1' )->group( function () {
    Route::post ( '/auth/login', [ AuthController::class, 'issueToken' ] );
    Route::middleware( 'auth:sanctum' )->get( '/me', fn () => auth()->user() );
}
```

```php
/// AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function issueToken( Request $request )
    {
        $creds = $request->validate( [
            'email' => [ 'required', 'email' ],
            'password' => [ 'required' ],
            'device_name' => [ 'required' ],
        ] );

        $user = User::where( 'email', $creds[ 'email' ] )->first();

        if ( ! $user || ! Hash::check( $creds[ 'password' ], $user->password ) ) {
            return response()->json( [ 'message' => 'Invalid credentials' ], 422 );
        }

        $token = $user->createToken( $creds[ 'device_name' ] )->plainTextToken;

        return [ 'token' => $token ];
    }
}
```

```php
/// app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

use HasApiTokens;

```


#### Test
Should return sane and expected errors at this point, complaining about missing tables

`curl -i -X POST http://localhost/api/v1/auth/login -H 'Content-Type: application/json' -d '{ "email": "tester@example.test", "password": "test", "device_name": "cli" }'`

#### DO
`sail artisan migrate`
`sail artisan tinker`

```php
/// TINKER!
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create( [
    'name' => 'Tester',
    'email' => 'tester@example.test',
    'password' => Hash::make( 'test' ),
] );
```

#### Test
```bash
curl -i -X POST http://localhost/api/v1/auth/login -H 'Content-Type: application/json' -d '{ "email": "tester@example.test", "password": "test", "device_name": "cli" }'
TOKEN="<paste token here>"
curl -s http://localhost/api/v1/me -H "Authorization: Bearer ${TOKEN}" | jq .
```


## Redis
`sail artisan sail:install --with=pgsql,redis --no-interaction`

Test, expect PONG:
`sail exec redis redis-cli PING`

```ini
# .env
# Cache through Redis
CACHE_STORE=redis
CACHE_PREFIX=api_backend_cache

# Redis connection used by cache, queues, and anything else
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1

# Sessions in an API should be stateless. Avoids disk writes for an API that does not use cookies. If you later add web routes, switch to redis.
SESSION_DRIVER=array

# Queue stays sync for now (change to redis when you actually run a worker)
QUEUE_CONNECTION=sync
```

`sail artisan config:clear`

#### Test
```php
/// TINKER!
use Illuminate\Support\Facades\Cache;

Cache::put( 'probe', 'ok', 600 );  // 10 minutes
Cache::get( 'probe' );             // expect 'ok'
```
`sail exec redis redis-cli -n 1 KEYS '*'`

#### Maybe
Only if things aren't working properly, try:
`sail composer require predis/predis`

## CORS
**WARNING:** This is work in progress. It feels like it's not working properly - test is allowing both calls which is not what I would expect.

```ini
# .env

# Comma separated, no spaces
CORS_ALLOWED_ORIGINS=http://localhost:5173
```
```php
/// bootstrap/app.php
use Illuminate\Http\Middleware\HandleCors;

// add inside withMiddleware( ... )
$middleware->append( HandleCors::class );
```

`sail artisan config:publish cors`

```php
/// config/cors.php
return [
    'paths' => [ 'api/*', 'sanctum/csrf-cookie' ],
    'allowed_methods' => [ '*' ],
    'allowed_origins' => explode( ',', env( 'CORS_ALLOWED_ORIGINS', '' ) ),
    'allowed_origins_patterns' => [],
    'allowed_headers' => [ '*' ],
    'exposed_headers' => [],
    'max_age' => 3600,
    'supports_credentials' => false,
];
```

#### Test
```bash
sail artisan config:clear

# Should fail
curl -i -X OPTIONS http://localhost/api/v1/ping -H 'Origin: https://example.com' -H 'Access-Control-Request-Method: GET'

# Should work
curl -i -X OPTIONS http://localhost/api/v1/ping -H 'Origin: https://localhos:5173' -H 'Access-Control-Request-Method: GET'
```