# Commands Reference

All commands run from `backend/` unless noted.

---

## Quick Start — First Time Setup

### One-Liner (fresh clone to running)

```bash
composer install && cp .env.example .env && php artisan key:generate && php artisan migrate && php artisan db:seed && php artisan psgc:seed && npm install && npm run build && php artisan config:clear && php artisan view:clear && php artisan route:clear
```

Then start the app with 3 terminals (see [Running the App](#7-running-the-app-3-terminals)).

### Step-by-Step

#### 1. Install Dependencies
```bash
composer install
npm install
```

#### 2. Environment
```bash
cp .env.example .env
```

Then **edit `.env`** with your database credentials, SMTP settings, and `APP_URL`.

#### 3. Generate App Key
```bash
php artisan key:generate
```

#### 4. Database
```bash
php artisan migrate
php artisan db:seed
php artisan psgc:seed   # Philippine addresses for cascading dropdowns (~1-2 min, requires internet)
```

#### 5. Frontend
```bash
npm run build
```

#### 6. Clear Cache
```bash
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

#### 7. Running the App (3 Terminals)

Open **3 separate PowerShell/CMD terminals**, all from `backend/`:

| Terminal | Command | What it does |
|---|---|---|
| **1** | `php artisan serve` | Laravel web server — open `http://localhost:8000` in browser |
| **2** | `npm run dev` | Vite frontend hot-reload |
| **3** | `php artisan queue:work --tries=3` | Email delivery — continuous, auto-sends queued emails |

Leave all 3 terminals open. No need to type anything else after starting them.

---

## Setup (Quick Reference)

| Command | What it does |
|---|---|
| `composer install` | Install PHP dependencies |
| `npm install` | Install frontend dependencies |
| `cp .env.example .env` | Create environment file |
| `php artisan key:generate` | Generate app key |
| `php artisan migrate` | Run pending migrations |
| `php artisan migrate:fresh --seed` | Reset DB + seed (dev only) |
| `php artisan psgc:seed` | Seed Philippine provinces, cities, and barangays from PSGC API (~30-60s, requires internet) |
| `composer setup` | Full install (composer, .env, key, migrate, npm) |

---

## Development

| Command | What it does |
|---|---|
| `composer dev` | Start server + queue + logs + Vite concurrently |
| `php artisan serve` | Start Laravel dev server (default port 8000) |
| `npm run dev` | Start Vite dev server (HMR for frontend) |

---

## Cache (Route, Config, View)

| Command | When to use |
|---|---|
| `php artisan route:clear` | **After adding/renaming any controller** — fixes "Target class does not exist" error |
| `php artisan config:clear` | After changing `.env` or config files |
| `php artisan view:clear` | After editing Blade templates if they don't reflect changes |
| `php artisan cache:clear` | After changing cached model data or settings |
| `php artisan optimize` | Production: cache routes + config + views for speed |

**Tip:** If you see `Target class [...] does not exist`, always run `php artisan route:clear` first.

**Full clear (do all at once):**
```bash
php artisan config:clear && php artisan view:clear && php artisan route:clear
```

---

## Database

| Command | What it does |
|---|---|
| `php artisan migrate` | Run pending migrations |
| `php artisan migrate:rollback` | Roll back last batch |
| `php artisan migrate:fresh` | Drop all tables + re-migrate |
| `php artisan migrate:fresh --seed` | Drop + migrate + seed |
| `php artisan db:seed` | Run seeders |
| `php artisan db:seed --class=StudentSeeder` | Run a specific seeder |

---

## Queue Worker (Email Delivery)

Required for **async emails** (DTR review, journal review, document updates, certificate uploads/verification, HTE evaluations).

**Continuous (auto — recommended):**
```bash
php artisan queue:work --tries=3
```
Stays running in the terminal. New emails are sent within seconds as they're queued.

**One-shot (manual — run after each action):**
```bash
php artisan queue:work --once
```

**PHP binary path (if `php` is not recognized in PowerShell):**
```bash
C:\xampp\php\php.exe artisan queue:work --tries=3
```

---

## Testing

| Command | What it does |
|---|---|
| `composer test` | `artisan config:clear` + `artisan test` — runs all PHPUnit tests |
| `php artisan test` | Run PHPUnit directly |
| `php artisan test --filter=StudentPortalTest` | Run a specific test class |
| `php artisan test --filter="test name"` | Run a specific test method |

**Database:** Tests use MySQL — `internship_testing` on 127.0.0.1.

---

## Email Testing

| Command | What it does |
|---|---|
| `php artisan tinker` | Open REPL, then use `Mail::to(...)->send(...)` |

**SMTP:** Gmail App Password — `chmsu.internship.system@gmail.com`

---

## Artisan Generators

| Command | Creates |
|---|---|
| `php artisan make:model Post -mfc` | Model + migration + factory + controller |
| `php artisan make:controller PostController` | Controller |
| `php artisan make:mail PostMail` | Mailable class |
| `php artisan make:migration create_posts_table` | Migration only |
| `php artisan make:factory PostFactory` | Factory only |

---

## Tinker (REPL)

```bash
php artisan tinker
```

Useful one-liners:

```php
// Send a test notification email
Mail::to('email@example.com')->send(new NotificationMail(
    recipientName: 'Test',
    subjectText: 'Test Subject',
    bodyText: 'Hello from tinker',
));

// Check how many jobs are in the queue
Queue::size();

// Run a single queue job from tinker
Artisan::call('queue:work', ['--once' => true]);
```

---

## Troubleshooting

| Problem | Fix |
|---|---|
| `Target class [XxxController] does not exist` | `php artisan route:clear` |
| Changes to `.env` not taking effect | `php artisan config:clear` |
| Blade template changes not showing | `php artisan view:clear` |
| `php` not recognized in PowerShell | Add `C:\xampp\php` to system PATH (see below) |
| Emails not sending | Make sure Terminal 3 (`queue:work --tries=3`) is running |
| `npm` not recognized in PowerShell | Add `C:\Program Files\nodejs` to system PATH |

### Fix: Add PHP to PATH (permanent)

1. **Win + X** → **System**
2. **Advanced system settings**
3. **Environment Variables**
4. Under **System variables**, select **Path** → **Edit**
5. **New** → paste `C:\xampp\php`
6. **OK** all windows → restart PowerShell

---

## Hostinger Deployment Notes

When deploying to Hostinger (shared hosting), the setup changes:

| Step | Local (XAMPP) | Hostinger |
|---|---|---|
| PHP binary | `C:\xampp\php\php.exe` | system `php` (check version via `php -v`) |
| Database | MySQL on localhost | Hostinger-provided MySQL credentials |
| `.env` | local config | Update DB, APP_URL, SMTP to Hostinger values |
| Queue worker | `php artisan queue:work --tries=3` (continuous) | Cron job: `* * * * * php /path/to/artisan queue:work --once` |
| SMTP | Gmail App Password | Hostinger email or Gmail SMTP |
| Public URL | `http://localhost:8000` | Your domain (e.g. `https://internship.yourdomain.com`) |
| File uploads | Local storage | May need symbolic link: `php artisan storage:link` |

**Hostinger checklist:**
- Set `APP_URL` to your domain
- Use Hostinger's MySQL database credentials (not localhost)
- Add a cron job for the queue worker (control panel → Cron Jobs):
  ```
  * * * * * php /home/username/domains/domain.com/public_html/backend/artisan queue:work --once
  ```
- Set `QUEUE_CONNECTION=database` in `.env`
- Run `npm run build` locally and upload the built `public/` assets
- Ensure `public/storage` symlink exists for file uploads: `php artisan storage:link`
