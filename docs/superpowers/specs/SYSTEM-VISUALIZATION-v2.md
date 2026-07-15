# System Visualization v2 — Silent Background Polling & Architecture Map

> **Purpose:** Complete visual reference for the Internship Deployment Management and Digital Documentation Compliance Monitoring System.
> **Audience:** Proponent, Adviser, Panelists, Future Developers
> **Status:** Post-Defense — Phase 26 (Silent Background Polling) Complete

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
  🔇 SILENT BACKGROUND: Instructor sees uploads via 10s background polling — no visual indicator
  🔇 SILENT BACKGROUND: Student sees status changes via notification bell (10s polling, no loading bar)


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
│  • 10s silent background refresh of document status (Phase 24 + 26)            │
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
│  • Silent queue polling (10s) — new uploads auto-appear, no visual indicator   │
│  • Silent dashboard KPI polling (30s) — cards update with no loading bar       │
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

## C. SILENT BACKGROUND POLLING — Complete Map

Every background polling mechanism now runs silently — no loading bars, no gradient animations, no visible indicators. All intervals reduced for near-real-time feel.

```
POLLING MECHANISM           │ INTERVAL │ TRIGGER                    │ WHAT REFRESHES
────────────────────────────┼──────────┼────────────────────────────┼────────────────────────────
Notification Bell           │ 10s      │ Alpine setInterval         │ Fetches unread count, dispatches
                            │          │                            │ 7 custom refresh events
Message Inbox               │ 10s      │ HTMX every + bell event   │ Inbox column HTMX swap
Document Queue              │ 10s      │ HTMX every + bell event   │ Queue table HTMX swap
Compliance Table            │ 10s      │ HTMX every + bell event   │ Compliance list HTMX swap
Weekly Journals List        │ 10s      │ HTMX every + bell event   │ Journals table HTMX swap
Attendance Smart Poll       │ 10s      │ JS setInterval            │ Lightweight timestamp check,
                            │          │                            │ full HTMX swap on change
Student Portal Access       │ 10s      │ JS setInterval            │ Checks limited→full upgrade
Unread Message Count (×4)   │ 10s      │ JS setInterval            │ Red dot badge on nav links
Queue Pending Count         │ 10s      │ JS setInterval            │ Red badge number on nav
Dashboard KPI Cards         │ 30s      │ HTMX every + bell event   │ KPI cards partial swap
Student Document Status     │ 10s      │ Bell event + htmx.ajax    │ Document list partial swap
```

**Key design decisions:**
- **No visual indicators**: The HTMX green gradient bar (`.htmx-request.htmx-target-indicator::after`) was removed entirely — no loading animation appears on any background refresh
- **Button spinners preserved**: User-initiated actions (clock in, upload, approve, submit) still show loading states on their buttons
- **Red badges preserved**: Notification count, unread message count, queue count remain visible — these are passive indicators, not loading animations
- **Toast notifications preserved**: Success/error feedback still shows via toasts
- **Full-page overlay preserved**: Form submissions still show the loading safety net

---

## D. AUTOMATION TOUCHPOINTS — Complete Map

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
Notification Bell Polling    │ 10s interval (silent)       │ 7 custom events dispatched
Message Inbox Polling        │ 10s interval (silent)       │ New messages auto-appear
Document Queue Polling       │ 10s interval (silent)       │ New uploads auto-appear
Dashboard KPI Polling        │ 30s interval (silent)       │ KPI cards auto-update
Student Document Auto-Refresh│ 10s interval (silent)       │ Status changes auto-appear
Toast Notifications          │ Any X-Toast-Message header  │ Global toast on any HTMX action
Alpine Modal Escape          │ Any modal open              │ Escape key + backdrop click dismisses
Session Timeout              │ 2h inactivity               │ Auto-redirect to login
```

---

## E. DEFENSE REVISIONS MAP — Phase 25 Detailed

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

## F. BEFORE vs AFTER AUTOMATION — Side-by-Side

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
                           │ with students                    │ email + in-app reminders (silent)
                           │                                  │
Message Notifications      │ Student must refresh page        │ 10s silent polling shows new
                           │ to see new messages              │ messages automatically
                           │                                  │
Document Queue             │ Instructor must refresh page     │ 10s silent polling shows new
                           │ to see new uploads               │ uploads automatically
                           │                                  │
Dashboard KPIs             │ Instructor must refresh page     │ 30s silent polling updates KPI
                           │ to see updated numbers           │ cards automatically
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
                           │                                  │
Loading Indicators         │ Green animated gradient bar on   │ No loading bar on any background
                           │ every HTMX request (30s-60s)     │ poll. Button spinners preserved
                           │                                  │ for user actions only.
```

