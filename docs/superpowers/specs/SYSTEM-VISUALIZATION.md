# System Visualization — Full Architecture & Automation Map

> **Purpose:** Complete visual reference for the Internship Deployment Management and Digital Documentation Compliance Monitoring System.
> **Audience:** Proponent, Adviser, Panelists, Future Developers
> **Status:** Post-Defense — 10 System Revisions Pending (Phase 25)

---

## A. THE STUDENT LIFECYCLE — 6 Phases End-to-End

```
PHASE 0: ACCOUNT CREATION (by Instructor)
═══════════════════════════════════════════
                   
  ┌─ Instructor fills New Student form ──────────────────────────┐
  │  • Personal info, Section, Assigned Instructor                │
  │  • OJT Type: [Unplaced] [Internal OJT] [External OJT]        │
  └──────────────────────────┬───────────────────────────────────┘
                             │
                             ▼
  ┌─ SYSTEM AUTO-CREATES ───────────────────────────────────────┐
  │  • Student record (ojt_type set)                            │
  │  • StudentAccount (password = student number)               │
  │  • Deployment (status: pending, company_id: null)           │
  │  • Portal opens in LIMITED MODE                             │
  └──────────────────────────┬───────────────────────────────────┘
                             │
                             ▼
              ╔═══════════════════════════════╗
              ║  STUDENT SEES: 8 Pre-Docs     ║
              ║  Clock In/Out: HIDDEN         ║
              ║  Dashboard: REDIRECT to Docs  ║
              ╚═══════════════════════════════╝


PHASE 1: PRE-REQUIREMENTS (by Student)
═══════════════════════════════════════

  8 Mandatory Documents:                           STATUS FLOW:
  ┌──────────────────────────────┐               ┌──────────────┐
  │ 1. Pledge of Good Conduct   │               │   Submitted   │
  │ 2. Memorandum of Agreement  │  ──► Upload ──►──────────────►│
  │ 3. Internship Agreement     │               │  In Review    │
  │ 4. Parent Consent Form      │               ├──────────────┤
  │ 5. Application Letter       │               │  Approved ◄──┤── Instructor reviews
  │ 6. Endorsement Letter       │               │  or          │  in Document Queue
  │ 7. Resume                   │               │  Returned    │
  │ 8. Acceptance Letter        │               └──────┬───────┘
  └──────────────────────────────┘                      │
                                                       ▼
  ⚡ REAL-TIME: Instructor sees uploads via 30s queue polling
  ⚡ REAL-TIME: Student sees status changes via notification bell


PHASE 2: AUTO-DEPLOYMENT (by SYSTEM — Zero Human Action)
══════════════════════════════════════════════════════════

  ┌─ ALL 8 Pre-Docs Approved? ──────────────────────────┐
  │                                                     │
  │  YES ──────────────────────────────────────────────┐ │
  │                                                     │ │
  │  ┌─ SYSTEM AUTO-ACTIVATES ────────────────────────┐ │ │
  │  │  • status: pending → active                    │ │ │
  │  │  • start_date: today                           │ │ │
  │  │  • Weekly journals auto-created                │ │ │
  │  │  • Attendance passcode auto-generated          │ │ │
  │  │  • Student + Instructor NOTIFIED               │ │ │
  │  └────────────────────────────────────────────────┘ │ │
  │                                                     │ │
  │  ┌─ STUDENT NOW HAS FULL ACCESS ──────────────────┐ │ │
  │  │  • Dashboard, Clock In/Out, DTR, Journals, Msg │ │ │
  │  │  • 4 Monitoring documents appear (12 total)     │ │ │
  │  └────────────────────────────────────────────────┘ │ │
  │                                                     │ │
  │  NO ──► Blocked until conditions met                │ │
  │          • Unplaced: needs company assignment        │ │
  │          • Internal: auto-activates without company  │ │
  │          • External: needs company assigned          │ │
  └───────────────────────────────────────────────────────┘ │
     ┌──────────────────────────────────────────────────────┘
     │
     ▼
  ╔══════════════════════════════════════════════════╗
  ║  BEFORE (Manual):                                ║
  ║  Instructor creates deployment,                  ║
  ║  manually sets dates, assigns company            ║
  ║                                                  ║
  ║  AFTER (Automated):                              ║
  ║  System detects all pre-docs approved,           ║
  ║  activates deployment instantly, no human action  ║
  ╚══════════════════════════════════════════════════╝


PHASE 3: MONITORING (by Student + Instructor)
══════════════════════════════════════════════

  4 Monitoring Documents (visible after Phase 2):
    9. Enrolment Form
   10. NBI Clearance          ← REVISION 25.9: Make optional
   11. Medical Certificate
   12. Training Plan

  ┌──────────────────────────────────────────────────────────────────┐
  │                    DAILY ROUTINE                                 │
  │                                                                  │
  │  STUDENT:                                                        │
  │  ┌────────┐   ┌──────────┐   ┌──────────┐   ┌──────────┐        │
  │  │ Clock  │   │ AM In   │   │ AM Out  │   │ PM In   │          │
  │  │ In/Out │──►│(Session)│──►│(Session)│──►│(Session)│──► PM Out │
  │  │ Button │   │ 7-9am   │   │ 12nn-1pm│   │ 1-2pm   │  4-6pm   │
  │  └────────┘   └──────────┘   └──────────┘   └──────────┘        │
  │       │                                                          │
  │       ├── Dashboard Quick Clock (1 click, no passcode)           │
  │       └── Full Check-in Page (with passcode, for kiosk)          │
  │                                                                  │
  │  ⚡ 10m geofence enforces location verification                  │
  │  ⚡ DTR auto-populated from each attendance session              │
  │  ⚡ Weekly journal auto-saves every 600ms of inactivity          │
  └──────────────────────────────────────────────────────────────────┘

  ┌──────────────────────────────────────────────────────────────────┐
  │  INSTRUCTOR:                                                     │
  │  • Reviews journals in grouped-by-student accordion              │
  │  • Monitors DTR via calendar view per student                    │
  │  • Approves/returns monitoring documents                         │
  │  • Sends HTE evaluation link to company supervisor               │
  │  • Views real-time attendance with geofence status badges        │
  └──────────────────────────────────────────────────────────────────┘


PHASE 4: HTE EVALUATION (by Company Supervisor)
══════════════════════════════════════════════════

  ┌─ Instructor generates one-time secure link ────────────────────┐
  │  → Emailed to supervisor (no login required)                   │
  │  → Student notified                                            │
  └──────────────────────────────┬──────────────────────────────────┘
                                 │
                                 ▼
  ┌─ SUPERVISOR FILLS 18 CRITERIA ───────────────────────────────┐
  │                                                               │
  │  WORK HABITS (5)     WORK SKILLS (6)      SOCIAL SKILLS (7)  │
  │  ┌─────────────────┐ ┌─────────────────┐ ┌─────────────────┐  │
  │  │ Punctuality  ◎ │ │ Tech Competence◎│ │ Communication◎ │  │
  │  │ Initiative   ◎ │ │ Problem Solving◎ │ │ Teamwork      ◎│  │
  │  │ Dependability ◎│ │ Quality of Work◎│ │ Interpersonal ◎│  │
  │  │ Attitude     ◎ │ │ Efficiency    ◎ │ │ Adaptability  ◎│  │
  │  │ ...           │ │ ...             │ │ ...             │  │
  │  └─────────────────┘ └─────────────────┘ └─────────────────┘  │
  │                                                               │
  │  ⚡ Alpine.js computes: category averages → overall % in real-time│
  │  ⚡ Submit disabled until ALL 18 criteria filled               │
  │  ⚡ On submit: Evaluation saved, link marked used              │
  │  ⚡ Student + Instructor notified                              │
  └───────────────────────────────────────────────────────────────┘

  ┌─ AUTOMATION: Deployment-end reminder ─────────────────────────┐
  │  → 7 days before end_date, system emails instructor           │
  │    to send HTE link if not yet sent                           │
  └───────────────────────────────────────────────────────────────┘


PHASE 5: AUTO-COMPLETION (by SYSTEM — Zero Human Action)
══════════════════════════════════════════════════════════

  ┌─ Clock-Out Trigger ──────────────────────────────────────────┐
  │  After every clock-out, system checks:                       │
  │  total attendance minutes ≥ 36,000 (600 hours)?              │
  │                                                              │
  │  YES ──────────────────────────────┐                         │
  │  ┌─ SYSTEM AUTO-COMPLETES ───────┐ │                         │
  │  │  • status: active → completed │ │                         │
  │  │  • end_date: today           │ │                         │
  │  │  • Student + Instructor NOTIFIED │                         │
  │  │  • Deployment auto-complete cron (daily fallback)          │
  │  └────────────────────────────────┘ │                         │
  │                                    │                         │
  │  ┌─ POST-REQUIREMENT DOCS APPEAR ─┐ │                         │
  │  │  13. Signed DTTR (Final DTR)   │ │                         │
  │  │  14. Signed Weekly Journal     │ │                         │
  │  └────────────────────────────────┘ │                         │
  │                                    │                         │
  │  NO ──► Continue monitoring        │                         │
  └────────────────────────────────────┘                         │
     ┌───────────────────────────────────────────────────────────┘
     │
     ▼
  ╔══════════════════════════════════════════════════╗
  ║  BEFORE (Manual):                                ║
  ║  Instructor tracks hours manually,               ║
  ║  manually marks deployment complete              ║
  ║                                                  ║
  ║  AFTER (Automated):                              ║
  ║  System auto-completes at 36,000 minutes,        ║
  ║  triggers post-requirement docs visibility       ║
  ╚══════════════════════════════════════════════════╝


PHASE 6: CERTIFICATE (by Instructor)
══════════════════════════════════════

  ┌─ Instructor notified of completion ──────────────────────────┐
  │  → Uploads completion certificate                            │
  │  → Student views/downloads in portal                         │
  │  → Certificate preview (PDF inline, images show)             │
  └───────────────────────────────────────────────────────────────┘


```

