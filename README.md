# 1. Build assets (once)

docker compose --profile build run --rm assets

# 2. Start app

docker compose up -d --build app nginx db

docker compose --profile build up --build -d

# CONFIRMATION command

docker exec -it recipe-app php -r "
echo 'Opcache: ' . (opcache_get_status()['opcache_enabled'] ? 'ON' : 'OFF') . PHP_EOL;
echo 'JIT: ' . (opcache_get_status()['jit']['enabled'] ? 'ON' : 'OFF') . PHP_EOL;
"

curl -o /dev/null -s -w "%{time_total} sec\n" http://localhost:9020
