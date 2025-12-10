# Symfony API with Nginx, PHP 8.4, MariaDB 11.4, Prometheus, Grafana

REST API на Symfony 8 с JWT аутентификацией, мониторингом через Prometheus и визуализацией в Grafana.

## Стек технологий

- **Backend**: Symfony 8 (ORM, Validator, Serializer, Security)
- **PHP**: 8.4-FPM с APCu для метрик Prometheus
- **Web Server**: Nginx
- **Database**: MariaDB 11.4
- **Monitoring**: Prometheus + Grafana
- **Authentication**: JWT (JSON Web Tokens)

## Структура проекта

- Порты/креды вынесены в корневой `.env` (см. `.env.example`)
- Каталоги сервисов в нижнем регистре (`nginx/`, `php/`, `mariadb/`, `prometheus/`, `grafana/`)
- Каталоги PHP-нэймспейсов внутри `app/src` — с заглавной (`Controller/`, `Entity/`, `Repository/`)
- Логи и данные в `logs/{service}/` и `data/{service}/`
- Миграции выполняются автоматически при запуске контейнера PHP

## Быстрый старт

1. Скопируйте `.env.example` в `.env` и настройте переменные:
```bash
cp .env.example .env
```

2. Запустите проект:
```bash
docker compose up --build
```

3. Доступ к сервисам:
- **API**: `http://localhost:${WEB_PORT}` (по умолчанию 8080)
- **Prometheus**: `http://localhost:${PROMETHEUS_PORT}` (по умолчанию 9090)
- **Grafana**: `http://localhost:${GRAFANA_PORT}` (по умолчанию 3000)
  - Логин: `admin` / Пароль: `admin`
- **Database**: `localhost:${DB_PORT}` (по умолчанию 3306)

## Переменные окружения (`.env`)

### Web Server
- `WEB_PORT` — внешний порт nginx

### Database
- `DB_PORT` — внешний порт MariaDB
- `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`
- `MYSQL_DATABASE`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD` — для MariaDB entrypoint

### Symfony
- `APP_ENV` — окружение (dev/prod)
- `APP_SECRET` — секретный ключ для шифрования
- `DATABASE_URL` — строка подключения к БД

### JWT Authentication
- `JWT_SECRET` — секретный ключ для подписи JWT токенов (минимум 32 символа)
- `JWT_EXPIRATION` — время жизни токена в секундах (по умолчанию 3600)

### Monitoring
- `PROMETHEUS_PORT` — внешний порт Prometheus
- `GRAFANA_PORT` — внешний порт Grafana

## Структура директорий

```
.
├── docker-compose.yml          # Конфигурация всех сервисов
├── .env                        # Переменные окружения (не в git)
├── .env.example                # Пример переменных окружения
├── .gitignore                  # Игнорируемые файлы
├── request.http                # Примеры HTTP запросов
│
├── app/                        # Symfony приложение
│   ├── src/
│   │   ├── Controller/         # Контроллеры API
│   │   ├── Entity/             # Doctrine сущности
│   │   ├── Repository/         # Репозитории
│   │   ├── Service/            # Сервисы (JWT, Metrics)
│   │   ├── Security/           # Security компоненты
│   │   └── ...
│   ├── migrations/             # Миграции БД
│   └── ...
│
├── php/                        # PHP-FPM контейнер
│   ├── Dockerfile
│   └── php.ini
│
├── nginx/                      # Nginx контейнер
│   ├── Dockerfile
│   └── conf.d/
│       └── default.conf
│
├── mariadb/                    # MariaDB контейнер
│   └── Dockerfile
│
├── prometheus/                 # Prometheus контейнер
│   ├── Dockerfile
│   ├── entrypoint.sh
│   └── prometheus.yml
│
├── grafana/                    # Grafana контейнер
│   ├── Dockerfile
│   ├── entrypoint.sh
│   └── provisioning/
│       ├── datasources/
│       └── dashboards/
│
├── data/                       # Данные сервисов (volumes)
│   ├── mariadb/
│   ├── prometheus/
│   └── grafana/
│
└── logs/                       # Логи сервисов
    ├── nginx/
    ├── php/
    ├── mariadb/
    ├── prometheus/
    └── grafana/
```

## API Endpoints

### Аутентификация (публичные)

- `POST /api/auth/register` — регистрация нового пользователя
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }
  ```
  Ответ: `{ "user": {...}, "token": "jwt_token" }`

- `POST /api/auth/login` — вход в систему
  ```json
  {
    "email": "john@example.com",
    "password": "password123"
  }
  ```
  Ответ: `{ "user": {...}, "token": "jwt_token" }`

