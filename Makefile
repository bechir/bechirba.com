build:
	$(MAKE) prepare-test
	$(MAKE) analyze
	$(MAKE) tests

it:
	$(MAKE) prepare-dev
	$(MAKE) analyze

tests: vendor
	make: prepare-test
	php vendor/bin/simple-phpunit

analyze: vendor
	yarn audit
	composer valid
	php bin/console doctrine:schema:validate
	php vendor/bin/phpcs

prepare-dev: bin
	yarn install
	yarn dev
	composer install --no-progress --no-suggest --prefer-dist
	php bin/console doctrine:database:drop --if-exists -f -n --env=dev
	php bin/console doctrine:database:create --env=dev
	php bin/console doctrine:schema:update -f --env=dev
	php bin/console doctrine:fixtures:load -n --env=dev

prepare-test: bin
	yarn install
	yarn build
	composer install --no-progress --no-suggest --prefer-dist
	php bin/console cache:clear --env=test
	php bin/console doctrine:database:drop --if-exists -f -n --env=test
	php bin/console doctrine:database:create --env=test
	php bin/console doctrine:schema:update -f --env=test
	php bin/console doctrine:fixtures:load -n --env=test