---

## B. THE FOUR USERS — Complete Role Map

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│                                                                                 │
│  STUDENT                                                                        │
│  ───────                                                                        │
│  GOAL: Submit docs, clock in/out, view grades — minimal friction                │
│                                                                                 │
│  ┌──────────────────────────────────────────────────────────────────────┐      │
│  │                     STUDENT INTERFACE (12 Screens)                    │      │
│  ├──────────────────────────────────────────────────────────────────────┤      │
│  │ LIMITED MODE (Pre-Deployment)    │ FULL ACCESS (Deployed)             │      │
│  │──────────────────────────────────┼────────────────────────────────────│      │
│  │ • My Documents (8 pre-docs)     │ • Dashboard (quick clock, grade)    │      │
│  │ • Announcements (view only)     │ • My Documents (12 docs)            │      │
│  │ • Certificates (view only)      │ • Clock In/Out (passcode + session) │      │
│  │ • Profile (edit contact info)   │ • DTR (calendar + inline edit)      │      │
│  │                                  │ • Weekly Journals (auto-save)      │      │
│  │                                  │ • Messages (inbox + sent)          │      │
│  │                                  │ • Certificates (view + download)   │      │
│  │                                  │ • Announcements                    │      │
│  │                                  │ • Profile                          │      │
│  └──────────────────────────────────┴────────────────────────────────────┘      │
│                                                                                 │
│  🎯 AUTOMATIONS:                                                               │
│  • Auto-upload on file select (Phase 4.4)                                      │
│  • Journal auto-save (600ms debounce, Phase 1)                                 │
│  • Journal auto-submit when week passes + all entries filled (cron)            │
│  • DTR auto-populated from attendance (Phase 14.4)                             │
│  • DTR inline task editing (Phase 16.1)                                        │
│  • Passcode one-click reveal (no double-click, Phase 20.20)                    │
│  • Trust this browser — skip OTP for 30 days (Phase S1.11)                     │
│  • 30s auto-refresh of document status (Phase 24)                              │
│                                                                                 │
│  🔄 REVISIONS 25.1-25.2 AFFECT:                                                │
│  • 25.1: Documents page gets table format with category tags                   │
│  • 25.2: New "Missing Docs" tab shows only incomplete requirements             │
│  • 25.5: Document cards show colored status tags (Pre/Monitoring/Post)         │
│  • 25.9: NBI Clearance no longer blocks progress when incomplete               │
└─────────────────────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────────┐
│                                                                                 │
│  INSTRUCTOR (OJT Coordinator)                                                   │
│  ─────────────────────────────                                                   │
│  GOAL: Manage everything — batch operations, search/filter, accurate data       │
│                                                                                 │
│  ┌──────────────────────────────────────────────────────────────────────┐      │
│  │                 INSTRUCTOR INTERFACE (15 Modules)                    │      │
│  ├──────────────────────────────────────────────────────────────────────┤      │
│  │ 1. Dashboard     — KPI cards, at-risk alerts, section compliance     │      │
│  │ 2. Companies     — CRUD + batch student assignment                   │      │
│  │ 3. Students      — CRUD + import Excel + 6-tab profile               │      │
│  │ 4. Deployments   — View/edit + status filter                         │      │
│  │ 5. Compliance    — Status + risk + section filters                    │      │
│  │ 6. Attendance    — Date filters + batch resolve + CSV export          │      │
│  │ 7. Doc Queue     — Grouped by student + review panel + workflow      │      │
│  │ 8. Evaluations   — CRUD + send HTE links + criteria management       │      │
│  │ 9. Weekly Jrnls   — Grouped by student + filter + review              │      │
│  │10. DTR           — Calendar view per student                          │      │
│  │11. Certificates  — CRUD + verify + bulk verify                       │      │
│  │12. Announcements — CRUD                                              │      │
│  │13. Messages      — WhatsApp-style split panel                        │      │
│  │14. Reports       — 4 tabs (deployed, missing, compliance, attendance)│      │
│  │15. Campus        — Geofence, time windows, manage instructors        │      │
│  └──────────────────────────────────────────────────────────────────────┘      │
│                                                                                 │
│  🎯 AUTOMATIONS:                                                               │
│  • Real-time queue polling (30s) — new uploads auto-appear                     │
│  • Real-time dashboard KPI polling (60s)                                       │
│  • Bulk document approval (Phase 21b.4)                                        │
│  • Batch attendance resolve (Phase 14.7)                                       │
│  • Batch certificate verify (Phase 3)                                          │
│  • At-risk auto-flagging (daily cron, 4 criteria)                              │
│  • Email + in-app reminders (hourly cron, 7 types)                             │
│  • Auto-resolve near-boundary attendance (>3 days old, daily cron)             │
│  • Global toast notifications via X-Toast-Message header (Phase 17.8)          │
│                                                                                 │
│  🔄 REVISIONS 25.3, 25.6-25.8 AFFECT:                                          │
│  • 25.3: Can upload sample document templates for student download             │
│  • 25.4: Sees separate DTR progress and document progress bars                 │
│  • 25.6: Delete replaced with Archive — records preserved                      │
│  • 25.7-25.8: Archived records include School Year, filterable by academic year│
│  • 25.10: Evaluations show separate scores: Instructor vs Supervisor           │
└─────────────────────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────────┐
│                                                                                 │
│  CHAIRPERSON                                                                     │
│  ────────────                                                                    │
│  GOAL: Oversight — compliance, forwarded documents, program health              │
│                                                                                 │
│  ACCESSIBLE:                                                                    │
│  ┌──────────────────────────────────────────────────────────────────────┐      │
│  │ • Dashboard (4 KPI cards + section compliance widget)                │      │
│  │ • Students (view only)                                               │      │
│  │ • Deployments (view only)                                            │      │
│  │ • Compliance (status + risk + section filters)                       │      │
│  │ • Attendance (view + filter)                                         │      │
│  │ • Evaluations (view only)                                            │      │
│  │ • Announcements (CRUD)                                               │      │
│  │ • Messages (full)                                                    │      │
│  │ • Reports (all tabs + export)                                        │      │
│  └──────────────────────────────────────────────────────────────────────┘      │
│                                                                                 │
│  HIDDEN (No access): Companies, Document Queue, Weekly Journals, DTR,           │
│  Certificates, Settings/Campus, Exec Summary                                   │
│                                                                                 │
│  🔄 REVISIONS 25.4, 25.6-25.8 AFFECT:                                          │
│  • 25.4: Can view separate DTR progress from document progress in reports       │
│  • 25.6-25.8: Archive section accessible with academic year filtering           │
└─────────────────────────────────────────────────────────────────────────────────┘


