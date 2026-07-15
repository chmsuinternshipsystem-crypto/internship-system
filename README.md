# Internship Deployment Management and Digital Documentation Compliance Monitoring System

**Beneficiary:** CHMSU-Talisay BSIS Program  
**Tech Stack:** Laravel 12 + PHP 8.2+ / Vite + Tailwind CSS 3 + Alpine.js / MySQL

---

## System Requirements

| Dependency | Version | Notes |
|---|---|---|
| PHP | ^8.2 | XAMPP: `C:\xampp\php\php.exe` |
| Composer | 2.x | PHP dependency manager |
| Node.js | ^20 | For Vite/asset building |
| npm | ^10 | Ships with Node.js |
| MySQL | ^8.0 | Both app + test databases |
| Queue Worker | — | Required for email delivery |

---

## Dependencies

### PHP (Composer) — `backend/composer.json`

| Package | Purpose |
|---|---|
| `laravel/framework` ^12.0 | Core framework |
| `maatwebsite/excel` ^3.1 | Student Excel import |
| `barryvdh/laravel-dompdf` ^3.1 | PDF report export |
| `laravel/tinker` ^2.10 | Artisan REPL |

Dev: `laravel/breeze`, `laravel/pint`, `phpunit/phpunit`, `fakerphp/faker`, `mockery/mockery`.

### JavaScript (npm) — `backend/package.json`

| Package | Purpose |
|---|---|
| `alpinejs` ^3.4 | Reactive UI (sidebar, loading, autodismiss) |
| `tailwindcss` ^3.1 + `@tailwindcss/forms` | Utility CSS framework |
| `flatpickr` ^4.6 | Date/time pickers (11 inputs across 8 views) |
| `htmx.org` ^2.0 | AJAX partial updates (review panel) |
| `sortablejs` ^1.15 | Drag-and-drop (reserved, not in active UI) |
| `select2` ^4.1 | Enhanced selects |
| `bootstrap-icons` ^1.13 | Icons throughout UI |
| `jquery` ^4.0 | Required by Select2 |
| `vite` ^7 + `laravel-vite-plugin` ^2 | Build tool |

---

## Database

### App Database: `internship_db`

Created manually once:
```sql
CREATE DATABASE internship_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Test Database: `internship_testing`

Created once (for running `composer test`):
```sql
CREATE DATABASE internship_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Migrations: 90 files

All run via `php artisan migrate`. Located at `backend/database/migrations/`.  
Never edit a migration that has been merged — create a new one instead.

Key migration groups (most recent):
- `2026_06_15_210000_*` — *(latest additions: see changelog below)*
- `2026_06_15_180000_create_daily_time_records_table.php` — DTR module (separate from journals)
- `2026_06_15_170000_drop_submission_mode_from_required_documents.php` — dropped `submission_mode`
- `2026_06_15_145000_drop_notifications_table.php` — removed in-app notification system
- `2026_06_15_000001_add_name_components_to_users_table.php` — staff name components
- `2026_06_14_214845_add_phase_to_required_documents_table.php` — pre/monitoring/post phases
- `2026_06_14_193305_create_certificates_table.php` — instructor certificate uploads
- `2026_06_14_193244_create_weekly_journals_table.php` — weekly journal submissions
- `2026_06_14_171510_remove_status_from_students_table.php` — replaced by `progress_pct`

### Seeders

Run via `php artisan db:seed` (order in `DatabaseSeeder.php`):

| Seeder | What it creates |
|---|---|
| `SampleDataSeeder` | 3 staff users, 5 students, 3 companies, 16 required docs, 3 deployments, sample document submissions, 2 announcements |
| `DocumentWorkflowSeeder` | 5 workflow templates + maps to required documents |
| `InstructorFirstLoginSeeder` | Marks instructor accounts with `first_login = true` |

### Login Credentials (after seeding)

| Role | Email | Password |
|---|---|---|
| Instructor | `instructor@chmsu.edu.ph` | `password` |
| Chairperson | `chairperson@chmsu.edu.ph` | `password` |
| Dean | `dean@chmsu.edu.ph` | `password` |

| Student | Student Number | Password |
|---|---|---|
| Dela Cruz, Juan | `20230001` | `20230001` |
| Santos, Maria | `20230002` | `20230002` |
| Reyes, Carlo | `20230003` | `20230003` |
| Lim, Angela | `20230004` | `20230004` |
| Villanueva, Mark | `20230005` | `20230005` |

---

## Quick Start (First Time)

```bash
# 1. Navigate to backend
cd backend

# 2. Install PHP dependencies
composer install

# 3. Create .env from template and edit it
cp .env.example .env
# → Edit DB credentials (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
# → Edit MAIL_* settings if email is needed

# 4. Generate app encryption key
php artisan key:generate

# 5. Create databases in MySQL (one-time)
# mysql -u root -e "CREATE DATABASE internship_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
# mysql -u root -e "CREATE DATABASE internship_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"

# 6. Run migrations
php artisan migrate

# 7. Seed sample data
php artisan db:seed

# 8. Create storage symlink
php artisan storage:link

# 9. Install JS dependencies
npm install

# 10. Build frontend assets
npm run build
```

Or use the `composer setup` shortcut:
```bash
composer setup
# Then manually: php artisan db:seed  &&  php artisan storage:link
```

---

## Running the System

### Full stack (dev server + queue + logs + Vite):
```bash
composer dev
```
This runs 4 processes concurrently via `concurrently`:
- `php artisan serve` — Laravel dev server (port 8000)
- `php artisan queue:listen` — processes queued emails
- `php artisan pail` — real-time log viewer
- `npm run dev` — Vite hot-reload

### Individual commands:

