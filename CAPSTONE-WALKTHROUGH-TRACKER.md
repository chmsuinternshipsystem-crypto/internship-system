# Walkthrough Tracker — Role-by-Role System Audit

**Syncs with:** `CAPSTONE-FINAL-TRACKER.md`
**Purpose:** Role-based walkthrough analysis, quality assurance findings, and Phase 5 plan.

---

## How to Read This

Each role section documents:
1. **Walkthrough** — what the user sees and does step-by-step
2. **Findings** — friction points, inconsistencies, missing features
3. **Validation audit** — what's validated and what's missing
4. **What we added/improved** — changes already made

The **Phase 5 plan** at the bottom lists prioritized fixes.

---

# Walkthrough: Student

## Flow
1. First login → OTP verification → password change (enforced)
2. Dashboard: welcome card → quick stats (documents, deployment, attendance, grade) → alerts → announcements → quick actions
3. Documents page: upload mandatory files (pre-requirements → monitoring → post-requirements)
4. Await instructor approval on documents
5. Once deployed: daily attendance check-in (quick clock from dashboard)
6. Weekly journals: auto-generated weeks, inline editor, submit when done
7. DTR: auto-populated from attendance, monthly DTTR upload
8. Messages: communicate with instructors
9. Certificates: view when instructor uploads
10. Evaluation: view grades when HTE + instructor submit scores

## What Works Well
- Quick Clock-In button on dashboard saves 4 clicks per day
- Journal auto-save with debounce + auto-submit when all days filled
- Bottom nav on mobile (Home/Docs/Clock/DTR/Journal)
- Document cards with per-requirement status badges
- Empty states are helpful ("Clock in to generate DTTR entries")

## What Needs Work
| Issue | Priority | Detail |
|---|---|---|
| No `x-alert-message` on student layout | P1 | Staff pages auto-show validation errors; student pages don't. Inconsistent error feedback. |
| Document upload feedback is per-card, not centralized | P1 | Each document card shows its own `$uploadError`. A top-of-page summary would help. |
| No "you're done" moment | P2 | Student completes all requirements but gets no celebration/checkmark. No clear "graduated" state. |
| Limited mode amber warning is easy to miss | P2 | Students in pre-deployment mode see an amber bar; could be more prominent. |

## Validation Audit (Student-Facing)
| Form/Field | Validated? | Notes |
|---|---|---|
| Document upload | ✅ | 2MB max, mimetypes checked, custom closure extension check |
| Journal activities | ✅ | Max 2000 chars per day |
| Journal file upload | ✅ | 10MB, specific mime types |
| DTTR upload | ✅ | 5MB, PDF only |
| Password change | ✅ | Min 8, confirmed |
| Attendance check-in (full page) | ✅ | Student number digits:8, passcode digits:6, lat/lng numeric |
| Attendance quick-clock | ✅ | Session-based identity, no manual validation needed |
| Messages | ✅ | Subject max 255, body max 3000 |

---

# Walkthrough: Instructor

## Flow
1. Dashboard: KPI cards, section compliance, at-risk alert, supervised students card
2. Students: create/edit/import, view profiles (tabbed: profile, documents, journals, DTR, attendance, certificates)
3. Deployments: create/edit, assign companies, bulk deploy
4. Document review: queue-based workflow (review → approve/return_for_revision)
5. Weekly journals: list with filters (Draft/Submitted/Reviewed), review + mark as reviewed
6. DTR: calendar view, drill-down per student per day
7. Attendance: filter by status/date, resolve flagged entries, batch resolve
8. Evaluations: create, send HTE link, edit scores
9. Certificates: upload for students, verify/bulk verify
10. Compliance: overview with risk filter
11. Messages: communicate with students and staff
12. Reports: deployed-per-company, missing-docs, compliance summary, attendance export
13. Settings: campus geofencing configuration

## What Works Well
- Batch operations (journals review, attendance resolve, certificates verify, student deploy/assign)
- Tabbed student profile (7 tabs, URL hash persistence)
- HTMX search/filter on all list views
- Document workflow engine with signing chains
- Calendar view for DTR (replaced paginated table)
- Select2 on student/company selectors