┌─────────────────────────────────────────────────────────────────────────────────┐
│                                                                                 │
│  DEAN                                                                            │
│  ────                                                                            │
│  GOAL: View-only oversight — trends, KPIs, executive summaries                  │
│                                                                                 │
│  ACCESSIBLE:                                                                    │
│  ┌──────────────────────────────────────────────────────────────────────┐      │
│  │ • Dashboard (4 KPI cards + evaluation overview)                      │      │
│  │ • Students (view only)                                               │      │
│  │ • Compliance (view + filter)                                         │      │
│  │ • Attendance (view + filter)                                         │      │
│  │ • Evaluations (view only)                                            │      │
│  │ • Announcements (view only)                                          │      │
│  │ • Messages (full)                                                    │      │
│  │ • Reports (all tabs + Executive Summary + export)                    │      │
│  └──────────────────────────────────────────────────────────────────────┘      │
│                                                                                 │
│  HIDDEN: Companies, Deployments, Document Queue, Weekly Journals, DTR,          │
│  Certificates, Program Health, Settings/Campus                                 │
│                                                                                 │
│  🔄 REVISIONS 25.10 AFFECT:                                                    │
│  • 25.10: Executive Summary report shows instructor vs supervisor scores        │
└─────────────────────────────────────────────────────────────────────────────────┘