| Command | Purpose |
|---|---|
| `php artisan serve` | Start dev server (http://localhost:8000) |
| `php artisan queue:work` | Process queued jobs (email delivery) |
| `npm run dev` | Vite hot-reload for Blade assets |
| `npm run build` | Production build of CSS/JS |
| `composer test` | Run PHPUnit |
| `php artisan migrate` | Run pending migrations |
| `php artisan migrate:fresh --seed` | Reset DB + reseed (dev only) |
| `php artisan storage:link` | Create public storage symlink |
| `php artisan config:cache` | Cache config (production) |
| `php artisan route:cache` | Cache routes (production) |

---

## Email Configuration

Default: `MAIL_MAILER=log` (writes to `storage/logs/laravel.log`, no real delivery).

### Gmail SMTP (testing):
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=chmsu.internship.system@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_FROM_ADDRESS=chmsu.internship.system@gmail.com
MAIL_ENCRYPTION=tls
```

### Hostinger (production):
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your@domain.com
MAIL_PASSWORD=mailbox-password
MAIL_FROM_ADDRESS=your@domain.com
MAIL_ENCRYPTION=tls
```

---

## Architecture Overview

### Roles (4): `instructor`, `chairperson`, `dean`, `student`

Removed: `employer`, `vpaa`, `admin`, `coordinator`.

### Authentication: Two separate systems

| System | Table | Login | Middleware |
|---|---|---|---|
| Staff | `users` | email + password | `auth` (Laravel `web` guard) |
| Student | `student_accounts` | student_number + password | `student.auth` (`EnsureStudentSession`) |

Staff and student sessions are mutually exclusive.

### Directory Structure

```
backend/
├── app/
│   ├── Console/Commands/
│   ├── Exports/               # Report PDF/Excel exports
│   ├── Http/
│   │   ├── Controllers/       # 29 controllers + auth controllers
│   │   ├── Middleware/        # EnsureStudentSession, RoleMiddleware
│   │   └── Requests/          # Form request classes
│   ├── Imports/               # StudentsImport (Excel)
│   ├── Mail/                  # OTP, notification, HTE link emails
│   ├── Models/                # 25 Eloquent models (DailyTimeRecord added)
│   ├── Policies/
│   ├── Services/              # DocumentWorkflowEngine, NotificationService
│   └── Support/               # InternshipRoles, MessageActor, helpers
├── database/
│   ├── migrations/            # 90 files
│   └── seeders/               # 4 seeders
├── resources/views/           # Blade templates + components
├── routes/
│   ├── web.php                # Main routes
│   └── auth.php               # Login/logout/reset
└── tests/                     # 81 tests (Feature + Unit)
```

### Key Modules

| Module | What it does |
|---|---|
| Students | CRUD, search, import Excel, progress % tracking, document compliance |
| Companies | CRUD, active/inactive |
| Deployments | Student-company assignment with date overlap validation, auto-computed status |
| Required Documents | Master checklist with pre/monitoring/post phases |
| Student Documents | Per-student uploads, review workflow, signing chains |
| Daily Time Records | Separate DTR module with draft/submit/approve/reject workflow |
| Weekly Journals | Pre-requirements, monitoring, post-requirements sections |
| Certificates | Instructor uploads after monitoring |
| Attendance | Geofenced check-in (10m), exception resolution, export |
| Evaluations | HTE evaluation with email notification to student |
| Announcements | Global broadcast |
| Settings | Maintenance mode, policy review, semester/academic year |
| Reports | Centralized: deployment locations, missing docs, compliance, attendance — PDF + CSV + Print |
| Document Workflow | Multi-step review with role-based signing chains |

---

## Testing

```bash
cd backend && composer test
```

- 23 test classes, ~500+ assertions
- Uses MySQL (`internship_testing`), not SQLite
- Uses `RefreshDatabase` trait
- Mail driver: `array` (no real email sent during tests)
- Queue: `sync` (jobs run inline)

---

## Pre-Deployment Checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false` in `.env`
- [ ] `APP_KEY` set (generated via `php artisan key:generate`)
- [ ] `APP_URL` set to real domain
- [ ] MySQL credentials configured
- [ ] `php artisan config:cache && php artisan route:cache`
- [ ] `php artisan storage:link`
- [ ] Queue worker running: `php artisan queue:work --daemon` (via Supervisor on Linux)
- [ ] `npm run build` for production assets
- [ ] Remove sample data / seed with real data

---

## Changelog

### v2.1 — Post Pre-Oral Refinements (June 2026)
- DTR split from Weekly Journal — separate model, controller, views, routes, sidebar
- Deployment status auto-computed from dates (manual dropdown removed)
- Journal-based progress metric (blue bar) alongside document progress (green bar)
- Dual progress bars on student list (ajax-list) and student show page
- Phase-grouped documents in student portal (Pre / Monitoring / Post / General)
- CSV export for Attendance report
- Print CSS across all pages (sidebar/header hidden, optimized table layout)
- Company address shown on Deployed Per Company report
- Duplicate name warning on student create and Excel import
- Explicit `first_login = true` on import (DB default was already true)

### v2.0 — Pre-Oral Implementation (June 2026)
- All 26 panelist recommendations implemented
- In-app notification system removed; email-only notifications
- Student Excel import (`maatwebsite/excel`)
- Weekly journal + certificate modules
- Real-world deployment date validations (overlap, completed-future-start)
- Flatpickr date pickers across all date inputs
- Email template beautification with CHMSU branding
- UI polish: wider containers, restructured student show page
- Email SMTP hardened with `encryption` key

### v1.x — Prior milestones
- Student portal, attendance geofencing, document workflow engine, reporting