## What Needs Work
| Issue | Priority | Detail |
|---|---|---|
| Student creation form is long (scrolls) | P1 | 14 fields + external OJT section makes a tall page. Grouped sections could collapse. |
| Edit form lacks duplicate name check | P1 | Changing a student's name to match another gets no warning. Create form has it; edit form doesn't. |
| Student name regex blocks ñ/Unicode | P0 | "Nuñez", "José" fail validation. Staff profile and company contacts allow it. |
| No file cleanup on document replace | P0 | Re-uploading a document leaves the old file on disk forever. |
| Import silently defaults bad phone to 09123456789 | P1 | No warning, wrong data silently stored. |
| Import: no email unique validation | P1 | Duplicate emails in import can crash the entire batch with MySQL constraint violation. |

## Validation Audit (Instructor-Facing)
| Form/Field | Validated? | Notes |
|---|---|---|
| Student create (all fields) | ✅ | 14 fields covered, 6 custom messages |
| Student update (all fields) | ✅ | Same as create, minus company/deployment fields |
| Company create (all fields) | ✅ | 16 fields, case-insensitive name unique, phone custom rule |
| Company update | ✅ | Same as create with ignore on name |
| Deployment create | ✅ | 5 fields + withValidator for overlap + section conflict |
| Deployment update | ✅ | Same as create + self-exclusion from overlap check |
| Required document create | ✅ | Name unique, order_slot regex, phase enum |
| Required document update | ✅ | Same as create with ignore on name |
| Announcement create | ✅ | Title unique, body max 3000 |
| Announcement update | ✅ | Same with ignore on title |
| Evaluation create | ✅ | Score 1-100, type enum, student exists |
| Evaluation update | ✅ | Same |
| Certificate create | ✅ | Type enum, file 2MB, mimes checked |
| Certificate verify | ✅ | Action enum (approve/reject), notes max 1000 |
| Batch operations | ✅ | IDs array min:1, action enum, each ID validated against table |
| Import (students) | ✅ | File mimes:xlsx,xls,csv, 10MB max |
| Import (companies) | ✅ | Same |
| Journal review | ✅ | Remarks max 1000 |
| Attendance resolve | ✅ | Note max 255 |
| Campus settings | ✅ | 9 fields with specific ranges |
| Document forwarding | ✅ | IDs array, release_at date, notes max 1000 |

## Missing Validation
| What | Risk |
|---|---|
| Student name no Unicode (ñ, accents) | Blocks Filipino names |
| Import email no unique check | Can crash entire import |
| Import phone silent default | Wrong data stored |
| Edit form no duplicate name check | Can change name to match another student |
| Certificate title no unique check | Low risk |
| Weekly journal (student+week combo) no unique check | Could create duplicate weeks |

---

# Walkthrough: Chairperson

## Flow
1. Dashboard: KPI cards (deployment rate, completion rate, compliance, at-risk), section compliance widget, at-risk alert
2. Compliance: filter by risk status, section, company
3. Reports: all standard reports + program health
4. Announcements: create/edit/delete
5. Messages: communicate

## What Works Well
- Section compliance on dashboard — quick comparison across sections
- At-risk alert on dashboard with drill-down to compliance
- Program Health report with section comparison table

## What Needs Work
| Issue | Priority | Detail |
|---|---|---|
| No sidebar link to Deployments | P1 | Route is accessible but no nav link. Chairperson needs to see deployment info. |
| Program Health has no PDF export | P1 | Other reports support `?export=pdf`; this one doesn't. |
| Section comparison table lacks document compliance % | P2 | Shows deployment % and at-risk but not document compliance by section. |

## Validation Audit (Chairperson-Specific)
Chairperson has no data-entry forms. All validation is read-side (reports, compliance). No issues.

---

# Walkthrough: Dean

## Flow
1. Dashboard: KPI cards (same basic set as chairperson but NO section compliance widget), at-risk alert
2. Reports: standard reports + executive summary
3. Compliance: overview (view only, same as chairperson)
4. Messages: communicate

