# Symfony API with Nginx, PHP 8.3, MariaDB 11.4

Стек по требованиям и правилам:
- Порты/креды вынесены в корневой `.env`.
- Каталоги сервисов в нижнем регистре (`nginx/`, `php/`), а каталоги PHP-нэймспейсов внутри `app/src` — с заглавной (например, `Controller`, `Entity`, `Repository`).
- Логи и данные в `logs/nginx/`, `logs/php/`, `logs/mariadb/`, `data/mariadb/`.
- Symfony 8 (ORM, Validator, Serializer) с миграцией для `users`, `posts`.

## Запуск
```
docker compose up --build
```
API: `http://localhost:${WEB_PORT}` (по умолчанию 8080)  
DB: `localhost:${DB_PORT}` (по умолчанию 3306)

## Переменные `.env`
- `WEB_PORT` — внешний порт nginx
- `DB_PORT` — внешний порт MariaDB
- `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`
- `MYSQL_*` аналоги для mariadb entrypoint
- `APP_ENV`, `APP_SECRET`, `DATABASE_URL`

## Структура
- `docker-compose.yml` — сервисы `php`, `nginx`, `db` (env_file -> `.env`)
- `php/` — Dockerfile + php.ini; сборка с composer внутри контейнера
- `nginx/` — Dockerfile и конфиги `conf.d`
- `mariadb/` — Dockerfile для MariaDB (имя папки соответствует базовому образу без версии)
- `app/` — Symfony код, миграции в `app/migrations` (каталоги нэймспейсов с заглавной)
- `data/mariadb/` — volume БД
- `logs/nginx/` — логи nginx
- `logs/php/` — логи PHP-FPM и ошибки PHP
- `logs/mariadb/` — логи MariaDB

## API
- `GET /api/users`, `POST /api/users`, `GET /api/users/{id}`, `PUT/PATCH /api/users/{id}`, `DELETE /api/users/{id}`
- `GET /api/posts`, `POST /api/posts`, `GET /api/posts/{id}`, `PUT/PATCH /api/posts/{id}`, `DELETE /api/posts/{id}`

### Примеры
```
curl -X POST http://localhost:8080/api/users -H "Content-Type: application/json" \
  -d '{"name":"Alice","email":"alice@example.com"}'

curl -X POST http://localhost:8080/api/posts -H "Content-Type: application/json" \
  -d '{"user_id":1,"title":"Hello","body":"First post"}'
```

## Миграции
- Первичная миграция: `app/migrations/Version20251208000000.php` (users, posts).
- При изменениях схемы: `docker compose run --rm php bin/console doctrine:migrations:migrate`.

