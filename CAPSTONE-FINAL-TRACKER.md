# Internship System — Master Plan

**Project:** Internship Deployment Management & Digital Documentation Compliance Monitoring System
**Beneficiary:** CHMSU-Talisay BSIS Program
**Started:** June 14, 2026

> **Single source of truth.** All completed work, in-progress items, and planned features are tracked here.
> **Walkthrough audit:** `CAPSTONE-WALKTHROUGH-TRACKER.md` contains role-by-role analysis, validation audit, UI consistency findings, and Phase 5 plan.
> Agents: always read this file first before starting new work.

---

## How This Document Works

- **Phases** = major milestones completed
- **Roadmap** = upcoming work, prioritized P0–P2
- Each roadmap item links to a detailed spec in `docs/superpowers/specs/` when available
- Status legend: ✅ Done | 🔧 In Progress | ⏳ Pending

---

# ✅ Phase 0: Foundation (Original Capstone Build)

Dates: June 14–20, 2026. The base system built per panel recommendations.

## Panel Recommendations (26/26 Complete)

| # | Recommendation | Status |
|---|---------------|--------|
| 1 | Display student number on profile | ✅ |
| 2 | Remove Yes/No (all mandatory) | ✅ |
| 3 | Import Excel for Students | ✅ |
| 4 | Middle Initial → Middle Name | ✅ |
| 5 | Auto password on first student login | ✅ |
| 6 | Import default password for instructors | ✅ |
| 7 | Status → Percentage Progress (full replace) | ✅ |
| 8 | Weekly Journal milestones (Pre/Monitoring/Post) | ✅ |
| 9 | Contact Number required | ✅ |
| 10 | All notifications via email (in-app removed) | ✅ |
| 11 | Prevent duplicate student registration | ✅ |
| 12 | Max 2MB file upload | ✅ |
| 13 | Separate DTR & Weekly Journal pages | ✅ |
| 14 | Validate before HTE eval + notify student | ✅ |
| 15 | Cannot approve if not reviewed | ✅ |
| 16 | "Open Checklist" → "Review" button | ✅ |
| 17 | Chairperson dashboard redesign | ✅ |
| 18 | Instructor upload certificate | ✅ |
| 19 | Deployed student → Attendance in monitoring | ✅ |
| 20 | Policy review + maintenance in settings | ✅ |
| 21 | Flag duplicate instructor per section | ✅ |
| 22 | Reports: deployment location | ✅ |
| 23 | Centralize reports + filters + export + print | ✅ |
| 24 | Filter missing/pending documents | ✅ |
| 25 | Config pre/post requirements for instructors | ✅ |
| 26 | Export attendance | ✅ |

## Database Migrations (11 Original)

| # | Migration | Status |
|---|-----------|--------|
| 1 | `add_middle_name_to_students_table` | ✅ |
| 2 | `add_progress_pct_to_students_table` | ✅ |
| 3 | `make_contact_number_required_on_students_table` | ✅ |
| 4 | `add_first_login_to_student_accounts_table` | ✅ |
| 5 | `create_weekly_journals_table` | ✅ |
| 6 | `create_certificates_table` | ✅ |
| 7 | `add_phase_to_required_documents_table` | ✅ |
| 8 | `add_policy_settings_to_settings_table` | ✅ |
| 9 | `add_deployment_location_to_deployments_table` | ✅ |
| 10 | `remove_status_from_students_table` | ✅ |
| 11 | `drop_notifications_table` | ✅ |

---

# ✅ Phase 1: Weekly Journal Automation

A complete overhaul of the weekly journal module: auto-generation, inline editor, auto-save, late detection, and streamlined staff review.

## Spec
`docs/superpowers/specs/2026-06-20-weekly-journal-automation-design.md`

## Changes

| Area | What Changed |
|------|-------------|
| **Model** | `WeeklyJournal`: added `submitted_at`, `is_late` accessor (auto-calculated: submitted_at > week_end_date) |
| **Controller** | Removed `create`/`studentStore`/`studentUpdate`/`downloadTemplate`. Added `updateActivities`, `uploadFile`, `deleteFile`, `ensureWeeksExist`, `studentProgress`. |
| **Student Views** | Index: week grid cards with status badges. Show: Alpine.js SPA with auto-save (600ms debounce on PATCH), inline file uploads via fetch, no Save buttons. |
| **Staff Views** | Index: corrected filter (Draft/Submitted/Reviewed). Show: activities table (Mon–Sat), supervisor info, late flag, single "Mark as Reviewed" button. |
| **Progress** | Added per-student progress page (`weekly-journals.student`), "View Journals" link on student profile, "All Journals" link on staff show |
| **Routing** | All URLs relative (no `route()` helper → broken on localhost) |
| **Submit Flow** | Native `confirm()` replaced with Alpine teleport modal |
| **Late Detection** | Red banner + badge on student index/show; badge + red header on staff show |
| **Migration** | `2026_06_20_000004_add_submitted_at_to_weekly_journals` |

## Files Changed

```
app/Http/Controllers/WeeklyJournalController.php (major rewrite)
app/Models/WeeklyJournal.php (+submitted_at, +is_late)
database/migrations/2026_06_20_000004_add_submitted_at_to_weekly_journals.php
resources/views/student/weekly-journals/index.blade.php (redesigned)
resources/views/student/weekly-journals/show.blade.php (redesigned, Alpine SPA)
resources/views/weekly-journals/index.blade.php (filter fix)
resources/views/weekly-journals/show.blade.php (redesigned staff view)
resources/views/weekly-journals/partials/ajax-list.blade.php (badges + late flag)
resources/views/weekly-journals/student.blade.php (new per-student page)
routes/web.php (old journal routes removed, AJAX endpoints added)
Deleted: resources/views/student/weekly-journals/create.blade.php
```

---

# ✅ Phase 2: 11 Usability & Feature Concerns

Post-automation polish pass addressing instructor/student pain points.

| # | Concern | Approach | Files Changed |
|---|---------|----------|--------------|
| 1 | **Student import: duplicate detection** | Data-level tracking: if ALL rows already exist (no new creates or deployments), refuse import with notification | `StudentsImport.php`, `StudentImportController.php` |
| 2 | **Company import: industry not visible** | Added LIKE fallback for fuzzy industry name matching; tracks unmatched names; reports in result modal | `CompaniesImport.php`, `CompanyImportController.php` |
| 3 | **Cascading address dropdown** | Full PSGC provinces/cities/barangays tables + seeder (`php artisan psgc:seed`); Alpine cascading selects on company form; 3 new models, 4 migrations, `AddressController` | `_form.blade.php`, `Company.php`, `StoreCompanyRequest.php`, `UpdateCompanyRequest.php`, `AddressController.php`, `web.php`, `SeedPhilippineAddresses.php`, 3 PSGC models, 4 migrations |
| 4 | **Geofence indicator + filter** | "Geofenced" badge on company index/show; checkbox filter `?geofenced=1` | `companies/index.blade.php`, `companies/show.blade.php`, `ajax-list.blade.php`, `CompanyController.php` |
| 5 | **Remove section conflict warning** | Removed amber "Section X" badge from deployment rows | `deployments/partials/ajax-list.blade.php` |
| 6 | **Student duplicate name modal** | Alpine intercepts form submit → AJAX checks → confirmation modal before proceeding | `students/create.blade.php`, `StudentController.php`, `web.php` |
| 7 | **Student creation flow** | Clear "Internal OJT" / "External OJT (With Company)" radio toggle; company section visually separated from personal info | `students/_form.blade.php` |
| 8 | **Attendance CSV export** | New `export()` method streams CSV with current filters; button on attendance page | `AttendanceController.php`, `attendance/index.blade.php`, `web.php` |
| 9 | **DTR calendar view** | Replaced search/paginated table with month calendar grid; per-cell student names with hours; prev/next nav | `DtrController.php`, `dtr/index.blade.php`, `dtr/partials/calendar.blade.php` (new) |
| 10 | **Certificate page fix** | Fixed broken route name `certificates.student.show` → `student.certificates.show`; added student download route with ownership guard; eager loading | `CertificateController.php`, `student/certificates/show.blade.php`, `web.php` |
| 11 | **Student first-login enforcement** | Added middleware check: if `first_login` true, redirect to password change for ALL student routes (not just post-OTP) | `EnsureStudentSession.php` |

## New Migrations (Phase 2)

| # | Migration | Purpose |
|---|-----------|---------|
| 1 | `2026_06_21_000001_create_philippine_provinces_table` | PSGC provinces |
| 2 | `2026_06_21_000002_create_philippine_cities_table` | PSGC cities/municipalities |
| 3 | `2026_06_21_000003_create_philippine_barangays_table` | PSGC barangays |
| 4 | `2026_06_21_000004_add_address_fks_to_companies` | province_id, city_id, barangay_id on companies |

## New Models

| Model | File |
|-------|------|
| `PhilippineProvince` | `app/Models/PhilippineProvince.php` |
| `PhilippineCity` | `app/Models/PhilippineCity.php` |
| `PhilippineBarangay` | `app/Models/PhilippineBarangay.php` |

## New Command

| Command | Purpose |
|---------|---------|
| `php artisan psgc:seed` | Fetches PSGC data from https://psgc.gitlab.io/api/ and populates address tables |

## Users Must Run

```bash
cd backend
php artisan migrate
php artisan psgc:seed   # requires internet, ~30-60 seconds for 42k barangays
```

---

# ✅ Phase 3: Big Guns

All P0 through P2 features completed on June 21, 2026.

## ✅ P0: Notification Bell (In-App Notifications)

Re-introduced in-app notification center alongside existing email channel.

| Area | What Changed |
|------|-------------|
| **DB** | `notifications` table (Laravel native), batch 19 |
| **Notification** | `InAppNotification` DB notification class |
| **Controller** | `NotificationController` — `index`, `unread`, `recent`, `markRead`, `markAllRead` |
| **Service** | `NotificationService` now creates DB + email in one call |
| **View** | Alpine bell component (`bell.blade.php`) with 30s polling, sidebar integration (staff + student), inbox page (`notifications/index.blade.php`) |
| **Models** | `User` + `StudentAccount` both use `Notifiable` trait |

## ✅ P0: Teaching Load / "My Students"

Assigned instructor filter + dashboard card for instructors.

| Area | What Changed |
|------|-------------|
| **Migration** | `assigned_instructor_id` FK on `students`, batch 19 |
| **Models** | `Student::assignedInstructor()`, `User::supervisedStudents()` |
| **Dashboard** | Card showing supervisee/pending/absent counts |
| **Student Index** | "My Students" toggle filter + instructor column |
| **Student Form** | Instructor selector (defaults to current user) |
| **Filtering** | `my_students` filter on journals index, attendance index |

## ✅ P0: Unified Student Detail Page

Tabbed student show view with 6 partials, URL hash persistence.

| Area | What Changed |
|------|-------------|
| **View** | `students/show.blade.php` → Alpine tab container |
| **Partials** | 6 tab views: `tab-profile`, `tab-documents`, `tab-journals`, `tab-dtr`, `tab-attendance`, `tab-certificates` |
| **Data** | All tabs load server-side (no async HTTP) |
| **Hash** | Tab state persists in URL hash (`#documents`, `#journals`) |
| **Fix** | Broken 403 "View Journals" link for chairperson/dean resolved |

## ✅ P0: Bulk Operations

Batch actions for journals, students, attendance, certificates.

| Area | What Changed |
|------|-------------|
| **Controller** | `BatchController` — `journals/review`, `students/deploy`, `students/assign-instructor`, `attendance/resolve`, `certificates/verify` |
| **Component** | `batchSelect()` Alpine component with `@once` definition |
| **Views** | Checkbox column + bottom action bar on ajax-list partials |

## ✅ P1: At-Risk Auto-Flagging

`php artisan flags:check` — 4 criteria, deduplicated per (student_id, type).

| Area | What Changed |
|------|-------------|
| **Migration** | `student_risk_flags` table with unique(student_id, type), batch 19 |
| **Model** | `StudentRiskFlag` — `student()` belongsTo, `scopeActive()` |
| **Student** | `riskFlags()` hasMany, `activeRiskFlags()` scoped hasMany |
| **Command** | `CheckRiskFlags` — checks: 3+ consecutive absences, 2+ late journals, missing docs 7+ days, expired deployment without cert |
| **Dashboard** | Red alert banner with at-risk count, links to compliance with `?risk=1` |
| **Compliance** | "At Risk" checkbox filter, risk column with flag count + messages |

## ✅ P1: Email Reminders

`php artisan reminders:send` — 5 checks, uses existing `NotificationMail`.

| Area | What Changed |
|------|-------------|
| **Command** | `SendReminders` — journal due tomorrow, journal overdue, no check-in after 4pm, docs pending 7+ days, deployment starting tomorrow |

## ✅ P2: Mass Communication

Batch student selection with group filters in message create form.

| Area | What Changed |
|------|-------------|
| **Query** | `create()` now includes `has_deployment` / `has_pending_documents` subqueries on student data |
| **UI** | Student picker: Select All/Deselect All per section, "All Deployed", "All Undeployed", "Pending Docs" quick-filter pills |
| **Badges** | Each student row shows Deployed/Pending badge inline |

## ✅ P2: Mobile Responsiveness Pass

Student portal bottom nav + responsive tweaks.

| Area | What Changed |
|------|-------------|
| **Bottom Nav** | Fixed bottom bar (Home/Docs/Clock/DTR/Journal) on mobile, hidden `md:` |
| **Padding** | `pb-20 md:pb-0` on main content for student routes |
| **Quick Actions** | Dashboard grid: `grid-cols-3 sm:grid-cols-6` (3 cols on mobile) |
| **File Input** | `w-full sm:max-w-[200px]` on document upload (full-width mobile) |

## ✅ P2: Role-Specific Reports

Two new role-gated PDF report pages.

| Report | Route | Role | Content |
|--------|-------|------|---------|
| Program Health | `reports.program-health` | Chairperson | KPI cards + section comparison table |
| Executive Summary | `reports.executive-summary` | Dean | Pipeline KPIs, company satisfaction, doc compliance, feedback count |

## New Migrations (Phase 3)

| # | Migration | Purpose | Batch |
|---|-----------|---------|-------|
| 1 | `2026_06_21_000005_create_notifications_table` | In-app notifications (Laravel standard schema) | 19 |
| 2 | `2026_06_21_000006_add_assigned_instructor_id_to_students` | Teaching load FK | 19 |
| 3 | `2026_06_21_000007_create_student_risk_flags_table` | Auto-flagging storage | 19 |

## New Commands

| Command | Purpose |
|---------|---------|
| `php artisan flags:check` | Run all 4 at-risk checks, upsert/resolve flags |
| `php artisan reminders:send` | Send 5 types of automated email reminders |

## Scheduler (routes/console.php)

```php
Schedule::command('flags:check')->daily();
Schedule::command('reminders:send')->hourly();
```

Cron entry (`* * * * * /mnt/c/xampp/php/php.exe /mnt/c/xampp/htdocs/intern/backend/artisan schedule:run >> /dev/null 2>&1`) installed on the host.

## Key Files Changed (Phase 3)

```
database/migrations/2026_06_21_000005_create_notifications_table.php
database/migrations/2026_06_21_000006_add_assigned_instructor_id_to_students.php
database/migrations/2026_06_21_000007_create_student_risk_flags_table.php
app/Models/User.php (+ Notifiable trait, + supervisedStudents())
app/Models/StudentAccount.php (+ Notifiable trait)
app/Models/Student.php (+ assignedInstructor(), riskFlags(), activeRiskFlags())
app/Models/StudentRiskFlag.php (new)
app/Notifications/InAppNotification.php (new)
app/Services/NotificationService.php (+ DB notification creation)
app/Http/Controllers/NotificationController.php (new)
app/Http/Controllers/BatchController.php (new)
app/Http/Controllers/ReportController.php (+ programHealth, executiveSummary)
app/Http/Controllers/DashboardController.php (+ at-risk count, supervisees)
app/Http/Controllers/StudentController.php (+ my_students filter, tab data)
app/Http/Controllers/ComplianceController.php (+ risk filter)
app/Http/Controllers/AttendanceController.php (+ my_students filter)
app/Http/Controllers/WeeklyJournalController.php (+ my_students filter)
app/Http/Controllers/MessageThreadController.php (+ has_deployment/pending subqueries, batch select)
app/Console/Commands/CheckRiskFlags.php (new)
app/Console/Commands/SendReminders.php (new)
routes/web.php (+ notification, batch, program-health, exec-summary routes)
routes/console.php (+ schedule for flags:check, reminders:send)
resources/views/notifications/ (bell partial, index page)
resources/views/students/show.blade.php (tab container)
resources/views/students/partials/tab-*.blade.php (6 new partials)
resources/views/students/partials/ajax-list.blade.php (+ checkbox, batch bar, instructor col)
resources/views/students/index.blade.php (+ "My Students" filter)
resources/views/students/_form.blade.php (+ instructor selector)
resources/views/dashboard.blade.php (+ at-risk banner, supervisees card)
resources/views/compliance/index.blade.php (+ risk filter)
resources/views/compliance/partials/ajax-list.blade.php (+ risk column)
resources/views/reports/program-health.blade.php (new)
resources/views/reports/executive-summary.blade.php (new)
resources/views/reports/index.blade.php (+ role-specific links)
resources/views/messages/create.blade.php (+ batch select, quick filters)
resources/views/student/dashboard.blade.php (quick actions responsive)
resources/views/student/partials/document-card.blade.php (file input responsive)
resources/views/layouts/app.blade.php (+ student route padding)
resources/views/layouts/navigation.blade.php (+ bottom nav, sidebar links)

```