## What Needs Work
| Issue | Priority | Detail |
|---|---|---|
| No sidebar link to Students | P1 | Route is accessible (dean is in institutionalMonitoringRoles) but no nav link. Dean literally cannot navigate to student profiles from the UI. |
| No sidebar link to Attendance | P1 | Route is open but no nav link. |
| Dashboard lacks section compliance widget | P2 | Chairperson gets it; dean doesn't. Inconsistency. |
| Executive summary has no PDF export | P1 | Like Program Health for chairperson. Dean can't generate an archivable report. |
| Executive summary is static snapshot | P2 | No trend data or "vs previous period" deltas. Can't tell if things are improving. |
| Dashboard subtitle says "Partner company..." — wrong for dean | P2 | The subtitle is designed for employer role, not dean oversight. |

## Validation Audit (Dean-Specific)
Like chairperson, dean has no data-entry forms. No validation issues.

---

# Cross-Cutting Findings

## UI Consistency Issues

| Issue | Detail | Impact |
|---|---|---|
| Two primary greens | CSS `#1a6b3c` vs Tailwind `emerald-600 #059669` | Buttons look different on auth pages vs CRUD pages |
| Three button systems | CSS classes, component, inline Tailwind | Inconsistent sizing (py-2 vs py-2.5) |
| Three input heights | 38px (search), 40px (text-input), 52px (float) | Form fields look mismatched when adjacent |
| No shared loading component | HTMX indicators, Alpine flags, full-page overlay | Each feature reinvents loading UX |
| Student layout missing `x-alert-message` | Staff has it; student doesn't | Inconsistent error feedback |
| Student dashboard gradient unique | No other page uses gradient cards | Feels disconnected from rest of system |
| Campus map geofencing (Leaflet) broken | Alpine component fails to init after `x-data` refactor to `Alpine.data()` | Settings page map is blank — cannot draw campus boundary polygon |

## Modal vs Page Recommendations

| Workflow | Current | Recommendation | Rationale |
|---|---|---|---|
| Quick company assignment | Full page redirect (`/deployments/create`) | **Modal from student profile** | Instructor is viewing student, doesn't want context switch |
| Document review | Modal (upload panel) | ✅ Keep modal | Already correct |
| Student creation | Full page form | ✅ Keep page | Too many fields for modal |
| Company creation | Full page form | ✅ Keep page | Address cascade needs space |
| Deployment creation | Full page form | ✅ Keep page | Searchable selects + date pickers need space |
| Reports | Full page | ✅ Keep page | Data-heavy, needs room |
| Quick Clock-In | Inline Alpine on dashboard | ✅ Already correct | Best pattern |

## Data Integrity Issues

| Issue | Severity | Detail |
|---|---|---|
| File leak on document replace | HIGH | Old file not deleted when re-uploading |
| File leak on record delete | HIGH | No deleting events on StudentDocument, Certificate, MonthlyDttr |
| OTP email silent failure | HIGH | Student redirected to OTP page even if email failed |
| Deployment deleteBlockers incomplete | MEDIUM | DailyTimeRecord and MonthlyDttr not checked |
| Instructor deletion no guardrails | MEDIUM | Students silently lose assigned instructor |
| Evaluation/Announcement no canDelete | MEDIUM | No FK protection on delete |
| WeeklyJournal soft-delete no file cleanup | LOW | JSON file references persist after soft-delete |

---

# What We Added/Improved/Updated (All Phases)

## Phase 0 (Foundation — 26 Panel Recommendations)
- Student number on profile, removed Yes/No, import Excel, middle name, auto-password, percentage progress
- Journal milestones, contact required, email notifications, duplicate prevention, 2MB limit
- Separate DTR/Journal pages, HTE eval validation, review-before-approve, review button
- Chairperson dashboard, instructor certificate upload, attendance for deployed students
- Policy settings, duplicate instructor flag, deployment location, centralized reports + filter + export
- Missing/pending doc filter, configurable pre/post requirements, attendance export
- 11 migrations

