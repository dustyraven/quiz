#!/usr/bin/env bash

echo "Check for PSR2"
phpcs --standard=PSR2 --ignore=*/vendor/* .
echo "OK"

echo "Check for PHP mess"
./vendor/phpmd/phpmd/src/bin/phpmd . text codesize,unusedcode,naming --exclude '*/vendor/*,*/.git/*' | grep -v 'Avoid variables with short names like'
echo "OK"

echo "Run Unit tests"
./vendor/bin/phpunit ./tests