```

---

## C. AUTOMATION TOUCHPOINTS — Complete Map

Every place the system acts automatically, saving human effort:

```
AUTOMATION                    │ TRIGGER                    │ WHAT HAPPENS
──────────────────────────────┼────────────────────────────┼────────────────────────────────────
Auto-Deployment (Phase 2)    │ ALL 8 Pre-docs approved    │ Deployment activates, full portal
Auto-Completion (Phase 5)    │ 36,000 min (600 hrs)       │ Deployment completes, post-docs show
Weekly Journal Creation      │ Deployment active (saved)  │ Mon-Sat rows auto-created for 15 wks
Attendance Passcode Gen      │ Deployment active (saved)  │ 6-digit passcode generated
Journal Auto-Save            │ 600ms of inactivity         │ Content saved via PATCH AJAX
Journal Auto-Submit          │ Daily cron (flags:check)    │ Draft journals with all days filled → submit
DTR Auto-Population          │ Attendance recorded         │ DailyTimeRecord created/updated
Document Upload              │ File selected               │ Auto-submits form
OTP Skip (Trust Browser)     │ Login + checkbox            │ 30-day encrypted cookie
Risk Flag Auto-Resolution    │ Daily cron (flags:check)    │ Stale flags resolved automatically
Near-Boundary Attendance     │ Daily cron                  │ >3 days old auto-resolved
Deployment Auto-Complete     │ Daily cron                  │ Past-end_date deployments completed
Reminders                    │ Hourly cron                 │ 7 types of email + in-app notifications
Notification Bell Polling    │ 30s interval                │ 6 custom events dispatched
Message Inbox Polling        │ 30s interval                │ New messages auto-appear
Document Queue Polling       │ 30s interval                │ New uploads auto-appear
Dashboard KPI Polling        │ 60s interval                │ KPI cards auto-update
Student Document Auto-Refresh│ 30s interval                │ Status changes auto-appear
HTMX Loading Indicator       │ Every HTMX request          │ Gradient bar on target elements
Toast Notifications          │ Any X-Toast-Message header  │ Global toast on any HTMX action
Alpine Modal Escape          │ Any modal open              │ Escape key + backdrop click dismisses
Session Timeout              │ 2h inactivity               │ Auto-redirect to login
```

---

## D. DEFENSE REVISIONS MAP — Phase 25 Detailed

```
REVISION 25.1: Document Table Format with Tags/Dropdown
─────────────────────────────────────────────────────────

  WHAT: Convert current document card layout to table with categorization
  
  ┌──────────────────────────────────────────────────────────────────┐
  │  CURRENT VIEW (Cards)         │  NEW VIEW (Table + Tags)        │
  │                               │                                  │
  │  ┌─────────────────────┐      │  ┌──────┬────────┬────────┬────┐│
  │  │ Pledge of Good      │      │  │ Doc  │ Status │ Phase  │    ││
  │  │ Conduct             │      │  ├──────┼────────┼────────┤    ││
  │  │ Status: Pending     │      │  │ MOA  │ ✅ Done│ Pre    │    ││
  │  └─────────────────────┘      │  │ COG  │ ⏳ Pending│Pre  │    ││
  │                               │  │ NBI  │ ❌ Miss │ Mon   │    ││
  │  Multiple cards per page      │  └──────┴────────┴────────┴────┘│
  │  No category column           │  Category dropdown: All/Pre/Mon/Post│
  │  Scroll to find documents     │  Sortable by phase, status      │
  └──────────────────────────────────────────────────────────────────┘

  FILES: resources/views/student/documents.blade.php, document-card.blade.php
  NEW: Tag/Category filter component
  
  ⭐ USER BENEFIT: Students see all documents in one organized view
     Instructors can filter by category to review specific phases