---

# 📝 Key Conventions (Must Follow)

- `CAPSTONE-FINAL-TRACKER.md` is the single source of truth for all work tracking
- Individual feature specs go in `docs/superpowers/specs/`
- Use relative URLs (never `route()` for student-facing links; never `Storage::url()`)
- Alpine.js for frontend interactivity, HTMX for partial page swaps
- Two auth systems (staff=web guard, student=session) — never mix
- Role middleware: `role:instructor,chairperson,...` syntax
- Literal `*/create` routes must be registered before `resource/{id}` wildcards
- All student routes under `student.` prefix with `student.auth` middleware
- All staff routes use explicit named routes (no `Route::resource`)
- File uploads: 10MB max for imports, 2MB for documents

---

# ✅ Phase S3: Login & JS Timing       → see below
# ✅ Phase S4: Reports Page Fixes      → see below
# ✅ Phase S5: Migration Integrity      → see below
# ✅ Phase S6: Import View Cleanup      → see below
# ✅ Phase 7: Reports Blank Data & Pagination → see below

---

# 🔧 Phase 4: Product Polish & Automation

Post-panel polish. Eliminating daily friction for students and instructors based on user-journey walkthrough.

## P0 — Core Friction Eliminators

| # | Item | Approach | Status |
|---|------|----------|--------|
| 4.1 | **Attendance: Quick Clock-In** | Prominent Clock In/Out button on student dashboard; posts attendance directly (session=identity, auto-geolocate, auto-detect time_in vs time_out). Full check-in page remains as fallback for kiosk/shared devices. | ✅ |
| 4.2 | **Journal auto-submit** | Auto-submit draft journals when week_end_date has passed + all entries have content. Integrated into `flags:check` command (runs daily). | ✅ |
| 4.3 | **Deployment end_date auto-calculation** | Added end_date to student creation form (shown when External OJT). Auto-computed default: start_date + 15 weeks (105 days). Added validation. | ✅ |
| 4.4 | **Auto-upload on file selection** | Added `onchange="this.closest('form').requestSubmit()"` to document upload forms (student document card + staff upload panel). | ✅ |
| 4.5 | **Risk flags auto-resolve** | Already implemented in `flags:check` command. Stale flags are resolved when criteria no longer match. | ✅ |

## P1 — Quality of Life

| # | Item | Approach | Status |
|---|------|----------|--------|
| 4.6 | **Batch operation summary feedback** | Already returns count-based status messages via toast notification. | ✅ |
| 4.7 | **Deployment status preview** | Live computed status badge in create/edit form (Alpine.js updates on date change). | ✅ |
| 4.8 | **Soft-delete with undo window** | `SoftDeletes` on `WeeklyJournal` + `Attendance`. 30-second undo cache for journal submissions. Undo route + toast button. | ✅ |
## P2 — Polish & Consistency

| # | Item | Approach | Status |
|---|------|----------|--------|
| 4.9 | **Journal textarea auto-grow** | `rows="2"` → `rows="4"` with Alpine auto-height on input. | ✅ |
| 4.11 | **Mobile journal editor: card layout** | Replace wide Mon-Sat table with stacked card-per-day on screens < 768px. | ✅ |
| 4.12 | **Mobile DTTR: card-per-day collapse** | Collapse 5-column grid to 2-column on mobile with responsive grid classes. | ✅ |


## New Migrations

| # | Migration | Purpose |
|---|-----------|---------|
| 1 | `2026_06_21_000008_add_deleted_at_to_weekly_journals_and_attendance` | Soft-deletes for journals + attendance |

## Files Changed

```
resources/views/student/dashboard.blade.php (+ Quick Clock-In)
app/Http/Controllers/Student/AttendanceController.php (+ quickClock method)
routes/web.php (+ quick-clock, undo restore routes)
app/Console/Commands/CheckRiskFlags.php (+ auto-submit journals step, simplified check)
resources/views/students/_form.blade.php (+ end_date field, Alpine auto-compute 15 weeks)
app/Http/Requests/StoreStudentRequest.php (+ end_date validation)
app/Http/Controllers/StudentController.php (+ end_date in deployment creation)
resources/views/student/partials/document-card.blade.php (+ onchange auto-submit)
resources/views/student-documents/partials/upload-panel.blade.php (+ onchange auto-submit)
resources/views/deployments/_form.blade.php (+ Alpine status preview badge)
resources/views/student/weekly-journals/show.blade.php (textarea auto-grow, mobile card-per-day layout)
resources/views/dtr/show.blade.php (responsive grid: grid-cols-2 sm:grid-cols-5)
resources/views/notifications/partials/bell.blade.php (fixed broken route)
resources/js/app.js (+ undoKey param in showToast)
resources/views/layouts/app.blade.php (+ undo_key session handling)
app/Http/Controllers/UndoController.php (new)
app/Http/Controllers/WeeklyJournalController.php (+ undo cache on submit)
app/Models/WeeklyJournal.php (+ SoftDeletes trait)
app/Models/Attendance.php (+ SoftDeletes trait)
database/migrations/2026_06_21_000008_add_deleted_at_to_weekly_journals_and_attendance.php (new)
```

---

# ✅ Phase 5: Clean Data & Consistency

Phase 5 addresses the role-by-role walkthrough audit. See `CAPSTONE-WALKTHROUGH-TRACKER.md` for the full analysis.

## P0 — Data Integrity

| # | Item | Detail | Status |
|---|------|--------|--------|
| 5.1 | **File cleanup on document replace** | Delete old file from disk when re-uploading. Fixed in StudentDocumentController + StudentPortalController. | ✅ |
| 5.2 | **Name regex: Unicode/ñ support** | Student name regex now uses `\p{L}` Unicode property with `/u` flag. Filipino names like "Nuñez" work. | ✅ |
| 5.3 | **Analytics deployment trend: use start_date** | Changed from `created_at` to `start_date` so chart reflects actual deployment months. | ✅ |
| 5.4 | **OTP email failure handling** | Show error message to student when email send fails, instead of silent redirect. | ✅ |
| 5.5 | **Model deleting events for file cleanup** | Added `deleting` event to StudentDocument, Certificate, MonthlyDttr to clean up files from disk. | ✅ |

## P1 — Validation Hardening

| # | Item | Detail | Status |
|---|------|--------|--------|
| 5.6 | **Duplicate name: include middle + extension** | Check `middle_name` and `name_extension` in duplicate name detection. | ✅ |
| 5.7 | **Import phone validation — no silent default** | `sanitizeContactNumber()` returns null instead of silently defaulting to '09123456789'. | ✅ |
| 5.8 | **Add messages() to 6 form requests** | Added custom messages to Store/Update Evaluation, Required Document, and Announcement requests. | ✅ |
| 5.9 | **Edit form duplicate name guard** | Added `duplicateNameGuard(excludeId)` Alpine component with `&exclude` param. | ✅ |
| 5.10 | **Import email unique validation** | Added `unique:student_accounts,email` to `StudentsImport::rules()`. | ✅ |

## P2 — Navigation & Consistency

| # | Item | Detail | Status |
|---|------|--------|--------|
| 5.11 | **Dean sidebar: Students + Attendance** | Removed from dean's hidden list in `InternshipRoles.php`. Dean sees both sidebar links. | ✅ |
| 5.12 | **Chairperson sidebar: Deployments** | Removed from chairperson's hidden list. | ✅ |
| 5.13 | **Unify button colors (one green)** | Changed `--primary` from `#1a6b3c` to `#059669` (emerald-600). Fixed 2 hardcoded references. | ✅ |
| 5.14 | **x-alert-message on student layout** | Already exists in shared app layout. | ✅ |
| 5.15 | **Deployment deleteBlockers: DTR + MonthlyDTT** | Added `DailyTimeRecord` and `MonthlyDttr` checks to `Deployment::deleteBlockers()`. | ✅ |
| 5.16 | **Instructor deletion guardrails** | Added `HasDeleteProtection` + `deleteBlockers()` to User model. `ProfileController::destroy()` checks `canDelete()`. | ✅ |

---

# ✅ Phase 5b: UI Polish & Walkthrough Fixes

Additional fixes from instructor walkthrough.

| # | Item | Detail | Status |
|---|------|--------|--------|
| 5b.1 | **Weekly Journal My Students fix** | Added HTMX branch + `$myStudents` pass to controller. | ✅ |
| 5b.2 | **At-risk checkbox auto-submit** | Added `change from:input[name='risk']` to HTMX triggers. | ✅ |
| 5b.3 | **Risk column cleanup** | Replaced text badges with colored dots + tooltips. | ✅ |
| 5b.4 | **Document Queue section filter** | Added section dropdown + My Students checkbox. | ✅ |
| 5b.5 | **New Message button position** | Moved from header into "Select a conversation" container. | ✅ |
| 5b.6 | **Company form redesign** | Unified cards, 1px borders, toggle switch, consistent inputs. | ✅ |
| 5b.7 | **Address routes fix** | Moved outside `student.auth` — staff can now access cascader. | ✅ |
| 5b.8 | **Loading overlay fix** | Added `pointer-events: none` to prevent click blocking. | ✅ |
| 5b.9 | **Navigation refresh fix** | Added `PreventHtmxCaching` middleware for HTMX responses. | ✅ |
| 5b.10 | **Notifications view fix** | Converted from `@extends` to `<x-app-layout>`. | ✅ |
| 5b.11 | **Back button positions** | "Back to Compliance", "Back to Inbox" moved from header to content area. | ✅ |
| 5b.12 | **Batch select all-matching persistence** | SessionStorage + filter-keyed storage + clear on submit. | ✅ |
| 5b.13 | **My Students checkbox HTMX auto-submit** | Added `change from:input[name='my_students']` to search-bar triggers. | ✅ |
| 5b.14 | **Attendance batch resolve UI** | Added checkboxes + batch action bar to attendance list. | ✅ |
| 5b.15 | **Reports consolidated dashboard** | Single-page tabbed view with KPIs, filters, HTMX-loaded data. | ✅ |
| 5b.16 | **Mobile table overflow** | Added `overflow-x-auto` to companies, deployments, compliance tables. | ✅ |
| 5b.17 | **Mobile tap targets** | Increased mobile nav links `py-1` → `py-2.5`, bottom nav `py-1` → `py-2`. | ✅ |
| 5b.18 | **DTR button sizes** | Month arrows `w-8` → `w-10`, action buttons `py-1.5` → `py-2`. | ✅ |
| 5b.19 | **Journal image preview** | Uploaded images show inline thumbnails in real-time. | ✅ |
| 5b.20 | **Student name regex (Unicode ñ)** | Changed from `[A-Za-z]` to `\p{L}` with `/u` flag. | ✅ |

---

# 🔧 Phase S1: Student Portal Overhaul

Student experience improvements based on full student walkthrough.

| # | Item | Detail | Status |
|---|------|--------|--------|
| S1.1 | **Attendance skip passcode when logged in** | Store method checks session — skips student_number + passcode for authenticated students. | ✅ |
| S1.2 | **Dashboard gradient card → page-card** | Replaced green gradient with standard `<x-page-card>` for consistency. | ✅ |
| S1.3 | **OTP resend text fix** | Fixed empty `['s' => '']` translation param. | ✅ |
| S1.4 | **Passcode two-step reveal** | First click "Tap to reveal", second shows code. Prevents shoulder-surfing. | ✅ |
| S1.5 | **Bottom nav: Journal → Messages** | Messages is primary communication channel — now in bottom nav. | ✅ |
| S1.6 | **Getting Started checklist** | New students see a 4-step guide on dashboard. Disappears after first action. | ✅ |
| S1.7 | **Messages Sent tab** | New tab filter showing only threads where user sent last message. | ✅ |
| S1.8 | **Student profile settings** | New page + route. Update email, contact number, password. Sidebar link. | ✅ |
| S1.9 | **Certificate preview** | Images show inline, PDFs preview in iframe. Consistent with document preview. | ✅ |
| S1.10 | **Session timeout 30m → 2h** | Changed idle timeout in `EnsureStudentSession` middleware. | ✅ |
| S1.11 | **Trust this browser skip OTP** | Checkbox on login → encrypted 30-day cookie skips OTP on subsequent logins. | ✅ |

---

# ✅ Phase S2: Profile Hardening & Data Integrity

Completed June 22, 2026. Strengthened the student profile page with proper validation sanitization, globalization, and data integrity — addressing UI Specialist and Data Analyst panel concerns.

## Changes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| S2.1 | **FormRequest for profile update** | Created `UpdateStudentProfileRequest` with `prepareForValidation()` that normalizes email (lowercase, trimmed) and contact number (strips non-digits). Email unique check ignores current account. Password nullable + confirmed + min:8. Custom error messages for all fields. | `app/Http/Requests/UpdateStudentProfileRequest.php` (new) |
| S2.2 | **Controller refactored** | `StudentPortalController@updateProfile` now type-hints `UpdateStudentProfileRequest` instead of inline `$request->validate()`. Data sanitization delegated to FormRequest. No redundant `preg_replace` or `strtolower` in controller. | `app/Http/Controllers/StudentPortalController.php` |
| S2.3 | **Profile view helper hints** | Added descriptive helper text: "Used for OTP verification and notifications" under email, "Format: 09XXXXXXXXX" under contact, "Leave blank to keep your current password" under password section. | `resources/views/student/profile.blade.php` |
| S2.4 | **Read-only registrar fields** | Name, student number, section, program, year level stay in the read-only info card. Only email, contact number, password are editable — no change to registrar data from student side. | `resources/views/student/profile.blade.php` |
| S2.5 | **PhoneHelper for mobile normalization** | Created `PhoneHelper::normalizeMobile()` — single source of truth for Philippine mobile numbers. Accepts `09XXXXXXXXX` (local) and `+639XXXXXXXXX` (international), normalizes both to `09XXXXXXXXX`. Strips all non-digit characters. Returns `null` for any invalid format. | `app/Support/PhoneHelper.php` (new) |
| S2.6 | **Phone normalization across all entry points** | All 3 locations (profile form, student create form, Excel import) now use `PhoneHelper::normalizeMobile()` for consistency. Previously only 11-digit format was accepted; international `+639` format silently failed. | `UpdateStudentProfileRequest.php`, `StoreStudentRequest.php`, `StudentsImport.php` |
| S2.7 | **Client-side input protection** | Added `oninput="this.value = this.value.replace(/[^0-9+]/g, '')"` to phone fields. Strips letters/dashes/spaces as user types. `maxlength="13"` accommodates `+639XXXXXXXXX`. | `profile.blade.php`, `_form.blade.php` |
| S2.8 | **Profile layout: single column → 2-column grid** | Replaced `max-w-2xl mx-auto` with `grid grid-cols-1 lg:grid-cols-5 gap-6`. Info card (col-span-2) on left, edit form (col-span-3) on right. Uses full page width. Mobile unaffected (collapses to single column). | `profile.blade.php` |

## Navigation Bug Fixes (June 22)

| # | Bug | Root Cause | Fix |
|---|-----|-----------|-----|
| 1 | `Undefined variable $canViewStudents` (staff sidebar) | 14 `$canView*` permissions never defined in `@php` block. Used at lines 22-35 but never assigned. | Added 14 `$canView*` definitions computed from `InternshipRoles` role groups with `$role !== 'student'` guard. |
| 2 | `Undefined variable $studentPortalLimited` (student sidebar) | `$hasFullAccess` was defined but `$studentPortalLimited` (its inverse) was never created. | Added `$studentPortalLimited = !$hasFullAccess` at line 12. |
| 3 | Desktop sidebar gap above footer | `<nav>` element missing `h-full`. The flex chain broke: `aside.h-full` → `nav(height:auto)` → desktop sidebar's `h-full` resolved to auto, so `flex-1` had no space to grow. Footer sat directly under content. | Added `class="md:h-full"` to `<nav>` so desktop sidebar inherits 100vh from aside. Mobile unaffected (`md:` responsive prefix). |
| 4 | **Student logout broken — Sign Out does nothing** | `logout` route at `routes/auth.php` is behind `middleware('auth')` (web guard). Students are NOT on the web guard — they use `student_account_id` session. Auth middleware blocks the POST, destroy() never runs. Session never cleared. Check-in page Sign Out was `<a href="{{ route('login') }}">` GET link — no session clearing. | Created `student.logout` route (outside auth middleware) + `StudentPortalController@logout()` method. Navigation computes `$logoutRoute` dynamically (`student.logout` for students, `logout` for staff). Check-in Sign Out replaced with POST form to `student.logout`. |

## Why Not Read-Only Email/Contact

