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
});
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

use HasApiTokens; // inside class
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
curl -i -X OPTIONS http://localhost/api/v1/ping -H 'Origin: http://localhost:5173' -H 'Access-Control-Request-Method: GET'
```


## Frontend
Make very simple Welcome page, with *SCSS* support, no Tailwind

```php
/// routes/web.php
Route::get( '/', fn () => view( 'welcome' ) );
```

```html
<!-- resources/views/welcome.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Hello API</title>
    @vite( 'resources/js/app.js' )
</head>
<body>
    <main>
        <h1>Hello API</h1>
        <p>This is a minimal Blade page served by the API project.</p>
    </main>
</body>
</html>
```

```js
/// resources/js/app.js
import '../scss/app.scss'; // add this to the file
```

```js
/// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel( {
            input: [ 'resources/js/app.js' ],
            refresh: true,
        } ),
    ],
});
```

```css
/* resources/scss/app.scss */
$brand: #0ea5e9;

:root { --brand: #{$brand}; }

body { background: black; color: silver; margin: 2em; }
h1 { color: var(--brand); }
```

```bash
sail npm ci || sail npm install
sail npm add -D sass
sail npm run dev
```

### Garbage cleanup

```bash
# remove Tailwind configs if present
rm -f tailwind.config.js tailwind.config.ts postcss.config.js

# remove the default Tailwind css file if you do not need it
rm -f resources/css/app.css

sail npm remove tailwindcss @tailwindcss/vite postcss autoprefixer
```


## Testing

Test will use dedicated Postgres test DB. This just covers setup - how testing is organised should be in a separate `testing.md` file.

```ini
APP_ENV=testing
APP_DEBUG=true

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=sail_test
DB_USERNAME=sail
DB_PASSWORD=password

CACHE_STORE=array
SESSION_DRIVER=array
QUEUE_CONNECTION=sync
MAIL_MAILER=array
```

```bash
# create once; safe to re-run
sail exec -T pgsql sh -lc '
  PGPASSWORD="$POSTGRES_PASSWORD" psql -U "$POSTGRES_USER" -d postgres -tAc \
    "SELECT 1 FROM pg_database WHERE datname = '\''sail_test'\''" \
  | grep -q 1 || \
  PGPASSWORD="$POSTGRES_PASSWORD" psql -U "$POSTGRES_USER" -d postgres -c \
    "CREATE DATABASE sail_test;"
'
# test
sail exec -T pgsql sh -lc 'PGPASSWORD="$POSTGRES_PASSWORD" psql -U "$POSTGRES_USER" -d sail_test -c "\conninfo"'

sail test
```

Have a look if there is `testing.md` file for more.



## Useful

### Stubs
Great for exposing templates for making different things like models, controllers, enums...
It will create `stubs/` dir in root of project with files.

`sail artisan stub:publish`

### PhpStan

sail composer require --dev larastan/larastan
sail composer require --dev phpstan/phpstan-phpunit

```yaml
# ./phpstan.neon.dist
includes:
  - vendor/larastan/larastan/extension.neon
  - vendor/phpstan/phpstan-phpunit/extension.neon
  - vendor/phpstan/phpstan-phpunit/rules.neon

parameters:
  level: 7
  paths:
    - app
    - tests

  checkMissingTypehints: false
  treatPhpDocTypesAsCertain: false
  reportUnmatchedIgnoredErrors: true

  ignoreErrors:
    - identifier: missingType.generics
    - identifier: missingType.iterableValue
    - identifier: assign.propertyType
    - identifier: property.notFound
      paths:
        - app/Http/Resources/*

    - message: '#should return array<string,\s*mixed> but returns array\|Illuminate\\Contracts\\Support\\Arrayable\|JsonSerializable#'
      paths:
        - app/Http/Resources/*
```

```bash
sail php -d memory_limit=1G vendor/bin/phpstan analyse
sail php vendor/bin/phpstan clear-result-cache
```


## Cloning
* Replace `COMPOSE_PROJECT_NAME` in `.env` value with new name

```bash
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$PWD":/var/www/html \
  -w /var/www/html \
  laravelsail/php84-composer:latest \
  composer install --no-interaction --prefer-dist
```

then `sail up` :)