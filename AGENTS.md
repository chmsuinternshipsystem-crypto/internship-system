# AGENTS.md — Internship System

> **⚠️ Before any work: read `/mnt/c/xampp/htdocs/intern/CAPSTONE-FINAL-TRACKER.md` first.**
> That file is the single source of truth for completed work, planned work, and project conventions.
>
> **Post-defense revisions tracked in `FINAL-DEFENSE-OUTCOME.md`.**

## Project structure

All application code lives under `backend/`. This is a Laravel 12 app (PHP 8.2+).

```
backend/
├── app/
│   ├── Console/Commands/       # Artisan commands
│   ├── Http/
│   │   ├── Controllers/        # 29 business controllers + auth controllers
│   │   ├── Middleware/         # EnsureStudentSession, RoleMiddleware, etc.
│   │   └── Requests/          # Form request classes
│   ├── Imports/               # StudentsImport (Excel)
│   ├── Mail/                  # OTP, notification, HTE link emails
│   ├── Models/                # 31 Eloquent models
│   ├── Policies/              # Authorization policies
│   ├── Services/              # DocumentWorkflowEngine, NotificationService (email + DB notifications)
│   └── Support/               # InternshipRoles, MessageActor, helpers
├── database/migrations/       # 90 migrations (never edit merged ones)
├── routes/
│   ├── web.php                # All main routes
│   └── auth.php               # Login/logout/reset routes
├── tests/Feature/             # Feature tests, use RefreshDatabase
└── tests/Unit/                # Unit tests
```

## Key commands (run from `backend/`)

| Command | What it does |
|---|---|
| `composer test` | `artisan config:clear` then `artisan test` — runs PHPUnit |
| `composer setup` | Full install: composer, .env, key:generate, migrate, npm |
| `composer dev` | Starts server + queue + logs + Vite concurrently |
| `php artisan migrate` | Run pending migrations |
| `php artisan migrate:fresh --seed` | Reset DB (dev only) |
| `php artisan make:model Post -mfc` | Model + migration + factory |

## Authentication

**Two separate auth systems — do not mix them.**

| System | Table | Login | Guard |
|---|---|---|---|
| Staff | `users` | email + password | Laravel `web` guard |
| Student | `student_accounts` | student_number + password | Session-based (`student_account_id`) |

- Staff roles (defined in `InternshipRoles.php`): `instructor`, `chairperson`, `dean`, `student`
- Student routes are under `student.` prefix, protected by `student.auth` middleware (`EnsureStudentSession`)
- Student login flow: `student_number + password` → OTP verification → session stored
- Student portal has two modes: limited (documents + announcements only) and full access
- Staff and student sessions are mutually exclusive (staff session clears student_account_id, student session requires no staff auth)

## Routing conventions

- Staff CRUD routes use explicit named routes (not `Route::resource`): `students.index`, `students.create`, `students.store`, etc.
- Literal `*/create` routes **must** be registered before `resource/{id}` routes or "create" is captured as an ID (noted in `web.php`)
- External HTE transaction routes use one-time tokens (no login required)
- Public attendance check-in is not behind auth middleware

## Roles & permissions

The single source of truth is `app/Support/InternshipRoles.php`. Role middleware uses `role:instructor,chairperson,...` syntax (see `RoleMiddleware.php`).

Key role groupings:
- `staffEmailRoles()` — instructor, chairperson, dean (all staff)
- `programAdministratorRoles()` / `operationalManagerRoles()` — instructor only
- `institutionalMonitoringRoles()` — instructor, chairperson, dean
- `sidebarShows()` — fine-grained sidebar visibility per role (chairperson hides companies, deployments, required-documents, weekly-journals, dtr, certificates; dean hides students, companies, deployments, required-documents, attendance, weekly-journals, dtr, certificates, workflow-queue)
- `weeklyJournalViewerRoles()` / `dtrViewerRoles()` / `certificateViewerRoles()` — instructor only (routes already gated to `role:instructor`)

## Testing

- **Uses MySQL** (not SQLite). DB: `internship_testing` on 127.0.0.1. See `phpunit.xml`.
- Feature tests use `RefreshDatabase` trait
- Test files create their own data via factories or direct model creation (see `StudentPortalTest.php` for patterns)
- Run with: `composer test` (or `php artisan test`)
- OTP-based tests require mail mock (array driver)

## Notable conventions

- Student attendance has a **10-meter geofence** rule with statuses: `inside_pass`, `near_boundary_review`, `outside_flagged`, `location_unavailable`
- Student feedback evaluation: one per active deployment period (duplicate prevention)
- Attendance: one check-in per student per day (duplicate prevention)
- Student model has `HasDeleteProtection` trait that blocks deletion when related records exist
- `progress_pct` on students is auto-computed from mandatory document completion
- Student name is auto-composed: `last_name, first_name middle_name ext`
- Document workflow uses signing chains (`DocumentWorkflowEngine`)
- First-login password change is enforced for both staff and students
- `.env` defaults to SQLite; `.env.testing` uses MySQL; test `phpunit.xml` overrides DB to MySQL

## Frontend

- Vite + Tailwind CSS + Alpine.js
- Run `npm run dev` (or via `composer dev` which includes it)
- Build: `npm run build`

## Existing skills

`opencode/agents/skills/` contains reusable guidance for:
- `laravel-best-practices/` — general Laravel 13 conventions
- `laravel-migrations-and-factories/` — safe migration patterns
- `systematic-debugging/` — bug investigation workflow
- `brainstorming/`, `frontend-design/`, `grill-me/`, `find-skills/`, `agent-browser/`

Load them with the `skill` tool when relevant.