- **Email**: Used for OTP delivery and all notifications (panel recommendation #10). If a student's email was mistyped during import and they can't fix it, they're locked out of receiving OTPs and notifications. Requires admin intervention unnecessarily.
- **Contact Number**: Personal number that students may change mid-semester. Forcing them to go through an instructor to update it adds friction with zero upside.

## Data Integrity Audit (June 22 — Full System Walkthrough)

### Audit Scope
All 17 FormRequest classes, 37 inline validation blocks across 22 controllers, 61 Blade view files with ~80 forms and ~190 named inputs. Every form field across all 4 roles (student, instructor, chairperson, dean) was inspected.

### Fixed Issues

| # | Issue | Files Changed |
|---|-------|--------------|
| 1 | **UpdateStudentRequest still used old phone sanitization** (preg_replace) instead of `PhoneHelper::normalizeMobile()` — missed in earlier pass | `UpdateStudentRequest.php` |
| 2 | **CertificateController@store** — `title` and `description` stored without `trim()` | `CertificateController.php` |
| 3 | **MessageThreadController@store** — `subject` and `body` stored without `trim()` | `MessageThreadController.php` |
| 4 | **MessageThreadController@reply** — `body` stored without `trim()` | `MessageThreadController.php` |
| 5 | **EvaluationController@storeHteLink** — `supervisor_name` stored without `trim()` | `EvaluationController.php` |
| 6 | **StudentDocumentController@workflowAction** — `note` stored without `trim()` | `StudentDocumentController.php` |
| 7 | **CampusSettingController@update** — `policy_review_notes`, `semester`, `academic_year` stored without `trim()` | `CampusSettingController.php` |
| 8 | **Company form** — `contact_phone` missing `oninput` filter (letters could be typed) | `companies/_form.blade.php` |
| 9 | **Certificate form** — `title` field missing `maxlength` attribute | `certificates/create.blade.php` |

### Verified Already Correct
- All 14 FormRequest classes have `prepareForValidation()` with proper normalization ✅
- All student phone entry points use `PhoneHelper::normalizeMobile()` (3 locations: StoreStudent, UpdateStudent, UpdateStudentProfile, StudentsImport) ✅
- Company phone uses `PhilippineContactNumber::normalize()` for both mobile + landline ✅
- All email fields normalized via `strtolower(trim())` in their respective FormRequests ✅
- All student number fields strip non-digits via `preg_replace('/\D+/', ...)` ✅
- All name fields use Unicode regex for Filipino names (ñ, accents) ✅
- All select/enum fields validated against allowed values (not free text) ✅
- Password fields: only `min:8` + `confirmed` — no uppercase/digit/special requirements ✅
- File uploads: type, mime, extension, and size validated on ALL entry points ✅

### Remaining Audit Trail
| Concern | How Addressed |
|---------|---------------|
| Email normalization | `strtolower(trim())` in all FormRequest `prepareForValidation()` methods |
| Contact number sanitization | `PhoneHelper::normalizeMobile()` for students (3 entry points), `PhilippineContactNumber::normalize()` for companies (2 entry points) |
| Phone client-side protection | `oninput="this.value = this.value.replace(/[^0-9+]/g, '')"` on all phone fields (profile, student form, company form) |
| Text input trimming | All text inputs across FormRequests + 7 controllers now have `trim()` before storage |
| Email uniqueness | `Rule::unique('student_accounts', 'email')->ignore(...)` on create, import, profile update |
| Password confirmation | `'confirmed'` rule ensures both fields match before `bcrypt()` |
| XSS prevention | Blade's `{{ }}` auto-escapes all output. `strip_tags()` on company fields |
| No silent failures | All FormRequests throw `ValidationException` with per-field custom messages |
| Globalization | All labels, hints, errors, and headers use `__()` helper |
| Client-side maxlength | All text/input fields have `maxlength` attribute matching server validation |
| Unicode name support | `\p{L}` regex with `/u` flag on all name fields (ñ, accents, hyphens, apostrophes) |
| Duplicate prevention | `unique` validation + AJAX duplicate name modal on student create/edit |

---

# ✅ Phase S3: Login & JS Timing Fixes

Completed June 22, 2026. Fixed student logout, login redirect loop, and double-click navigation caused by JavaScript timing issues.

## Bugs Fixed

| # | Bug | Root Cause | Fix | Files Changed |
|---|-----|-----------|-----|--------------|
| S3.1 | **Student logout does nothing** | `logout` route at `routes/auth.php` behind `middleware('auth')` (web guard). Students not on web guard — use `student_account_id` session variable. Auth middleware blocks POST, destroy() never runs. | Created `student.logout` route outside auth middleware + `StudentPortalController@logout()`. Navigation dynamically selects `student.logout` vs `logout` per role. | `routes/web.php`, `StudentPortalController.php`, `navigation.blade.php` |
| S3.2 | **Check-in page Sign Out is GET link** | `check-in.blade.php:12` had `<a href="{{ route('login') }}">Sign out</a>` — just navigates to login page, doesn't clear session. | Replaced with POST form to `route('student.logout')`. | `check-in.blade.php` |
| S3.3 | **Login redirect loop** | `Alpine.start()` ran at module scope before DOM ready. Capture-phase `click` listener fired before Alpine/HTMX handlers. `initSelect2()` failures blocked entire `DOMContentLoaded` handler. | Moved `Alpine.start()` into `DOMContentLoaded`. Removed capture-phase listener. Wrapped `initSelect2()` in try-catch. Added `Auth::guard('web')->logout()` to student logout to clear stale web guard state. Made `student.logout` POST-only. | `app.js`, `StudentPortalController.php`, `routes/web.php` |
| S3.4 | **Double-click navigation** | Capture-phase click listener at `app.js:179` fired on every click before Alpine/HTMX handlers. First click missed target, second worked. | Removed capture-phase listener. Overlay already hidden on `pageshow` + `load` + `DOMContentLoaded`. | `app.js` |

---

# ✅ Phase S4: Reports Page Fixes

Completed June 22, 2026. Fixed 4 bugs causing empty data in reports tabs.

## Bugs Fixed

| # | Tab | Bug | Root Cause | Fix |
|---|-----|-----|-----------|-----|
| S4.1 | **Deployed** | Filters ignored (search, section, My Students had no effect) | `deployedPerCompany()` never called `applyReportFilters()`. Showed ALL companies regardless of filters. | Added `applyReportFilters()` call. Filter deployments to `active`/`completed` only. Filter companies to those with matching deployments. |
| S4.2 | **Compliance** | HTMX partial renders empty cells | Controller returns per-student `['student','submitted','total','status']` but partial expected section-grouped `['section','total','compliant','partial','non_compliant']`. Every `$row['section']` etc. was undefined. | Restructured partial to render per-student rows matching the working full-page view pattern. |
| S4.3 | **Compliance** | "No compliance data" empty state never shows | `@if (empty($rows))` on a Collection object — `empty()` on any object always returns `false`. | Changed to `$rows->isEmpty()`. |
| S4.4 | **Deployed** | Shows ALL companies even with no active deployments | `Company::with(['deployments.student'])` loads all companies. Companies with zero relevant deployments still appeared. | Added `whereHas('deployments', ...)` filter + empty student ID guard. |

## Files Changed
```
app/Http/Controllers/ReportController.php
resources/views/reports/partials/compliance-table.blade.php (rewritten)
```

---

# ✅ Phase S5: Migration Schema Integrity

Completed June 22, 2026. Scanner 89 migration files, found 10 critical + 22 warnings. Created fix migration for actionable issues.

## New Migration
`2026_06_22_000002_fix_schema_integrity.php`

## Fixes Applied

| # | Issue | Severity | Before | After |
|---|-------|----------|--------|-------|
| S5.1 | `monthly_dttrs.month` signed `tinyInteger` | MEDIUM | Allowed -128 to 127 for month values (domain: 1-12) | `unsignedTinyInteger` (0-255) |
| S5.2 | `certificates.uploaded_by` cascade on delete | MEDIUM | Deleting an instructor deleted their uploaded certificates (data loss) | `nullOnDelete` — preserves certificates, sets FK to NULL |
| S5.3 | Missing index on `deployments.status` | MEDIUM | Full table scans on every status filter | Added index |
| S5.4 | Missing index on `deployments.start_date`, `end_date` | LOW | Slow date-range queries | Added composite index |
| S5.5 | Missing index on `attendances.resolution_status` | MEDIUM | Full table scans on status/resolution filters | Added index |
| S5.6 | Missing index on `attendances.geofence_status` | MEDIUM | Full table scans on geofence filters | Added index |
| S5.7 | Missing index on `evaluations.evaluation_type` | MEDIUM | Slow type-filtered aggregations | Added index |
| S5.8 | Missing index on `evaluations.score` | LOW | Slow score-based reports | Added index |
| S5.9 | Missing index on `evaluations.evaluated_at` | LOW | Slow date-range queries on evaluations | Added index |
| S5.10 | Missing index on `certificates.issued_at` | LOW | Slow date-range queries on certificates | Added index |
| S5.11 | Missing index on `certificates.verified_at` | LOW | Slow date-range queries on verified certificates | Added index |

## Findings Not Actioned
- `student_feedback_type` filename mismatch in migration: column never referenced in code, no runtime impact
- Empty `down()` in 11 data-only migrations: intentional — data migrations cannot be safely reversed
- Raw MySQL SQL in 4 migrations: system uses MySQL exclusively (confirmed in `phpunit.xml`)
- Redundant old columns (`students.name`, `companies.contact_person`, `companies.address`): kept for backward compatibility
- Missing charset/collation: relies on MySQL defaults, standard Laravel practice

---

# ✅ Phase S6: Import View Cleanup

Completed June 22, 2026.

| # | Item | Detail |
|---|------|--------|
| S6.1 | **Student import — removed broken loading spinner** | Removed broken SVG circle spinner from submit button. Full-screen overlay with indeterminate bar handles loading state. |
| S6.2 | **Company import — removed broken loading spinner** | Same fix. SVG circle spinner was not working; full-screen overlay already handles the state. |

## Files Changed
```
resources/views/students/import.blade.php
resources/views/companies/import.blade.php
```

---

# ✅ Phase 7: Reports Blank Data Fix & Pagination

Completed June 22, 2026. Fixed two silent bugs preventing all reports tabs from loading, then added pagination for real-world data volumes.

## Phase 7a — Blank Data Root Cause

| # | Bug | Root Cause | Fix | Files Changed |
|---|-----|-----------|-----|--------------|
| 7.1 | **All tabs show "Select a tab above"** | `window.htmx` was undefined. `import 'htmx.org'` without assigning to `window`. Every `if (!window.htmx) return` guard blocked the HTMX request. | Changed to `import htmx from 'htmx.org'` + `window.htmx = htmx;` | `app.js` |
| 7.2 | **loadTab() never runs on page load** | `DOMContentLoaded` listener inside `@push('scripts')` — runs at bottom of `<body>`, well after DOMContentLoaded already fired. | Removed wrapper, call `loadTab()` directly | `reports/index.blade.php` |

## Phase 7b — Real-World Scalability

| # | Tab | Concern | Fix |
|---|-----|---------|-----|
| 7.3 | **Deployed** | Loads ALL companies into memory regardless of filters | `->get()->map()` → `->paginate(20)->through()`. `applyReportFilters()` filters students BEFORE pagination. Paginated companies render 20 per page. |
| 7.4 | **Missing Documents** | Loads ALL students via `->lazy()` and iterates in PHP (7,500 iterations for 500 students × 15 docs) | SQL `WHERE` clause with `orWhereDoesntHave` subquery filters students with issues BEFORE pagination. `paginate(50)` limits per-page work. |
| 7.5 | **Compliance** | Loads ALL students via `->lazy()` | `paginate(50)` with `through()` for per-student computation. Only 50 students processed per page. |
| 7.6 | **Attendance** | Loads ALL attendance records (30k+ per semester) | Default month filter (`whereMonth('check_in_at', now()->month)`). `paginate(50)`. PDF/CSV exports bypass pagination and still get all records. |
| 7.7 | **All tabs** | No pagination on any partial | `hx-boost="true" hx-target="#report-content" hx-swap="innerHTML"` on pagination links enables AJAX page switching without full reloads. |

## Files Changed
```
resources/js/app.js
resources/views/reports/index.blade.php
app/Http/Controllers/ReportController.php
resources/views/reports/partials/deployed-table.blade.php
resources/views/reports/partials/missing-table.blade.php
resources/views/reports/partials/compliance-table.blade.php
resources/views/reports/partials/attendance-export-table.blade.php
```

## Phase 7c — Tab Navigation, Export, & Filter Polish

| # | Issue | Root Cause | Fix | Files Changed |
|---|-------|-----------|-----|--------------|
| 7.8 | **Tab clicks revert to Deployments** | `loadTab()` never pushed URL. Pagination links used `hx-push-url` but tab switching didn't. URL vs. content mismatch caused refreshes to show wrong tab. | `loadTab()` now uses `pushUrl: true`. Removed `hx-push-url` from all 4 pagination containers. Added `x-init="loadTab(activeTab)"` to Alpine component for proper initialization timing. | `reports/index.blade.php`, all 4 partials |
| 7.9 | **Export buttons invisible (never rendered)** | Export buttons placed in `<x-slot name="header">` — outside Alpine's `x-data` scope. `x-if="activeTab === 'attendance'"` couldn't reach `activeTab` since they were sibling scopes. | Moved export CSV/PDF buttons into the tab bar area (inside `x-data` scope). Chairperson Health and Dean Executive links also moved. | `reports/index.blade.php` |
| 7.10 | **Search box too wide, pushes filters right** | Search container had `flex-1 min-w-[180px]` — grew to fill all space. Section dropdown + My Students + Apply button squeezed to right edge. | Changed to `w-[260px] shrink-0` — fixed width, other elements sit naturally beside it. | `reports/index.blade.php` |
| 7.11 | **My Students requires manual Apply click** | 6 other pages use `search-bar` component with built-in HTMX auto-submit. Reports page used Alpine `x-model` without `@change` handler. | Added `@change="loadTab(activeTab)"` to the checkbox — toggling immediately applies the filter. | `reports/index.blade.php` |

---

# ✅ Phase 8: HTE Evaluation Overhaul & DOCX Exports

Completed June 22, 2026. Integrated the official OJT Evaluation Sheet template into the HTE evaluation form, added DOCX exports with CHMSU school branding, and automated deployment-end reminder.

## P0 — HTE Evaluation Template

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 8.1 | **OJT Evaluation Sheet template** | Replaced single "Score 1-100" with full 18-criteria form matching the official CHMSU OJT Evaluation Sheet template. 3 categories: Work Habits (5 criteria), Work Skills (6), Social Skills (7). Each rated 1-5. Alpine.js live computation of category totals + overall rating + percentage. | `HteTransactionController.php`, `hte-transactions/show.blade.php` |
| 8.2 | **criteria_scores JSON storage** | New migration `add_criteria_scores_to_evaluations`. Stores all 18 criterion scores as JSON. Model cast to `array` with helper `computeOverallScore()` converts 1-5 average to 1-100 scale (×20). Mass assignment protection filters only known keys. | `2026_06_22_000003_add_criteria_scores_to_evaluations.php`, `Evaluation.php` |
| 8.3 | **HTE evaluation DOCX export** | New `EvaluationExportService` generates CHMSU-branded DOCX of completed evaluation: title, student/company info, rating scale, 3 category tables with ✓ marks for each score, category totals, overall rating + percentage, comments, supervisor signature line. CHMSU header/footer images from `public/images/`. | `EvaluationExportService.php` (new), `StudentPortalController.php`, `web.php`, `student/dashboard.blade.php` |
| 8.4 | **Updated required document description** | Changed "OJT Supervisor Evaluation Form" description to instruct students: upload the signed copy of the online evaluation form completed by their HTE supervisor. Seeder + live DB updated. | `SampleDataSeeder.php` |

## P1 — Weekly Journal & DOCX Exports

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 8.5 | **Weekly Journal DOCX export** | New `WeeklyJournalExportService` generates CHMSU-branded DOCX: title, student/company info, Mon–Sat activity table (activities + uploaded file names), supervisor signature. CHMSU header/footer. | `WeeklyJournalExportService.php` (new), `WeeklyJournalController.php`, `web.php`, `student/weekly-journals/show.blade.php` |
| 8.6 | **DTR MS Word compatibility fix** | Added `$phpWord->getCompatibility()->setOoxmlVersion(15)` and document metadata to existing DttrExportService. DOCX now opens in MS Word (not just WPS). | `DttrExportService.php` |

## P1 — Automation

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 8.7 | **Deployment-end HTE reminder** | Added 6th check to `reminders:send` command: finds active deployments ending within 7 days with no industry evaluation → emails the instructor to send the HTE link. Uses `assigned_instructor_id` or falls back to first instructor. | `SendReminders.php` |

## P2 — Cleanup

| # | Item | Detail |
|---|------|--------|
| 8.8 | **Root file cleanup** | Deleted 12 old/redundant files: `PANEL-RECOMMENDATIONS.md`, `REQUIREMENTS-AUDIT.md`, `SYSTEM-CHECK.md`, `UI-README.md`, `USE-CASE-SCENARIOS-3.14.md`, `INTERNSHIP-SYSTEM-DFD.md`, `docs/README_PROGRESS_TRACKER.md`, `DTR (1).docx`, `OJT Evaluation Sheet.docx`, `ON-THE-JOB-TRAINING-WEEKLY-JOURNAL_NEW (1).docx`, `node-v22.14.0-linux-x64.tar.xz`, `Untitled` |
| 8.9 | **Analytics removed (out of scope)** | Deleted `AnalyticsController.php`, `views/analytics/`, 3 routes, Chart.js import, `chart.js` dependency. Updated both tracker files. |
| 8.10 | **Tracker counts updated** | `README.md` + `AGENTS.md` counts updated: models 27→31, migrations 73→90, controllers updated. |
| 8.11 | **Agent reference file created** | `docs/superpowers/specs/INTERNAL-AGENT-MEMO.md` — personal grounding document with 4-user perspective, panelist checklist, non-negotiables, form audit checklist. |

## New Migrations

| # | Migration | Purpose |
|---|-----------|---------|
| 1 | `2026_06_22_000003_add_criteria_scores_to_evaluations` | Adds JSON column for 18 HTE evaluation criteria scores |

## Files Created

```
app/Services/WeeklyJournalExportService.php
app/Services/EvaluationExportService.php
database/migrations/2026_06_22_000003_add_criteria_scores_to_evaluations.php
docs/superpowers/specs/INTERNAL-AGENT-MEMO.md
```

## Files Deleted

```
PANEL-RECOMMENDATIONS.md
REQUIREMENTS-AUDIT.md
SYSTEM-CHECK.md
UI-README.md
USE-CASE-SCENARIOS-3.14.md
INTERNSHIP-SYSTEM-DFD.md
docs/README_PROGRESS_TRACKER.md
DTR (1).docx
OJT Evaluation Sheet.docx
ON-THE-JOB-TRAINING-WEEKLY-JOURNAL_NEW (1).docx
node-v22.14.0-linux-x64.tar.xz
Untitled
backend/app/Http/Controllers/AnalyticsController.php
backend/resources/views/analytics/
```

---

# 🛡️ Panel Defense Strategy

## Panelist Profiles & Focus Areas

| Panelist | Role | Specialization | Likely Focus in Defense |
|----------|------|---------------|------------------------|
| Danes P. Tumabing, MIT | Chairman | IT Management | System architecture, SDLC methodology, project scope alignment with objectives |
| Cristine V. Redoblo, PhD | Member | Research / Data | Data integrity, consistency, validation rigor, duplicate handling, reporting accuracy |
| Tisha May Tizon | Member | UI/UX (? — confirm) | Interface design, user experience, navigation flow, consistency across roles |
| John Paul Ubas | Member | Infrastructure (? — confirm) | Deployment, performance, security, networking, hosting |
| Jason Flor | Member | Data Analysis (? — confirm) | Data quality, normalization, reporting, analytics, export accuracy |
| *(Adviser)* Charwin Padilla, MIT | Adviser | System oversight | Pre-vetted; supports the proponent during defense |

> **From your description:** 6 panels including 1 UI Specialist, 1 Data Analyst, 1 Infra/Networking. Positions marked with `(? — confirm)` need verification. Update this table with their actual specializations.

## Per-Specialization Defense Points

### UI Specialist — What They'll Check
| Concern | Our Answer | Where It's Proven |
|---------|-----------|-------------------|
| Consistent button colors | Single emerald-600 primary across all pages. `--primary` variable in CSS. Phase 5.13. | `custom.css`, Phase 5 |
| Mobile responsiveness | Bottom nav, responsive grid, `pb-20 md:pb-0`, tablets handled. Phase 2 mobile pass + S1. | `navigation.blade.php`, Phase S1 |
| Input consistency | Same `rounded-lg border px-3 py-2` class on all inputs. `x-input-label` component used everywhere. | All `_form.blade.php` files |
| Navigation clarity | Role-based sidebar (staff), bottom nav (student mobile), hamburger (mobile). Desktop sidebar with `mt-auto` footer fix. | `navigation.blade.php` |
| Error feedback | `x-alert-message` on staff+student layouts, per-field `x-input-error`, toast notifications with auto-dismiss. | `app.blade.php`, `x-input-error` |
| Empty states | "Clock in to generate DTTR entries", "No messages yet" — helpful, not bare. | Various index views |
| Loading states | `loading-overlay` with spinner, HTMX indicators, Alpine flags. Phase 5b.8 fixed pointer-events. | `app.blade.php`, Phase 5b |

### Data Analyst — What They'll Check
| Concern | Our Answer | Where It's Proven |
|---------|-----------|-------------------|
| Duplicate student prevention | `unique:students,student_number` on import + form. Duplicate name modal with AJAX check. `prepareForValidation()` normalizes data. | `StoreStudentRequest`, `StudentsImport`, `StudentController` |
| Data normalization | Email lowercased+trimmed. Student contact number normalized via `PhoneHelper::normalizeMobile()`; company contact phone via `PhilippineContactNumber::normalize()`. Both accept local + international formats. All text inputs trimmed before storage. | `PhoneHelper`, `PhilippineContactNumber`, All FormRequest `prepareForValidation()` methods |
| File cleanup on replace | Old file deleted from disk on re-upload. `deleting` events on `StudentDocument`, `Certificate`, `MonthlyDttr`. Phases 5.1, 5.5. | Models + Phase 5 |
| No silent data corruption | Import phone number defaults to `null` (not '09123456789') if invalid. Phase 5.7. | `StudentsImport::sanitizeContactNumber()` |
| Unicode name support | `\p{L}` regex with `/u` flag allows ñ/accents. Phase 5.2, 5.20. | `StoreStudentRequest`, `UpdateStudentRequest` |

| Audit trail | All created/updated timestamps on models. Risk flags with resolved_at. Weekly journal `submitted_at`. | Migrations + models |
| Email uniqueness | `unique:student_accounts,email` on create + import + profile update. Phase 5.10. | `StudentsImport`, `StoreStudentRequest`, `UpdateStudentProfileRequest` |

### Infra/Networking — What They'll Check
| Concern | Our Answer | Where It's Proven |
|---------|-----------|-------------------|
| File upload limits | 2MB for documents, 10MB for imports, 5MB for DTTR. Configured in validation + php.ini. | All file upload FormRequests |
| Session security | Two separate auth systems: web guard (staff) + student session (custom middleware). 2h timeout, "Trust this device" cookie. | `EnsureStudentSession`, Phase S1.10, S1.11 |
| File storage | Local `storage/app/` with symlink to `public/storage/`. Cleanup on delete/replace. | `StudentDocument`, `Certificate`, `MonthlyDttr` models |
| Email delivery | All notifications via email. OTP, document status, reminders, flags. Queue worker processes asynchronously. | `NotificationMail`, `SendReminders` |
| CSRF protection | Laravel's built-in CSRF token on all forms. | All `<form>` elements |
| XSS prevention | Blade's `{{ }}` auto-escapes. No raw `{!! !!}` without explicit need. | All views |
| MySQL testing | Tests use MySQL (`internship_testing`), not SQLite — matches production. | `phpunit.xml` |

### General Defense Strategy
1. **Scope first**: Every feature ties back to the BSIS internship process at CHMSU-Talisay. If a panelist questions why X exists, answer in terms of student/instructor daily workflow.
2. **Show the pre-oral checklist**: All 26 panel recommendations from the pre-oral are implemented (see Phase 0 table). This proves we listened.
3. **Data over opinion**: For any design choice, reference a specific validator rule, a Phase# item, or a test. Examples: "Contact number is validated via `/^09[0-9]{9}$/` in the FormRequest's `prepareForValidation()` which also strips non-digits — this is the same pattern used in the Student import for consistency."
4. **Acknowledge limits honestly**: If something is out of scope (e.g., AI-powered anomaly detection), say so clearly and point to the manual alternative (risk flags run daily via `php artisan flags:check`).
5. **Track changes**: Every significant change is logged in this tracker with Phase#, approach, and files changed. The walkthrough tracker covers role-by-role validation.

### System Standardization Checklist (for Final Defense)
- [x] All form validation uses dedicated FormRequest classes (not inline)
- [x] All FormRequests have `prepareForValidation()` for data normalization
- [x] All FormRequests have `messages()` for user-facing error text
- [x] All views use `__()` helper for globalization
- [x] Consistent input styling (same Tailwind classes)
- [x] Single primary color (emerald-600 `#059669`)
- [x] Auth separation (web guard + student session — never mixed)
- [x] File upload limits enforced (2MB/5MB/10MB)
- [x] Soft-deletes with undo (journals + attendance)
- [x] Risk flags auto-resolve when criteria clear
- [x] File cleanup on record delete/replace
- [x] Unicode name support (ñ, accents)
- [x] Phone number normalized via `PhoneHelper`/`PhilippineContactNumber` on all entry points
- [x] Email uniqueness enforced at all entry points
- [x] Role-based sidebar visibility (fine-grained per staff role)
- [x] Contact number accepts both `09XXXXXXXXX` and `+639XXXXXXXXX` formats
- [x] Both international (`+639`) and local (`09`) phone inputs normalized to local format
- [x] `oninput` filter on all phone fields prevents letter/symbol input client-side
- [x] All text inputs trimmed before storage (FormRequest `prepareForValidation()` + controller-level)
- [x] Client-side `maxlength` matches server-side `max:` validation on all fields
- [x] No uppercase/digit/special requirements on passwords — just `min:8` + confirmation
- [x] Select/enum fields validated against whitelist (not free text)

---

# ✅ Phase 9: Performance Optimization & UX Polish

Completed June 22, 2026. Comprehensive performance pass and UX hardening across all 4 roles. All P0 and P1 issues from the system audit resolved.

## P0 — Critical Bugs Fixed

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 9.1 | **500 errors from missing imports** | Fixed 3 missing `use` imports causing crash on `/students/create`, `/student/documents`, and attendance polling. All were `Class not found` errors. | `StudentController.php`, `StudentPortalController.php`, `AttendanceController.php` |
| 9.2 | **Batch attendance table name** | Validation rule used `exists:attendance,id` but MySQL table is `attendances`. Fixed singular → plural. | `BatchController.php` |
| 9.3 | **HTE form crash on load** | `mapWithKeys()` called on a plain array instead of Collection. Wrapped with `collect()`. | `hte-transactions/show.blade.php` |
| 9.4 | **Getting Started checklist was dead code** | Only rendered on dashboard, but new students (no docs, no deployment) are in limited mode and can't access dashboard. Moved to documents page. | `StudentPortalController.php`, `student/documents.blade.php` |

## P1 — Performance Optimizations

| # | Item | Before | After | Files Changed |
|---|------|--------|-------|--------------|
| 9.5 | **Students list N+1** | 45 extra queries from per-student accessors (`progress_pct_computed`, `journal_progress_pct`). 1.53s | **0.63s (2.4×)** | `StudentController.php`, `ajax-list.blade.php` |
| 9.6 | **Compliance page (chairperson)** | Loaded ALL student columns then filtered in PHP. 3.55s | **0.59s (6.0×)** | `ComplianceController.php` |
| 9.7 | **Dean Dashboard** | 4 separate aggregation queries scanning full evaluation table. 1.24s | **0.56s (2.2×)** | `DashboardController.php` |
| 9.8 | **Dean Messages** | N+1 participant loop loading all eager relationships. 3.64s | **~0.6s** | `MessageThreadController.php` |
| 9.9 | **Dean Reports — Deployed tab** | `pluck('id')` loaded all student IDs into memory then `whereIn` with 100+ values. 6.38s | **0.95s (6.7×)** | `ReportController.php` |

## P1 — UX Fixes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 9.10 | **Loading overlay hardened** | `Alpine.start()` moved before `DOMContentLoaded`. `pageshow` always hides overlay. `pointer-events: none` on all children. Added `htmx:responseError`/`htmx:sendError` listeners. | `app.js`, `custom.css` |
| 9.11 | **HTMX loading indicator** | Animated gradient bar on HTMX target elements during requests. CSS + JS event handlers. | `app.js`, `custom.css` |
| 9.12 | **8 modals fixed** | Added ESC keys, backdrop clicks, and X buttons to delete confirmation, duplicate name, import results, review panel, journal submit, and DTR modals. | 8 Blade files |
| 9.13 | **Batch operation confirmations** | Added Alpine confirmation modals with record count to students + attendance batch forms. | `ajax-list.blade.php` (2 files) |
| 9.14 | **Migration FK fix** | `certificates.uploaded_by` FK didn't drop existing constraint before adding new one. Fixed `up()` method. | `2026_06_22_000002_fix_schema_integrity.php` |
| 9.15 | **"My Students" checkbox hidden** | Now only visible for instructor role — dean and chairperson no longer see it. | `students/index.blade.php` |
| 9.16 | **Reports subtitle added** | Was empty dash `—`. Now shows: "View deployed students, missing documents, compliance summaries, and attendance records." | `reports/index.blade.php` |
| 9.17 | **Poppins font removed** | Removed `font-family: 'Poppins', sans-serif` from `.stat-number`. Font was never loaded. | `custom.css` |
| 9.18 | **Dean Announcements sidebar link** | Added `announcementViewerRoles()` so dean sees the Announcements link in sidebar. Route was accessible but had no nav link. | `InternshipRoles.php`, `navigation.blade.php` |
| 9.19 | **Dean seed name fixed** | Changed from "User, Dean" (placeholder) to "Santos, Maria". | DB + `SampleDataSeeder.php` |
| 9.20 | **Student onboarding checklist fixed** | Getting Started checklist now renders on documents page (where new students actually land), not dashboard (which they can't access). | `StudentPortalController.php`, `student/documents.blade.php` |

## Audit Documentation

| # | Item | Detail |
|---|------|--------|
| 9.21 | **Merged audit files** | Consolidated `SYSTEM-WALKTHROUGH-AUDIT.md` and `SYSTEM-AUDIT-COMPLETE.md` into single `DEFENSE-READY-AUDIT.md` with 9 sections, panelist attribution, and cheat sheet. Removed 2 old files. |
| 9.22 | **Defense cheat sheet** | Section 9 maps every issue type to the panelist who'd catch it during demo. |

---

# 🔧 Phase 10: Student Lifecycle Automation

Completed June 24, 2026. Overhauled the student lifecycle to automate deployment activation and completion based on document approval and accumulated hours.

## New Student Workflow

```
Instructor creates student + assigns company
  → Deployment created (status: pending)
  → Student sees 8 Pre documents on My Documents page
  → Student uploads → Instructor approves in Document Queue
  → ALL 8 Pre docs approved?
      → SYSTEM auto-activates deployment (start_date = today, status: active)
      → Full portal access granted (clock in, DTR, journals, messages)
      → 4 Monitoring documents appear
  → Student clocks in daily → DTR tracks hours
  → System accumulates total hours
  → Total ≥ 600 hours?
      → SYSTEM auto-completes deployment (end_date = today, status: completed)
      → Instructor notified → Issues certificate
```

## Changes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 10.1 | **Removed OJT Supervisor Evaluation Form** | Removed from Required Documents seeder + workflow mapping + HTE controller. The online evaluation (18 criteria via HTE link) replaces paper upload entirely. | `SampleDataSeeder.php`, `DocumentWorkflowSeeder.php`, `HteTransactionController.php` |
| 10.2 | **Auto-deploy on Pre doc approval** | When all 8 Pre mandatory documents are Submitted with completed workflow, the pending deployment auto-activates with `start_date = now()`. | `StudentDocumentController.php`, `Student.php` |
| 10.3 | **Auto-complete on 600 hours** | After each clock-out, system checks total `total_minutes` across all attendances. If ≥ 600 hours (36,000 min), deployment auto-completes with `end_date = now()`. | `AttendanceController.php`, `Deployment.php`, `Student.php` |
| 10.4 | **Phase-aware document visibility** | Pre-deployment students (pending deployment) see only 8 Pre documents. Deployed students see all 12 (8 Pre + 4 Monitoring). | `StudentPortalController.php` |
| 10.5 | **Progress denominator = 12** | Removed OJT Eval Form from mandatory count. All 12 documents are visible and countable at every phase. | `Student.php` |
| 10.6 | **Clock In/Out guarded** | Sidebar link hidden for non-deployed students. Check-in page blocks session-authenticated access without active deployment. | `navigation.blade.php`, `AttendanceController.php` |
| 10.7 | **Removed auto-submit on file select** | Students now select file → click Upload/Replace button. No more confusing redundant buttons. | `document-card.blade.php` |
| 10.8 | **Notifications 500 fixed** | `$unreadCount` variable was missing from `index()` method. | `NotificationController.php` |
| 10.9 | **Notification auto-scroll + highlight** | Documents page reads `?focus={id}` from URL, scrolls to card, highlights with ring animation for 4 seconds. | `documents.blade.php` |

# 🔧 Phase 11: Instructor Walkthrough Fixes

Completed June 24, 2026. Bulk-fixed all issues found during the full instructor role walkthrough.

## Changes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 11.1 | **Pagination: 5 per page** | Compliance and Students tables changed from 8/15 to 5 per page to reduce scrolling. | `ComplianceController.php`, `StudentController.php` |
| 11.2 | **100% progress bug** | When no mandatory documents are configured, `getProgressPctComputedAttribute()` returned 100 (misleading for new students). Changed to return 0. | `Student.php` |
| 11.3 | **Lazy tabs raw JS fix** | Student profile tabs (Journals/DTR/Attendance/Certificates) showed raw `&quot;` HTML entities in fallback content. Replaced inline HTML strings with `window.emptyTabHtml()` helper function. | `students/show.blade.php` |
| 11.4 | **Message conversation redesign** | Added fixed header with subject + info button (participants popover with name, email, role badge) + new message button. Message viewport set to `max-h-[calc(100vh-320px)]` with scrollable content. Reply form pinned at bottom. | `messages/partials/conversation.blade.php` |
| 11.5 | **Import company tracking** | `StudentsImport` now tracks companies auto-created during student import. Result message shows count and warns instructor to complete company details. | `StudentsImport.php`, `StudentImportController.php` |
| 11.6 | **Document queue X button** | Removed overlapping circle X button from slide panel — the review content's own square X button remains. | `student-documents/queue.blade.php` |
| 11.7 | **Back to evaluations moved** | Moved from header area (beside notification bell) to content area (below recent links section), consistent with other pages. | `evaluations/send-hte-link.blade.php` |
| 11.8 | **Auto-fill supervisor from company** | HTE link form now falls back to `contact_person` field when `contact_person_name` is empty on the company record. | `evaluations/send-hte-link.blade.php` |
| 11.9 | **Student has company in view but not edit** | Show view reads from deployment relationship; edit form now properly reads pending deployment's company. | Data consistency addressed — pending deployments created at student creation time. |
| 11.10 | **Deployment create button** | Manual deployment creation still available for edge cases but auto-deployment is the primary flow. | N/A (design decision) |
| 11.11 | **Highlight effect: ring → glow** | Changed from green ring border to soft box-shadow glow on document focus from notifications. | `documents.blade.php` |
| 11.12 | **First-login redirect loop** | `student.password.change` was missing from limited-mode allowed routes in `EnsureStudentSession`, causing infinite redirect loop. | `EnsureStudentSession.php` |
| 11.13 | **Remove deployment create button** | Removed "Add Deployment" button from index page. Routes kept for backward compatibility. | `deployments/index.blade.php` |
| 11.14 | **Send Message from student registry** | Fixed button styling from inline style to `btn-primary`. Links to create page with student pre-selected via `student_account_id`. | `tab-profile.blade.php` |
| 11.15 | **Lock OJT assignment for active deployments** | Shows locked badge with company name and message when student has active deployment. Radio toggles and company selector hidden. Edit form passes `$hasActiveDeployment` flag. | `students/_form.blade.php`, `StudentController.php` |
| 11.16 | **Group document queue by student** | Queue now groups pending documents by student. Each row shows student name + count with expandable chevron. Click to see individual document rows with Review button. | `StudentDocumentController.php`, `queue-table.blade.php` |
| 11.17 | **Sidebar reordered** | Changed sidebar order: Dashboard → Companies → Students → Deployments (previously was Students → Companies → Deployments). | `navigation.blade.php` |
| 11.18 | **Document count fix on My Documents** | Stats now only count documents visible in current phase (Pre: 8, Monitoring: 12). `$existingVisible` filters by `$requiredDocuments` IDs. Fixed 150% progress bug. | `student/documents.blade.php` |
| 11.19 | **Seeder cleanup** | Added `RequiredDocument::where('name', 'OJT Supervisor Evaluation Form')->delete()` to remove orphaned record. Updated info message to reflect 12 documents. | `SampleDataSeeder.php` |
| 11.20 | **HTE email delivery fixed** | Switched `MAIL_MAILER` from `log` back to `smtp`. Removed `Queueable` from `HteEvaluationLinkMail` and `NotificationMail`. Added try-catch in `storeHteLink()` for SMTP errors. | `.env`, `HteEvaluationLinkMail.php`, `NotificationMail.php`, `EvaluationController.php` |

---

## Session Log

| Date | Session | Focus |
|------|---------|-------|
| June 14–20, 2026 | Phase 0–3 | Original capstone build + panel recommendations |
| June 21, 2026 | Phase 4–7 | Product polish, data integrity, reports, pagination |
| June 22, 2026 | Phase 8–9 | HTE evaluation overhaul, DOCX exports, performance optimization |
| June 23, 2026 | Phase S1–S3 + Walkthrough | Student portal overhaul, profile hardening, login fixes, full system walkthrough |
| June 23, 2026 (evening) | Bug fixes | 3 critical bugs found during walkthrough (check-in 500, profile redirect, seeder columns) |
| June 24, 2026 | Phase 10 + 11 | Student lifecycle automation, full instructor walkthrough fixes, document reorganization, conversation redesign, sidebar reorder |

**Next session:** Pending — verify all fixes end-to-end across all 4 roles, prepare defense demo flow.

---

## Comprehensive System Workflow

### Student Lifecycle

```
PHASE 0: ACCOUNT CREATION (by Instructor)
──────────────────────────────────────────
  → Instructor fills New Student form:
     - Personal info, Section, Assigned Instructor
     - OJT Assignment: Internal (no company) or External (select company)
     - Deployment start/end dates removed — auto-set later
  → System creates:
     - Student record
     - StudentAccount (password defaults to student number)
     - Deployment record (status: pending, company from selection)
  → Student sees 8 Pre documents only (phase-filtered)
  → Clock In/Out hidden from sidebar
  → Portal access: limited (documents + announcements + certificates + profile)

PHASE 1: PRE-REQUIREMENTS (by Student)
──────────────────────────────────────────
  8 documents — all mandatory, all Pre phase:
    1. Pledge of Good Conduct
    2. Memorandum of Agreement
    3. Internship Agreement
    4. Parent Consent Form
    5. Application Letter
    6. Endorsement Letter
    7. Resume
    8. Acceptance Letter

  → Student selects file → clicks Upload button (no auto-submit)
  → Instructor reviews in Document Queue
  → Status flow: Submitted → In Review → Approved / Returned

PHASE 2: AUTO-DEPLOY (by System)
──────────────────────────────────────────
  → ALL 8 Pre docs have status=Submitted AND workflow_status=completed?
     → SYSTEM activates pending deployment:
        - status: pending → active
        - start_date: today
  → Triggered on three paths:
     1. Document Queue → Review → Approve (workflowAction)
     2. Student Checklist → Edit status (update)
     3. Any student page load (EnsureStudentSession retroactive check)
  → Student gets full portal access:
     - Dashboard, Clock In/Out, DTR, Weekly Journals, Messages
     - 4 Monitoring documents appear on My Documents
  → Student + Instructor notified via bell + email

PHASE 3: MONITORING (by Student + Instructor)
──────────────────────────────────────────
  4 monitoring documents (visible only after deployment active):
    9. Enrolment Form
   10. NBI Clearance
   11. Medical Certificate
   12. Training Plan

  → Student clocks in/out daily (passcode or session-based)
  → DTR accumulates total_minutes per attendance record
  → Weekly journals submitted every week
  → HTE evaluation link sent by instructor to supervisor

PHASE 4: HTE EVALUATION (by Supervisor)
──────────────────────────────────────────
  → Instructor generates one-time secure link → emailed to supervisor
  → Supervisor opens link → public HTE form (no login required)
  → 18 criteria across 3 categories (1-5 scale each):
     - Work Habits (5 criteria): punctuality, initiative, etc.
     - Work Skills (6 criteria): technical competence, problem-solving, etc.
     - Social Skills (7 criteria): communication, teamwork, etc.
  → Alpine.js computes: category averages → overall average × 20 = percentage
  → Submit button disabled until ALL 18 criteria are filled
  → On submit:
     - Evaluation record created (score, criteria_scores JSON, comments)
     - Link marked as used
     - Student + Instructor notified
  → Student can download evaluation as DOCX from dashboard

PHASE 5: AUTO-COMPLETION (by System)
──────────────────────────────────────────
  → Total attendance minutes ≥ 36,000 (600 hours)?
     → SYSTEM auto-completes deployment:
        - status: active → completed
        - end_date: today
  → Triggered after every clock-out (store + quickClock)
  → Instructor notified → issues certificate

PHASE 6: CERTIFICATE (by Instructor)
──────────────────────────────────────────
  → Instructor uploads completion certificate
  → Certificate visible in student portal
  → Student downloads certificate
```

### Role Permissions & Access

```
INSTRUCTOR (OJT Coordinator)
─────────────────────────────
  Dashboard          ✅ Full (KPIs, at-risk alerts, section compliance)
  Companies          ✅ CRUD
  Students           ✅ CRUD (create, edit, delete, import Excel)
  Deployments        ✅ View + Edit + Delete (no manual create)
  Compliance         ✅ Full (status filters, risk flags, section filter)
  Attendance         ✅ Full (date/month filter, batch resolve)
  Document Queue     ✅ Full (review, approve, return documents)
  Evaluations        ✅ CRUD + send HTE links
  Weekly Journals    ✅ View + review (status, section, My Students filters)
  DTR                ✅ View calendar
  Certificates       ✅ CRUD + verify
  Announcements      ✅ CRUD
  Messages           ✅ Full (staff + students)
  Reports            ✅ View all tabs + export PDF/CSV (except exec summary)
  Settings/Campus    ✅ Full (geofence, time windows, policy notes)

CHAIRPERSON
─────────────────────────────
  Dashboard          ✅ Full (4 KPI cards, section compliance widget)
  Students           ✅ View only
  Companies          ❌ Hidden from sidebar
  Deployments        ✅ View only
  Compliance         ✅ Full (status, section, risk filters)
  Attendance         ✅ View + filter
  Document Queue     ❌ Hidden from sidebar
  Evaluations        ✅ View only
  Weekly Journals    ❌ Hidden from sidebar
  DTR                ❌ Hidden from sidebar
  Certificates       ❌ Hidden from sidebar
  Announcements      ✅ CRUD
  Messages           ✅ Full (staff)
  Reports            ✅ View tabs + Program Health + export
  Settings/Campus    ❌ 403
  Exec Summary       ❌ 403

DEAN
─────────────────────────────
  Dashboard          ✅ Full (4 KPI cards, evaluations overview)
  Students           ✅ View only (no create, no edit, no delete)
  Companies          ❌ Hidden from sidebar
  Deployments        ❌ Hidden from sidebar
  Compliance         ✅ View + filter
  Attendance         ✅ View + filter
  Document Queue     ❌ Hidden from sidebar
  Evaluations        ✅ View only
  Weekly Journals    ❌ 403
  DTR                ❌ 403
  Certificates       ❌ 403
  Announcements      ✅ View only (no create)
  Messages           ✅ Full (staff)
  Reports            ✅ View tabs + Executive Summary + export
  Program Health     ❌ 403
  Settings/Campus    ❌ 403

STUDENT (Pre-deployment)
─────────────────────────────
  My Documents       ✅ 8 Pre documents (upload + track)
  Announcements      ✅ View only
  Certificates       ✅ View only
  Profile            ✅ Edit email, contact number, password
  Clock In/Out       ❌ Hidden from sidebar
  Dashboard          ❌ Redirected to Documents
  DTR                ❌ Redirected to Documents
  Weekly Journals    ❌ Redirected to Documents
  Messages           ❌ Redirected to Documents

STUDENT (Deployed — Full Access)
─────────────────────────────
  Dashboard          ✅ Quick clock, grade card, passcode, announcements
  My Documents       ✅ 12 documents (8 Pre + 4 Monitoring)
  Clock In/Out       ✅ Passcode + session-based
  DTR                ✅ Calendar view + export DOCX
  Weekly Journals    ✅ Auto-save editor + submit + export DOCX
  Messages           ✅ Inbox + Sent + reply (staff + students)
  Certificates       ✅ View + download
  Announcements      ✅ View only
  Profile            ✅ Edit email, contact number, password
```

### Key System Rules

| Rule | Where Enforced |
|------|---------------|
| Auto-deploy: all 8 Pre docs approved → deployment activates | `StudentDocumentController::workflowAction()` + `update()` + `EnsureStudentSession` middleware |
| Auto-complete: 600 hours → deployment completes | `AttendanceController::store()` + `quickClock()` |
| Phase-aware docs: Pre-deployment sees 8 docs, deployed sees 12 | `StudentPortalController::documents()` |
| Clock In/Out: hidden for non-deployed students | `navigation.blade.php` + `EnsureStudentSession` |
| No manual deployment creation | Routes `deployments.create` + `store` removed |
| OJT Assignment locked when deployment active | `students/_form.blade.php` — shows locked badge |
| HTE evaluation replaces paper upload | OJT Evaluation Form removed from RequiredDocuments |
| Progress denominator: 8 for pre-deployment, 12 for deployed | `documents.blade.php` — `$visibleDocIds` |
| No auto-submit on file select | `onchange` removed from `document-card.blade.php` |
| Notifications: auto-scroll + highlight | `documents.blade.php` — `?focus={id}` + glow effect |
| Action menu: horizontal overlay (no dropdown overflow) | `action-menu.blade.php` — `absolute right-full` |

### Session Log

| Date | Session | Focus |
|------|---------|-------|
| June 14-20 | Phase 0-3 | Original capstone build + panel recommendations |
| June 21 | Phase 4-7 | Product polish, data integrity, reports, pagination |
| June 22 | Phase 8-9 | HTE evaluation overhaul, DOCX exports, performance optimization |
| June 23 AM | Phase S1-S3 | Student portal overhaul, profile hardening, login fixes |
| June 23 PM | Walkthrough | Full system walkthrough — found 3 critical bugs |
| June 24 AM | Phase 10 | Student lifecycle automation (auto-deploy, auto-complete, phase visibility) |
| June 24 PM | Phase 11 | Instructor walkthrough fixes (30+ items: pagination, conversation redesign, import tracking, queue grouping, OJT lock, sidebar reorder, document count fix) |
| June 24 Evening | Polish | Action menu overlay, hover effects, deployment routes removed, UTF-8 fix, comprehensive workflow documented |
| June 24 Late | Settings redesign | Collapsible cards reverted to single form — map geofencing (Leaflet) broken after `x-data` refactor, needs fix |
| June 25 AM | Phase 12: Messages + Notifications | WhatsApp-style split panel (inbox always visible), removed Sent tab, 5/page pagination, global HTMX error handler, markAllAsRead redirect fix, Leaflet z-index fix |
| June 25 PM | Phase 13: Auto-Deployment + Attendance 4-Session | auto-deployment creates deployment when none exists (company_id nullable), Assign Company UI on profile, 4-session attendance (AM in/out + PM in/out), clockpicker library, gradient clock-in page, AM/PM color-coded dashboard |
| June 25 Late | Phase 14: Attendance + DTR Fixes | Fixed instructor tab HTMX loading (removed guard/fallback), fetch() instead of htmx.ajax() for DTR/attendance tabs, batch resolve consolidation (removed per-row resolve, fixed checkbox triple-binding, added HX-Trigger refresh), 5/page pagination on all tabs and attendance index |

---

## Phase 12: Messages & Notifications Overhaul (June 25)

Completed June 25, 2026. WhatsApp-style message panel, notification fixes, map z-index fix.

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 12.1 | **WhatsApp-style message layout** | Split panel: left inbox (always visible) + right panel transitions (empty/new message/conversation). No page refreshes — all HTMX partial swaps. Removed Sent tab. Back to Inbox button moved from header to content. | `index.blade.php`, `show.blade.php`, `create.blade.php`, `inbox-column.blade.php`, `create-form.blade.php`, `conversation.blade.php`, `empty-state.blade.php` (new), `thread-item.blade.php` |
| 12.2 | **Message thread deduplication** | `findExistingThread()` checks for existing thread with same participants before creating new one. Self-messaging prevented. Draft recovery via sessionStorage. Double-click prevention. | `MessageThreadController.php` |
| 12.3 | **In-app message notifications** | Messages now create in-app (database) notifications via `NotificationService` in both store and reply. | `MessageThreadController.php` |
| 12.4 | **Notification markAllAsRead fix** | Returns redirect for standard POST (not raw JSON). Checks `expectsJson()`. | `NotificationController.php` |
| 12.5 | **Leaflet map z-index fix** | Added `isolation: isolate` to map container to prevent tile layers from overlapping dropdowns. | `campus.blade.php` |
| 12.6 | **Pagination 5/page** | Changed message threads from 10 to 5 per page. | `MessageThreadController.php` |

## Phase 13: Auto-Deployment & 4-Session Attendance (June 25)

Completed June 25, 2026. Full student lifecycle automation and enhanced attendance tracking.

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 13.1 | **Auto-deployment creates deployment** | `autoActivateDeployment()` now creates a deployment with `company_id = null` + `status = active` when all pre-docs approved but no deployment exists. | `Student.php` |
| 13.2 | **company_id nullable** | Migration drops FK constraint, makes company_id nullable with `nullOnDelete`. | `2026_06_25_004817_make_company_id_nullable_in_deployments.php` (new) |
| 13.3 | **Assign Company UI** | Dropdown + button on student profile tab when deployment has no company. Route + controller for company assignment. | `tab-profile.blade.php`, `DeploymentController.php`, `web.php` |
| 13.4 | **4-session attendance model** | Added `am_check_in`, `am_check_out`, `pm_check_in`, `pm_check_out` columns. 4-session clock-in/out logic (AM In → AM Out → PM In → PM Out). Auto-detection via session state machine. | `2026_06_25_013753_add_am_pm_sessions_to_attendances.php` (new), `Attendance.php`, `AttendanceController.php` |
| 13.5 | **Settings time windows redesign** | Grouped AM/PM session cards with clock icons, arrow separators, Grace Period in separate card. Clockpicker integration (circular analog clock). | `campus.blade.php`, `CampusSettingController.php` |
| 13.6 | **Time window validation** | Added `validateTimeWindows()` — checks start < end for all ranges. | `CampusSettingController.php` |
| 13.7 | **Check-in page gradient** | AM session = emerald-50 gradient, PM session = indigo-50 gradient. Session badge (☀️ AM / 🌙 PM) shown. | `check-in.blade.php`, `AttendanceController.php` |
| 13.8 | **Dashboard quick clock colors** | AM buttons = emerald border/hover, PM buttons = indigo border/hover. | `dashboard.blade.php` |
| 13.9 | **Settings cache fix** | `campusCached()` strips binary geometry column before serialization to prevent cache corruption. | `Setting.php` |
| 13.10 | **Weekly journal null company fix** | Added `?->` null-safe operator on deployment company name. | `weekly-journals/index.blade.php` |
| 13.11 | **Clockpicker library** | Installed and integrated jQuery clockpicker with dynamic import. Added to `initDatePickers()` flow. | `app.js`, `app.css`, `package.json` |

## Phase 14: Instructor Tab Fixes & Attendance Batch Consolidation (June 25)

Completed June 25, 2026. Fixed HTMX loading issues, simplified attendance review workflow.

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 14.1 | **Instructor tab $student fix** | Added `$student` to `compact()` in all 4 tab controllers (journals, DTR, attendance, certificates). | `StudentController.php` |
| 14.2 | **Tab HTMX guard removed** | Removed `document.getElementById('...loaded')` guard and `setTimeout` fallbacks that prevented HTMX from firing. | `show.blade.php` |
| 14.3 | **DTR tab uses fetch()** | Replaced broken `htmx.ajax()` with native `fetch()` for DTR and attendance tabs. | `show.blade.php` |
| 14.4 | **DTR tab data population** | `tabDtr()` now auto-creates DailyTimeRecord entries from Attendance records + updates existing ones when attendance changes. | `StudentController.php` |
| 14.5 | **DTR tab view** | Simplified DTR display with null-safe formatting. Removed Carbon::parse() wrapping. | `tab-dtr.blade.php` |
| 14.6 | **Attendance tab view** | Shows all 4 session events with session icons and geofence status. Null-safe formatting. | `tab-attendance.blade.php` |
| 14.7 | **Attendance batch resolve consolidated** | Removed per-row "Resolve" form — now batch-only via checkboxes. Fixed checkbox triple-binding conflict (x-model + :checked + @change). Added HX-Trigger refresh after batch. Added resolution_note to batch modal. | `ajax-list.blade.php`, `BatchController.php`, `index.blade.php` |
| 14.8 | **Evaluation ID hidden** | Removed "Evaluation #ID" from breadcrumbs and detail card. | `evaluations/show.blade.php` |
| 14.9 | **Pagination 5/page on attendance** | Changed attendance index from 10 to 5 per page. | `AttendanceController.php` |

### New Migrations (Phase 12-14)

| # | Migration | Purpose |
|---|-----------|---------|
| 1 | `2026_06_25_004817_make_company_id_nullable_in_deployments` | nullable company_id with nullOnDelete |
| 2 | `2026_06_25_013753_add_am_pm_sessions_to_attendances` | 4-session attendance columns |

### New/Modified Commands
None.

### Session Log Updates

| Date | Session | Focus |
|------|---------|-------|
| June 25 AM | Phase 12 | Messages: WhatsApp-style panel, notifications, thread dedup, draft recovery |
| June 25 PM | Phase 13 | Auto-deployment, 4-session attendance, clockpicker, gradient check-in page |
| June 25 Late | Phase 14 | Instructor tab fixes, attendance batch consolidation, evaluation ID cleanup |
| June 25 Extra | Phase 15–19 | See below for full phase breakdown |

---

# 🔧 Phase 15: Company-Centric Student Assignment (June 25–26)

Overhauled the student–company assignment workflow. Company is no longer assigned during student creation — instructors assign students to companies from the company page.

## Changes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 15.1 | **Remove company from student import** | Removed `ensureDeployment()`, company/deployment creation from `StudentsImport`. Import is pure student data only. | `StudentsImport.php`, `StudentImportController.php` |
| 15.2 | **Remove company from student form** | Removed `company_id`, `start_date`, `end_date` from `StoreStudentRequest` and student creation/`_form` views. | `StoreStudentRequest.php`, `_form.blade.php` |
| 15.3 | **Auto-pending deployment** | Every new student auto-gets a pending deployment with `company_id = null`. | `StudentController.php`, `StudentsImport.php` |
| 15.4 | **Company show page: unified student list** | Replaced separate Deployments + Assign Students sections with a single tabbed list (All / Pending / Deployed). Read-only view. Pagination 5/page. | `companies/show.blade.php`, `companies/partials/students-list.blade.php` |
| 15.5 | **Company edit page: student assignment hub** | Batch assign students to company via multi-select checkboxes. Search + "My Students" filter. Paginated assigned list with Remove button (Alpine modal, no native confirm). | `companies/edit.blade.php`, `CompanyController.php` (attachStudents, detachStudent, assignableStudents), `companies/partials/assigned-list.blade.php`, `companies/partials/assignable-list.blade.php` |
| 15.6 | **Removed old dead partials** | Deleted `pending-students-list`, `deployed-students-list`, `student-assignment`, `deployments-list` partials. | 4 files removed |
| 15.7 | **Block auto-deploy without company** | `autoActivateDeployment()` no longer activates when no company assigned. Instructor notified via DB notification + email reminder. | `Student.php`, `EnsureStudentSession.php`, `SendReminders.php` |
| 15.8 | **Unified company show page layout** | Full-height flex layout: header + company info fixed, student list scrolls internally. | `companies/show.blade.php` |

---

# 🔧 Phase 16: DTR Task Editing & UI Overhaul (June 25–26)

## Changes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 16.1 | **DTR inline task editing** | Per-day Alpine.js inline editor. Click to edit → type → blur/Enter to auto-save. Only today's date editable. Max 100 chars. | `student/dtr/index.blade.php`, `DtrController.php` (updateTasks), `routes/web.php` |
| 16.2 | **DTR auto-create on absent days** | `updateTasks()` creates a DTR record on-demand if none exists for that date (`source = 'manual'`). | `DtrController.php` |
| 16.3 | ~~DTR export removed~~ | Export button restored (Phase 22.1). Route/controller was kept for backward compatibility. | `student/dtr/index.blade.php` |
| 16.4 | ~~Weekly Journal export removed~~ | Export button restored (Phase 22.1). Both routes kept for backward compatibility. | `student/weekly-journals/show.blade.php` |
| 16.5 | **Instructor DTR table: Tasks column** | Added Tasks column to the instructor's monthly DTR table, with truncation + tooltip. | `dtr/partials/student-list.blade.php` |
| 16.6 | **Student registry DTR tab: proper table** | Replaced stacked flex rows with a full 7-column table (Day, AM Arr, AM Dep, PM Arr, PM Dep, Hours, Tasks) matching the system's DTR table style. | `tab-dtr.blade.php` |
| 16.7 | **DTR monitoring page: scrollable containers** | Both panels (student list + DTR table) capped at `max-h-[500px]` with independent overflow-y scrolling. No page-level scroll. Table compacted (`text-xs`, `px-2`) to fit without horizontal scroll. | `dtr/partials/student-list.blade.php` |
| 16.8 | **Summary bar layout fix** | Fixed broken `</div>` that caused cards to stack vertically instead of horizontally. | `student/dtr/index.blade.php` |

---

# 🔧 Phase 17: Global UI Consistency & Validation Hardening (June 25–26)

## Changes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 17.1 | **Loading overlay fix** | Removed `pointer-events: none` (clicks now blocked). Added `cursor: wait`. Removed children `pointer-events: none`. Added 10s safety auto-hide timeout. Added `popstate` + `htmx:beforeSwap` hide triggers. | `public/css/custom.css`, `resources/js/app.js` |
| 17.2 | **Native confirm → Alpine modal** | Replaced browser `confirm()` on Remove button in assigned-list with teleported Alpine confirmation modal (backdrop click + Escape to dismiss). | `assigned-list.blade.php` |
| 17.3 | **Messages container fix** | Added `min-h-[520px]` to right panel so compose form doesn't shrink. | `messages/index.blade.php` |
| 17.4 | **Company form field limits** | Updated client `maxlength` and server `max:` for all fields: name 150, street_address 100, notes 100, contact_last_name 60, contact_first_name 60, contact_email 120. | `_form.blade.php`, `StoreCompanyRequest.php`, `UpdateCompanyRequest.php` |
| 17.5 | **Import validation hardening** | Added `contact_phone`, `contact_email`, `notes` to `CompaniesImport::rules()`. Fixed street_address `max:100`. Added name `max:150`. | `CompaniesImport.php` |
| 17.6 | **PhoneHelper format guard** | `formatPhone()` now rejects numbers >15 digits to prevent displaying garbage data. | `PhoneHelper.php` |
| 17.7 | **Batch select persistence fix** | Clear sessionStorage on full page load (so stale selections don't appear), preserve during HTMX swaps (so selection survives pagination). | `app.blade.php`, `ajax-list.blade.php` |
| 17.8 | **Global toast notification system** | Added `X-Toast-Message` response header support in `htmx:afterRequest` handler — any HTMX controller can add `->header('X-Toast-Message', '...')` to show toast. Applied to: document workflow (approve/return/forward/sign), message toggle read/archive, send message, send reply, DTR task save, journal file upload/delete. | `app.js`, `StudentDocumentController.php`, `MessageThreadController.php`, `dtr/index.blade.php`, `weekly-journals/show.blade.php` |

---

# 🔧 Phase 18: UX & Feature Polish (June 25–26)

## Changes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 18.1 | **Dashboard "Not Deployed" clickable** | Added `link` to NOT DEPLOYED KPI card → filters students to pending only. | `DashboardController.php` |
| 18.2 | **Student page Pending/Deployed toggle** | Pill toggle (All \| Pending \| Deployed) in student index. `deployment_status` query filter on controller. | `StudentController.php`, `students/index.blade.php` |
| 18.3 | **Journal progress denominator fix** | `journal_progress_pct` now only counts weeks whose `week_end_date` is past or today. Future weeks excluded from denominator. | `Student.php`, `StudentController.php` (N+1 optimized query) |
| 18.4 | **Remove "Wait for instructor" text** | Removed step 2 from Getting Started checklist on both dashboard and documents pages. Renumbered steps. | `student/dashboard.blade.php`, `student/documents.blade.php` |
| 18.5 | **Program Health report removed** | Route, controller method, view, and navigation link removed — not in scope per manuscript. | `routes/web.php`, `ReportController.php`, `program-health.blade.php`, `reports/index.blade.php` |
| 18.6 | **Evaluation criteria: category-grouped form** | Replaced flat list with 3 category cards (Work Habits, Work Skills, Social Skills) each with inline "Add" form + existing criteria list + remove button. | `EvaluationCriterionController.php` (new), `evaluations/criteria.blade.php` (new), `routes/web.php`, `evaluations/index.blade.php` |
| 18.7 | **Phone number formatting (global)** | `PhoneHelper::formatPhone()` applied to all 4 read-only display locations + 3 input fields show formatted values. Mobile: `+63 XXX XXX XXXX`. Landline: `(0XX) XXX XXXX`. | `PhoneHelper.php`, `tab-profile.blade.php`, `student/profile.blade.php`, `companies/show.blade.php`, `companies/ajax-list.blade.php`, `companies/_form.blade.php`, `students/_form.blade.php` |

---

# 🔧 Phase 19: PSGC Address Seeding & Fixes (June 25–26)

## Changes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 19.1 | **Seeder: added municipalities endpoint** | The seeder only fetched from `/cities/` endpoint, skipping municipalities entirely. Fixed to try both `/cities/` and `/municipalities/` endpoints. | `SeedPhilippineAddresses.php` |
| 19.2 | **Seeder: barangay dual endpoint** | Barangay fetch now tries both `/cities/{code}/barangays/` and `/municipalities/{code}/barangays/`. Added timeout + error handling. | `SeedPhilippineAddresses.php` |
| 19.3 | **Full seeding completed** | Ran full seeding + missing municipality + missing barangay fixes: 81 provinces, 1,615 cities/municipalities, 40,136 barangays. | — |
| 19.4 | **COMMANDS.md updated** | Added `php artisan psgc:seed` command to setup section. | `COMMANDS.md` |

---

# 🔧 Phase 20: System-wide Bug Fixes & UI Overhaul (June 26)

Completed June 26, 2026. Comprehensive pass over 22+ issues across all user roles.

## General Fixes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 20.1 | **Assign instructor button not working** | Bug: `$el.closest('form')` in teleported modal returned `null` (form not in parent chain after teleport). Fix: changed to `document.getElementById('batch-assign-form').submit()`. | `ajax-list.blade.php` |
| 20.2 | **Reports dashboard pagination color black** | Default `pagination::tailwind` used `bg-gray-200` for active page — gray/black instead of emerald-600 theme. Fix: published + customized pagination view with emerald-600 active state. | `vendor/pagination/tailwind.blade.php` |
| 20.3 | **Document deadline update resets to page 1** | `RequiredDocumentController::update()` always redirected to `required-documents.index` without page param. Fix: appended `?page=N` from request to redirect. | `RequiredDocumentController.php` |
| 20.4 | **Batch form inside HTMX target resets on filter** | Batch action bar was inside `#students-ajax-mount` — every filter change recreated the form. Fix: moved batch bar outside HTMX target into standalone partial `batch-bar.blade.php`, parent `x-data` wraps both. | `index.blade.php`, `batch-bar.blade.php` (new), `ajax-list.blade.php` |

## Chairperson

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 20.5 | **Total students card on dashboard** | Added TOTAL STUDENTS KPI card as first card on chairperson dashboard. | `DashboardController.php` |
| 20.6 | **My Students checkbox for chairperson** | Added `@if (auth()->user()?->role === 'instructor')` gate on weekly-journals, attendance, and queue views. | `weekly-journals/index.blade.php`, `attendance/index.blade.php`, `student-documents/queue.blade.php` |

## Instructor

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 20.7 | **Return note max 1000 → 50** | Changed server validation `max:1000` → `max:50`, client `maxlength="1000"` → `maxlength="50"`. | `StudentDocumentController.php`, `review-panel.blade.php` |
| 20.8 | **Return button transition fixed** | Return form used `hx-swap="none"` — no visual feedback. Changed to `hx-target="#review-panel-content" hx-swap="innerHTML"` matching approve/forward pattern. | `review-panel.blade.php` |
| 20.9 | **Phone maxlength 17 → 11** | Changed `maxlength="17"` → `maxlength="11"` (student) and `maxlength="13"` (company). Updated hint text. | `students/_form.blade.php`, `student/profile.blade.php`, `companies/_form.blade.php` |
| 20.10 | **Company address "see more"** | Truncated at 40 chars with `Str::limit` + "see more" link to company show page. | `companies/partials/ajax-list.blade.php` |
| 20.11 | **Deployment status filter** | Added status dropdown (All/Active/Completed/Pending) on deployment index with controller handling. | `deployments/index.blade.php`, `DeploymentController.php` |
| 20.12 | **Journal submission notification + red dot** | Added `NotificationService::notifyRole('instructor')` on journal submit. Added `$pendingJournalCount` red dot badge on sidebar journal link. | `WeeklyJournalController.php`, `navigation.blade.php` |
| 20.13 | **Journal attachments 404/access denied** | Created `downloadFile()` controller method with proper auth + route, replaced all `/storage/` direct URLs with named routes. Files served through Laravel with authorization. | `WeeklyJournalController.php`, `routes/web.php`, `weekly-journals/show.blade.php`, `student/weekly-journals/show.blade.php` |
| 20.14 | **Weekly journal UI grouped by student** | Redesigned from flat table to accordion grouped by student with expandable rows, pending badges, status colors. | `weekly-journals/partials/ajax-list.blade.php` |
| 20.15 | **My Students default checked** | Changed to `$request->has('my_students') ? $request->boolean('my_students') : true` pattern. Added hidden input `value="0"` for proper unchecked handling. | `WeeklyJournalController.php`, `DeploymentController.php`, `ComplianceController.php`, index blade files |
| 20.16 | **Instructor creation form** | Added "Manage Instructors" panel to Campus Settings. Split name into last/first/middle fields. Proper regex validation, sanitization, aria-labels, maxlength, error messages. | `CampusSettingController.php`, `settings/campus.blade.php` |
| 20.17 | **Remove Manage Criteria button** | Removed redundant button from evaluations index — same functionality exists in Settings → Evaluation Criteria. | `evaluations/index.blade.php` |
| 20.18 | **Section filter added to evaluations** | Replaced score filter dropdown with section filter (A/B/C/D). Removed score filter from controller and blade. | `EvaluationController.php`, `evaluations/index.blade.php` |

## Student

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 20.19 | **Due Today filter fixed** | Removed `&& $deadlineAt->gte($nowTs)` — documents due earlier today now appear in "Due Today" filter. | `student/documents.blade.php` |
| 20.20 | **Passcode double-click → single-click** | Simplified from two-click "confirmed → revealed" to one-click toggle. | `student/dashboard.blade.php` |

## OJT Type (Three-State Deployment)

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 20.21 | **ojt_type migration** | Added `string('ojt_type', 20)->default('unplaced')` to students table. | `2026_06_25_231336_add_ojt_type_to_students.php` (new) |
| 20.22 | **Three-state deployment logic** | `unplaced`: no auto-deploy, notifies instructor. `internal`: auto-deploy without company (school-based). `external`: requires company for auto-deploy. | `Student.php`, `StoreStudentRequest.php`, `UpdateStudentRequest.php`, `StudentController.php`, `DeploymentController.php` |
| 20.23 | **OJT Assignment form UI** | Collapsible card with 3 radio options (No Placement Yet / Internal OJT / External OJT). No company selected by default. | `students/_form.blade.php` |
| 20.24 | **Internal OJT display + lock** | Shows "Internal OJT (School-based)" with lock icon. Company assignment hidden for internal students. | `tab-profile.blade.php`, `student/dashboard.blade.php` |
| 20.25 | **Notification on company assign** | `DeploymentController::assignCompany()` now notifies student via `NotificationService` and triggers auto-deployment. | `DeploymentController.php` |

## Batch Select Fixes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 20.26 | **Select-all-matching checkbox submits invisible input** | `x-show` (CSS display:none) kept `select_all_matching=1` in DOM → always submitted. Changed to `<template x-if>` (DOM removal). | `batch-bar.blade.php` |
| 20.27 | **Confirm modal shows 0 students** | Modal used `selected.length` which is 0 when `selectAllMatching` is true. Fixed to `selectAllMatching ? (totalMatching - deselected.length) : selected.length`. | `batch-bar.blade.php` |
| 20.28 | **Items accumulate across HTMX pages** | `items` array never cleared across page changes → IDs from all visited pages accumulated. Fix: `x-init="items = []; restorePersisted()"` on table wrapper. | `ajax-list.blade.php` |
| 20.29 | **Header checkbox state for select-all-matching** | `:checked` didn't account for `selectAllMatching` mode. Fixed to `selectAllMatching || (selected.length === items.length && items.length > 0)`. | `ajax-list.blade.php` |
| 20.30 | **"All 0 on this page selected" on empty table** | `updateSelectAllPrompt()` showed prompt when both `selected` and `items` were empty but `totalMatching > 0`. Added `this.items.length > 0` guard. | `ajax-list.blade.php` |

## Evaluation Criteria (Settings) — ⚠️ NOT WORKING

| # | Item | Detail | Status |
|---|------|--------|--------|
| 20.31 | **Add button** | Clicking "Add" in per-category inline forms does not add the item. Form submits but new criteria is not created. | ❌ BROKEN |
| 20.32 | **Save Changes button** | Saving criteria edits (toggle on/off, rename labels) does not persist changes. Form submits but no changes are saved. | ❌ BROKEN |
| 20.33 | **Delete button** | Clicking Delete in the confirmation modal reports "Criterion removed" success but the item is not deleted from the database. | ❌ BROKEN |

## Files Changed (Phase 20)

```
database/migrations/2026_06_25_231336_add_ojt_type_to_students.php (new)
app/Models/Student.php
app/Http/Controllers/StudentDocumentController.php
app/Http/Controllers/BatchController.php
app/Http/Controllers/RequiredDocumentController.php
app/Http/Controllers/DashboardController.php
app/Http/Controllers/DeploymentController.php
app/Http/Controllers/ComplianceController.php
app/Http/Controllers/WeeklyJournalController.php
app/Http/Controllers/EvaluationController.php
app/Http/Controllers/CampusSettingController.php
app/Http/Controllers/EvaluationCriterionController.php
app/Http/Middleware/EnsureStudentSession.php
app/Http/Requests/StoreStudentRequest.php
app/Http/Requests/UpdateStudentRequest.php
routes/web.php
resources/views/vendor/pagination/tailwind.blade.php (published+styled)
resources/views/students/index.blade.php
resources/views/students/partials/ajax-list.blade.php
resources/views/students/partials/batch-bar.blade.php (new)
resources/views/students/_form.blade.php
resources/views/students/partials/tab-profile.blade.php
resources/views/student/dashboard.blade.php
resources/views/student/documents.blade.php
resources/views/student/profile.blade.php
resources/views/student/weekly-journals/show.blade.php
resources/views/student-documents/partials/review-panel.blade.php
resources/views/student-documents/queue.blade.php
resources/views/weekly-journals/index.blade.php
resources/views/weekly-journals/show.blade.php
resources/views/weekly-journals/partials/ajax-list.blade.php
resources/views/deployments/index.blade.php
resources/views/attendance/index.blade.php
resources/views/compliance/index.blade.php
resources/views/evaluations/index.blade.php
resources/views/companies/partials/ajax-list.blade.php
resources/views/companies/_form.blade.php
resources/views/students/_form.blade.php
resources/views/settings/campus.blade.php
resources/views/components/confirm-delete.blade.php
resources/views/layouts/navigation.blade.php
```

---

## Session Log (Updated)

| Date | Session | Focus |
|------|---------|-------|
| June 25–26 | Phase 15 | Company-centric assignment: removed company from import/form, company show/edit redesign, batch assign, auto-deploy block |
| June 25–26 | Phase 16 | DTR task editing (inline Alpine), export removal, instructor DTR tasks column, student registry DTR table, scrollable monitoring layout |
| June 25–26 | Phase 17 | Loading overlay fix, Alpine confirm modals, form validation hardening, import validation, phone guard, batch select fix, global toast system |
| June 25–26 | Phase 18 | Dashboard clickable card, student Pending/Deployed toggle, journal progress fix, onboarding text removal, Program Health removal, evaluation criteria UI, phone formatting |
| June 25–26 | Phase 19 | PSGC seeder fixes (municipalities + barangay dual endpoint), full address data seeding |
| June 26 | Phase 20 | System-wide bug fixes (22+ items), three-state OJT deployment, batch select overhaul, evaluation criteria UI, instructor settings, journal redesign, notification system |

## Files Changed Summary (Phases 15–19)

```
app/Support/PhoneHelper.php
app/Models/Student.php
app/Models/Deployment.php
app/Imports/StudentsImport.php
app/Imports/CompaniesImport.php
app/Console/Commands/SendReminders.php
app/Console/Commands/SeedPhilippineAddresses.php
app/Http/Controllers/StudentController.php
app/Http/Controllers/StudentImportController.php
app/Http/Controllers/DashboardController.php
app/Http/Controllers/CompanyController.php
app/Http/Controllers/DtrController.php
app/Http/Controllers/ReportController.php
app/Http/Controllers/StudentDocumentController.php
app/Http/Controllers/MessageThreadController.php
app/Http/Controllers/EvaluationCriterionController.php (new)
app/Http/Requests/StoreStudentRequest.php
app/Http/Requests/StoreCompanyRequest.php
app/Http/Requests/UpdateCompanyRequest.php
app/Http/Middleware/EnsureStudentSession.php
routes/web.php
resources/views/companies/show.blade.php
resources/views/companies/edit.blade.php
resources/views/companies/_form.blade.php
resources/views/companies/partials/students-list.blade.php (new)
resources/views/companies/partials/assigned-list.blade.php (new)
resources/views/companies/partials/assignable-list.blade.php (new)
resources/views/students/_form.blade.php
resources/views/students/index.blade.php
resources/views/students/partials/ajax-list.blade.php
resources/views/students/partials/tab-dtr.blade.php
resources/views/student/dtr/index.blade.php
resources/views/student/dashboard.blade.php
resources/views/student/documents.blade.php
resources/views/student/profile.blade.php
resources/views/student/weekly-journals/show.blade.php
resources/views/dtr/partials/student-list.blade.php
resources/views/evaluations/criteria.blade.php (new)
resources/views/evaluations/index.blade.php
resources/views/messages/index.blade.php
resources/views/layouts/app.blade.php
resources/js/app.js
public/css/custom.css
Deleted: 4 old company partials
```

---

# 🔧 Phase 21: Real-Time, Automation & Student Tabs Fix (June 26)

Completed June 26, 2026. Real-time polling across 6 pages, 8 automation items, student profile tabs fix, and 10 quality round fixes.

## Real-Time Polling (Phase 21a)

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 21a.1 | **Notification bell heartbeat extended** | Bell 30s poll now dispatches 6 custom events (`refresh-inbox`, `refresh-attendance`, `reload-queue`, `refresh-journals`, `refresh-compliance`, `refresh-dashboard`) so visible pages auto-update. | `bell.blade.php` |
| 21a.2 | **Message inbox polling** | Added `every 30s` HTMX trigger so inbox column auto-refreshes. Recipients see new messages without manual refresh. | `messages/index.blade.php` |
| 21a.3 | **Instructor dashboard KPI polling** | Extracted KPI cards + at-risk alert + section compliance to partial. Added 60s HTMX polling + event-driven refresh via `refresh-dashboard`. | `DashboardController.php`, `dashboard.blade.php`, `dashboard-partials/kpi-cards.blade.php` (new) |
| 21a.4 | **Document queue polling** | Added `every 30s` HTMX trigger on `#queue-table-wrapper`. New uploads from students auto-appear. | `queue.blade.php` |
| 21a.5 | **Compliance page polling** | Added `every 30s` HTMX trigger + event refresh. Document status changes auto-refresh. | `compliance/index.blade.php` |
| 21a.6 | **Weekly journals list polling** | Added `every 30s` HTMX trigger + event refresh. Submissions auto-appear for instructors. | `weekly-journals/index.blade.php` |
| 21a.7 | **Student dashboard reload → HTMX swap** | Replaced `window.location.reload()` after quick-clock with targeted `htmx.ajax()` swap of `#student-dashboard-content`. No more full page flash. | `student/dashboard.blade.php` |
| 21a.8 | **Student profile tabs re-fetch** | Centralized tab loading into parent Alpine component. Tabs re-fetch content on every re-selection (not just first visit). Fixes broken `\\'` escaping that crashed all tabs. | `students/show.blade.php` |

## Automation (Phase 21b)

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 21b.1 | **Deployment auto-complete cron** | `deployments:auto-complete` sets active deployments with past `end_date` to `completed`. Notifies student + instructor. Runs daily. | `AutoCompleteDeployments.php` (new), `console.php` |
| 21b.2 | **600hr completion notification** | When attendance hits 36,000 mins, deployment auto-completes AND student + instructor get in-app notification. | `AttendanceController.php` |
| 21b.3 | **Risk flag notifications** | `flags:check` now creates in-app notifications for student + assigned instructor on every new risk flag (absences, late journals, missing docs, expired deployments). | `CheckRiskFlags.php` |
| 21b.4 | **Batch document approval** | New `BatchController::documents()` method supports approving multiple student documents at once via DocumentWorkflowEngine. | `BatchController.php` |
| 21b.5 | **Weekly journals on deployment activation** | `Deployment::saved` model event auto-creates all weekly journal rows (Mon–Sat, week-by-week) when deployment becomes `active`. No more lazy-creation on page load. | `Deployment.php` |
| 21b.6 | **Passcode on deployment activation** | `Deployment::saved` event generates 6-digit attendance passcode on activation (not deferred to page load). | `Deployment.php` |
| 21b.7 | **Scheduled reminders → in-app** | All 7 reminder types (journal due, overdue, attendance, pending docs, deployment start/end, no-company) now also create DB in-app notifications alongside email. | `SendReminders.php` |
| 21b.8 | **Auto-resolve near-boundary attendance** | `attendance:auto-resolve` resolves `near_boundary_review` records older than 3 days. Runs daily. | `AutoResolveAttendance.php` (new), `console.php` |

## Quality Round (Phase 21c)

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 21c.1 | **Evaluation Criteria: Add/Save/Delete fixed** | `new_criteria` inputs used `x-show` (always submitted empty) → `:disabled="!open"`. GET criteria route was shadowed by `evaluations/{evaluation}` wildcard → moved before. Destroy hardened with post-delete verification. | `campus.blade.php`, `web.php`, `EvaluationCriterionController.php` |
| 21c.2 | **Fallback geofence simplified** | Removed `student_geofence_radius_meters` and `geofence_review_buffer_meters`. Kept lat/lng/radius/Google Maps link. | `campus.blade.php`, `CampusSettingController.php` |
| 21c.3 | **External OJT company dropdown** | Company `<select>` appears via Alpine when `ojt_type === 'external'`. Handled in Store/Update requests + controllers. | `_form.blade.php`, `StoreStudentRequest.php`, `UpdateStudentRequest.php`, `StudentController.php` |
| 21c.4 | **Section conflict warning removed** | Deleted "Section C already has active deployments" validator block. | `UpdateDeploymentRequest.php` |
| 21c.5 | **Lock fields when deployed** | Name fields `readonly` when pending/active. `name_extension` uses hidden input when locked. OJT+company locked when active/completed. Controller skips locked fields. | `_form.blade.php`, `StudentController.php` |
| 21c.6 | **Add Deployment button restored** | `create`/`store` routes + methods. Card-based layout. Student list filtered to unassigned only. Button in container. Creates pending; auto-activates if pre-docs approved. | `DeploymentController.php`, `web.php`, `deployments/create.blade.php` (new), `deployments/index.blade.php` |
| 21c.7 | **Post-requirement documents** | Signed DTTR + Signed Weekly Journal (phase=post, renamed Phase 22.2). Visible when all pre + monitoring docs submitted. `progress_pct` respects phase visibility. | `SampleDataSeeder.php`, `StudentPortalController.php`, `Student.php`, `student/documents.blade.php` |
| 21c.8 | **Queue My Students default on** | Checkbox defaults checked via `$request->has()` → `:true`. Hidden `value=0` for proper off-state. `onchange` auto-submit. | `StudentDocumentController.php`, `queue.blade.php` |
| 21c.9 | **Company edit → unplaced only** | `whereIn('ojt_type', ['unplaced', 'external'])` on both assignable queries. | `CompanyController.php` |
| 21c.10 | **Demo attendance time windows** | Migration sets AM 00:00–23:59, PM 00:00–23:59, grace=0. | `2026_06_26_033848_set_demo_attendance_time_windows.php` (new) |

## Files Changed (Phase 21)

```
app/Console/Commands/AutoCompleteDeployments.php (new)
app/Console/Commands/AutoResolveAttendance.php (new)
app/Console/Commands/CheckRiskFlags.php
app/Console/Commands/SendReminders.php
app/Http/Controllers/EvaluationCriterionController.php
app/Http/Controllers/StudentController.php
app/Http/Controllers/DeploymentController.php
app/Http/Controllers/CompanyController.php
app/Http/Controllers/StudentDocumentController.php
app/Http/Controllers/CampusSettingController.php
app/Http/Controllers/StudentPortalController.php
app/Http/Controllers/AttendanceController.php
app/Http/Controllers/DashboardController.php
app/Http/Controllers/BatchController.php
app/Http/Requests/UpdateDeploymentRequest.php
app/Http/Requests/StoreStudentRequest.php
app/Http/Requests/UpdateStudentRequest.php
app/Models/Student.php
app/Models/Deployment.php
routes/web.php
routes/console.php
database/seeders/SampleDataSeeder.php
database/migrations/2026_06_26_033848_set_demo_attendance_time_windows.php (new)
resources/views/settings/campus.blade.php
resources/views/students/_form.blade.php
resources/views/students/show.blade.php
resources/views/student/dashboard.blade.php
resources/views/student/documents.blade.php
resources/views/student-documents/queue.blade.php
resources/views/dashboard.blade.php
resources/views/dashboard-partials/kpi-cards.blade.php (new)
resources/views/deployments/index.blade.php
resources/views/deployments/create.blade.php (new)
resources/views/messages/index.blade.php
resources/views/compliance/index.blade.php
resources/views/weekly-journals/index.blade.php
resources/views/notifications/partials/bell.blade.php
```

## Comprehensive Student Lifecycle Workflow (Updated)

```
PHASE 0: ACCOUNT CREATION (by Instructor) — Three OJT Paths
─────────────────────────────────────────────────────────────

  ┌─────────────────────────────────────────────────────────────────┐
  │ Instructor fills New Student form:                              │
  │ • Personal info, Section, Assigned Instructor                  │
  │ • One of three OJT types must be selected                      │
  └─────────────────────────────────────────────────────────────────┘

          ▼
  ┌──────────────────────────────────────────────────────────────────┐
  │ System creates for ALL paths:                                    │
  │ • Student record (ojt_type set)                                  │
  │ • StudentAccount (password = student number)                     │
  │ • Deployment record (status: pending, company_id: null)          │
  │ • Student sees 8 Pre documents only (phase-filtered)             │
  │ • Clock In/Out hidden from sidebar                               │
  │ • Portal: limited (docs + announcements + certificates + profile)│
  │ • REAL-TIME: Instructor dashboard KPI updates via 60s polling    │
  └──────────────────────────────────────────────────────────────────┘
          │
          │  Choose OJT Type
          ▼
  ┌─────────────────────┐  ┌─────────────────────┐  ┌─────────────────────┐
  │ ① NO PLACEMENT YET  │  │ ② INTERNAL OJT      │  │ ③ EXTERNAL OJT      │
  │    (unplaced)        │  │    (internal)        │  │    (external)        │
  ├─────────────────────┤  ├─────────────────────┤  ├─────────────────────┤
  │ Default state for   │  │ Student works within │  │ Student assigned to │
  │ new students. No    │  │ the school — no      │  │ a partner company.  │
  │ company or school   │  │ external company     │  │ Company is REQUIRED │
  │ department assigned │  │ required. Company    │  │ before deployment   │
  │ yet.                │  │ field stays LOCKED.  │  │ can activate.       │
  ├─────────────────────┤  ├─────────────────────┤  ├─────────────────────┤
  │ Auto-deploy BLOCKED │  │ Auto-deploy ALLOWED  │  │ Auto-deploy BLOCKED │
  │ until company is    │  │ without company —    │  │ until company is    │
  │ assigned OR OJT     │  │ activates as soon as │  │ assigned via:       │
  │ type is changed.    │  │ Pre docs complete.   │  │  • Dropdown on      │
  │                     │  │ Shows "Internal OJT  │  │    student form     │
  │ Student name+dept   │  │ (School-based)" on   │  │  • Company edit     │
  │ info NOT locked     │  │ profile.             │  │    page (bulk)      │
  │ (can still adjust). │  │                      │  │  • Student profile  │
  │                     │  │ Company assignment   │  │    tab (single)     │
  │ Name LOCKED once    │  │ form is HIDDEN —     │  │                     │
  │ deployment becomes  │  │ internal students    │  │ Company dropdown    │
  │ pending.            │  │ don't need one.      │  │ APPEARS when        │
  │                     │  │                      │  │ ojt_type=external   │
  │ Instructor notified │  │ Name LOCKED once     │  │ selected.           │
  │ via bell: "Pre-docs │  │ deployment becomes   │  │                     │
  │ done, assign        │  │ pending.             │  │ Name LOCKED once    │
  │ company"            │  │                      │  │ deployment becomes  │
  └─────────────────────┘  └─────────────────────┘  │ pending.             │
                              │                      │ OJT+company LOCKED  │
                              │                      │ once active.        │
                              │                      └─────────────────────┘
                              │                              │
                              ▼                              ▼
                    ┌─────────────────────┐       ┌─────────────────────┐
                    │  Pre-docs complete? │       │  Company assigned?  │
                    │  → Auto-activate    │       │  + Pre-docs done?   │
                    │    deployment       │       │  → Auto-activate    │
                    │  → Full portal      │       │    deployment       │
                    │  → Monitoring docs  │       │  → Full portal      │
                    └─────────────────────┘       └─────────────────────┘

PHASE 1: PRE-REQUIREMENTS (by Student)
──────────────────────────────────────────
  8 documents — all mandatory, all Pre phase:
    1. Pledge of Good Conduct
    2. Memorandum of Agreement
    3. Internship Agreement
    4. Parent Consent Form
    5. Application Letter
    6. Endorsement Letter
    7. Resume
    8. Acceptance Letter

  → Student selects file → clicks Upload button (no auto-submit)
  → REAL-TIME: Document queue polling (30s) shows new uploads to instructor
  → REAL-TIME: Compliance page polling (30s) updates document status
  → Instructor reviews in Document Queue
  → Status flow: Submitted → In Review → Approved / Returned
  → Email + in-app notification sent on each workflow transition
  → REAL-TIME: Student sees updated status without refresh (bell event triggers)

PHASE 2: AUTO-DEPLOY (by System)
──────────────────────────────────────────
  → ALL 8 Pre docs have status=Submitted AND workflow_status=completed?
     → SYSTEM activates pending deployment:
        - status: pending → active
        - start_date: today
  → AUTOMATION: Deployment::saved model event fires:
     1. Weekly journal rows auto-created (no lazy page-load creation)
     2. Attendance passcode auto-generated (no deferred generation)
  → REAL-TIME: Instructor dashboard KPI + deployment list update
  → Student gets full portal access:
     - Dashboard, Clock In/Out, DTR, Weekly Journals, Messages
     - 4 Monitoring documents appear on My Documents
  → Student + Instructor notified via bell + email
  → REAL-TIME: Student limited-mode polling detects upgrade → auto-reloads

PHASE 3: MONITORING (by Student + Instructor)
──────────────────────────────────────────
  4 monitoring documents (visible only after deployment active):
     9. Enrolment Form
    10. NBI Clearance
    11. Medical Certificate
    12. Training Plan

  → Student clocks in/out daily (passcode or session-based)
  → REAL-TIME: Attendance list polling (12s) shows new check-ins to instructor
  → REAL-TIME: Student tabs re-fetch on each visit (not one-shot)
  → DTR accumulates total_minutes per attendance record
  → Weekly journals submitted every week (auto-save 600ms debounce)
  → REAL-TIME: Journals list polling (30s) shows submissions to instructor
  → AUTO-SUBMIT: Draft journals auto-submitted after grace period via cron
  → REMINDERS: Journal due/overdue/attendance reminders sent hourly (email + in-app)

PHASE 4: HTE EVALUATION (by Supervisor)
──────────────────────────────────────────
  → Instructor generates one-time secure link → emailed to supervisor
  → Supervisor opens link → public HTE form (no login required)
  → 18 criteria across 3 categories (1-5 scale each):
     - Work Habits (5 criteria), Work Skills (6), Social Skills (7)
  → Alpine.js computes: category averages → overall average × 20 = percentage
  → Submit button disabled until ALL 18 criteria are filled
  → On submit:
     - Evaluation record created (score, criteria_scores JSON, comments)
     - Link marked as used
     - Student + Instructor notified
  → Student can download evaluation as DOCX from dashboard

PHASE 5: AUTO-COMPLETION (by System)
──────────────────────────────────────────
  → Total attendance minutes ≥ 36,000 (600 hours)?
     → SYSTEM auto-completes deployment:
        - status: active → completed
        - end_date: today
  → AUTOMATION: Notification sent to student + instructor immediately
  → AUTOMATION: Daily cron also checks past-end_date deployments
  → Triggered after every clock-out (store + quickClock)
  → REAL-TIME: Dashboard KPI updates via polling
  → 2 Post-requirement documents appear:
     13. Final DTR
     14. Final Weekly Journal

PHASE 6: CERTIFICATE (by Instructor)
──────────────────────────────────────────
  → Instructor uploads completion certificate
  → Certificate visible in student portal
  → Student downloads certificate
  → REAL-TIME: Student profile certificates tab re-fetches on click

AUTOMATED MAINTENANCE (Daily Cron)
──────────────────────────────────────────
  deployments:auto-complete    → Auto-complete past-end_date deployments
  attendance:auto-resolve      → Auto-resolve near_boundary > 3 days old
  flags:check                  → Check risk flags, auto-submit drafts
  reminders:send               → Send email + in-app reminders (hourly)

REAL-TIME INFRASTRUCTURE (System-Wide)
──────────────────────────────────────────
  Notification bell 30s poll   → Central heartbeat → dispatches 6 custom events
  Attendance list 12s poll     → Lightweight timestamp diff + HTMX swap
  Instructor dashboard 60s     → KPI cards + at-risk + section compliance
  Message inbox 30s            → New messages appear without refresh
  Document queue 30s           → Uploads auto-appear to instructors
  Compliance page 30s          → Status changes auto-refresh
  Weekly journals 30s          → Submissions auto-appear
  Student dashboard            → HTMX swap replaces full page reload
  Student profile tabs         → Re-fetch on every tab selection
  Student limited-mode 30s     → Auto-reload when full access granted

NOTIFICATION TRIGGERS (All Events)
──────────────────────────────────────────
  ✅ Document submitted/approved/returned    ✅ Certificate uploaded/verified
  ✅ Journal submitted/reviewed              ✅ Deployment activated/completed
  ✅ Company assigned                        ✅ HTE evaluation submitted
  ✅ New message received                    ✅ Risk flag created
  ✅ Journal due tomorrow (reminder)         ✅ Journal overdue (reminder)
  ✅ No check-in today (reminder)            ✅ Documents pending 7+ days (reminder)
  ✅ Deployment starting tomorrow (reminder) ✅ Deployment ending soon (reminder)
  ✅ Pre-docs done, no company (reminder)    ✅ Deployment auto-completed
```

## Session Log (Updated)

| Date | Session | Focus |
|------|---------|-------|
| June 26 | Phase 21a | Real-time polling: bell heartbeat dispatch, inbox, dashboard KPI, queue, compliance, journals, student swap, profile tabs |
| June 26 | Phase 21b | Automation: deployment auto-complete cron, 600hr notification, risk flag notifications, batch document approval, journals+passcode on activation, in-app reminders, auto-resolve attendance |
| June 26 | Phase 21c | Quality round: eval criteria, geofence, OJT dropdown, section warning, field locking, deployment button, post docs, queue default, company filter, demo times |
| June 26 | Phase 22 | Lifecycle alignment: export buttons restored, post-doc naming fixed, document-gated phase visibility, completion requires 600hrs + all docs submitted |
| June 26 | Phase 23 | Company edit fixes: cascading address dropdown initialization, assignable student list Alpine/HTMX compatibility, pagination 5/page, filter persistence |
| June 26 | Phase 24 | Real-time document status + import skip count fix: refresh-documents event, 30s polling on student docs, import onFailure row counting |
| **July 2, 2026** | **🎉 DEFENSE COMPLETED** | **System successfully defended. Only system revisions remain — 0 document revisions needed.** |
| **July 3, 2026** | **Phase 25 — Final Defense Revisions** | 10 panel revision items added. See `FINAL-DEFENSE-OUTCOME.md` for full list. Performance optimization: OPcache, .env tuning, N+1 fix in DocumentWorkflowEngine, documents phase bug fix, middleware gating. |
| **July 13, 2026** | **Phase 25 Complete + Phase 26-27** | All 10 revisions implemented. Silent background polling (10s, no green bar). Production optimization (model caching, Vite cleanup). UX polish (input masks, read more). Test suite hardened (103/103 passing). |
| **July 14, 2026** | **Phase 28: Master Optimization** | Command palette (Cmd+K), FAB, smart empty states, performance indexes, slow query logging, action feedback audit, navigation overhaul. 103/103 tests passing. |

---

# ✅ Phase 22: Lifecycle Alignment (June 26)

Completed June 26, 2026. Aligned recent changes with established lifecycle rules after full tracker audit.

## Changes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 22.1 | **DTR + WJ export restored** | Added "Download DTTR" button to student DTR monthly view and "Download DOCX" button to student weekly journal show view. Services and routes were already functional. | `student/dtr/index.blade.php`, `student/weekly-journals/show.blade.php` |
| 22.2 | **Post-requirement docs renamed** | Renamed "Final DTR" → "Signed DTTR", "Final Weekly Journal" → "Signed Weekly Journal". Removed duplicate records from earlier migration. Cleaned up stale records. Updated seeder to use new names. | `2026_06_26_124735_fix_post_requirement_documents.php`, `2026_06_26_124954_cleanup_duplicate_post_requirement_docs.php`, `SampleDataSeeder.php` |
| 22.3 | **Phase visibility: document-gated** | Changed from deployment-status gating to document-submission gating: Pre always visible → Monitoring visible when all pre-docs approved → Post visible when all pre + monitoring submitted. | `StudentPortalController.php` |
| 22.4 | **Completion: 600hrs + all docs** | `areAllMandatoryDocsSubmitted()` added to all 3 completion paths (store, quickClock, auto-complete cron). Deployment saving event checks docs before marking completed. | `AttendanceController.php`, `Deployment.php`, `AutoCompleteDeployments.php`, `Student.php` |
| 22.5 | **Progress pct updated** | Changed `getProgressPctComputedAttribute()` from deployment-status-based to document-gated phase counting. | `Student.php` |

## New Models/Methods

| Method | Location | Purpose |
|--------|----------|---------|
| `areAllPhaseDocsSubmitted($phase)` | `Student.php` | Generic check for any phase |
| `areAllMonitoringDocsSubmitted()` | `Student.php` | Monitoring phase check |
| `areAllPostDocsSubmitted()` | `Student.php` | Post phase check |
| `areAllMandatoryDocsSubmitted()` | `Student.php` | All phases check |

## New Migrations

| # | Migration | Purpose |
|---|-----------|---------|
| 1 | `2026_06_26_124735_fix_post_requirement_documents` | Rename post-docs, remove duplicates |
| 2 | `2026_06_26_124954_cleanup_duplicate_post_requirement_docs` | Final cleanup of stale post-doc records |

## Files Changed

```
resources/views/student/dtr/index.blade.php
resources/views/student/weekly-journals/show.blade.php
app/Http/Controllers/StudentPortalController.php
app/Http/Controllers/AttendanceController.php
app/Models/Deployment.php
app/Models/Student.php
app/Console/Commands/AutoCompleteDeployments.php
database/migrations/2026_06_26_124735_fix_post_requirement_documents.php (new)
database/migrations/2026_06_26_124954_cleanup_duplicate_post_requirement_docs.php (new)
database/seeders/SampleDataSeeder.php
```

---

# ✅ Phase 23: Company Edit Interaction Overhaul (June 26)

Completed June 26, 2026. Fixed cascading address dropdown state, assignable student list Alpine/HTMX compatibility, pagination routing, and filter persistence.

## Changes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 23.1 | **Address cascader init fix** | Alpine `x-model` was overwriting saved province/city/barangay IDs during async init because no matching `<option>` existed yet. Fix: start with empty values, capture saved IDs in locals, load async data first, then assign IDs after options populate. String coercion for type consistency. | `companies/_form.blade.php` |
| 23.2 | **Pagination 5 per page** | Changed from 10 to 5 per page in both `edit()` and `assignableStudents()` methods. | `CompanyController.php` |
| 23.3 | **Pagination routing fix** | `edit()` method's pagination URLs pointed to `/companies/{id}/edit` which returned the wrong partial on HTMX click. Fix: `$students->withPath(route('companies.students.assignable', $company))` so pagination links hit the correct route. | `CompanyController.php` |
| 23.4 | **Filter persistence on pagination** | Added `->withQueryString()` so `search` and `my_students` params survive page changes. | `CompanyController.php` |
| 23.5 | **Search/My Students swapped via HTMX** | Replaced raw `fetch()` + `innerHTML` with `htmx.ajax()` so HTMX processes swapped content — pagination links, Alpine `initTree`, and HTMX events all fire correctly. | `companies/edit.blade.php` |
| 23.6 | **Selection state survives pagination** | Moved `x-data="{ selected: [], allIds: [] }"` from inside `#assignable-list-mount` (destroyed on every swap) to the parent wrapper. `refreshAllIds()` re-syncs `allIds` from DOM checkboxes after every HTMX swap. | `companies/partials/assignable-list.blade.php`, `companies/edit.blade.php` |
| 23.7 | **Loading spinner removed** | Removed Alpine `loading` state and spinner since HTMX natively handles request indicators. | `companies/edit.blade.php` |

## Files Changed

```
app/Http/Controllers/CompanyController.php
resources/views/companies/_form.blade.php
resources/views/companies/edit.blade.php
resources/views/companies/partials/assignable-list.blade.php
```

---

# ✅ Phase 24: Real-Time Document Status & Import Fix (June 26)

Completed June 26, 2026. Added auto-refresh for student document status changes and fixed company import skip count.

## Changes

| # | Item | Detail | Files Changed |
|---|------|--------|--------------|
| 24.1 | **Import skip count fixed** | `onFailure()` was counting validation failures instead of rows — 10 rows × 4 field errors showed "40 skipped". Fixed to `$this->skippedCount++` (1 per failed row). | `CompaniesImport.php` |
| 24.2 | **refresh-documents event** | Added `dispatchEvent(new CustomEvent('refresh-documents'))` to notification bell 30s polling so student docs page receives real-time status updates. | `notifications/partials/bell.blade.php` |
| 24.3 | **Student docs auto-refresh** | Added `htmx.ajax()` auto-refresh on 30s interval + `refresh-documents` event listener. Uses `hx-select` to extract only the document cards from the full page response. | `student/documents.blade.php` |

## Files Changed

```
app/Imports/CompaniesImport.php
resources/views/notifications/partials/bell.blade.php
resources/views/student/documents.blade.php
```

---

# ✅ Phase 25: Final Defense Revisions (July 13, 2026)

**Status:** 🎉 All 10 system revision items completed.

Source: `FINAL-DEFENSE-OUTCOME.md` — the single source of truth for all post-defense revision work.

| # | Item | Priority | Approach | Status |
|---|------|----------|----------|--------|
| 25.1 | **Table format for documents with tags/dropdown** | P1 | Convert document list to compact table layout with phase tabs (All/Missing/Completed) and phase filter dropdown. Added `compact` mode to document-card. | ✅ |
| 25.2 | **Missing documents dedicated tab** | P1 | New tab navigation (All Documents / Missing / Completed) on student documents page with filtered views. | ✅ |
| 25.3 | **Instructor uploads sample document templates** | P1 | New `document_templates` table, model, migration. Upload/download/destroy routes on Required Documents page. Download link on student document card. | ✅ |
| 25.4 | **Separate DTR and document progress** | P1 | Added `doc_progress_pct` and `dtr_progress_pct` accessors to Student model. Updated profile tab and student list to show 3 separate progress bars (Docs/DTR/Journal). | ✅ |
| 25.5 | **Tag document statuses (Pre/Monitoring/Post)** | P1 | Phase badges on every document card: Pre=blue-600, Monitoring=amber-600, Post=emerald-600. Applied to both compact and full card layouts. | ✅ |
| 25.6 | **Replace Delete with Archive** | P1 | Added `Archivable` trait with `archived_at` + `archive_reason` columns to Student, Company, Deployment. Created ArchiveController with index/archive/restore. Archive sidebar link. | ✅ |
| 25.7 | **Include School Year in archived records** | P2 | Added `school_year` column to deployments. Auto-derived from `start_date` on save (June–May academic year). | ✅ |
| 25.8 | **Academic Year dropdown in Archive** | P2 | School Year filter dropdown in archive view. Filters all 3 record types (students/companies/deployments) by school year. | ✅ |
| 25.9 | **Make NBI Clearance optional** | P1 | Migration + seeder update: `is_mandatory` = false for NBI Clearance. Existing code already handles optional docs correctly (excluded from progress, doesn't block deployment). | ✅ |
| 25.10 | **Separate OJT Instructor vs Supervisor scores** | P1 | Updated student dashboard grade card to show two separate cards (Company Supervisor + OJT Instructor) with score/100, pending state, and final grade computation. | ✅ |
| 28.1 | **Global Command Palette (Cmd+K)** | P1 | Alpine + HTMX search modal searching students, companies, and 15+ page routes. Keyboard navigation. | ✅ |
| 28.2 | **Floating Quick-Action Button (FAB)** | P1 | Floating "+" button with role-specific actions per user type. | ✅ |
| 28.3 | **Smart Empty States** | P1 | Students, Companies, Deployments lists show helpful messages + action buttons instead of generic "No records found." | ✅ |
| 28.4 | **Performance Indexes** | P1 | 5 composite indexes on heavy query tables. | ✅ |
| 28.5 | **Slow Query Logging** | P1 | Middleware logging queries >100ms. | ✅ |
| 28.6 | **Action Feedback Audit** | P1 | 14 gaps fixed (bell notifications, toast messages, JSON responses). | ✅ |
| 28.7 | **Test Suite Hardened** | P1 | 103/103 passing, 0 failures, 337 assertions. | ✅ |