## Phase 1 (Journal Automation)
- Auto-generated weekly journals per deployment week range
- Inline Alpine.js editor with 600ms debounce auto-save
- File upload per day with Alpine fetch
- Staff review with late detection (red banner/badge)
- Per-student progress page
- `submitted_at` tracking + `is_late` computed attribute

## Phase 2 (11 Usability Fixes)
- Student import: duplicate detection with data-level tracking
- Company import: fuzzy industry matching + unmatched name reporting
- PSGC address cascade (provinces, cities, barangays) — 3 models, 4 migrations
- Geofence badge + filter on companies
- Section conflict badge removed
- Student duplicate name modal (AJAX check before submit)
- Student creation form: Internal/External OJT toggle
- Attendance CSV export
- DTR calendar view (replaced paginated table)
- Certificate page route fix
- Student first-login enforcement on ALL routes

## Phase 3 (Big Features)
- **Notification bell**: In-app notification center with 30s polling + email
- **Teaching load**: assigned_instructor_id on students, "My Students" filter, dashboard card
- **Unified student detail**: 6-tabbed show page with URL hash persistence
- **Batch operations**: journals review, attendance resolve, certificate verify, student deploy/assign
- **At-risk auto-flagging**: `flags:check` command — 4 criteria with dedup
- **Email reminders**: `reminders:send` — 5 types of automated reminders
- **Mass communication**: batch student select with group filters in messages
- **Mobile responsive**: bottom nav for student portal, responsive quick actions
- **Role-specific reports**: Program Health (chairperson), Executive Summary (dean)

## Phase 4 (Product Polish & Automation)
- **Quick Clock-In**: Dashboard button skips passcode/student number, uses fetch + geolocation
- **Journal auto-submit**: `flags:check` auto-submits completed draft journals
- **Deployment end_date**: Auto-computed 15 weeks (105 days) on student creation
- **Auto-upload**: Document uploads trigger immediately on file selection
- **Risk flags auto-resolve**: Stale flags resolve when criteria no longer met
- **Deployment status preview**: Live Alpine badge in create/edit form
- **Soft-deletes**: `SoftDeletes` on WeeklyJournal + Attendance
- **Undo for journal submit**: 30-second cache-based undo with toast button
- **Journal textarea auto-grow**: rows=4 with Alpine auto-height
- **Mobile journal cards**: Stacked card-per-day layout on mobile
- **Mobile DTTR**: Responsive 2-column grid on mobile
- **Notification bell fix**: Broken route in bell partial
- **Migration**: `add_deleted_at_to_weekly_journals_and_attendance`

## Phase 12 (Messages & Notifications — June 25)
- **WhatsApp-style message panel**: Left inbox always visible, right panel transitions via HTMX (empty/compose/conversation). Removed Sent tab.
- **Message thread dedup**: Auto-detects existing thread with same participants, appends message instead of creating duplicate.
- **Message draft recovery**: sessionStorage saves on input, restores on page load with amber "Draft restored" banner.
- **In-app message notifications**: Messages now trigger bell notifications (not just email).
- **Notification markAllAsRead fix**: Returns redirect for standard form POST instead of raw JSON.
- **Leaflet z-index fix**: `isolation: isolate` on map container prevents tile overlap with UI dropdowns.
- **Pagination 5/page**: Message threads reduced from 10 to 5.

## Phase 13 (Auto-Deployment & 4-Session Attendance — June 25)
- **Auto-deployment**: Creates deployment with `company_id = null` + `status = active` when all pre-docs approved but no deployment exists.
- **company_id nullable**: Migration making deployments table company_id nullable with nullOnDelete.
- **Assign Company UI**: Dropdown on student profile tab when deployment has no company.
- **4-session attendance model**: `am_check_in`, `am_check_out`, `pm_check_in`, `pm_check_out` columns. State machine: AM In → AM Out → PM In → PM Out.
- **AM/PM time windows**: Clock-in validates against AM window, clock-out validates against PM window.
- **Clockpicker library**: Circular analog clock picker for settings time inputs.
- **Check-in gradient**: AM = emerald-50, PM = indigo-50 gradient container with session badge.
- **Dashboard quick clock colors**: AM = emerald, PM = indigo border/hover.
- **Settings cache fix**: `campusCached()` strips binary geometry before serialization.
- **Evaluation criteria configurable**: DB-backed criteria with settings management UI.

