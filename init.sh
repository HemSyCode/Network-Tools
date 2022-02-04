#!/bin/bash

date


php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create

php bin/console doctrine:schema:update --force

php bin/console doctrine:fixtures:load --no-interaction --no-debug

php bin/console app:scan:rirallocations