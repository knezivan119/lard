BASH := $(shell command -v bash)
ifeq ($(BASH),)
  $(error bash not found. On Nix, run `nix develop` or `nix-shell -p bash` first)
endif

SHELL := $(BASH)
.SHELLFLAGS := -eu -o pipefail -c

HAS_SAIL := $(shell test -f ./vendor/bin/sail && echo 1 || echo 0)
ifeq ($(HAS_SAIL),1)
  PHP=./vendor/bin/sail php
  COMPOSER=./vendor/bin/sail composer
  ARTISAN=./vendor/bin/sail artisan
  NPM=./vendor/bin/sail npm
else
  PHP=php
  COMPOSER=composer
  ARTISAN=php artisan
  NPM=npm
endif

.PHONY: dev-update dev-reset dev-major-laravel dev-major-node dev-sanity hello

hello:
	@echo "No default action."
	@echo "Pick a target, e.g.:"
	@echo "  make dev-update        # update composer/npm, build, clear caches"
	@echo "  make dev-reset         # migrate:fresh --seed and clear caches"
	@echo "  make dev-major-laravel # try next Laravel major (DEV only)"
	@echo "  make dev-major-node    # try latest JS majors (DEV only)"

dev-update:
	$(COMPOSER) update --with-all-dependencies
	$(NPM) ci
	$(NPM) update
	$(NPM) run build
	$(ARTISAN) optimize:clear
	$(MAKE) dev-sanity

dev-reset:
	$(ARTISAN) migrate:fresh --seed --force
	$(ARTISAN) optimize:clear
	$(MAKE) dev-sanity

dev-major-laravel:
	@if [ -f composer.lock ]; then cp composer.lock composer.lock.bak; fi
	$(COMPOSER) require laravel/framework:^13 --update-with-all-dependencies
	$(NPM) run build
	$(MAKE) dev-sanity

dev-major-node:
	@if [ -f package-lock.json ]; then cp package-lock.json package-lock.json.bak; fi
	$(NPM) exec npm-check-updates -u
	$(NPM) install
	$(NPM) run build
	$(MAKE) dev-sanity

dev-sanity:
	-$(ARTISAN) about
	-$(ARTISAN) test

