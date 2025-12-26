# Copilot / AI agent instructions for this repository

Purpose: Give an AI coding agent the minimal, specific knowledge to be productive in this Laravel app.

- **Quick summary:** Laravel 10 app for managing courts, bookings and payments. Admin UI lives under `admin` routes and controllers at `app/Http/Controllers/Admin` with views in `resources/views/Admin` (capital A).

- **Quick setup (local dev)**
  - Install PHP deps: `composer install`
  - Install JS deps: `npm install`
  - Build assets (dev): `npm run dev` (uses Vite)
  - Run migrations + seeders: `php artisan migrate --seed`
  - Start dev server (if not using XAMPP): `php artisan serve`

- **Tests & linting**
  - Run tests: `./vendor/bin/phpunit` (project has `phpunit.xml` with testing env values)
  - Lint/format: `./vendor/bin/pint` (Pint is included in dev deps)
  - Note: `phpunit.xml` comments out `sqlite` memory DB; tests may require DB config in `.env.testing` or enabling sqlite in `phpunit.xml`.

- **Key files to inspect for behavior examples**
  - Routes: [routes/web.php](routes/web.php) — admin routes use `Route::prefix('admin')` and name `admin.*`.
  - Admin controller patterns: [app/Http/Controllers/Admin/PaymentController.php](app/Http/Controllers/Admin/PaymentController.php) and other controllers in the same folder.
  - Models: [app/Models/Booking.php](app/Models/Booking.php), [app/Models/Payment.php](app/Models/Payment.php) — note `fillable`, relationships, and method names used in controllers.
  - DB schema: `database/migrations/*_create_*.php` — look at `payments` and `bookings` migrations for enum values and cascade rules.
  - Seeders: `database/seeders/DatabaseSeeder.php` (calls `UserSeeder`, `CourtSeeder`, `BookingSeeder`, `PaymentSeeder`) — useful to generate realistic local data.
  - Layout: [resources/views/Admin/layout/app.blade.php](resources/views/Admin/layout/app.blade.php) — all admin views extend this.

- **Project-specific conventions / gotchas**
  - Admin area convention: controllers under `app/Http/Controllers/Admin` + route prefix `admin`. Views follow `resources/views/Admin/*` (capital A) and extend `Admin.layout.app`.
  - Models use explicit `protected $fillable` arrays — prefer mass assignment through fillable fields.
  - Enums in migrations: Payments `method` = `cash|bank_transfer|momo|vnpay`; `status` = `unpaid|paid|failed`. Bookings `status` = `pending|confirmed|cancelled`. Use these exact strings.
  - Relationships: `Booking::hasOne(Payment)`; controllers use `whereDoesntHave('payment')` to find unpaid bookings.
  - Migrations use `cascadeOnDelete()` for FK constraints — deleting a `booking` will remove related `payment` automatically.
  - Seeding creates an admin user with email `admin@pickleball.com` and password `admin123` (see `UserSeeder`). Useful for UI tests.
  - Controllers use `DB::transaction` for payment operations to ensure data integrity.
  - View names in controllers must match case: use `'Admin.payments.index'` not `'admin.payments.index'` (views are in `Admin/` capital).
  - Flash messages often include emojis for user feedback.

- **Common developer workflows**
  - Create a new feature: add route in `routes/web.php`, create controller under `app/Http/Controllers/Admin`, add view in `resources/views/Admin/*`, update model if needed and run `php artisan migrate`.
  - Recreate DB locally: `php artisan migrate:fresh --seed`
  - Quick manual test of payments: use `admin/payments/create` UI (controller: `storeManual`) or call `admin/payments/{booking}/pay` to pay a booking.
  - For bookings, validate no overlapping times in `BookingController::store`.

- **Integration & external deps**
  - Composer deps: `laravel/framework`, `laravel/sanctum`, `guzzlehttp/guzzle`. Expect standard Laravel middleware and Sanctum for API auth if used.
  - Frontend: Vite + `laravel-vite-plugin`. JS entry at `resources/js/bootstrap.js` and `resources/js/app.js`.

- **What an AI agent should do first when editing code**
  1. Run `composer install` and `npm install` (or confirm deps) to ensure autoloaders and assets build.
  2. Run `php artisan migrate --seed` or `php artisan migrate:fresh --seed` in a disposable DB to validate DB changes.
  3. Run `./vendor/bin/phpunit` after edits that touch behavior.

- **Examples (concrete patterns to follow)**
  - When creating controllers, follow the `Admin` folder naming and use route names like `admin.payments.index` (see [routes/web.php](routes/web.php)).
  - Follow model fillables, e.g. `Payment::create([...])` uses attributes in `Payment::$fillable` (see [app/Models/Payment.php](app/Models/Payment.php)).
  - Use `DB::transaction` for multi-step operations like payments (see `PaymentController::confirmTransfer`).

If anything here is unclear or you'd like more detail (e.g., CI steps, environment variables, or example tests), tell me which areas to expand and I'll update this file.