REVISION 25.2: Missing Documents Dedicated Tab
───────────────────────────────────────────────

  WHAT: New tab showing only incomplete/pending documents

  ┌──────────────────────────────────────────────────────────────────┐
  │  STUDENT DOCUMENTS PAGE                                         │
  │  ┌──────────┬──────────┬──────────┐                              │
  │  │ All Docs │ Missing  │ Completed│  ← New Tab                   │
  │  ├──────────┴──────────┴──────────┤                              │
  │  │  ⚠ You have 3 missing docs:   │                              │
  │  │  • NBI Clearance (Optional)    │                              │
  │  │  • Medical Certificate         │                              │
  │  │  • Training Plan               │                              │
  │  │                                │                              │
  │  │  [Upload Now] [Upload Now]     │                              │
  │  └────────────────────────────────┘                              │
  └──────────────────────────────────────────────────────────────────┘

  FILES: resources/views/student/documents.blade.php, StudentPortalController.php
  
  ⭐ USER BENEFIT: Students immediately see what's missing — no scrolling
     Instructors can quickly identify non-compliant students


REVISION 25.3: Instructor Uploads Sample Templates
────────────────────────────────────────────────────

  WHAT: Instructors upload document templates for student download

  ┌──────────────────────────────────────────────────────────────────┐
  │  INSTRUCTOR: Required Documents Page                            │
  │                                                                  │
  │  ┌─────────────────────────────────────────────────────────────┐ │
  │  │ Pledge of Good Conduct                                      │ │
  │  │ is_mandatory: ✅   phase: pre                                │ │
  │  │ Sample Template: [Choose File] [Upload]  ← New button        │ │
  │  │ Currently: pledge_template.docx  [Download] [Replace]       │ │
  │  └─────────────────────────────────────────────────────────────┘ │
  │                                                                  │
  │  STUDENT: Document card shows:                                   │
  │  📎 Sample Template [Download] ← New link                        │
  └──────────────────────────────────────────────────────────────────┘

  NEW: document_templates table, migration, controller, upload route
  FILES: RequiredDocumentController (+ template methods), student document card
  
  ⭐ USER BENEFIT: Students get properly formatted templates → fewer rejections


REVISION 25.4: Separate DTR and Document Progress
───────────────────────────────────────────────────

  WHAT: Two progress bars instead of one combined

  ┌──────────────────────────────────────────────────────────────────┐
  │  BEFORE:                                                        │
  │  ████████████████░░░░░░  75% Overall Progress                   │
  │                                                                  │
  │  AFTER:                                                         │
  │  📄 Documents: ████████████████░░░░  80% (10/12 submitted)      │
  │  📅 DTR Hours: ████████████░░░░░░░░  55% (330/600 hrs)          │
  └──────────────────────────────────────────────────────────────────┘

  NEW: dtr_progress_pct + doc_progress_pct columns on students table
  FILES: Student.php (accessors), student dashboard, staff student list
  
  ⭐ USER BENEFIT: Clear view of two independent progress metrics


REVISION 25.5: Document Status Tags
────────────────────────────────────

  WHAT: Color-coded phase badges on every document

  ┌──────────────────────────────────────────────────────────────────┐
  │  🔵 PRE-DEPLOYMENT          🟡 MONITORING       🟢 POST-DEPLOYMENT│
  │  ┌─────────────────────┐   ┌─────────────────┐  ┌───────────────┐│
  │  │ Pledge of Good      │   │ Enrolment Form  │  │ Signed DTTR   ││
  │  │ Conduct             │   │                 │  │               ││
  │  │ [Pre-Deployment]    │   │ [Monitoring]    │  │ [Post-Deploy] ││
  │  │ Status: ✅ Approved  │   │ Status: ⏳ Pend  │  │ Status: 📋 N/A ││
  │  └─────────────────────┘   └─────────────────┘  └───────────────┘│
  └──────────────────────────────────────────────────────────────────┘

  COLORS: Pre=blue-600, Monitoring=amber-600, Post=emerald-600
  FILES: document-card.blade.php, student documents index
  
  ⭐ USER BENEFIT: Instant visual cue of what phase a document belongs to


REVISION 25.6: Archive Instead of Delete
─────────────────────────────────────────

  WHAT: Replace destructive Delete with reversible Archive

  ┌──────────────────────────────────────────────────────────────────┐
  │  BEFORE:                                                        │
  │  [Delete] → Popup "Are you sure?" → Permanently removed         │
  │                                                                  │
  │  AFTER:                                                         │
  │  [Archive] → "Why are you archiving?" → {dropdown reason}       │
  │           → Record preserved, hidden from active views           │
  │           → Archived records visible in Archive section          │
  └──────────────────────────────────────────────────────────────────┘

  NEW: 
  - archived_at (timestamp, nullable) on major models
  - archive_reason (text, nullable)
  - global scope: ->whereNull('archived_at') on active queries
  - ArchiveController + Archive views

  MODELS AFFECTED: Student, Company, Deployment, StudentDocument, etc.
  
  ⭐ USER BENEFIT: Accidental deletions become recoverable
     Panelist (Cristine Redoblo): "Data integrity preserved"


