# 1. Build assets (once)

docker compose --profile build run --rm assets

# 2. Start app

docker compose --profile build up --build -d

# 3. PHP Test confirmation command

./vendor/bin/phpunit --configuration phpunit.xml

# 4. Vitest confirmation command

docker compose --profile build run --rm assets npm run test
