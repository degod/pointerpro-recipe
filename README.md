# 1. Build assets (once)

docker compose --profile build run --rm assets

# 2. Start app

docker compose up -d --build app nginx db

docker compose --profile build up --build -d

# Test confirmation command

./vendor/bin/phpunit --configuration phpunit.xml