---

## G. DATA FLOW & INTEGRATION MAP

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
│  SILENT BACKGROUND POLLING:                                                                 │
│  ┌──────────────────────────────────────────────────────────────────────────────────────┐  │
│  │ Student Dashboard (on action) │ Bell (10s) │ Queue (10s) │ MSG Inbox (10s)          │  │
|  │ Attendance List (10s)         │ Dashboard KPIs (30s) │ Journals (10s)              │  │
│  │ Compliance (10s)             │ Student Docs (10s)                                  │  │
│  │ All polling is SILENT — no visual loading indicators                                │  │
│  └──────────────────────────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## H. PERFORMANCE OPTIMIZATION IMPACT

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
Polling Intervals           │ 30s-60s (visible loading) │ 10s-30s (silent, no      │ 3x faster
                            │                           │ visual indicator)         │ refresh rate
                            │                           │                          │
TOTAL ESTIMATED SPEEDUP     │                           │                          │ 3-5x
```

---

## I. REVISION PRIORITY & DEPENDENCY MAP

```
                    ┌─────────────────────────────────────────────────┐
                    ╔══════════════════════════════════════════════════╗
                    ║  PHASE 25: DEFENSE REVISIONS ✅ ALL COMPLETE    ║
                    ║  All 10 items implemented July 13, 2026        ║
                    ╚══════════════════════════════════════════════════╝

  ITEM                    │ DESCRIPTION                              │ COMPLETED
  ────────────────────────┼──────────────────────────────────────────┼──────────
  25.9 NBI Optional       │ is_mandatory → false for NBI Clearance   │ ✅
  25.5 Phase Tags         │ Colored badges (blue/amber/emerald)      │ ✅
  25.4 Separate Progress  │ doc_progress_pct + dtr_progress_pct      │ ✅
  25.6 Archive System     │ Archivable trait + ArchiveController     │ ✅
  25.1 Table Format       │ Compact table + phase filter dropdown    │ ✅
  25.2 Missing Tab        │ All/Missing/Completed tab navigation     │ ✅
  25.10 Eval Scores       │ Separate instructor/supervisor score cards│ ✅
  25.3 Template Uploads   │ DocumentTemplate model + upload/download │ ✅
  25.7 School Year        │ Auto-derived from deployment start_date  │ ✅
  25.8 Academic Filter    │ School Year dropdown in archive view     │ ✅

  KEY FILES: 16 files changed across models, controllers, views, routes, migrations

                    ┌─────────────────────────────────────────────────┐
                    │  PHASE 26: SILENT BACKGROUND POLLING ✅ DONE   │
                    ├─────────────────────────────────────────────────┤
                    │ 26.1 Reduced all polling intervals to 10s/30s  │
                    │ 26.2 Removed HTMX green gradient loading bar   │
                    │ 26.3 All background polling now has zero       │
                    │      visible UI indicators                     │
                    │ 26.4 Button spinners preserved for user        │
                    │      actions (clock-in, upload, submit)        │
                    │ 26.5 Red badge counters preserved (messages,   │
                    │      queue, notifications)                     │
                    │ 26.6 Toast notifications preserved             │
                    └─────────────────────────────────────────────────┘
```

---

## J. SYSTEM WIDE IMPACT — By the Numbers

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
│ Silent Polling Endpoints  │ 10                 │ All 10s or 30s intervals (no UI)   │
│ User Roles                │ 4                  │ Student, Instructor, Chair, Dean    │
│ File Upload Limit         │ 2MB-10MB           │ Docs, imports, certificates         │
│ Session Timeout           │ 2 hours            │ Student portal session              │
│ OTP Skip Duration         │ 30 days            │ Encrypted cookie                    │
| Deployment Hours Threshold│ 600 hours (36,000m)│ Auto-completion trigger             │
│ Journal Auto-Save Delay   │ 600ms              │ Debounce before save                │
│ Risk Flag Criteria        │ 4                  │ Absences, journals, docs, deploy    │
│ Reminder Types            │ 7                  │ Journal, attendance, docs, deploy   │
│ Loading Indicators        │ REMOVED            │ Green gradient bar deleted from     │
│                           │                    │ custom.css. Button spinners kept.   │
└─────────────────────────────────────────────────────────────────────────────────────┘
```

---

## K. PRODUCTION OPTIMIZATION STRATEGY — Zero-Bottleneck Architecture

> **Goal:** Regardless of hosting environment (shared, VPS, cloud), the system should feel instant — page loads, navigation, and all CRUD operations complete without perceptible delay.

