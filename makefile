.PHONY : build test

# Сборка проекта (Default)
build: vendor/composer/installed.json
	composer dump
	composer validate --no-check-all --strict

vendor/composer/installed.json: composer.json
	composer update


# Тесты
phpunit.xml:
	cp phpunit-dist.xml phpunit.xml

test: phpunit.xml
	@echo
	-./vendor/bin/phpunit
