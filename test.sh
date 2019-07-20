docker-compose -f ./tests/docker-compose.yml up -d
php tests/wait.php
./vendor/bin/phpunit
docker-compose -f ./tests/docker-compose.yml down