# Product Catalog

Product catalog web application for photovoltaic industry products.

## Quick Start

For new developers setting up the project for the first time:

```bash
# 1. Clone the repository (if not already done)
mkdir sun-test && cd sun-test
git clone <repository-url> .

# 2. Start infrastructure
docker compose up -d

# 3. Install PHP dependencies
docker compose run --rm console composer install

# 4. Copy environment file and configure if needed
cp src/.env.example src/.env

# 5. Generate application key
docker compose run --rm console php artisan key:generate

# 6. Run migrations
docker compose run --rm console php artisan migrate

# 7. Seed database (optional)
docker compose run --rm console php artisan db:seed

# 8. Import products from CSV files
docker compose run --rm php php artisan products:import imports/batteries.csv --category=battery
docker compose run --rm php php artisan products:import imports/solar_panels.csv --category=solar-panel
docker compose run --rm php php artisan products:import imports/connectors.csv --category=connector
```

## Working with Console

You can also use the console container for interactive work:

```bash
# Enter console container
docker compose run --rm console bash

# Inside the container, you can run:
composer install
php artisan migrate
php artisan db:seed
php artisan tinker
# etc.
```

## Frontend Development

For development with hot-reload:

```bash
# Start Vite dev server
docker compose up -d node

# Or run manually
docker compose run --rm node npm run dev
```

For production build:

```bash
docker compose run --rm node npm run build
```

## Import Products

Import products from CSV files located in `src/imports/` directory:

```bash
# Import batteries
docker compose run --rm php php artisan products:import imports/batteries.csv --category=battery

# Import solar panels
docker compose run --rm php php artisan products:import imports/solar_panels.csv --category=solar-panel

# Import connectors
docker compose run --rm php php artisan products:import imports/connectors.csv --category=connector
```

**Note:** The file path is relative to the container's working directory (`/srv/www/app`), so use `imports/` not `src/imports/`.

## Access

- Application: http://localhost
- phpMyAdmin: http://localhost:8080
- Vite Dev Server: http://localhost:5173 (when running in dev mode)