### Пользователи (требуется авторизация)

Все запросы требуют заголовок: `Authorization: Bearer {token}`

- `GET /api/users` — список всех пользователей
- `GET /api/users/{id}` — получить пользователя по ID
- `GET /api/users/me` — получить текущего авторизованного пользователя
- `PUT/PATCH /api/users/me` — обновить профиль текущего пользователя
  ```json
  {
    "name": "Updated Name",
    "email": "updated@example.com"
  }
  ```

### Посты (требуется авторизация)

Все запросы требуют заголовок: `Authorization: Bearer {token}`

- `GET /api/posts` — список всех постов
- `GET /api/posts/{id}` — получить пост по ID
- `POST /api/posts` — создать пост (автоматически привязывается к текущему пользователю)
  ```json
  {
    "title": "Post Title",
    "body": "Post content"
  }
  ```
- `PUT/PATCH /api/posts/{id}` — обновить пост (только владелец)
  ```json
  {
    "title": "Updated Title",
    "body": "Updated content"
  }
  ```
- `DELETE /api/posts/{id}` — удалить пост (только владелец)

### Мониторинг

- `GET /metrics` — метрики Prometheus (публичный endpoint)

## Примеры использования

### Регистрация и создание поста

```bash
# 1. Регистрация
curl -X POST http://localhost:8080/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Alice","email":"alice@example.com","password":"password123"}'

# Ответ содержит token, сохраните его
TOKEN="your_jwt_token_here"

# 2. Создание поста
curl -X POST http://localhost:8080/api/posts \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"title":"My First Post","body":"Post content"}'

# 3. Получение всех постов
curl -X GET http://localhost:8080/api/posts \
  -H "Authorization: Bearer $TOKEN"
```

### Использование request.http

В файле `request.http` содержатся готовые примеры всех запросов. Используйте расширение REST Client для VS Code или аналогичное.

## Мониторинг

### Prometheus

Доступен по адресу `http://localhost:9090`

**Собираемые метрики:**
- `app_http_requests_total` — общее количество HTTP запросов (по method, route, status)
- `app_http_request_duration_seconds` — длительность HTTP запросов (гистограмма)
- `app_validation_errors_total` — количество ошибок валидации (по endpoint)
- `app_users_total` — общее количество пользователей (gauge)
- `app_posts_total` — общее количество постов (gauge)

**Примеры запросов:**
- `rate(app_http_requests_total[5m])` — количество запросов в секунду за 5 минут
- `histogram_quantile(0.95, app_http_request_duration_seconds)` — 95-й перцентиль времени ответа

### Grafana

Доступен по адресу `http://localhost:3000` (admin/admin)

**Предустановленный дашборд:**
- `api-metrics.json` — дашборд с визуализацией метрик API:
  - HTTP запросы (rate, total)
  - Длительность запросов
  - Коды статусов
  - Ошибки валидации
  - Количество пользователей и постов

**Настройка:**
- Prometheus автоматически подключен как datasource
- Дашборды загружаются автоматически из `grafana/provisioning/dashboards/`

## Миграции базы данных

Миграции выполняются автоматически при запуске контейнера PHP через `php/Dockerfile`.

**Ручной запуск миграций:**
```bash
docker compose exec php bin/console doctrine:migrations:migrate
```

**Создание новой миграции:**
```bash
docker compose exec php bin/console doctrine:migrations:generate
```

**Первичная миграция:** `app/migrations/Version20251208000000.php`
- Создает таблицы `users` (с полем `password` для JWT) и `posts`
- При запуске удаляет существующие таблицы для чистой установки

## Особенности реализации

- **JWT Authentication**: Токены выдаются при регистрации/логине, проверяются через `JwtAuthListener`
- **Авторизация**: Пользователи могут изменять/удалять только свои посты
- **Валидация**: Все входные данные валидируются через Symfony Validator
- **Метрики**: Используется APCu для хранения метрик между PHP-FPM воркерами
- **JSON Responses**: Все ответы API возвращаются в формате JSON, включая ошибки
- **Error Handling**: Централизованная обработка исключений через `ExceptionListener`

## Подготовка к использованию

Для очистки данных и перезапуска проекта:

```bash
# Остановить и удалить контейнеры
docker compose down

# Удалить данные (опционально)
rm -rf data/*

# Запустить заново
docker compose up --build
```

## Разработка

- Все изменения в `app/` применяются сразу (volume mount)
- Логи доступны в `logs/{service}/`
- Для отладки используйте `APP_ENV=dev` в `.env`