REVISION 25.7 + 25.8: School Year in Archive + Filter
───────────────────────────────────────────────────────

  WHAT: Archived records include academic year, filterable

  ┌──────────────────────────────────────────────────────────────────┐
  │  ARCHIVE SECTION                                                │
  │                                                                  │
  │  Academic Year: [2025-2026 ▼] ← Dropdown filter                 │
  │                                                                  │
  │  ┌───────┬──────────┬──────────┬──────────────────┐             │
  │  │ Type  │ Record   │ Archived │ School Year      │             │
  │  ├───────┼──────────┼──────────┼──────────────────┤             │
  │  │ Student │ Doe, John │ 2026-07-03│ 2025-2026       │             │
  │  │ Company │ ABC Corp  │ 2026-07-02│ 2025-2026       │             │
  │  └───────┴──────────┴──────────┴──────────────────┘             │
  └──────────────────────────────────────────────────────────────────┘

  school_year auto-derived from: deployment.start_date
  Format: "2025-2026" (first year-second year)
  
  ⭐ USER BENEFIT: Organized archive, easy retrieval


REVISION 25.9: NBI Clearance Optional
──────────────────────────────────────

  WHAT: Change one document from mandatory to optional

  ┌──────────────────────────────────────────────────────────────────┐
  │  BEFORE: NBI Clearance is_mandatory: true                        │
  │  → Prevents deployment if missing                               │
  │  → Shows in progress denominator                                │
  │                                                                  │
  │  AFTER:  NBI Clearance is_mandatory: false                       │
  │  → Does NOT block deployment if missing                         │
  │  → NOT counted in progress denominator                          │
  │  → Student can still upload if they have one                    │
  └──────────────────────────────────────────────────────────────────┘

  FILES: database/seeders/SampleDataSeeder.php (update), new migration
  
  ⭐ USER BENEFIT: Students without NBI aren't blocked from deploying


REVISION 25.10: Separate OJT Instructor vs Supervisor Scores
─────────────────────────────────────────────────────────────

  WHAT: Two independent evaluation scores displayed separately

  ┌──────────────────────────────────────────────────────────────────┐
  │  STUDENT DASHBOARD — GRADE CARD                                  │
  │                                                                  │
  │  ┌─────────────────────┐  ┌─────────────────────┐                │
  │  │ OJT INSTRUCTOR      │  │ COMPANY SUPERVISOR  │                │
  │  │ Score: 92%          │  │ Score: 88%          │                │
  │  │ Rated by: Dr. Cruz  │  │ Rated by: Mr. Reyes │                │
  │  │ Date: 2026-06-15    │  │ Date: 2026-06-20    │                │
  │  │ [View Details]      │  │ [View Details]      │                │
  │  └─────────────────────┘  └─────────────────────┘                │
  │                                                                  │
  │  INSTRUCTOR EVALUATION PAGE:                                     │
  │  ┌─────────────────────────────────────────────────────────────┐ │
  │  │ Student: Juan Dela Cruz                                      │ │
  │  │ OJT Instructor Score: 92/100  [Edit]  [Send HTE Link - Inst] │ │
  │  │ Supervisor Score:      88/100  [View]  [Send HTE Link - Sup]  │ │
  │  └─────────────────────────────────────────────────────────────┘ │
  └──────────────────────────────────────────────────────────────────┘

  NEW: evaluation_type enum on evaluations (instructor/supervisor)
  FILES: Evaluation.php, evaluation controller, seeder, student dashboard
  
  ⭐ USER BENEFIT: Clear separation of two independent assessments
```

---

## E. BEFORE vs AFTER AUTOMATION — Side-by-Side

```
PROCESS                    │ BEFORE (Manual)                  │ AFTER (Automated)
───────────────────────────┼──────────────────────────────────┼─────────────────────────────────────
Deployment Activation      │ Instructor creates deployment,   │ System detects all pre-docs
                           │ manually sets dates, assigns     │ approved, activates instantly
                           │ company                          │
                           │                                  │
Deployment Completion      │ Instructor tracks hours          │ System auto-completes at
                           │ manually, marks complete         │ 36,000 total minutes (600 hrs)
                           │                                  │
Weekly Journal Creation    │ Student creates each week        │ System auto-creates Mon-Sat
                           │ manually                         │ rows for 15 weeks on activation
                           │                                  │
Journal Saving             │ Student clicks "Save" button    │ Auto-saves 600ms after user
                           │ each time                        │ stops typing (no save button)
                           │                                  │
Journal Submission         │ Student submits each week        │ Draft journals auto-submit when
                           │ manually                         │ week ends + all days filled
                           │                                  │
DTR Creation               │ Student fills DTR fields         │ DTR auto-populated from
                           │ manually each day                │ attendance clock-in/out times
                           │                                  │
Attendance Passcode        │ Instructor generates manually    │ System generates on deployment
                           │                                  │ activation
                           │                                  │
Document Upload            │ Student selects file → clicks    │ Select file → auto-uploads
                           │ Upload button                    │ (with upload button fallback)
                           │                                  │
OTP Verification           │ Student enters OTP every login  │ "Trust this device" skips OTP
                           │                                  │ for 30 days
                           │                                  │
Risk Flag Detection        │ Instructor manually checks for   │ Daily cron checks 4 criteria,
                           │ absences, late journals, etc.    │ auto-flags and auto-notifies
                           │                                  │
Flag Resolution            │ Instructor manually resolves     │ Stale flags auto-resolve when
                           │ each flag                        │ criteria no longer met
                           │                                  │
Attendance Resolution      │ Instructor resolves each         │ Near-boundary >3 days old
                           │ near-boundary entry manually     │ auto-resolved daily
                           │                                  │
Reminders                  │ Instructor manually follows up   │ Hourly cron sends 7 types of
                           │ with students                    │ email + in-app reminders
                           │                                  │
