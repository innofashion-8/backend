# Innofashion Backend

Innofashion Backend is a Laravel API for managing events, competitions, participant registration, attendance, QR scanning, evaluation forms, exports, and admin permissions.

## Overview

The backend powers the Innofashion frontend through public APIs, authenticated user APIs, and protected admin APIs. It supports Google login flows, user registration status, competition submissions, event attendance, evaluation responses, Excel exports, and role/permission management.

## Key Features

- User and admin authentication flows, including Google login support.
- Public event and competition listing APIs.
- Participant profile completion and registration draft/submit/status flows.
- Competition registration, upload, chunk upload, draft, and status endpoints.
- Event registration, attendance scan, and evaluation endpoints.
- Admin statistics, user management, registration review, status updates, and exports.
- Event and competition CRUD APIs.
- Evaluation question builder and response collection.
- QR attendance workflows, including scan and rotating QR support.
- Division, admin, role, and permission management.

## Tech Stack

- PHP 8.2
- Laravel 12
- Laravel Sanctum
- Laravel Socialite
- Spatie Laravel Permission
- Maatwebsite Excel
- Simple QR Code
- MySQL/MariaDB
- Vite and Tailwind CSS for Laravel assets

## Project Structure

```text
app/Http/Controllers/       API and admin controllers
app/Models/                 Domain models
database/migrations/        Database schema
database/seeders/           Seed data
routes/api.php              API route definitions
routes/web.php              Web routes
config/                     Laravel configuration
```

## Getting Started

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Configure database and frontend URL values:

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
DB_DATABASE=innof_backend
```

Run migrations and seeders:

```bash
php artisan migrate --seed
```

Start the backend:

```bash
php artisan serve
```

For local development with queue and frontend asset watcher, use the project's Composer development script if configured:

```bash
composer run dev
```

## API Areas

- `auth`: login, logout, profile, Google auth
- `events`: public data, registration, attendance, evaluation
- `competitions`: public data, registration, upload, status
- `admin`: stats, users, events, competitions, registrations, exports, roles, permissions

## Security Notes

- Admin endpoints must remain protected by authentication and authorization middleware.
- Upload endpoints should enforce file type, size, ownership, and storage visibility rules.
- QR attendance codes should be time-bound or rotated to reduce replay risk.
- Export endpoints may contain personal data and should be permission-gated.

## Suggested Tests

- Feature tests for auth, registration submission, admin review, QR scan, and exports.
- Authorization tests for admin-only routes.
- File upload tests for invalid size/type and unauthorized access.

## Status

Backend API for a full-stack event and competition management platform.
