# Laravel Excel Importer

Система для импорта данных из Excel-файлов в базу данных с валидацией, обработкой ошибок и API для доступа к данным.


# Локальное разворачивание проекта с помощью Docker

```
git clone git@github.com:MkhK6/excel-import.git
```

```bash
cp .env.example .env
```

```bash
cp src/.env.example src/.env
```

Задать значения переменным окружения в .env и src/.env


```bash
docker-compose up -d
```

```bash
docker-compose exec app php artisan key:generate
```

```bash
docker-compose exec app php artisan migrate
```

```bash
npm run dev
```

```bash
docker-compose exec app php artisan queue:work --queue=imports
```

Проект будет доступен по адресу:
- `/import`

Api эндпоинт для получения импортированных данных:
- `GET /api/rows`