# Hotel Booking System

A hotel room booking engine built with Laravel 12, featuring per-guest pricing, meal plan options, tiered discounts, and an admin panel for inventory and discount management.

## Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- MySQL
- XAMPP (or any local MySQL server)

## Setup

### 1. Clone & Install Dependencies

```bash
cd C:\xampp\htdocs
git clone <repo-url> hotel-booking
cd hotel-booking
composer install
npm install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with your database credentials:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotel-booking
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Database

Create the database, then run migrations and seeders:

```bash
php artisan migrate --seed
```

This seeds:

- **Room types:** Standard (5 rooms, ₹200 breakfast surcharge) and Deluxe (5 rooms, ₹400 breakfast surcharge)
- **Inventory:** 30 days of per-guest nightly rates
- **Discounts:** Long stay (3+ nights = 10%, 6+ nights = 20%) and Last minute (≤3 days = 5%)
- **Admin user:** admin@hotel.com / admin@123

### 4. Build Frontend

```bash
npm run build
```

For development with hot reload:

```bash
npm run dev
```

### 5. Start the Server

```bash
php artisan serve
```

Visit [http://localhost:8000](http://localhost:8000)

## Running Tests

```bash
php artisan test
```

Tests use SQLite in-memory — no database setup needed.

## Project Structure

```
app/
├── Application/Search/          # SearchService (pricing, availability, discounts)
├── Domain/Search/
│   ├── Contracts/               # Repository interfaces
│   └── DTOs/                    # RoomAvailabilityDTO, SearchRequestDTO
├── Http/
│   ├── Controllers/             # BookingController, SearchController, Api/*
│   ├── Middleware/               # AdminMiddleware
│   └── Requests/                # Form request validation
├── Infrastructure/Repositories/ # Eloquent repository implementations
├── Models/                      # Booking, RoomType, Inventory, Discount, User
└── Providers/                   # Interface bindings in AppServiceProvider

routes/
├── web.php                      # Blade page routes + auth
└── api.php                      # JSON API endpoints

tests/
├── Feature/                     # BookingController integration tests
└── Unit/                        # SearchService unit tests
```

## Key Features

- **Per-guest pricing:** Different nightly rates for 1, 2, or 3 guests
- **Dual meal plans:** Room Only and With Breakfast shown side by side
- **Tiered discounts:** Long stay and last minute discounts, combinable (capped at 90%)
- **Discount highlighting:** Strikethrough original price, green badge, and discount labels on cards
- **Overbooking prevention:** Transactional availability checks with row locking
- **Admin panel:** SPA-style inventory and discount management (auth required)