## Phase 14 (Instructor Tabs & Attendance Batch — June 25)
- **Instructor tab $student fix**: Added to all 4 tab controllers — fixed journals.
- **Tab HTMX guard removed**: Removed `document.getElementById()` guard and 10s fallbacks that prevented HTMX from firing.
- **DTR/Attendance tabs use fetch()**: Replaced broken `htmx.ajax()` with native `fetch()`.
- **DTR auto-population**: tabDtr auto-creates/updates DailyTimeRecord entries from Attendance records.
- **Attendance batch resolve consolidated**: Removed per-row "Resolve" form — batch-only via checkboxes. Added HX-Trigger refresh. Fixed checkbox triple-binding (x-model vs :checked vs @change).
- **Evaluation ID hidden**: Internal primary key removed from frontend.
- **Pagination 5/page**: Attendance index changed from 10 to 5.

---

# Phase 5 Plan: Clean Data & Consistency

## P0 — Data Integrity (Real Bugs)

| # | Item | Detail | Status |
|---|---|---|---|
| 5.1 | **File cleanup on document replace** | Delete old file from disk when student re-uploads a document. Fixed in StudentDocumentController + StudentPortalController. | ✅ |
| 5.2 | **Name regex fix for Filipino names** | Student name regex now uses `\p{L}` Unicode property with `/u` flag. "Nuñez", "José" now pass validation. | ✅ |
| 5.3 | **OTP email failure handling** | Silent failure: student redirected to OTP page even if email send failed. Now throws ValidationException + error message. | ✅ |
| 5.4 | **File cleanup on record delete** | Added `deleting` events to StudentDocument, Certificate, MonthlyDttr. Files cleaned up from disk when records are deleted. | ✅ |

## P1 — Validation Hardening

| # | Item | Detail | Status |
|---|---|---|---|
| 5.5 | **Duplicate name: include middle_name + extension** | Backend check, frontend AJAX (create + edit), and display name all include middle_name + name_extension. | ✅ |
| 5.6 | **Import phone validation — no silent default** | `sanitizeContactNumber()` returns null instead of silently defaulting to '09123456789'. | ✅ |
| 5.7 | **Add messages() to 6 form requests** | Added custom messages to Store/Update Evaluation, Required Document, and Announcement requests. | ✅ |
| 5.8 | **Edit form duplicate name guard** | Added `duplicateNameGuard(excludeId)` Alpine component with `&exclude` param to skip current student. | ✅ |
| 5.9 | **Import email unique validation** | Added `unique:student_accounts,email` to `StudentsImport::rules()`. | ✅ |

## P2 — Navigation & Consistency

| # | Item | Detail | Status |
|---|---|---|---|
| 5.10 | **Dean sidebar: add Students + Attendance** | Removed `students` and `attendance` from dean's hidden list in `InternshipRoles.php`. Dean now sees both sidebar links. | ✅ |
| 5.11 | **Chairperson sidebar: add Deployments** | Removed `deployments` from chairperson's hidden list. | ✅ |
| 5.12 | **Unify button colors (pick one green)** | Changed `--primary` to `#059669`, fixed hardcoded `#1a6b3c`. Single green across system. | ✅ |
| 5.13 | **Add x-alert-message to student layout** | Already exists in shared app layout (line 63). Applies to all authenticated users. | ✅ |
| 5.14 | **Deployment deleteBlockers: add DTR + MonthlyDTT** | Added `DailyTimeRecord` and `MonthlyDttr` count checks to `Deployment::deleteBlockers()`. | ✅ |
| 5.15 | **Instructor deletion: add guardrails** | Added `HasDeleteProtection` trait + `deleteBlockers()` to User model. `ProfileController::destroy()` now checks `canDelete()`. | ✅ |

## Files Changed (Phase 5)

