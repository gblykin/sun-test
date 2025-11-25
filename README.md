# Product Catalog

Product catalog web application for photovoltaic industry products.

## Quick Start

For new developers setting up the project for the first time:

```bash
# 1. Clone the repository (if not already done)
mkdir sun-test && cd sun-test
git clone <repository-url> .

# 2. Start infrastructure
docker-compose up -d

# 3. Install PHP dependencies
docker-compose run --rm console composer install

# 4. Copy environment file and configure if needed
cp src/.env.example src/.env

# 5. Generate application key
docker-compose run --rm console php artisan key:generate

# 6. Run migrations
docker-compose run --rm console php artisan migrate

# 7. Seed database (optional)
docker-compose run --rm console php artisan db:seed
```

## Working with Console

You can also use the console container for interactive work:

```bash
# Enter console container
docker-compose run --rm console bash

# Inside the container, you can run:
composer install
php artisan migrate
php artisan db:seed
php artisan tinker
# etc.
```

## Access

- Application: http://localhost
- phpMyAdmin: http://localhost:8080