**Current PHP Environment:**
| Setting | Value | Status |
|---------|-------|--------|
| PHP Version | 8.2.12 | ✅ |
| JIT Compiler | tracing mode (compiles hot paths to native code) | ✅ |
| OPcache | 128MB, 10,000 files | ✅ |
| Config/Route/View cache | All cached | ✅ |
| LOG_LEVEL | error (no debug overhead) | ✅ |
| DB Indexes | All key columns indexed (Phase S5) | ✅ |
| N+1 Queries | Fixed (Phase 9) | ✅ |
| Queue Driver | database (async email/notifications) | ✅ |

### Tier 1 — No-Code (Server/Hosting Configuration)

These are set by the hosting provider or sysadmin. No Laravel changes needed.

```
OPTIMIZATION                │ HOW IT HELPS                          │ REQUIREMENT
────────────────────────────┼───────────────────────────────────────┼────────────────
Gzip / Brotli Compression  │ Compresses HTML, CSS, JS 60-80%       │ Nginx/Apache config
Browser Cache Headers      │ Cache-Control: public, max-age=31536000│ Nginx/Apache config
                            │ on versioned assets (manifest.json)   │
HTTPS + HSTS               │ Encrypted traffic, better HTTP/2 perf  │ SSL cert + config
HTTP/2                     │ Multiplexed requests, no head-of-line  │ Server config (TLS)
PHP-FPM Tuning             │ pm.max_children, pm.max_requests       │ php-fpm pool config
                            │ prevent worker exhaustion             │
MySQL Query Cache          │ Caches repeated SELECT queries         │ my.cnf config
                            │ (MySQL 8: disabled by default)        │
```

### Tier 2 — Low-Code (High Impact, Safe)

Simple Laravel-side changes that eliminate redundant work.

```
OPTIMIZATION                │ WHAT IT DOES                          │ FILES TO CHANGE
────────────────────────────┼───────────────────────────────────────┼────────────────
Model Caching               │ Cache Setting, Campus config,         │ Setting.php,
                            │ RequiredDocument queries with         │ CampusSettingController.php,
                            │ rememberForever(). Flush on update.   │ RequiredDocument.php
                            │ Eliminates DB query on EVERY page.    │
Vite Asset Cleanup          │ Remove old duplicate builds, keep     │ public/build/ cleanup
                            │ only latest versioned files.          │
Vite Code Splitting         │ Split vendor chunk (Alpine, HTMX,     │ vite.config.js
                            │ Bootstrap Icons) from app code.       │
                            │ Shrinks initial JS from 376KB→~200KB. │
Session Garbage Collection  │ Ensure expired file sessions are      │ config/session.php
                            │ cleaned up periodically.              │ (lottery: [2, 100])
Database Session Driver     │ Optional: switch from file to DB      │ .env (SESSION_DRIVER)
                            │ for session storage (more reliable    │
                            │ on shared hosting with multiple        │
                            │ frontends).                           │
```

### Tier 3 — Code-Level (When Profiling Shows Need)

If a specific page or action feels slow after Tier 1+2, profile first before optimizing.

```
APPROACH                    │ WHEN TO USE                           │ TOOLS
────────────────────────────┼───────────────────────────────────────┼────────────────
Laravel Debugbar (dev)      │ Measure query count, memory, time     │ barryvdh/laravel-debugbar
Slow Query Logging          │ Log queries >100ms to detect issues   │ Custom middleware or DB::listen()
Eager Loading Audit         │ Check N+1 on heavy pages (reports,    │ Laravel Debugbar queries tab
                            │ compliance, student tabs)             │
Select Optimization         │ On large joins: only fetch needed     │ Controller query review
                            │ columns, avoid SELECT *               │
Query Pagination            │ Already done on most pages (Phase 7)  │ —
Deferred Queue              │ Defer non-critical jobs (email,       │ QUEUE_CONNECTION=database
                            │ notifications) to background.         │
```

### Hosting Compatibility Matrix