```
app/Http/Controllers/StudentDocumentController.php (+ old file cleanup on document replace)
app/Http/Controllers/StudentPortalController.php (+ old file cleanup on document replace)
app/Http/Controllers/StudentController.php (+ duplicate check includes middle_name + extension, checkDuplicateName includes all name fields)
app/Http/Controllers/Auth/AuthenticatedSessionController.php (+ OTP failure error message on resend)
app/Http/Controllers/ProfileController.php (+ canDelete() check before user deletion)
app/Http/Requests/Auth/LoginRequest.php (+ OTP failure throws ValidationException)
app/Http/Requests/StoreStudentRequest.php (+ name regex uses \p{L} Unicode for ñ/accents)
app/Http/Requests/UpdateStudentRequest.php (+ name regex uses \p{L} Unicode for ñ/accents)
app/Http/Requests/StoreEvaluationRequest.php (+ messages())
app/Http/Requests/UpdateEvaluationRequest.php (+ messages())
app/Http/Requests/StoreRequiredDocumentRequest.php (+ messages())
app/Http/Requests/UpdateRequiredDocumentRequest.php (+ messages())
app/Http/Requests/StoreAnnouncementRequest.php (+ messages())
app/Http/Requests/UpdateAnnouncementRequest.php (+ messages())
app/Models/StudentDocument.php (+ deleting event for file cleanup)
app/Models/Certificate.php (+ deleting event for file cleanup)
app/Models/MonthlyDttr.php (+ deleting event for file cleanup)
app/Models/Deployment.php (+ deleteBlockers expanded with DTR + MonthlyDTT checks)
app/Models/User.php (+ HasDeleteProtection trait + deleteBlockers for supervised students)
app/Imports/StudentsImport.php (+ email unique validation, sanitizeContactNumber returns null on invalid)
app/Support/InternshipRoles.php (+ dean sidebar shows students + attendance; chairperson shows deployments)
resources/views/students/create.blade.php (+ duplicate name check includes middle_name + extension)
resources/views/students/edit.blade.php (+ duplicateNameGuard Alpine component + modal)
```

## Phase 5b — UI Polish & Walkthrough Fixes

| # | Item | Detail | Status |
|---|---|---|---|
| 5b.1 | Weekly Journal My Students HTMX | Added HTMX branch + my_students variable to controller | ✅ |
| 5b.2 | At-risk checkbox auto-submit | Added to HTMX triggers | ✅ |
| 5b.3 | Risk column cleanup | Colored dots + tooltips instead of text badges | ✅ |
| 5b.4 | Document Queue section filter | Section dropdown + My Students + controller filtering | ✅ |
| 5b.5 | New Message button in empty state | Moved to "Select a conversation" panel | ✅ |
| 5b.6 | Company form redesign | Unified cards, toggle switch, clean inputs | ✅ |
| 5b.7 | Address routes fix | Moved outside student.auth middleware | ✅ |
| 5b.8 | Loading overlay click fix | Added pointer-events: none CSS | ✅ |
| 5b.9 | HTMX caching fix | PreventHtmxCaching middleware added | ✅ |
| 5b.10 | Notifications layout fix | @extends → x-app-layout | ✅ |
| 5b.11 | Back buttons to content area | Moved from header to page content | ✅ |
| 5b.12 | Batch all-matching persistence | sessionStorage + filter keys + clear on submit | ✅ |
| 5b.13 | My Students HTMX triggers | Added to search-bar component globally | ✅ |
| 5b.14 | Attendance batch resolve | Checkboxes + batch action bar | ✅ |
| 5b.15 | Reports dashboard | Single page with KPIs + tabs + HTMX + export | ✅ |
| 5b.16 | Mobile table overflow | overflow-x-auto on 3 tables | ✅ |
| 5b.17 | Mobile tap targets | py-1 → py-2.5 on nav links | ✅ |
| 5b.18 | DTR mobile buttons | w-8→w-10, py-1.5→py-2 | ✅ |
| 5b.19 | Journal image preview | Inline thumbnails via Alpine | ✅ |
| 5b.20 | Analytics removed (out of scope) | Controller, views, routes, Chart.js dependency deleted | ✅ |