Message Notifications      │ Student must refresh page        │ 30s polling shows new messages
                           │ to see new messages              │ automatically
                           │                                  │
Document Queue             │ Instructor must refresh page     │ 30s polling shows new uploads
                           │ to see new uploads               │ automatically
                           │                                  │
Dashboard KPIs             │ Instructor must refresh page     │ 60s polling updates KPI cards
                           │ to see updated numbers           │ automatically
                           │                                  │
Deployment End Reminder    │ Instructor must remember to      │ System auto-reminds 7 days
                           │ send HTE link                    │ before end_date
                           │                                  │
Document Forwarding        │ Instructor manually notifies     │ System auto-assigns to next
                           │ next reviewer                    │ holder role, sends notification
                           │                                  │
Error Feedback             │ Generic error messages           │ Field-level validation with
                           │                                  │ toast notifications + Alpine modal
                           │                                  │
Session Management         │ 30-minute timeout, frequent      │ 2-hour timeout, stable sessions
                           │ re-login                         │
                           │                                  │
Performance                │ No OPcache, database sessions,   │ OPcache + file sessions +
                           │ slow page loads                  │ cached routes/views = 3-5x faster
```

---

## F. DATA FLOW & INTEGRATION MAP

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                    SYSTEM DATA FLOW                                         │
│                                                                                             │
│  ┌──────────────┐    ┌──────────────────────┐    ┌──────────────────┐                      │
│  │  INSTRUCTOR   │───►│    Laravel App        │◄───│    STUDENT       │                      │
│  │  (Web Guard)  │    │    (PHP 8.2.12)       │    │  (Session Auth)  │                      │
│  └──────────────┘    │                        │    └──────────────────┘                      │
│                      │  ┌──────────────────┐  │                                            │
│  ┌──────────────┐    │  │  Controllers (29) │  │    ┌──────────────────┐                      │
│  │  CHAIRPERSON  │───►│  ├──────────────────┤  │◄───│  HTE SUPERVISOR  │                      │
│  │  (Web Guard)  │    │  │  Services (5)     │  │    │  (Public - 1-time │                      │
│  └──────────────┘    │  ├──────────────────┤  │    │   token/ no login)│                      │
│                      │  │  Models (31)       │  │    └──────────────────┘                      │
│  ┌──────────────┐    │  ├──────────────────┤  │                                            │
│  │  DEAN         │───►│  Middleware (6)    │  │                                            │
│  │  (Web Guard)  │    │  └──────────────────┘  │                                            │
│  └──────────────┘    └─────────────────────────┘                                            │
│                               │          │                                                  │
│                               ▼          ▼                                                  │
│                    ┌──────────────────────────────┐                                         │
│                    │         MySQL Database        │                                         │
│                    │  ┌────────────────────────┐  │                                         │
│                    │  │ Tables (30+):          │  │                                         │
│                    │  │ students               │  │                                         │
│                    │  │ student_accounts        │  │                                         │
│                    │  │ users                  │  │                                         │
│                    │  │ companies              │  │                                         │
│                    │  │ deployments            │  │                                         │
│                    │  │ required_documents      │  │                                         │
│                    │  │ student_documents       │  │                                         │
│                    │  │ attendances            │  │                                         │
│                    │  │ weekly_journals        │  │                                         │
│                    │  │ daily_time_records     │  │                                         │
│                    │  │ evaluations            │  │                                         │
│                    │  │ certificates           │  │                                         │
│                    │  │ messages/message_partic│  │                                         │
│                    │  │ notifications          │  │                                         │
│                    │  │ student_risk_flags     │  │                                         │
│                    │  │ ... (90 migrations)    │  │                                         │
│                    │  └────────────────────────┘  │                                         │
│                    └──────────────────────────────┘                                         │
│                               │                                                             │
│                               ▼                                                             │
│                    ┌──────────────────────────────┐                                         │
│                    │      File Storage (Local)     │                                         │
│                    │  storage/app/public/          │                                         │
│                    │  ├── student-documents/{id}/  │                                         │
│                    │  ├── certificates/{id}/       │                                         │
│                    │  ├── journal-files/{id}/      │                                         │
│                    │  ├── monthly-dttrs/{id}/      │                                         │
│                    │  └── templates/{id}/ ← NEW 25.3│                                         │
│                    └──────────────────────────────┘                                         │
│                                                                                             │
│  AUTOMATED JOBS (Cron):                                                                     │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐               │
│  │ flags:check  │  │reminders:send│  │auto-complete │  │ auto-resolve     │               │
│  │ (daily)      │  │ (hourly)     │  │ (daily)      │  │ (daily)          │               │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────────────┘               │
│                                                                                             │
│  REAL-TIME POLLING:                                                                         │
│  ┌──────────────────────────────────────────────────────────────────────────────────────┐  │
│  │ Student Dashboard (on action) │ Bell (30s) │ Queue (30s) │ MSG Inbox (30s)          │  │
|  │ Attendance List (12s)         │ Dashboard KPIs (60s) │ Journals (30s)              │  │
│  │ Compliance (30s)             │ Student Docs (30s)                                  │  │
│  └──────────────────────────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## G. PERFORMANCE OPTIMIZATION IMPACT

```
OPTIMIZATION                │ BEFORE                    │ AFTER                    │ GAIN
────────────────────────────┼───────────────────────────┼──────────────────────────┼─────────
OPcache                     │ PHP recompiles every file │ Bytecode cached in memory│ 50-60%
                            │ on every request          │ (128MB, 10k files)       │
                            │                           │                          │