```
FEATURE                     │ SHARED cPanel  │ VPS (DO/Linode) │ Cloud (Vapor)
────────────────────────────┼────────────────┼─────────────────┼──────────────
OPcache + JIT               │ ✅ Available   │ ✅ Available    │ ✅ Available
Config/Route/View Cache     │ ✅ Available   │ ✅ Available    │ ✅ Available
Model Caching               │ ✅ Available   │ ✅ Available    │ ✅ Available
Vite Build Optimization     │ ✅ Available   │ ✅ Available    │ ✅ Available
Gzip/Brotli                 │ ✅ Usually on  │ ✅ Configurable │ ✅ Built-in
HTTP/2                      │ ✅ Usually on  │ ✅ Configurable │ ✅ Built-in
PHP-FPM Tuning              │ ❌ Fixed pool  │ ✅ Configurable │ ✅ Managed
Database Queue              │ ✅ Available   │ ✅ Available    │ ✅ Built-in
Redis Cache/Session/Queue   │ ❌ Rarely      │ ✅ Installable  │ ✅ Built-in
Laravel Octane (Swoole)     │ ❌ Not possible│ ✅ Installable  │ ❌ Not needed
```

**Rule of thumb:** Tiers 1+2 give 80% of the performance gain with 20% of the effort. Tier 3 is only needed if specific pages profile as slow after those are in place.

---

## L. PHASE 26 — SILENT BACKGROUND POLLING ✅

Completed July 13, 2026. All background network activity hidden from the UI.

| # | Item | Detail | Status |
|---|------|--------|--------|
| 26.1 | **Reduced all polling intervals** | Bell 15s→10s, Attendance 12s→10s, Compliance 30s→10s, Journals 30s→10s, Portal 15s→10s, Nav 15s→10s/30s→10s, KPI 60s→30s | ✅ |
| 26.2 | **Removed HTMX green gradient bar** | Deleted `.htmx-request.htmx-target-indicator::after` from custom.css. Removed JS class adder from app.js. No loading bar on any HTMX request. | ✅ |
| 26.3 | **All background polling silent** | Zero visible UI indicators on any timed refresh. | ✅ |
| 26.4 | **Button spinners preserved** | User-initiated actions (clock-in, upload, approve, submit) still show loading states. | ✅ |
| 26.5 | **Red badge counters preserved** | Unread messages, queue count, notification count remain visible. | ✅ |
| 26.6 | **Toast notifications preserved** | Success/error feedback still shows via toasts. | ✅ |

---

## M. PHASE 27 — PRODUCTION OPTIMIZATION ✅

Completed July 13, 2026. Model caching, Vite cleanup, input masks, test suite hardening.

| # | Item | Detail | Files Changed | Status |
|---|------|--------|--------------|--------|
| 27.1 | **Setting model caching** | `Setting::campus()` now uses `cache()->rememberForever()` instead of hitting DB on every request. `flushCampusCache()` called automatically on any settings update. | `app/Models/Setting.php`, `CampusSettingController.php` | ✅ |
| 27.2 | **RequiredDocument caching** | `cachedMandatoryIds()` and `cachedOrdered()` cache forever. All Student model methods and key controllers filter from memory instead of DB. Flushed on create/update/delete. | `app/Models/RequiredDocument.php`, `app/Models/Student.php`, `StudentPortalController.php`, `RequiredDocumentController.php` | ✅ |
| 27.3 | **Vite asset cleanup** | Removed 578KB of orphaned build artifacts (duplicate app.js + app.css). Kept only the 5 files referenced by current manifest. | `public/build/assets/` cleanup | ✅ |
| 27.4 | **Phone + student number input masks** | Real-time formatting as user types. Phone: `+63 ___ ____ ___`. Student number: `__-____-_______-_`. Applied to all 5 editable fields. | `resources/js/app.js`, 4 Blade files | ✅ |
| 27.5 | **Test suite hardened** | Fixed 5 pre-existing test failures in ChairpersonNavigationTest, EmployerDeploymentAccessTest, MessageThreadModuleTest. Suite now 103/103 passing. | `routes/web.php`, `InternshipRoles.php`, `MessageThreadModuleTest.php` | ✅ |
| 27.6 | **"Read more" truncation** | Line-clamp with "Read more" link on announcement bodies longer than 200 chars. | `student/dashboard.blade.php` | ✅ |

## N. TEST RESULTS — Complete Suite

```
Tests:    103 passed, 1 skipped (336 assertions)
Failures:  0
```

| Phase | Total Checks | ✅ Passed |
|-------|-------------|-----------|
| Infrastructure | 4 | 4 |
| Student Portal | ~30 | 30 |
| Instructor | ~50 | 50 |
| Chairperson | ~12 | 12 |
| Dean | ~12 | 12 |
| HTE & Exports | ~18 | 18 |
| Regression | ~25 | 25 |
| **Total** | **~151** | **151** |

---

*Document generated July 13, 2026 — Post-Defense + Phase 25-27 Complete*
*v2 reflects all 10 Phase 25 panel revisions, Phase 26 silent background polling, and Phase 27 production optimization.*
