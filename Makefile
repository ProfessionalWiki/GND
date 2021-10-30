.PHONY: ci test phpunit

ci: test
test: phpunit

phpunit:
	php ../../tests/phpunit/phpunit.php -c phpunit.xml.dist