Session Driver              │ MySQL (disk I/O + query)  │ File (flat file read)   │ 10-20ms
Cache Driver                │ MySQL (disk I/O + query)  │ File (flat file read)   │ 5-10ms
                            │                           │                          │
Config Caching              │ 50+ files read per request│ 1 cached file            │ ~50ms
Route Caching               │ 145+ routes parsed per req│ 1 compiled method call   │ ~30ms
View Caching                │ Blade compiled on 1st hit │ All precompiled          │ ~100ms
                            │                           │                          │
DocumentWorkflowEngine N+1  │ 45-60 queries per queue   │ 0 queries (in-memory)   │ 3-5x faster
                            │ page load                 │                          │
                            │                           │                          │
autoActivateDeployment      │ Runs on EVERY workflow    │ Only runs on pre-doc     │ ~30ms avg
Guard                       │ action (3 queries)        │ approvals                │
                            │                           │                          │
LOG_LEVEL                   │ debug (every query logged)│ error (only errors)      │ Variable
                            │                           │                          │
RequestTimingMiddleware     │ DB listener on every req  │ Only in local mode       │ 5-10ms
                            │                           │                          │
CheckMaintenanceMode        │ DB query on every request │ Removed (native Laravel  │ ~5ms
                            │                           │ php artisan down)        │
                            │                           │                          │
TOTAL ESTIMATED SPEEDUP     │                           │                          │ 3-5x
```

---

## H. REVISION PRIORITY & DEPENDENCY MAP

```
                    ┌─────────────────────────────────────┐
                    │  PHASE 25: DEFENSE REVISIONS         │
                    │  Total: 10 items                     │
                    └─────────────────────────────────────┘
                                  │
         ┌────────────────────────┼────────────────────────┐
         ▼                        ▼                        ▼
  ┌──────────────┐        ┌──────────────┐        ┌──────────────┐
  │  TIER 1 (P1) │        │  TIER 2 (P1) │        │  TIER 3 (P2) │
  │  Do First    │        │  Do Second   │        │  Do Last     │
  ├──────────────┤        ├──────────────┤        ├──────────────┤
  │ 25.9 NBI Opt │──┐     │ 25.1 Table   │──┐     │ 25.7 School   │
  │ (1 file)     │  │     │ (3 files)    │  │     │ Year         │
  ├──────────────┤  │     ├──────────────┤  │     │ (depends on  │
  │ 25.4 Separate │  │     │ 25.2 Missing │  │     │ 25.6)        │
  │ Progress     │  │     │ Docs Tab    │  │     │              │
  │ (2 files)    │  │     │ (2 files)    │  │     ├──────────────┤
  ├──────────────┤  │     ├──────────────┤  │     │ 25.8 Academic │
  │ 25.5 Status  │  │     │ 25.3 Template│  │     │ Year Filter  │
  │ Tags (1 file)│  │     │ Uploads     │  │     │ (depends on  │
  ├──────────────┤  │     │ (new model)  │  │     │ 25.6+25.7)   │
  │ 25.6 Archive │──┤     ├──────────────┤  │     └──────────────┘
  │ (multi-file) │  │     │ 25.10 Eval   │  │                      
  └──────────────┘  │     │ Scores      │  │                      
                    │     │ (3 files)    │  │                      
                    │     └──────────────┘  │                      
                    └───────────────────────┘                      

  ESTIMATED EFFORT: 3-5 days total (experienced dev)
  RECOMMENDED ORDER: 25.9 → 25.5 → 25.4 → 25.6 → 25.1 → 25.2 → 25.10 → 25.3 → 25.7 → 25.8
```

---

## I. SYSTEM WIDE IMPACT — By the Numbers

```
┌─────────────────────────────────────────────────────────────────────────────────────┐
│  STAT                     │ VALUE              │ DETAIL                              │
├───────────────────────────┼────────────────────┼─────────────────────────────────────┤
│ Models                    │ 31                 │ Eloquent models                     │
│ Migrations                │ 90+                │ Database schema versions            │
│ Routes                    │ 145+               │ Named routes across all roles       │
│ Controllers               │ 29                 │ Business logic controllers          │
│ Middleware                 │ 6                  │ Auth, role, session, timing         │
│ Services                  │ 5                  │ Workflow, export, notification      │
│ Blade Views               │ 100+               │ UI templates                        │
│ Database Tables           │ 30+                │ Core + pivot tables                 │
│ Automated Cron Jobs       │ 4                  │ Daily + hourly commands             │
│ Real-Time Polling Endpoints│ 7                  │ 12s-60s intervals                   │
│ User Roles                │ 4                  │ Student, Instructor, Chair, Dean    │
│ File Upload Limit         │ 2MB-10MB           │ Docs, imports, certificates         │
│ Session Timeout           │ 2 hours            │ Student portal session              │
│ OTP Skip Duration         │ 30 days            │ Encrypted cookie                    │
| Deployment Hours Threshold│ 600 hours (36,000m)│ Auto-completion trigger             │
│ Journal Auto-Save Delay   │ 600ms              │ Debounce before save                │
│ Risk Flag Criteria        │ 4                  │ Absences, journals, docs, deploy    │
│ Reminder Types            │ 7                  │ Journal, attendance, docs, deploy   │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

*Document generated July 3, 2026 — Post-Final Defense*
*Single source of truth for system architecture visualization and Phase 25 revision planning*