## Phase S1 — Student Portal Overhaul

| # | Item | Detail | Status |
|---|---|---|---|
| S1.1 | Attendance skip passcode when logged in | Session-based auth skip | ✅ |
| S1.2 | Dashboard gradient card → page-card | Consistent with rest of system | ✅ |
| S1.3 | OTP resend text fix | Empty param fix | ✅ |
| S1.4 | Passcode two-step reveal | Shoulder-surfing protection | ✅ |
| S1.5 | Bottom nav Journal → Messages | Primary communication in nav | ✅ |
| S1.6 | Getting Started checklist | 4-step guide for new students | ✅ |
| S1.7 | Messages Sent tab | New tab filter | ✅ |
| S1.8 | Student profile settings | New page + route + sidebar | ✅ |
| S1.9 | Certificate preview | Inline image + PDF preview | ✅ |
| S1.10 | Session timeout 30m → 2h | EnsureStudentSession middleware | ✅ |
| S1.11 | Trust this browser skip OTP | 30-day encrypted cookie | ✅ |

## All Files Changed (Phase 5 + 5b + S1 + Phases 12-14)

```
app/Http/Controllers/StudentDocumentController.php
app/Http/Controllers/StudentPortalController.php
app/Http/Controllers/StudentController.php
app/Http/Controllers/Auth/AuthenticatedSessionController.php
app/Http/Controllers/AttendanceController.php
app/Http/Controllers/BatchController.php
app/Http/Controllers/WeeklyJournalController.php
app/Http/Controllers/ProfileController.php
app/Http/Controllers/ReportController.php
app/Http/Controllers/CompanyController.php
app/Http/Controllers/DtrController.php
app/Http/Controllers/MessageThreadController.php
app/Http/Controllers/NotificationController.php
app/Http/Controllers/DeploymentController.php
app/Http/Controllers/CampusSettingController.php
app/Http/Controllers/EvaluationController.php
app/Http/Middleware/PreventHtmxCaching.php (new)
app/Http/Requests/Auth/LoginRequest.php
app/Http/Requests/StoreStudentRequest.php
app/Http/Requests/UpdateStudentRequest.php
app/Http/Requests/StoreEvaluationRequest.php
app/Http/Requests/UpdateEvaluationRequest.php
app/Http/Requests/StoreRequiredDocumentRequest.php
app/Http/Requests/UpdateRequiredDocumentRequest.php
app/Http/Requests/StoreAnnouncementRequest.php
app/Http/Requests/UpdateAnnouncementRequest.php
app/Models/StudentDocument.php
app/Models/Certificate.php
app/Models/MonthlyDttr.php
app/Models/Deployment.php
app/Models/User.php
app/Models/Student.php
app/Models/Attendance.php
app/Models/Setting.php
app/Models/EvaluationCriterion.php (new)
app/Imports/StudentsImport.php
app/Support/InternshipRoles.php
app/Services/DocumentWorkflowEngine.php
app/Services/NotificationService.php
app/Services/EvaluationExportService.php
bootstrap/app.php
resources/views/layouts/app.blade.php
resources/views/layouts/navigation.blade.php
resources/views/components/search-bar.blade.php
resources/views/attendance/check-in.blade.php
resources/views/attendance/index.blade.php
resources/views/attendance/partials/ajax-list.blade.php
resources/views/student/dashboard.blade.php
resources/views/student/profile.blade.php (new)
resources/views/student/certificates/show.blade.php
resources/views/student/weekly-journals/index.blade.php
resources/views/student/weekly-journals/show.blade.php
resources/views/student/dtr/index.blade.php
resources/views/reports/index.blade.php
resources/views/reports/partials/deployed-table.blade.php (new)
resources/views/reports/partials/missing-table.blade.php (new)
resources/views/reports/partials/compliance-table.blade.php (new)
resources/views/companies/_form.blade.php
resources/views/companies/create.blade.php
resources/views/companies/edit.blade.php
resources/views/companies/partials/ajax-list.blade.php
resources/views/deployments/_form.blade.php
resources/views/deployments/partials/ajax-list.blade.php
resources/views/students/partials/ajax-list.blade.php
resources/views/students/create.blade.php
resources/views/students/edit.blade.php
resources/views/students/show.blade.php
resources/views/students/partials/tab-profile.blade.php
resources/views/students/partials/tab-dtr.blade.php
resources/views/students/partials/tab-attendance.blade.php
resources/views/students/partials/tab-journals.blade.php
resources/views/students/partials/tab-certificates.blade.php
resources/views/notifications/index.blade.php
resources/views/notifications/partials/bell.blade.php
resources/views/messages/index.blade.php
resources/views/messages/show.blade.php
resources/views/messages/create.blade.php
resources/views/messages/partials/inbox-column.blade.php
resources/views/messages/partials/create-form.blade.php (new)
resources/views/messages/partials/conversation.blade.php
resources/views/messages/partials/empty-state.blade.php (new)
resources/views/messages/partials/thread-item.blade.php
resources/views/settings/campus.blade.php
resources/views/evaluations/show.blade.php
resources/views/errors/403.blade.php
resources/views/compliance/partials/ajax-list.blade.php
resources/views/student-documents/edit.blade.php
resources/views/student-documents/partials/checklist-table.blade.php
resources/views/student-documents/queue.blade.php
resources/views/weekly-journals/index.blade.php
resources/views/weekly-journals/partials/ajax-list.blade.php
resources/views/dtr/index.blade.php
resources/views/dtr/partials/student-list.blade.php (new)
resources/views/hte-transactions/show.blade.php
public/css/custom.css
resources/js/app.js
resources/css/app.css
database/migrations/2026_06_21_000008_add_deleted_at_to_weekly_journals_and_attendance.php
database/migrations/2026_06_24_225511_create_evaluation_criteria_table.php (new)
database/migrations/2026_06_25_004817_make_company_id_nullable_in_deployments.php (new)
database/migrations/2026_06_25_013753_add_am_pm_sessions_to_attendances.php (new)
database/seeders/EvaluationCriterionSeeder.php (new)
```

