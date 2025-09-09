# Update Procedure

## Easy way
Check Makefile. Run `make`. It will display options.

Run `make dev-update` and hope for the best :)


## Update
```bash
sail composer update --with-all-dependencies

sail npm ci
sail npm update

sail artisan optimize:clear
sail artisan migrate --force
```


## Test
```bash
sail artisan about
sail artisan test || true
```


## Diagnostic etc.
```bash
composer outdated --direct
composer why-not laravel/framework 13.*
composer prohibits vendor/package 2.*
composer check-platform-reqs

npm outdated
```

```bash
sail artisan route:clear
sail artisan config:clear
sail artisan view:clear
sail artisan event:clear
```