## Phase 21 (Quality Round — June 26)
- **Eval criteria CRUD fixed**: `:disabled="!open"` on new_criteria inputs; GET route moved before `evaluations/{evaluation}` wildcard; destroy hardened with post-delete check
- **Geofence simplified**: Removed student_geofence_radius_meters & geofence_review_buffer_meters (kept lat/lng/radius/G Maps link)
- **External OJT company dropdown**: Alpine `x-show` company `<select>` when ojt_type=external; handled in Store/Update requests + controllers
- **Section warning removed**: Deleted "Section C already has active deployments" from UpdateDeploymentRequest
- **Field locking**: Name fields `readonly` when pending/active; OJT+company locked when active/completed; controller skips locked fields
- **Add Deployment button**: Restored create/store routes; card-based layout; filtered to unassigned students; auto-activates if pre-docs approved
- **Post-requirement docs**: Added Final DTR + Final Weekly Journal (phase=post, visible after completion); progress_pct respects phase visibility
- **Queue default**: "My Students" checkbox defaults to checked; hidden `value=0` ensures proper toggle
- **Company assignable filter**: `whereIn('ojt_type', ['unplaced', 'external'])` excludes internal students
- **Demo attendance**: Migration sets all windows 00:00–23:59, grace=0
```

---

## 🎉 Defense Completed — July 2, 2026

**System successfully defended. Only system revisions remain — 0 document revisions needed.**

All future work is post-defense quality-of-life improvements, UI polish, and feature enhancements — no scope changes.

## Post-Defense — Phase 25 Revisions (July 3, 2026)

10 system revision items from the panel. See `FINAL-DEFENSE-OUTCOME.md` for the full list and `CAPSTONE-FINAL-TRACKER.md` (Phase 25) for implementation tracking.

Key areas: document table view, missing docs tab, template uploads, separate DTR/doc progress, status tags, archive system, optional NBI, separate evaluation scores.


