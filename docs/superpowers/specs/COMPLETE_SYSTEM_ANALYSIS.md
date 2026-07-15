# OJT Internship Management System — Complete Business Analysis

> **System Name**: OJT Internship Management System  
> **Version**: 1.0  
> **Platform**: Web-based (Laravel 12 + MySQL + Tailwind CSS + Alpine.js)  
> **Target Users**: BSIS 4th Year Students, OJT Coordinators (Instructors), Chairperson, Dean

---

## SECTION 1: SYSTEM OVERVIEW

### 1.1 Purpose

The OJT Internship Management System is a web-based platform designed to manage the entire lifecycle of student internships for the BSIS program. It replaces manual paperwork and fragmented communication with a centralized digital system that handles student deployment, document compliance, attendance monitoring, weekly journaling, performance evaluation, and reporting.

### 1.2 Scope

The system covers 17 major functional areas:

- **Student Registry**: Import and manage all BSIS 4th-year students from Registrar data
- **Company Partnership**: Maintain a directory of partner companies with full address and contact details
- **Deployment Management**: Assign students to companies with start/end dates and status tracking
- **Document Compliance**: Define required internship documents, track student submissions, and manage multi-step review workflows
- **Attendance Monitoring**: Geofence-based check-in system with real-time location validation
- **Weekly Journals**: Student submits weekly activity reports with task descriptions and supporting files
- **Daily Time Records (DTR)**: Auto-populated from attendance data with monthly DTTR exports
- **Certificates**: Upload, verify, and distribute internship completion certificates
- **Evaluations**: Industry, school, and student feedback evaluations with numerical scoring
- **External HTE Transactions**: One-time token-based links for company supervisors to evaluate and upload forms without login
- **Announcements**: Role-targeted announcements visible to students and staff
- **Messaging**: Internal threaded communication between students and staff
- **Reports & Analytics**: Compliance summaries, deployment locations, attendance exports
- **Document Workflow**: Multi-step review chain (Student → Instructor → Chairperson)
- **Document Forwarding**: Batch transmittal of physical documents with acknowledgment tracking
- **Dashboard**: Role-specific KPI dashboards for instructors, chairperson, and dean
- **System Configuration**: Campus geofence settings, semester tracking, maintenance mode

### 1.3 Objectives

1. Centralize all internship-related data in one digital platform
2. Automate document review workflows to reduce processing time
3. Provide real-time attendance tracking with geofence validation
4. Enable students to submit weekly journals and DTRs online
5. Give instructors full visibility into student compliance and progress
6. Generate reports for institutional monitoring and accreditation purposes
7. Facilitate communication between students, instructors, and company supervisors
8. Support the entire internship lifecycle from pre-deployment to completion

### 1.4 Stakeholders

| Stakeholder | Interest |
|---|---|
| Students (BSIS 4th Year) | Submit documents, log attendance, write journals, view grades, communicate with staff |
| Instructors (OJT Coordinators) | Manage students, companies, deployments, review documents, monitor attendance, generate reports |
| Chairperson | Oversee program compliance, review forwarded documents in workflow queue |
| Dean | View compliance summaries and evaluation analytics for departmental oversight |
| Company Supervisors | Evaluate students via HTE links, sign documents (no login required) |
| Registrar | Provides official student data for bulk import into the system |
| System Administrator | Maintains system configuration, user accounts, and server infrastructure |

### 1.5 User Roles

#### Student
- Logs in using student number + password + OTP (email-based)
- Accesses limited portal (documents + announcements) until deployment is active AND mandatory documents are complete
- Accesses full portal after deployment activation
- Submits required documents (PDF, DOC, DOCX) with automatic workflow initialization
- Views role-targeted announcements
- Submits weekly journals with auto-save task descriptions and inline file attachments
- Views daily time records auto-populated from attendance check-ins
- Downloads issued certificates
- Sends/receives messages with staff members
- Changes password on first login (enforced)
- Views OJT grade summary on dashboard
- Submits internship feedback evaluation (once per deployment)
- Checks in/out daily using attendance passcode and GPS geolocation

#### Instructor (OJT Coordinator)
- Full CRUD access to students, companies, deployments, required documents
- Imports students and companies from Excel/CSV files
- Reviews and approves/forwards/rejects student documents through multi-step workflow
- Monitors attendance records and resolves flagged geofence entries
- Reviews weekly journals (mark as reviewed only — no reject/return)
- Views DTRs and certificates for all students
- Creates and manages announcements
- Creates evaluations and sends HTE links to company supervisors
- Manages document forwarding batches for physical document transmittal
- Configures campus geofence settings (coordinates, radius, buffer)
- Views all reports and dashboard KPIs

#### Chairperson
- Views students, attendance, evaluations (read-only access)
- Reviews documents forwarded by instructors in the workflow queue
- Can approve or return for revision
- Creates and manages announcements
- Views compliance reports
- Restricted sidebar: companies, deployments, required-documents, weekly-journals, DTR, certificates are hidden

#### Dean
- Views dashboard with compliance and evaluation analytics
- Cannot see students, companies, deployments, required-documents, attendance, weekly-journals, DTR, certificates, or workflow-queue in sidebar
- High-level oversight role

### 1.6 Major Modules

| Module | Description |
|---|---|
| **Authentication & Access** | Staff login (email + password), Student login (student number + password + OTP), password reset, first-login password change, session management with 30-min idle timeout |
| **Dashboard** | Role-specific KPI cards: instructor sees student counts + company stats, chairperson sees pending queue + compliance, dean sees compliance + evaluation averages |
| **Student Management** | CRUD operations, Excel/CSV import with validation, profile view with document progress and journal progress percentages |
| **Company Management** | CRUD operations, Excel/CSV import with auto-geocoding via OpenStreetMap, industry classification |
| **Deployment Management** | Assign students to companies, auto-compute status (pending/active/completed) based on dates, section conflict prevention |
| **Document Management** | Define required documents with phases (Pre/Deployment/Monitoring/Post), student uploads, multi-step workflow review (Instructor → Chairperson) with DocumentWorkflowEngine |
| **Document Forwarding** | Batch scheduling and acknowledgment tracking for physical document transmittal between offices |
| **Attendance Monitoring** | Public check-in page, 6-digit passcode, GPS-based geofence validation with 10-meter rule, time-out tracking, DTR auto-population, flagged record resolution |
| **Weekly Journals** | Auto-generated weeks from deployment start date, Mon–Sat activity editor with AJAX auto-save, inline file uploads, late flag computation, instructor review (mark as reviewed only) |
| **Daily Time Records** | Auto-populated from attendance daily, monthly DTTR export, signed DTTR upload for archival |
| **Certificates** | Upload by instructor (PDF), verify, student view and download |
| **Evaluations** | Three types: Industry (supervisor via HTE link), School (instructor), Student Feedback (student). Score 1-100 with comments. Duplicate prevention per deployment period. |
| **External HTE Transactions** | One-time token links emailed to company supervisors. No login required. Supports evaluation submission and document upload. Token expires after use or after 7 days. |
| **Announcements** | Role-targeted visibility (all, student, instructor, chairperson, dean). Instructors and chairpersons can create/manage. |
| **Messaging** | Internal threaded messaging between students and staff. Read/unread tracking, archiving, participant management. |
| **Reports & Analytics** | Deployed per company, missing documents, compliance summary, deployment locations (map), attendance export (CSV) |
| **System Settings** | Campus GPS coordinates, geofence radius/buffer, campus boundary, semester/year, maintenance mode toggle |

### 1.7 Functional Requirements

| ID | Requirement |
|---|---|
| FR-01 | System shall support two separate authentication systems: staff (email+password via Laravel web guard) and student (student_number+password+OTP via custom session) |
| FR-02 | System shall enforce 30-minute idle timeout for student sessions (tracked via `studentLatestActivity` timestamp) |
| FR-03 | System shall restrict student portal access based on deployment status and mandatory document completion (`hasFullStudentPortalAccess()`) |
| FR-04 | System shall prevent duplicate student records by `student_number` (update existing record on import) |
| FR-05 | System shall auto-compute deployment status (pending/active/completed) based on dates and document completion |
| FR-06 | System shall enforce 10-meter geofence rule for attendance check-in with four statuses: `inside_pass`, `near_boundary_review`, `outside_flagged`, `location_unavailable` |
| FR-07 | System shall allow one check-in per student per day (duplicate prevention via unique constraint on student_id + date) |
| FR-08 | System shall auto-generate weekly journal entries from deployment start date to today/end date |
| FR-09 | System shall auto-calculate late flag on weekly journals as a computed attribute (`submitted_at > week_end_date`) |
| FR-10 | System shall auto-populate Daily Time Records from attendance check-in/out data |
| FR-11 | System shall prevent duplicate evaluation submissions per active deployment period |
| FR-12 | System shall support multi-step document review workflow with states: received → under_review → pending_review → completed/rejected/for_revision |
| FR-13 | System shall generate one-time HTE transaction tokens for external company supervisor access (no login required) |
| FR-14 | System shall enforce first-login password change for both staff and students |
| FR-15 | System shall auto-generate 6-digit attendance passcodes for deployed students, refreshed on each dashboard visit |

### 1.8 Non-Functional Requirements

| ID | Requirement |
|---|---|
| NFR-01 | System shall respond to page requests within 3 seconds under normal load |
| NFR-02 | System shall support up to 500 concurrent users |
| NFR-03 | System shall use MySQL 8+ for data persistence with Laravel Eloquent ORM |
| NFR-04 | System shall store uploaded files on `public` disk with `storage` symlink for web access |
| NFR-05 | System shall send email notifications via Laravel queue system (database queue driver) |
| NFR-06 | System shall enforce CSRF protection on all state-changing POST/PUT/DELETE requests |
| NFR-07 | System shall throttle login attempts, OTP requests, and HTE submissions to prevent brute force |
| NFR-08 | System shall support file uploads up to 10MB for imports (Excel) and 2MB for document submissions |
| NFR-09 | System shall log all application errors to `storage/logs/laravel.log` with stack trace and user context |
| NFR-10 | System shall support responsive design for mobile attendance check-in page |
| NFR-11 | System shall use HTTPS for all communications in production |
| NFR-12 | System shall maintain strict session isolation between staff and student sessions (mutually exclusive) |
| NFR-13 | System shall use Bcrypt hashing for all stored passwords (cost factor 12) |
| NFR-14 | System shall operate on PHP 8.2+ and support Windows/Linux environments |

---

## SECTION 2: COMPLETE BUSINESS PROCESS

### End-to-End Internship Lifecycle

---

**STEP NUMBER**: 1  
**PROCESS NAME**: Registrar Data Import (Bulk Student Creation)

- **Actor**: Instructor (OJT Coordinator)
- **Trigger**: Start of academic year, enrollment period, or need to onboard new batch
- **Input**: Excel file (.xlsx, .xls, .csv, max 10MB) with columns: student_number, last_name, first_name, middle_name, name_extension, section, contact_number, email, company, start_date
- **Process**: Instructor navigates to Students → Import, selects the Excel file, and clicks "Import". The system parses the file using Maatwebsite/Laravel-Excel. Each row is validated individually. For valid rows, the system either creates a new Student + StudentAccount pair or updates an existing record (matched by student_number). Student accounts are created with password = student_number (Bcrypt hashed, first_login = true). If a company column is provided, the system looks up or creates the Company and automatically creates a Deployment record linking student to company with the specified start_date.
- **System Actions**: 
  - Validates file format and size (mimes:xlsx,xls,csv, max:10240KB)
  - Normalizes student_number (strips non-digits, checks 8-digit length)
  - Normalizes contact_number (strips non-digits, defaults to '09123456789' if invalid)
  - Creates/updates Student records via `Student::create()` / `$existing->fill()->save()`
  - Creates StudentAccount with `StudentAccount::create(['student_id' => $student->id, 'email' => ..., 'password' => Hash::make($studentNumber), 'is_active' => true, 'first_login' => true])`
  - For company column: `Company::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($companyName)])->first()` then creates if not found, then `Deployment::create()` if not already exists
  - Tracks counters: createdCount, updatedCount, skippedCount, duplicateNameWarnings, deploymentsCreated
  - Uses `SkipsErrors` trait to silently skip PHP exceptions (prevents single bad row from crashing entire import)
  - Uses `set_time_limit(0)` to prevent timeout on large files
- **Database Actions**: INSERT/UPDATE students, INSERT student_accounts, SELECT/INSERT/UPDATE companies, INSERT deployments
- **Output**: Success/failure JSON response with counts. Modal shows: "Students imported successfully: 150 created, 5 updated, 3 skipped." and optionally "2 deployment(s) created." If duplicate names found: "12 student(s) share a name with existing records — please verify."
- **Next Process**: Manual student management (edits, corrections) → Document requirement definition → Deployment setup

---

**STEP NUMBER**: 2  
**PROCESS NAME**: Company Data Import (Bulk Partner Company Creation)

- **Actor**: Instructor
- **Trigger**: Need to bulk-add partner company records
- **Input**: Excel file (.xlsx, .xls, .csv, max 10MB) with columns: name, street_address, barangay, city_municipality, contact_last_name, contact_first_name, contact_middle_initial, contact_name_extension, contact_email, contact_phone, company_industry, notes, geofence_radius_meters
- **Process**: Instructor navigates to Companies → Import, selects the Excel file. System parses each row, validates required fields (name, street_address, barangay, city_municipality), and for each valid company:
  1. Geocodes the full address via OpenStreetMap Nominatim API (rate-limited to 1 request/sec via usleep)
  2. Looks up CompanyIndustry by name (case-insensitive)
  3. Creates new Company or updates existing (matched by LOWER(TRIM(name)))
  4. Sets GPS coordinates from geocoding result (or null if geocoding failed)
- **System Actions**: Requires `set_time_limit(0)` for large files. Geocoding failures increment `geocodeFailures` counter and are reported as warnings.
- **Database Actions**: INSERT/UPDATE companies
- **Output**: "Companies imported: 20 created, 3 updated, 1 skipped." + "2 address(es) could not be geocoded — you can set coordinates on the edit form."
- **Next Process**: Company management → Create deployments linking students to companies

---

**STEP NUMBER**: 3  
**PROCESS NAME**: Manual Student/Company/Deployment Management

- **Actor**: Instructor
- **Trigger**: Need to add, edit, or remove individual records outside of bulk import
- **Input**: Form data for student details, company details, or deployment assignments
- **Process**: Instructor navigates to the relevant module (Students, Companies, or Deployments) and performs CRUD operations. System validates input data, checks unique constraints, and processes accordingly. For deployments, the system auto-computes status:
  - `pending`: start_date is in the future
  - `active`: start_date <= today AND all mandatory documents submitted
  - `completed`: end_date has passed
- **System Actions**: 
  - Student name is auto-composed from name parts via `composeDisplayName()` on `saving` event
  - Delete operations check `HasDeleteProtection` trait for related records
  - Deployments check section conflicts (same section cannot have overlapping deployments at same company)
- **Database Actions**: INSERT/UPDATE/DELETE on respective tables
- **Output**: Redirect to list view with success/error flash message
- **Next Process**: Document requirement definition → Student portal setup

---

**STEP NUMBER**: 4  
**PROCESS NAME**: Define Required Document Catalog

- **Actor**: Instructor
- **Trigger**: Need to establish the master document checklist for the internship program
- **Input**: Document name, description, mandatory flag (boolean), order_index (for display sorting), workflow_template_id (optional, for review routing), company_id (optional, for company-specific requirements), phase (pre/deployment/monitoring/post), submission_deadline_at (optional)
- **Process**: Instructor navigates to Required Documents page, creates new document entries with phase classification. Each document can be linked to a workflow template (which defines the review chain: e.g., Step 1 = Instructor Review → Step 2 = Chairperson Approval). Documents can be globally required (all students must submit) or company-specific.
- **System Actions**: Creates required_document record. Validates phase against allowed values.
- **Database Actions**: INSERT required_documents
- **Output**: Document added to master checklist
- **Next Process**: Student begins document submission

---

**STEP NUMBER**: 5  
**PROCESS NAME**: Student Login & First-Time Access (OTP Verification)

- **Actor**: Student
- **Trigger**: Student receives student number and default password from instructor/import
- **Input**: Student number, password (default = student number)
- **Process**: 
  1. Student navigates to the login page
  2. Selects the student login option
  3. Enters student number and password
  4. System validates against `student_accounts` table
  5. On success, generates a random 6-digit OTP and sends it to the student's registered email via `StudentPortalOtpMail`
  6. Displays OTP entry form
  7. Student checks email, copies OTP, enters it
  8. System verifies OTP (single-use, case-sensitive)
  9. On success, creates session with `student_account_id` and sets `studentLatestActivity`
  10. Checks `first_login` flag:
      - If true, redirects to password change page
      - Student enters new password (min 8 chars, confirmed)
      - System hashes and saves, marks `first_login = false`
  11. Checks `hasFullStudentPortalAccess()`:
      - If true → redirect to student dashboard
      - If false → redirect to documents page (limited mode)
- **System Actions**: Staff and student sessions are mutually exclusive — student login logs out any staff session. `EnsureStudentSession` middleware checks 30-min idle timeout on every request. Passcode for attendance is auto-generated via `ensureAttendancePasscode()`.
- **Decision Points**:
  - Is student account active (`is_active = true`)? If not, show "Account is disabled"
  - Is OTP correct? If not, show error (max attempts throttled)
  - Is first login? Force password change
  - Is full portal access granted? Route to dashboard or documents page
- **Database Actions**: SELECT student_accounts (validation), SELECT/UPDATE sessions
- **Output**: Student dashboard or documents page (limited mode)
- **Next Process**: Document submission → Attendance check-in → Weekly journals

---

**STEP NUMBER**: 6  
**PROCESS NAME**: Student Document Submission

- **Actor**: Student
- **Trigger**: Student needs to submit a required document as part of internship compliance
- **Input**: File (PDF, DOC, or DOCX format, max 2MB)
- **Process**: 
  1. Student navigates to Documents page in their portal
  2. Views the complete checklist of all required documents with status indicators: Submitted (green), Pending (yellow), Missing (red)
  3. Clicks the upload button for a specific required document
  4. Selects a file from their device
  5. System validates file MIME type (application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document) and extension (pdf, doc, docx)
  6. If document is company-scoped, verifies student's active deployment company matches
  7. Stores file to `student-documents/{student_id}/` on the public disk
  8. Creates or updates StudentDocument record via `updateOrCreate()`
  9. Checks for resubmission after rejection — if previous workflow_status was 'rejected', resets workflow fields to null
  10. Calls `DocumentWorkflowEngine::initialize()` to start the review chain
  11. If workflow template exists: sets first step, current_holder_role, next_step_role, workflow_status = 'received'
  12. Sends email notification to current holder role (typically instructor)
  13. Calls `Deployment::checkAndActivateForStudent()` to potentially activate deployment
- **System Actions**: File validation, storage, workflow initialization, email notification, deployment activation check
- **Decision Points**:
  - Is document scoped to a specific company? Verify match with student's active deployment
  - Is this a resubmission after rejection? Reset workflow to step 1
  - Does workflow template exist? Initialize multi-step chain or simple direct review
  - Are all mandatory documents now submitted? Try to activate deployment
- **Database Actions**: INSERT/UPDATE student_documents, INSERT student_document_actions, UPDATE deployments (if activated)
- **Output**: Document card updates to show "Pending" status. Instructor notified via email.
- **Next Process**: Workflow review by instructor/chairperson

---

**STEP NUMBER**: 7  
**PROCESS NAME**: Document Workflow Review (Instructor → Chairperson chain)

- **Actor**: Instructor (first step), Chairperson (second step)
- **Trigger**: Student submits document OR document is forwarded from previous step
- **Input**: Review action (review, approve, forward, return_for_revision, sign), optional note
- **Process**:
  **Instructor Phase**:
  1. Instructor receives email notification: "[Student] submitted [Document]"
  2. Opens Workflow Queue page
  3. Sees documents pending review with student name, document type, date
  4. Clicks on a document
  5. System auto-starts review: transitions from 'received' to 'under_review'
  6. Instructor views the uploaded file (download/preview) and action history
  7. Available actions appear based on current workflow state:
     - From 'received': Review (acknowledge, start examining)
     - From 'under_review': Approve (final), Forward (to chairperson), Return for Revision (to student with notes)
  8. Instructor selects action, optionally enters note, clicks confirm
  9. System executes action:
     - Approve: workflow_status = 'completed', student status = 'Submitted'
     - Forward: current_step_order++ (moves to chairperson step), workflow_status = 'pending_review', email notifies chairperson
     - Return for Revision: reverts to previous step or stays at step 1, workflow_status = 'for_revision', student status = 'Missing', email notifies student
  10. Action is logged in student_document_actions

  **Chairperson Phase** (if forwarded):
  1. Chairperson receives email notification
  2. Opens Workflow Queue, sees forwarded document
  3. Reviews document, can Approve (final) or Return for Revision (back to instructor/student)
  4. Approval completes the document permanently
- **System Actions**: 
  - `DocumentWorkflowEngine::allowedActions()` checks current state + role to determine available actions
  - `DocumentWorkflowEngine::applyAction()` executes the action in a DB transaction, updates all workflow fields, syncs student-facing status
  - Email notifications sent to appropriate parties
- **Decision Points**:
  - Is the actor the current holder? If not, deny (403)
  - Is the action allowed at this state? If not, throw InvalidArgumentException
  - Is the action 'return_for_revision'? Note is required
  - Is the action 'forward'? Is there a next step? If not, auto-approve
- **Database Actions**: UPDATE student_documents (workflow fields, status), INSERT student_document_actions
- **Output**: Document status updated. Email notification to next holder or student.
- **Next Process**: If completed → Student notified. If returned → Student resubmits (cycle back to Step 6).

---

**STEP NUMBER**: 8  
**PROCESS NAME**: Attendance Check-In (Daily)

- **Actor**: Student (must be deployed with active status)
- **Trigger**: Student arrives at company for daily internship
- **Input**: 6-digit attendance passcode, GPS coordinates (from browser geolocation API)
- **Process**:
  1. Student opens the public check-in page at `/attendance/check-in` (no login required; accessible from any device)
  2. Browser requests GPS location permission
  3. Student enters their 6-digit attendance passcode (visible on student dashboard, auto-generated via `ensureAttendancePasscode()`)
  4. System validates passcode:
     - Looks up student by passcode (LOWER(attendance_passcode) match)
     - Checks student has active deployment
     - Checks duplicate: no existing check-in for today
  5. System computes distance between student's GPS location and company's coordinates using Haversine formula
  6. Geofence decision tree:
     - GPS unavailable (lat/lng null) → `location_unavailable`, `review_required = true`
     - Distance ≤ company.geofence_radius → `inside_pass`, `review_required = false`
     - Distance ≤ (geofence_radius + geofence_review_buffer) → `near_boundary_review`, `review_required = true`
     - Distance > buffer → `outside_flagged`, `review_required = true`
  7. Attendance record is created with all location data, geofence status, timestamps, accuracy, and distance
  8. DTR is auto-populated via DailyTimeRecord creation
- **System Actions**: Passcode validation, geofence computation, attendance recording, DTR sync
- **Decision Points**:
  - Is passcode valid? Show "Invalid passcode" if not
  - Is student already checked in today? Show "Already checked in today" (duplicate prevention)
  - Is GPS within geofence? inside_pass vs flagged statuses
  - Is campus boundary check needed? Additional `is_within_campus` flag for school-based scenarios
- **Database Actions**: INSERT attendances, INSERT/UPDATE daily_time_records
- **Output**: Check-in confirmation showing time, geofence status, and company name
- **Next Process**: Time-out (end of day)

---

**STEP NUMBER**: 9  
**PROCESS NAME**: Time-Out (Daily)

- **Actor**: Student
- **Trigger**: Student leaves company at end of work day
- **Input**: (Optional) GPS location for time-out geofence verification
- **Process**: Student returns to check-in page, clicks "Time Out". System records departure time, optionally captures location for geofence verification. Computes total minutes worked (time_out - time_in). Updates the attendance record and DTR entry.
- **System Actions**: Updates attendance with time_out_at, total_minutes, time_out_location data. Updates DailyTimeRecord for the day.
- **Decision Points**: Must have valid check-in for today (cannot time-out without checking in first)
- **Database Actions**: UPDATE attendances, UPDATE daily_time_records
- **Output**: Time-out confirmation with total hours worked
- **Next Process**: DTR export (weekly/monthly)

---

**STEP NUMBER**: 10  
**PROCESS NAME**: Weekly Journal Auto-Generation & Submission

- **Actor**: Student (weekly activity)
- **Trigger**: Student opens Weekly Journals page
- **Input**: Task descriptions per day (Monday through Saturday), file attachments (PDF, DOC, DOCX, JPG, PNG)
- **Process**:
  1. Student navigates to Weekly Journals in their portal
  2. System calls `ensureWeeksExist()`: checks if week entries are needed based on deployment start date
     - Finds latest deployment with active/completed status
     - Determines cursor: last existing week's end + 1 day (or deployment start date if no weeks exist)
     - Determines end: deployment end_date or today
     - Loops while cursor ≤ end, creating WeeklyJournal records with sequential week numbers, Mon-Sun date ranges, status = 'draft'
  3. Student sees a grid of week cards with week number, date range, and status badge
  4. Student clicks on a draft week to open the editor (Alpine.js SPA)
  5. Editor shows six rows (Mon–Sat) with date fields and task description textareas
  6. Student types tasks → auto-saved via AJAX PATCH on blur (no Save button)
  7. Student can upload files per day via inline AJAX upload button
  8. Supervisor name auto-filled from company contact (read-only)
  9. When ready, student clicks Submit → Alpine modal asks for confirmation
  10. On confirm, status changes to 'submitted', `submitted_at = now()`
  11. Late flag auto-computed (displayed to instructor): `submitted_at > week_end_date`
- **System Actions**: Week generation, AJAX auto-save, file storage, late flag computation
- **Decision Points**:
  - Is student deployed? Only generate weeks for active/completed deployments
  - Are weeks already generated? Resume from last week; only generate new ones
  - Is journal editable? Only draft journals can be edited/submitted
  - Is submission after week end? Auto-set is_late flag (computed, not stored)
- **Database Actions**: SELECT/INSERT weekly_journals (generation), UPDATE weekly_journals (activities, files, status, submitted_at)
- **Output**: Journal submitted. Status changes to 'submitted'.
- **Next Process**: Instructor reviews the submitted journal

---

**STEP NUMBER**: 11  
**PROCESS NAME**: Weekly Journal Review (Instructor)

- **Actor**: Instructor
- **Trigger**: Student submits weekly journal
- **Input**: Review action (Mark as Reviewed), optional remarks (max 1000 chars)
- **Process**: 
  1. Instructor opens Weekly Journals page (staff view)
  2. Filterable list with search (student name/number), status filter (Draft/Submitted/Reviewed), section filter
  3. List shows week number, student info (number + name), section, company, status badge, late badge, action menu
  4. Click a journal → staff show view
  5. View shows: Mon–Sat activities table with task text and file download links, supervisor info, late flag (if applicable), submitted timestamp, review panel
  6. Instructor reads entries, enters optional remarks
  7. Clicks "Mark as Reviewed"
  8. System updates: status = 'reviewed', reviewed_by = Auth::id(), reviewed_at = now(), remarks saved
  9. Email notification to student: "Your weekly journal for Week X has been reviewed."
- **System Actions**: No reject/return — instructor only marks as reviewed. Student owns the journal.
- **Decision Points**: Is journal already reviewed? If yes, cannot review again.
- **Database Actions**: UPDATE weekly_journals (status, reviewed_by, reviewed_at, remarks)
- **Output**: Journal status changed to 'reviewed'. Student notified via email.
- **Next Process**: None (completion of week's journal cycle)

---

**STEP NUMBER**: 12  
**PROCESS NAME**: Company Supervisor Evaluation via HTE Link

- **Actor**: Instructor (initiates), Company Supervisor (submits)
- **Trigger**: Instructor wants company supervisor to evaluate student
- **Input**: 
  - Instructor: Student, Company, Supervisor name, Supervisor email
  - Supervisor: Score (1-100), Comments, (optional) uploaded document
- **Process**:
  **Instructor Phase**:
  1. Instructor navigates to Evaluations → Send HTE Link
  2. Selects student and company (auto-filled from deployment)
  3. Enters supervisor name and email
  4. System generates unique one-time token (random 64-char hex, URL-safe)
  5. Creates `hte_transaction_link` record with token, student_id, company_id, supervisor info, expires_at (7 days from now)
  6. Sends email to supervisor with the link: "Please evaluate [Student]'s performance at [Company]"

  **Supervisor Phase**:
  1. Supervisor clicks the link in email (no login required)
  2. Server validates: token exists? not used (used_at is null)? not expired (expires_at > now)?
  3. If valid → shows evaluation form with student name, company name, score field, comments field, optional file upload
  4. Supervisor fills out score (1-100) and comments, optionally uploads signed document
  5. Submits form
  6. System creates evaluation record (evaluation_type = 'industry')
  7. Marks token as used (used_at = now())
  8. Sends confirmation to supervisor
  9. Optionally notifies instructor of new evaluation
- **System Actions**: Token generation, email sending, evaluation creation, token invalidation
- **Decision Points**:
  - Is token valid? Show form if valid
  - Is token already used? Show "This link has already been used"
  - Is token expired? Show "This link has expired"
- **Database Actions**: INSERT hte_transaction_links, INSERT evaluations, UPDATE hte_transaction_links (used_at)
- **Output**: Evaluation recorded. Supervisor sees confirmation. Instructor optionally notified.
- **Next Process**: Instructor reviews evaluation (optional)

---

**STEP NUMBER**: 13  
**PROCESS NAME**: Certificate Issuance and Verification

- **Actor**: Instructor (uploads), Student (views/downloads)
- **Trigger**: Student completes internship and is eligible for certificate
- **Input**: Certificate file (PDF), type, title, description
- **Process**: 
  **Instructor Phase**:
  1. Instructor navigates to Certificates → Create
  2. Selects student, enters type (e.g., Completion), title, description
  3. Uploads PDF certificate file
  4. System stores file, creates certificate record
  5. Optionally, instructor can verify the certificate (marks as verified)

  **Student Phase**:
  1. Student opens Certificates in their portal
  2. Sees list of their certificates with status (verified/unverified)
  3. Clicks to view or download certificate file
- **System Actions**: File storage, student ownership verification on download (abort 403 if not owner)
- **Database Actions**: INSERT certificates, UPDATE certificates (verification)
- **Output**: Certificate available in student portal for download
- **Next Process**: Student downloads certificate

---

**STEP NUMBER**: 14  
**PROCESS NAME**: Daily Time Record Management

- **Actor**: Student (views/exports/uploads)
- **Trigger**: Need to view or export attendance summary
- **Input**: Month/Year selection (export), signed DTTR file (upload)
- **Process**:
  1. Student opens DTR page in portal
  2. Views daily records auto-populated from attendance check-in/out data
  3. Each record shows: date, time_in, time_out, total_minutes, source (attendance or manual)
  4. Student can filter by month
  5. Student clicks Export → system generates monthly DTTR summary (spreadsheet export)
  6. Student gets the DTTR signed by company supervisor (offline)
  7. Student uploads the signed DTTR PDF back to system for archival
- **System Actions**: Aggregate daily records by month, generate export, store signed upload
- **Database Actions**: SELECT daily_time_records (view), INSERT monthly_dttrs (signed upload)
- **Output**: Monthly DTTR export (file), signed DTTR stored in system
- **Next Process**: None (archival)

---

## SECTION 3: DETAILED PROCESS BREAKDOWN

### Module 1: Authentication & Access

#### Subprocess 1.1: Staff Login
- **Actor**: Staff (Instructor, Chairperson, Dean)
- **Input**: Email, Password
- **Trigger**: Staff navigates to login page
- **Process**: Staff enters email and password. System validates against `users` table using Laravel's built-in authentication with `Auth::attempt()`. Checks email verification status. Checks `first_login` flag. On success, creates authenticated web guard session and redirects to dashboard. On first login, redirects to password change page.
- **Database Tables**: users
- **Business Rules**: Staff sessions use Laravel `web` guard. Email must be verified (`email_verified_at` not null). First-login flag forces password change.
- **Validation Rules**: Email required + valid format. Password required + min 8 characters.
- **Exception Handling**: Invalid credentials → "These credentials do not match our records." Email not verified → redirect to `verification.notice`. First login → redirect to `staff.password.change`.

#### Subprocess 1.2: Student Login
- **Actor**: Student
- **Input**: Student Number, Password
- **Trigger**: Student navigates to login page and selects student login option
- **Process**: Student enters student number and password. System looks up `student_accounts` table via StudentAccount model. Validates password using `Hash::check()`. On success, generates random 6-digit OTP and stores it in session (not persistent), sends email via `StudentPortalOtpMail`. Student enters OTP. System verifies match. On successful OTP verification, creates session with `student_account_id`. Staff session is logged out if one exists (mutual exclusivity).
- **Database Tables**: student_accounts, students
- **Business Rules**: Student sessions managed separately from staff sessions. 30-minute idle timeout enforced by `EnsureStudentSession` middleware. OTP is single-use, sent to registered email. `student_account_id` is stored in session (not Laravel's auth).
- **Validation Rules**: Student number required (8 digits). Password required.
- **Exception Handling**: Invalid credentials → "Invalid student number or password." Inactive account → "Account is disabled. Contact your coordinator." OTP mismatch → "Invalid verification code."

#### Subprocess 1.3: First Login Password Change
- **Actor**: Student or Staff
- **Trigger**: `first_login` flag is true after successful authentication
- **Input**: Current password, New password (min 8 chars), New password confirmation
- **Process**: 
  - **Student**: Redirects to `/student/password/change`. Form submits POST with password + password_confirmation. Validates min:8, confirmed. Updates `student_accounts.password` with bcrypt(). Calls `markFirstLoginComplete()` to set `first_login = false`. Redirects to dashboard or documents page based on portal access.
  - **Staff**: Redirects to `/password/change`. Same flow but updates `users.password`.
- **Database Tables**: student_accounts (student), users (staff)
- **Business Rules**: Password minimum 8 characters. Password confirmation required. `first_login` set to false after successful change.
- **Validation Rules**: Password required, string, min:8, confirmed.
- **Exception Handling**: Validation errors shown inline.

#### Subprocess 1.4: Logout
- **Actor**: All users
- **Trigger**: User clicks Logout button
- **Input**: CSRF token (POST request)
- **Process**: 
  - **Staff**: `Auth::logout()` clears web guard session, invalidates session, regenerates CSRF token.
  - **Student**: `session()->forget('student_account_id')` removes student session marker, regenerates session.
- **Database Tables**: None
- **Business Rules**: Staff and student sessions are independent.
- **Validation Rules**: CSRF token required.
- **Exception Handling**: N/A

---

### Module 2: Student Management

#### Subprocess 2.1: Import Students (Bulk)
- **Actor**: Instructor
- **Input**: Excel file (.xlsx, .xls, .csv, max 10MB)
- **Trigger**: Start of semester or need to populate student records
- **Process**: Upload file via FormData AJAX call. `StudentsImport` class (implements `ToModel`, `WithHeadingRow`, `WithValidation`, `SkipsOnFailure`) processes each row:
  1. Sanitize student_number: strip non-digits, ensure 8 digits
  2. Sanitize contact_number: strip non-digits, ensure 11 chars starting with 09
  3. Look up existing student by student_number
  4. If exists: fill + save update. If account exists, update email. Otherwise: create Student + StudentAccount (first_login = true, password = student number hashed)
  5. If company column present: find or create company → find or create deployment
  6. Track duplicate names for warning
- **Database Tables**: students, student_accounts, companies, deployments
- **Business Rules**: Duplicate student_number → update existing. Duplicate name (first + last) → warning, not blocking. Validations: student_number digits:8, contact_number regex:/^09[0-9]{9}$/, section required.
- **Exception Handling**: Skipped rows counted. PHP errors silently skipped via `SkipsErrors` trait. Timeout prevented by `set_time_limit(0)`.

#### Subprocess 2.2: Create Student (Manual)
- **Actor**: Instructor
- **Input**: Form fields: student_number, last_name, first_name, middle_name, name_extension, section, contact_number, email
- **Process**: Instructor fills out form, system validates unique student_number, creates Student + StudentAccount (password = student_number, first_login = true, is_active = true). Name auto-composed on save.
- **Database Tables**: students, student_accounts
- **Business Rules**: Student number unique. Program default = BSIS. Year level default = 4. Name auto-composed via `composeDisplayName()`.
- **Validation Rules**: student_number required, digits:8, unique. contact_number required, regex:/^09[0-9]{9}$/. last_name, first_name, section required. email nullable|email.
- **Exception Handling**: Duplicate → "The student number has already been taken."

#### Subprocess 2.3: Update Student
- **Actor**: Instructor
- **Input**: Updated form fields
- **Process**: Instructor edits student record. System validates data, updates Student. Name recomposed on save. Student account email synced if changed.
- **Database Tables**: students
- **Business Rules**: Student number cannot be changed to a duplicate.
- **Validation Rules**: Same as create.
- **Exception Handling**: Same as create.

#### Subprocess 2.4: Delete Student
- **Actor**: Instructor
- **Input**: Confirmation (via delete modal)
- **Process**: Instructor clicks delete. System checks `HasDeleteProtection` trait's `deleteBlockers()`: checks deployments, documents, evaluations, remarks, weekly journals, certificates, attendance. If any exist, deletion is blocked with specific error message listing each blocker.
- **Database Tables**: students (blocked if related records exist)
- **Business Rules**: `HasDeleteProtection` trait prevents deletion.
- **Exception Handling**: Blocked → flash message: "Cannot delete: [Student Name] has [N] active deployment(s)."

#### Subprocess 2.5: View Student Profile
- **Actor**: Instructor, Chairperson, Dean
- **Input**: Student ID
- **Process**: System loads student with eager-loaded relationships: deployments (with company), document compliance progress, journal progress, remarks, evaluations. Displays profile page with personal info, deployment history, compliance checklist, progress percentages.
- **Database Tables**: students, deployments, companies, student_documents, required_documents, weekly_journals, remarks, evaluations
- **Business Rules**: `progress_pct` auto-computed from mandatory document completion. `journal_progress_pct` auto-computed from reviewed vs total journals.
- **Validation Rules**: N/A (read-only)

---

### Module 3: Company Management

#### Subprocess 3.1: Import Companies (Bulk)
- **Actor**: Instructor
- **Input**: Excel file (.xlsx, .xls, .csv, max 10MB)
- **Process**: Similar to student import but for companies. Each row is geocoded via `GeocodingService` (OpenStreetMap Nominatim, 1 req/sec). CompanyIndustry looked up by name. Data fields: name, street_address, barangay, city_municipality, contact fields, company_industry, notes, geofence_radius_meters.
- **Database Tables**: companies, company_industries
- **Business Rules**: Company name unique via LOWER(TRIM()) comparison. Geocoding failures reported but not blocking.
- **Exception Handling**: Geocoding failures counted and reported as warning.

#### Subprocess 3.2: Create/Update/Delete Company
- **Actor**: Instructor
- **Input**: Company form data
- **Process**: Standard CRUD. Delete blocked if company has related deployments (HasDeleteProtection). Geocoding available via separate geocode action.
- **Database Tables**: companies
- **Business Rules**: Company name uniqueness via LOWER(TRIM()). Geocoding optional.
- **Validation Rules**: name required, unique.

---

### Module 4: Deployment Management

#### Subprocess 4.1: Create Deployment
- **Actor**: Instructor
- **Input**: Student ID, Company ID, Start date, End date (optional)
- **Process**: Instructor selects student and company, sets dates. System validates no section conflicts: checks existing deployments for students in same section at same company with overlapping dates. Auto-computes status: if end_date passed → completed, if start_date <= today AND documents complete → active, else → pending. Auto-sets end_date to 6 months from start if not provided.
- **Database Tables**: deployments
- **Business Rules**: Section conflict prevents overlap. Status auto-computed via `computeStatus()` on `saving` event.
- **Validation Errors**: Section conflict → validation error message.

#### Subprocess 4.2: Update Deployment
- **Actor**: Instructor
- **Input**: Updated fields
- **Process**: Edit deployment dates, status, remarks. Status recomputed on save.
- **Database Tables**: deployments
- **Business Rules**: Status always recomputed.

#### Subprocess 4.3: Auto-Activate Deployment
- **Actor**: System (automatic, triggered by document upload)
- **Trigger**: `Deployment::checkAndActivateForStudent()` called after document submission
- **Input**: Student object
- **Process**: Check if student has all mandatory documents (status = 'Submitted'). If yes and student has pending deployment with start_date <= today → update status to 'active'.
- **Database Tables**: deployments, student_documents, required_documents
- **Business Rules**: Only pending deployments considered. Start date must be <= today.

---

### Module 5: Document Workflow Management

#### Subprocess 5.1: Define Required Document
- **Actor**: Instructor
- **Input**: name, description, is_mandatory, order_index, workflow_template_id, company_id, phase, submission_deadline_at
- **Process**: Create document definition in master catalog. Can link to workflow template (defines review chain). Can scope to specific company or leave global. Can set phase for organizational purposes.
- **Database Tables**: required_documents, document_workflow_templates
- **Business Rules**: Phase must be one of: pre, deployment, monitoring, post.
- **Validation Rules**: name required. phase nullable|in:pre,deployment,monitoring,post.

#### Subprocess 5.2: Student Uploads Document
- **Actor**: Student
- **Input**: File (PDF/DOC/DOCX, max 2MB)
- **Process**: Described in detail in Step 6 above.
- **Database Tables**: student_documents, student_document_actions

#### Subprocess 5.3: Workflow Review Actions
- **Actor**: Instructor or Chairperson
- **Input**: Action (review, approve, forward, return_for_revision, sign), note
- **Process**: Described in detail in Step 7 above. The `DocumentWorkflowEngine` manages all transitions.
- **Database Tables**: student_documents, student_document_actions, document_workflow_steps

#### Subprocess 5.4: Document Forwarding (Physical Documents)
- **Actor**: Instructor
- **Input**: Selection of student documents, release date, notes
- **Process**: Create forwarding batch, select documents, set release date. Batch tracks physical document transmittal. Recipients acknowledge receipt.
- **Database Tables**: document_forwarding_batches, document_forwarding_items, transmittal_logs
- **Business Rules**: Acknowledgment tracked per item.

---

### Module 6: Attendance Monitoring

#### Subprocess 6.1: Check-In
- **Actor**: Student
- **Input**: Attendance passcode, GPS coordinates
- **Process**: Described in Step 8 above. Public page, no auth required. Validates passcode, geofence, duplicate check.
- **Database Tables**: attendances, daily_time_records, student_accounts, companies, settings

#### Subprocess 6.2: Time-Out
- **Actor**: Student
- **Input**: (Optional) GPS coordinates
- **Process**: Records departure time, computes total minutes, verifies geofence at time-out.
- **Database Tables**: attendances, daily_time_records

#### Subprocess 6.3: Resolve Flagged Attendance
- **Actor**: Instructor or Chairperson
- **Input**: Resolution decision, note
- **Process**: Instructor reviews flagged records (outside_flagged, near_boundary_review, location_unavailable). Acknowledges as valid or marks invalid.
- **Database Tables**: attendances

---

### Module 7: Weekly Journals

#### Subprocess 7.1: Auto-Generate Weeks
- **Actor**: System
- **Trigger**: Student opens Weekly Journals page
- **Input**: Deployment start date, end date (or today)
- **Process**: Described in Step 10. Creates sequential week entries from deployment start to end/today.
- **Database Tables**: weekly_journals

#### Subprocess 7.2: Edit Activities (AJAX Auto-Save)
- **Actor**: Student
- **Input**: Task descriptions (Mon–Sat)
- **Process**: Inline editor. Changes saved via AJAX PATCH on blur. Activities stored as JSON array.
- **Database Tables**: weekly_journals
- **Business Rules**: Only editable when status = 'draft'.

#### Subprocess 7.3: Upload Files (AJAX)
- **Actor**: Student
- **Input**: File (PDF, DOC, DOCX, JPG, PNG, max 10MB)
- **Process**: Inline file upload per day. File stored, metadata added to JSON `files` array.
- **Database Tables**: weekly_journals

#### Subprocess 7.4: Submit Journal
- **Actor**: Student
- **Input**: Confirmation (Alpine modal)
- **Process**: Status → 'submitted', submitted_at → now().
- **Database Tables**: weekly_journals

#### Subprocess 7.5: Review Journal (Instructor)
- **Actor**: Instructor
- **Input**: Review action, optional remarks
- **Process**: "Mark as Reviewed" only — no reject/return.
- **Database Tables**: weekly_journals

---

### Module 8: Daily Time Records

#### Subprocess 8.1: Auto-Populate from Attendance
- **Actor**: System
- **Trigger**: Attendance check-in/out
- **Input**: Attendance record data
- **Process**: Creates/updates DailyTimeRecord for same student/deployment/date.
- **Database Tables**: daily_time_records, attendances

#### Subprocess 8.2: Export Monthly DTTR
- **Actor**: Student
- **Input**: Month, Year
- **Process**: Aggregates daily records, generates export file.
- **Database Tables**: daily_time_records

#### Subprocess 8.3: Upload Signed DTTR
- **Actor**: Student
- **Input**: Signed DTTR file (PDF)
- **Process**: Upload signed version back to system for archival.
- **Database Tables**: monthly_dttrs

---

### Module 9: Certificates

#### Subprocess 9.1: Create Certificate
- **Actor**: Instructor
- **Input**: Student, type, title, description, file (PDF)
- **Process**: Upload certificate, assign to student.
- **Database Tables**: certificates

#### Subprocess 9.2: Verify Certificate
- **Actor**: Instructor
- **Input**: Verification action
- **Process**: Mark certificate as verified with timestamp.
- **Database Tables**: certificates

#### Subprocess 9.3: Download Certificate
- **Actor**: Student
- **Input**: Certificate ID
- **Process**: Download file with ownership check.
- **Database Tables**: certificates

---

### Module 10: Evaluations

#### Subprocess 10.1: Create Evaluation
- **Actor**: Instructor
- **Input**: Student, Company, Score (1-100), Comments
- **Process**: Create evaluation record (type = industry or school).
- **Database Tables**: evaluations

#### Subprocess 10.2: Send HTE Link
- **Actor**: Instructor
- **Input**: Student, Company, Supervisor name, Supervisor email
- **Process**: Generate one-time token, create HTE link record, email supervisor.
- **Database Tables**: hte_transaction_links

#### Subprocess 10.3: Submit via HTE Link
- **Actor**: Company Supervisor
- **Input**: Score (1-100), Comments, optional file
- **Process**: External access (no login). Validate token. Create evaluation. Mark token used.
- **Database Tables**: evaluations, hte_transaction_links

#### Subprocess 10.4: Submit Student Feedback
- **Actor**: Student
- **Input**: Score (1-100), Comments
- **Process**: One feedback per deployment period (duplicate prevention).
- **Database Tables**: evaluations

---

### Module 11: Announcements

#### Subprocess 11.1: Create Announcement
- **Actor**: Instructor or Chairperson
- **Input**: Title, Body, visible_to_role (all, student, instructor, chairperson, dean)
- **Process**: Create announcement with role-based visibility.
- **Database Tables**: announcements

#### Subprocess 11.2: View Announcements
- **Actor**: All users (role-filtered)
- **Input**: None
- **Process**: Role-based filtering via `visibleToStaffUser()` scope.
- **Database Tables**: announcements

---

### Module 12: Messaging

#### Subprocess 12.1: Create Thread
- **Actor**: Staff or Student
- **Input**: Subject, Recipients, Body
- **Process**: Create message thread with participants. First message sent.
- **Database Tables**: message_threads, message_thread_participants, messages

#### Subprocess 12.2: Reply to Thread
- **Actor**: Staff or Student
- **Input**: Body
- **Process**: Add message to existing thread.
- **Database Tables**: messages

#### Subprocess 12.3: Toggle Read/Unread
- **Actor**: Staff or Student
- **Input**: Thread ID
- **Process**: Update `last_read_at` for participant.
- **Database Tables**: message_thread_participants

#### Subprocess 12.4: Archive Thread
- **Actor**: Staff or Student
- **Input**: Thread ID
- **Process**: Set `archived_at` for participant.
- **Database Tables**: message_thread_participants

---

### Module 13: Reports

#### Subprocess 13.1: Deployed Per Company
- **Actor**: Instructor, Chairperson, Dean
- **Input**: None
- **Process**: Query active/completed deployments grouped by company.
- **Database Tables**: deployments, companies, students
- **Output**: HTML table

#### Subprocess 13.2: Missing Documents
- **Actor**: Instructor, Chairperson, Dean
- **Input**: Section filter
- **Process**: Query students with incomplete mandatory document submissions.
- **Database Tables**: students, student_documents, required_documents
- **Output**: HTML table

#### Subprocess 13.3: Compliance Summary
- **Actor**: Instructor, Chairperson, Dean
- **Input**: None
- **Process**: Compute compliant/partial/non-compliant counts per section.
- **Database Tables**: students, student_documents, required_documents
- **Output**: HTML summary

#### Subprocess 13.4: Attendance Export (CSV)
- **Actor**: Instructor, Chairperson, Dean
- **Input**: Date range, section filter
- **Process**: Export attendance records within date range.
- **Database Tables**: attendances, students
- **Output**: CSV file

---

## SECTION 4: CONTEXT DIAGRAM ANALYSIS

### External Entities

| Entity | Description | Direction |
|---|---|---|
| **Student** | BSIS 4th-year intern. Submits documents, checks in/out, writes journals, views grades, messages staff. | Bidirectional |
| **Instructor** | OJT Coordinator. Manages students, companies, deployments, reviews documents, monitors attendance. | Bidirectional |
| **Chairperson** | Program oversight. Reviews forwarded documents, views compliance. | Bidirectional |
| **Dean** | Department oversight. Views compliance and evaluation analytics. | Bidirectional |
| **Company Supervisor** | External evaluator. Receives HTE link via email, submits evaluation and documents. | Bidirectional (web + email) |
| **Registrar** | Provides student data. One-way: sends Excel files for import. | One-way (to system) |
| **Email System** | Sends notifications, OTPs, HTE links. | One-way (system to users) |
| **OpenStreetMap Nominatim** | External geocoding API for address → GPS coordinates. | One-way (system to API) |
| **File Storage (Public Disk)** | Stores uploaded documents, journal files, certificates, DTRs. | Bidirectional |

### System Boundary

The system boundary encompasses all Laravel application code: routes, controllers, models, views, middleware, services, and database operations. External interactions are:
- Browser HTTP requests from users (HTML forms, AJAX, Alpine.js, HTMX)
- Email sending via Laravel mail system (SMTP/queue)
- Geocoding API calls to OpenStreetMap (HTTPS)
- File system operations on `storage/app/public/`
- Database operations on MySQL

### Data Flows (Key)

| Flow | Source → Destination | Data |
|---|---|---|
| Login credentials | Student/Staff → System | student_number + password / email + password |
| OTP code | System → Student | 6-digit code via email |
| Student data (Excel) | Registrar/Instructor → System | Bulk student records |
| Company data (Excel) | Instructor → System | Bulk company records |
| Document definitions | Instructor → System | Required document fields |
| File uploads | Student → System | PDF/DOC files |
| Review actions | Instructor/Chairperson → System | Approve/forward/return decisions |
| Check-in data | Student → System | GPS coordinates, passcode, timestamp |
| Journal entries | Student → System | Task descriptions, file attachments |
| Evaluation scores | Instructor/Supervisor → System | Numerical scores, comments |
| Notifications | System → Student/Staff | Emails about submissions, reviews |
| HTE link | System → Company Supervisor | One-time URL via email |
| Reports | System → Staff | Compliance summaries, CSV exports |
| Messages | Student/Staff → System | Text communications |

---

## SECTION 5: DFD LEVEL 0 ANALYSIS

### Main Processes

| Process | Description |
|---|---|
| **P1: Authentication** | Staff login, student login, OTP verification, password management, logout |
| **P2: Student Management** | CRUD + import for student records |
| **P3: Company Management** | CRUD + import for company records with geocoding |
| **P4: Deployment Management** | Assign students to companies, status computation |
| **P5: Document Management** | Required document catalog, student submissions, workflow review |
| **P6: Attendance Monitoring** | Check-in/out with geofence, record keeping, resolution |
| **P7: Weekly Journal Management** | Week generation, activity entry, file upload, submission, review |
| **P8: DTR Management** | Auto-population, export, signed upload |
| **P9: Certificate Management** | Upload, verify, download |
| **P10: Evaluation Management** | Create, HTE link, submit feedback |
| **P11: Announcement Management** | Create, publish, view |
| **P12: Messaging** | Threads, replies, read/archive |
| **P13: Reporting** | Compliance, deployment, attendance reports |
| **P14: Document Forwarding** | Physical document transmittal batches |

### Data Stores

| Store | Description |
|---|---|
| D1: users | Staff accounts |
| D2: student_accounts | Student login credentials |
| D3: students | Student profiles |
| D4: companies | Partner companies |
| D5: deployments | Student-company assignments |
| D6: required_documents | Master document checklist |
| D7: student_documents | Per-student submissions |
| D8: student_document_actions | Workflow audit log |
| D9: document_workflow_templates | Workflow routing definitions |
| D10: document_workflow_steps | Individual workflow steps |
| D11: attendances | Check-in/out records |
| D12: daily_time_records | DTR entries |
| D13: weekly_journals | Journal entries |
| D14: certificates | Certificate records |
| D15: evaluations | Performance evaluations |
| D16: hte_transaction_links | External token records |
| D17: announcements | Announcement content |
| D18: message_threads | Conversation threads |
| D19: message_thread_participants | Thread membership |
| D20: messages | Individual messages |
| D21: document_forwarding_batches | Forwarding batches |
| D22: document_forwarding_items | Batch items |
| D23: transmittal_logs | Acknowledgment history |
| D24: company_industries | Industry classification |
| D25: monthly_dttrs | Signed DTTR uploads |
| D26: settings | System configuration |
| D27: remarks | Staff notes |

---

## SECTION 6: DFD LEVEL 1 ANALYSIS

### P1: Authentication — Decomposition

| Subprocess | Description |
|---|---|
| P1.1 Staff Login | Validate email + password against users table, create web guard session |
| P1.2 Student Login | Validate student_number + password, generate OTP, verify OTP, create custom session |
| P1.3 OTP Verification | Send 6-digit code via email, validate against session-stored OTP |
| P1.4 Password Management | First-login password change, forgot/reset password |
| P1.5 Logout | Clear session for staff (Auth::logout) or student (session forget) |

**Business Rules**: Staff and student sessions mutually exclusive. 30-min idle timeout for students. OTP single-use.

### P2: Student Management — Decomposition

| Subprocess | Description |
|---|---|
| P2.1 Import Students | Parse Excel, validate rows, create/update Student + StudentAccount + optional Deployment |
| P2.2 Create Student | Manual form entry, validate unique student_number, create Student + StudentAccount |
| P2.3 Update Student | Edit form, validate data, update record, recompose name |
| P2.4 Delete Student | Check HasDeleteProtection, delete if no blockers |
| P2.5 View Student Profile | Load student with relationships, display profile |

**Data Stores**: D3 (students), D2 (student_accounts)
**Business Rules**: Student number unique. Auto-compose name from parts. Delete blocked by related records.

### P3: Company Management — Decomposition

| Subprocess | Description |
|---|---|
| P3.1 Import Companies | Parse Excel, geocode addresses, find/create companies |
| P3.2 Create Company | Manual form, validate unique name |
| P3.3 Update Company | Edit form, update record |
| P3.4 Delete Company | Check deployments, block if exist |
| P3.5 View Company | Display company profile with deployments |

**Data Stores**: D4 (companies), D24 (company_industries)

### P4: Deployment Management — Decomposition

| Subprocess | Description |
|---|---|
| P4.1 Create Deployment | Select student + company, set dates, auto-compute status |
| P4.2 Update Deployment | Edit dates/status, recompute status |
| P4.3 Auto-Activate | Triggered by document completion, checks mandatory docs |
| P4.4 View Deployments | List view with filters |

**Data Stores**: D5 (deployments), D3 (students), D4 (companies)
**Business Rules**: Section conflict check. Status auto-computed.

### P5: Document Management — Decomposition

| Subprocess | Description |
|---|---|
| P5.1 Define Required Document | Create document entry in master catalog |
| P5.2 Student Upload Document | Validate, store file, initialize workflow, notify holder |
| P5.3 Initialize Workflow | Set first workflow step, current_holder_role, status='received' |
| P5.4 Review Document | Instructor/Chairperson actions via WorkflowEngine |
| P5.5 View Workflow Queue | List documents pending current user's review |

**Data Stores**: D6, D7, D8, D9, D10

### P6: Attendance — Decomposition

| Subprocess | Description |
|---|---|
| P6.1 Validate Passcode | Look up student by passcode, verify active deployment |
| P6.2 Validate Geofence | Compute distance, determine status (inside/buffer/outside/unavailable) |
| P6.3 Record Check-In | Create attendance record with location data and status |
| P6.4 Record Time-Out | Update attendance with departure time, total minutes |
| P6.5 Resolve Flagged | Review and resolve flagged records |

**Data Stores**: D11, D12, D26, D4

### P7-P14: Further decomposition follows same pattern — each process broken into distinct subprocesses with specific data store interactions.
# OJT Internship Management System — Complete Business Analysis (Part 2)

> **System Name**: OJT Internship Management System  
> **Sections**: 7–18 (Detailed DFD, Database, Use Cases, Activity, PERT, CPM, Gantt, Validation, Security, Reports, Workflow)

---

## SECTION 7: DFD LEVEL 2 ANALYSIS

### P5.3: Student Upload Document (Detailed)

1. **Student** selects a required document from the checklist page
2. **System** checks: Is the document scoped to a specific company?
   - YES: Verify student's active deployment company matches the document's company_id
   - NO: Continue
3. **Student** selects a file (PDF, DOC, DOCX, max 2MB)
4. **System** validates file format:
   - Extension check: pdf, doc, docx
   - MIME type check: application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document
   - Size check: max 2048KB
   - INVALID: Display error, return to form
   - VALID: Continue
5. **System** stores file to `storage/app/public/student-documents/{student_id}/{unique_filename}`
6. **System** creates/updates `student_documents` record via `updateOrCreate()`:
   - Match on: `student_id` + `required_document_id`
   - Set: `file_path`, `submitted_at = now()`, `status = 'Pending'`, `workflow_template_id` from required_document
7. **System** checks if resubmission after rejection (`workflow_status = 'rejected'`):
   - YES: Reset workflow fields to null (`current_step_order`, `current_holder_role`, `next_step_role`, `workflow_status`, `last_action_at`)
   - NO: Continue
8. **System** calls `DocumentWorkflowEngine::initialize()`:
   - If `workflow_template_id` exists:
     - Query `DocumentWorkflowStep` where `workflow_template_id = X` ordered by `step_order`, first
     - Set: `current_step_order = firstStep.step_order`, `current_holder_role = firstStep.role`
     - Compute `next_step_role`: find next step after first
     - Set `workflow_status = 'received'`, `last_action_at = now()`
   - If no template: Return (no workflow routing)
9. **System** sends email notification via `NotificationService::notifyRole()`:
   - Target: `current_holder_role` (typically 'instructor')
   - Email: `NotificationMail` with event_type='document.submitted', title, body, action_url
10. **System** calls `Deployment::checkAndActivateForStudent($student)`:
    - Query all mandatory required_documents
    - For each, check if student_document exists with status='Submitted'
    - If all submitted AND student has pending deployment with start_date <= today:
      - Update deployment status to 'active'
    - If not → No action

### P5.5: Workflow Review Actions (Detailed)

1. **User** opens Workflow Queue
2. **System** queries `student_documents` where `current_holder_role = user->role` AND `workflow_status NOT IN ('completed', 'rejected')`
3. **User** clicks a document
4. **System** calls `DocumentWorkflowEngine::autoStartReview()`:
   - Check: `workflow_status === 'received'` AND `current_holder_role === actor->role` AND 'review' is in `allowedActions()`
   - YES: Call `applyAction(actor, 'review')` → `workflow_status = 'under_review'`
   - NO: Skip
5. **System** calls `DocumentWorkflowEngine::allowedActions()`:
   - Based on current `workflow_status`:
     - 'received': ['review'] or ['review', 'sign'] (if step requires_signature)
     - 'under_review': ['approve', 'forward' (if next step), 'return_for_revision' (if previous step or step 1)]
     - 'pending_review': ['review', 'sign', 'return_for_revision'] (for signature steps)
6. **User** selects action, enters note, submits
7. **System** calls `DocumentWorkflowEngine::applyAction(actor, action, note, returnStepOrder)`:
   - Validates: action in `allowedActions()`
   - Validates: note present if action='return_for_revision'
   - **DB Transaction**:
     - **action='review'**: `workflow_status = 'under_review'`. Sync `next_step_role` if blank.
     - **action='approve'**: `workflow_status = 'completed'`, `next_step_role = null`, student `status = 'Submitted'`, `verified_by = actor->id`, `submitted_at = now()`
     - **action='forward'**: Find next step (step_order > current). If found: move `current_step_order`, `current_holder_role`, `next_step_role` to next step. `workflow_status = 'pending_review'`. If no next step: `workflow_status = 'completed'`.
     - **action='return_for_revision'**: Find previous step (step_order < current). Move back. If step 1 returning to student: keep step 1 as holder. `workflow_status = 'for_revision'`, student `status = 'Missing'`, `verified_by = actor->id`.
     - **action='sign'**: If step requires_signature. `workflow_status = 'completed'`.
     - Save `studentDocument`
     - Create `student_document_actions` record with all transition details
8. **System** sends appropriate email notification

### P6.2: Geofence Validation (Detailed)

1. **System** receives check-in request with `passcode`, `latitude`, `longitude`, `accuracy_meters`
2. **System** looks up `student_accounts` by `LOWER(attendance_passcode) = passcode`
3. **System** gets student's active deployment → assigned company
4. **System** reads company's `latitude`, `longitude`, `geofence_radius_meters` (default: 100)
5. **System** reads `settings`: `geofence_review_buffer_meters` (default: 50)
6. **Decision Tree for GPS availability**:
   - `latitude` is null OR `longitude` is null → status = `location_unavailable`, `review_required = true`
7. **System** computes distance using Haversine formula:
   - `dlat = lat2 - lat1`, `dlon = lon2 - lon1`
   - `a = sin²(dlat/2) + cos(lat1)·cos(lat2)·sin²(dlon/2)`
   - `c = 2 · atan2(√a, √(1-a))`
   - `distance = R · c` where R = 6371000 meters
8. **Decision Tree**:
   - `distance <= geofence_radius` → status = `inside_pass`, `review_required = false`
   - `distance <= (geofence_radius + buffer)` → status = `near_boundary_review`, `review_required = true`
   - `distance > (geofence_radius + buffer)` → status = `outside_flagged`, `review_required = true`
9. **Return**: `{ geofence_status, review_required, distance, company_coords }`

### P7.1: Ensure Weeks Exist (Detailed)

1. **Input**: Student object
2. Get student's latest active/completed deployment
3. **IF** no deployment OR no `start_date` → **RETURN** (nothing to generate)
4. Query existing `weekly_journals` for this student, ordered by `week_start_date`
5. **Determine cursor**:
   - Weeks exist → `cursor = last week's week_end_date + 1 day`, then adjust to next Monday
   - No weeks → `cursor = deployment.start_date`, then adjust to that week's Monday (`startOfWeek()` using Carbon)
6. **Determine end**:
   - Deployment has `end_date` → `end = end_of_week(end_date)`
   - No end_date → `end = end_of_week(today)`
7. **Set weekNumber**: `max(existing week_numbers, 0)`
8. **Loop**: `while (cursor <= end)`:
   - `weekNumber++`
   - `WeeklyJournal::create([student_id, deployment_id, week_start_date = cursor->format('Y-m-d'), week_end_date = cursor->copy()->endOfWeek()->format('Y-m-d'), week_number = weekNumber, status = 'draft'])`
   - `cursor->addWeek()`
9. **Results**: All weeks from deployment start to end/today now exist as Draft records

---

## SECTION 8: DATABASE ANALYSIS

### Entity: `users`
- **Purpose**: Staff accounts (instructor, chairperson, dean)
- **Attributes**: id (PK), name, last_name, first_name, middle_name, name_extension, email (unique), email_verified_at (nullable), password, role (enum: instructor/chairperson/dean), first_login (boolean), remember_token, timestamps
- **Relationships**: HasMany message_thread_participants, HasMany messages (as sender), HasMany student_document_actions (as actor)
- **Indexes**: email (unique)

### Entity: `student_accounts`
- **Purpose**: Student login credentials
- **Attributes**: id (PK), student_id (FK→students, unique), email (nullable), password, is_active (boolean), last_login_at (nullable), attendance_passcode (nullable, 6-digit), attendance_passcode_generated_at (nullable), first_login (boolean), timestamps
- **Relationships**: BelongsTo student
- **Indexes**: student_id (unique)

### Entity: `students`
- **Purpose**: Student biographical and academic data
- **Attributes**: id (PK), user_id (FK→users, nullable), name (computed), last_name, first_name, middle_name, name_extension, student_number (unique), program (default BSIS), year_level (default 4), section, contact_number, progress_pct, timestamps
- **Relationships**: HasMany deployments, HasMany student_documents, HasMany evaluations, HasMany remarks, HasOne student_account, HasMany weekly_journals, HasMany certificates, HasMany attendances, HasMany daily_time_records
- **Indexes**: student_number (unique)

### Entity: `companies`
- **Purpose**: Partner company records
- **Attributes**: id (PK), name, street_address, barangay, city_municipality, company_industry_id (FK→company_industries, nullable), contact_last_name, contact_first_name, contact_middle_initial, contact_name_extension, contact_email, contact_phone, is_active (boolean), notes (text), latitude (decimal:7), longitude (decimal:7), geofence_radius_meters (integer, default 100), timestamps
- **Relationships**: HasMany deployments, BelongsTo industry
- **Indexes**: name (with LOWER TRIM)

### Entity: `company_industries`
- **Purpose**: Industry classification
- **Attributes**: id (PK), name, slug, color, is_active, timestamps
- **Relationships**: HasMany companies

### Entity: `deployments`
- **Purpose**: Student-company assignments
- **Attributes**: id (PK), student_id (FK→students), company_id (FK→companies), start_date (date), end_date (date, nullable), status (enum: pending/active/completed), remarks (text, nullable), timestamps
- **Relationships**: BelongsTo student, BelongsTo company, HasMany weekly_journals, HasMany daily_time_records, HasMany certificates
- **Business Logic**: Status auto-computed on every save based on dates and document completion.

### Entity: `required_documents`
- **Purpose**: Master document checklist
- **Attributes**: id (PK), name, description, is_mandatory (boolean), order_index (integer), workflow_template_id (FK→document_workflow_templates, nullable), company_id (FK→companies, nullable), phase (nullable, enum: pre/deployment/monitoring/post), submission_deadline_at (datetime, nullable), timestamps
- **Relationships**: HasMany student_documents, BelongsTo workflowTemplate, BelongsTo company

### Entity: `student_documents`
- **Purpose**: Per-student document submissions with workflow state
- **Attributes**: id (PK), student_id (FK→students), required_document_id (FK→required_documents), status (enum: Submitted/Pending/Missing), file_path, submitted_at (nullable), verified_by (FK→users, nullable), uploaded_by (FK→users, nullable), workflow_template_id (FK→document_workflow_templates, nullable), current_step_order (integer, nullable), current_holder_role (string, nullable), next_step_role (string, nullable), workflow_status (enum: received/under_review/pending_review/completed/rejected/for_revision, nullable), last_action_at (datetime, nullable), timestamps
- **Relationships**: BelongsTo student, BelongsTo requiredDocument, BelongsTo verifier (User), HasMany actions
- **Indexes**: Unique on (student_id, required_document_id)

### Entity: `student_document_actions`
- **Purpose**: Workflow audit log
- **Attributes**: id (PK), student_document_id (FK→student_documents), actor_id (FK→users, nullable), actor_role (string), action (string), from_status (string, nullable), to_status (string), note (text, nullable), metadata (json, nullable), acted_at (datetime), timestamps
- **Relationships**: BelongsTo studentDocument, BelongsTo actor (User)

### Entity: `document_workflow_templates`
- **Purpose**: Workflow routing templates
- **Attributes**: id (PK), name, code, description, is_active (boolean), timestamps
- **Relationships**: HasMany steps, HasMany required_documents

### Entity: `document_workflow_steps`
- **Purpose**: Individual steps within a workflow
- **Attributes**: id (PK), workflow_template_id (FK→document_workflow_templates), step_order (integer), role (string), can_return (boolean), requires_signature (boolean), timestamps
- **Relationships**: BelongsTo workflowTemplate

### Entity: `attendances`
- **Purpose**: Check-in/out records with geofence data
- **Attributes**: id (PK), student_id (FK→students), check_in_at (datetime), time_out_at (datetime, nullable), total_minutes (integer, nullable), latitude (decimal:7, nullable), longitude (decimal:7, nullable), time_out_latitude, time_out_longitude, accuracy_meters, distance_meters, geofence_status (enum: inside_pass/near_boundary_review/outside_flagged/location_unavailable), review_required (boolean), resolution_status (enum: acknowledged/invalid, nullable), resolved_by (FK→users, nullable), resolved_at, resolution_note, is_within_campus (boolean, nullable), timestamps
- **Relationships**: BelongsTo student, BelongsTo resolver (User)
- **Indexes**: Unique on (student_id, date(check_in_at))

### Entity: `daily_time_records`
- **Purpose**: Auto-populated DTR from attendance
- **Attributes**: id (PK), student_id (FK→students), deployment_id (FK→deployments), source (enum: attendance/manual), date (date), am_arrival (time, nullable), am_departure (time, nullable), pm_arrival (time, nullable), pm_departure (time, nullable), time_in (time, nullable), time_out (time, nullable), total_minutes (integer, nullable), remarks, tasks (text), timestamps
- **Relationships**: BelongsTo student, BelongsTo deployment
- **Indexes**: Unique on (student_id, deployment_id, date)

### Entity: `weekly_journals`
- **Purpose**: Weekly activity reports
- **Attributes**: id (PK), student_id (FK→students), deployment_id (FK→deployments), week_start_date (date), week_end_date (date), week_number (integer), activities (json), files (json), supervisor_name, status (enum: draft/submitted/reviewed), submitted_at (datetime, nullable), reviewed_by (FK→users, nullable), reviewed_at (datetime, nullable), remarks (text), timestamps
- **Relationships**: BelongsTo student, BelongsTo deployment, BelongsTo reviewer (User)

### Entity: `certificates`
- **Purpose**: Completion certificates
- **Attributes**: id (PK), student_id (FK→students), deployment_id (FK→deployments, nullable), uploaded_by (FK→users, nullable), type, title, description, file_path, issued_at (date, nullable), status, verified_by (FK→users, nullable), verified_at, verification_notes, timestamps
- **Relationships**: BelongsTo student, BelongsTo deployment, BelongsTo uploader (User), BelongsTo verifier (User)

### Entity: `evaluations`
- **Purpose**: Performance evaluations
- **Attributes**: id (PK), student_id (FK→students), company_id (FK→companies, nullable), evaluator_id (FK→users, nullable), evaluation_type (enum: industry/school/student_feedback), score (integer, 1-100), comments (text), evaluated_at (datetime, nullable), timestamps
- **Relationships**: BelongsTo student, BelongsTo evaluator (User), BelongsTo company
- **Business Logic**: Score clamped to 1-100 on save. One evaluation per type per deployment period.

### Entity: `hte_transaction_links`
- **Purpose**: One-time external access tokens
- **Attributes**: id (PK), token (unique, string:64), student_id (FK→students), company_id (FK→companies), created_by (FK→users, nullable), supervisor_name, supervisor_email, expires_at (datetime), used_at (datetime, nullable), used_for (string, nullable), timestamps
- **Relationships**: BelongsTo student, BelongsTo company, BelongsTo sender (User)

### Entity: `announcements`
- **Purpose**: Published announcements
- **Attributes**: id (PK), title, body (text), visible_to_role (string, nullable), created_by (FK→users), timestamps
- **Relationships**: BelongsTo author (User)

### Entity: `message_threads`
- **Purpose**: Conversation grouping
- **Attributes**: id (PK), subject, context_type, context_id, created_by (FK→users, nullable), created_by_student_account_id (FK→student_accounts, nullable), timestamps
- **Relationships**: HasMany participants, HasMany messages

### Entity: `message_thread_participants`
- **Purpose**: Thread membership
- **Attributes**: id (PK), thread_id (FK→message_threads), user_id (FK→users, nullable), student_account_id (FK→student_accounts, nullable), last_read_at (datetime, nullable), archived_at (datetime, nullable), timestamps
- **Relationships**: BelongsTo thread, BelongsTo user, BelongsTo studentAccount

### Entity: `messages`
- **Purpose**: Message content
- **Attributes**: id (PK), thread_id (FK→message_threads), sender_id (FK→users, nullable), sender_student_account_id (FK→student_accounts, nullable), body (text), timestamps
- **Relationships**: BelongsTo thread, BelongsTo senderUser, BelongsTo senderStudentAccount

### Entity: `settings`
- **Purpose**: System configuration (singleton)
- **Attributes**: id (PK), campus_lat (decimal:7), campus_lng (decimal:7), campus_radius_meters (integer), student_geofence_radius_meters (integer), geofence_review_buffer_meters (integer), campus_boundary_buffer_meters (integer), maintenance_mode (boolean), policy_review_notes (text), semester, academic_year, timestamps

### Entity: `document_forwarding_batches`
- **Purpose**: Physical document transmittal groups
- **Attributes**: id (PK), created_by (FK→users), release_at (datetime), status, notes, timestamps
- **Relationships**: HasMany items, HasMany logs

### Entity: `document_forwarding_items`
- **Purpose**: Individual forwarded documents
- **Attributes**: id (PK), batch_id (FK→document_forwarding_batches), student_document_id (FK→student_documents), student_id (FK→students), required_document_id (FK→required_documents), released_at, acknowledged_by (FK→users, nullable), acknowledged_at, timestamps

### Entity: `transmittal_logs`
- **Purpose**: Forwarding acknowledgment history
- **Attributes**: id (PK), batch_id, item_id, student_id, required_document_id, action_type, acted_by (FK→users), acted_at, note, timestamps

### Entity: `remarks`
- **Purpose**: Staff notes about students
- **Attributes**: id (PK), student_id (FK→students), author_id (FK→users), content (text), timestamps
- **Relationships**: BelongsTo student, BelongsTo author (User)

### Entity: `monthly_dttrs`
- **Purpose**: Signed monthly DTTR uploads
- **Attributes**: id (PK), student_id (FK→students), deployment_id (FK→deployments), year (integer), month (integer), file_path, file_name, timestamps
- **Relationships**: BelongsTo student, BelongsTo deployment

---

## SECTION 9: USE CASE ANALYSIS

### UC-01: Staff Login
- **Actors**: Instructor, Chairperson, Dean
- **Preconditions**: Staff account exists in `users` table with email verified.
- **Main Flow**:
  1. Staff navigates to `/login`
  2. Enters email and password
  3. System validates via `Auth::attempt()` checking `users` table
  4. If `first_login = true` → redirect to `/password/change`
  5. Otherwise → redirect to `/dashboard`
- **Alternative Flow**:
  - 3a. Invalid credentials → "These credentials do not match our records."
  - 3b. Email not verified → "Please verify your email address."
  - 4a. Student session active → cleared (mutual exclusivity)
- **Postconditions**: Authenticated web guard session created.

### UC-02: Student Login (with OTP)
- **Actors**: Student
- **Preconditions**: Student account exists in `student_accounts` and `students` tables. `is_active = true`.
- **Main Flow**:
  1. Student selects "Student Login"
  2. Enters student number and default password
  3. System validates against `student_accounts` table
  4. System generates 6-digit OTP, sends via email
  5. Student enters OTP
  6. System verifies OTP match
  7. Creates session with `student_account_id`
  8. If `first_login = true` → password change
  9. If limited portal access → redirect to documents
  10. If full portal access → redirect to dashboard
- **Alternative Flow**:
  - 3a. Invalid credentials → "Invalid student number or password."
  - 6a. OTP mismatch → "Invalid verification code." Option to resend.
  - 8a. Password change → validates min 8 chars, confirmed, saves new hash.
- **Postconditions**: Custom session created. 30-min idle timeout active.

### UC-03: Import Students (Bulk)
- **Actors**: Instructor
- **Preconditions**: Authenticated as instructor.
- **Main Flow**:
  1. Navigate to Students → Import
  2. Upload Excel file (.xlsx/.xls/.csv, max 10MB)
  3. System processes file: validates each row, creates/updates records
  4. Success modal shows counts: created, updated, skipped
  5. Click "Go to List" → navigate to student list
- **Alternative Flow**:
  - 3a. Invalid file format → validation error
  - 3b. All rows skipped → modal shows "0 created, 0 updated, N skipped"
  - 3c. Duplicate names found → warning with count
- **Postconditions**: Students created/updated in database. Student accounts created.

### UC-04: Create Deployment
- **Actors**: Instructor
- **Preconditions**: Student and company records exist. Authenticated as instructor.
- **Main Flow**:
  1. Navigate to Deployments → Create
  2. Select student from dropdown
  3. Select company from dropdown
  4. Enter start date (and optional end date)
  5. System checks section conflicts
  6. System auto-computes initial status
  7. Submit → deployment created
- **Alternative Flow**:
  - 5a. Section conflict → validation error, deployment not created
- **Postconditions**: Deployment record created with computed status.

### UC-05: Upload Document (Student)
- **Actors**: Student
- **Preconditions**: Authenticated student session. Required document exists.
- **Main Flow**:
  1. Navigate to Documents page
  2. View checklist with statuses
  3. Click upload on a specific required document
  4. Select file (PDF/DOC/DOCX, max 2MB)
  5. System validates and stores file
  6. Workflow initialized
  7. Instructor notified
  8. Deployment activation check
  9. Document card updates to "Pending"
- **Alternative Flow**:
  - 5a. Invalid file type → error message on card
  - 5b. File too large → error
  - 5c. Not student's company document → 403 error
- **Postconditions**: StudentDocument created/updated. Workflow started. Email sent.

### UC-06: Review Document (Instructor/Chairperson)
- **Actors**: Instructor, Chairperson
- **Preconditions**: Document submitted and workflow active. Current holder role matches actor role.
- **Main Flow**:
  1. Open Workflow Queue
  2. View pending documents
  3. Open a document → auto-starts review
  4. View file and history
  5. Select action (approve/forward/return) with note
  6. System executes action via WorkflowEngine
  7. Document status updated
  8. Notification sent to next party
- **Alternative Flow**:
  - 5a. No note on return → validation error
  - 5b. Action not allowed → 403
- **Postconditions**: Workflow state advanced/completed. Action logged.

### UC-07: Check-In (Student)
- **Actors**: Student
- **Preconditions**: Active deployment. Attendance passcode available. Not checked in today.
- **Main Flow**:
  1. Open `/attendance/check-in` (public page)
  2. Browser requests GPS location
  3. Enter attendance passcode
  4. System validates passcode, geofence, duplicate
  5. Check-in recorded with geofence status
- **Alternative Flow**:
  - 4a. Invalid passcode → "Invalid passcode"
  - 4b. Already checked in → "Already checked in today"
  - 4c. Outside geofence → flagged for review but allowed
- **Postconditions**: Attendance recorded. DTR entry created.

### UC-08: Submit Weekly Journal
- **Actors**: Student
- **Preconditions**: Authenticated. Active deployment. Journal in draft status.
- **Main Flow**:
  1. Open Weekly Journals
  2. System auto-generates week entries
  3. Click on draft week
  4. Enter tasks per day (auto-saved)
  5. Upload files per day (optional)
  6. Click Submit → Alpine modal confirmation
  7. Journal status → 'submitted'
- **Alternative Flow**:
  - 4a. Auto-save on blur via AJAX
  - 6a. Cancel modal → stays in draft
- **Postconditions**: Journal submitted. Late flag computed.

### UC-09: Review Weekly Journal
- **Actors**: Instructor
- **Preconditions**: Journal status = 'submitted'.
- **Main Flow**:
  1. Open Weekly Journals (staff)
  2. Filter by submitted status
  3. Click on journal
  4. View activities table with files
  5. Check late flag, supervisor info
  6. Enter optional remarks
  7. Click "Mark as Reviewed"
  8. Status → 'reviewed'. Student notified.
- **Postconditions**: Journal reviewed.

### UC-10: Submit HTE Evaluation (Company Supervisor)
- **Actors**: Company Supervisor
- **Preconditions**: Valid, unused, non-expired HTE token.
- **Main Flow**:
  1. Click link in email (no login)
  2. View student and company info
  3. Enter score (1-100) and comments
  4. Optionally upload file
  5. Submit
  6. Token marked as used
  7. Confirmation shown
- **Alternative Flow**:
  - 1a. Token used → "Already used"
  - 1b. Token expired → "Expired"
- **Postconditions**: Evaluation recorded. Token invalidated.

---

## SECTION 10: ACTIVITY FLOW ANALYSIS

### Activity: Student Document Submission & Review

```
[Start]
  |
  v
Student logs in → Navigates to Documents page
  |
  v
System displays required document checklist with statuses (Submitted/Pending/Missing)
  |
  v
Student selects a required document → clicks Upload
  |
  v
System shows file picker
  |
  v
Student selects file (PDF/DOC/DOCX ≤ 2MB)
  |
  v
[Decision: valid file type & size?]
  |--- NO → Show error → Return to upload
  |--- YES → Store file to disk
  |
  v
[Decision: company-scoped document?]
  |--- YES → [Decision: matches student's active deployment?]
  |            |--- NO → 403 error
  |            |--- YES → Continue
  |--- NO → Continue
  |
  v
Create/update StudentDocument record (status = 'Pending')
  |
  v
[Decision: resubmission after rejection?]
  |--- YES → Reset workflow fields
  |--- NO → Keep existing workflow
  |
  v
Initialize workflow → Set first step holder = instructor
  |
  v
Send email notification to instructor
  |
  v
Check deployment activation (all mandatory docs submitted?)
  |
  v
[End of student flow]
  |
  v
--- Instructor receives email ---
  |
  v
Instructor opens Workflow Queue
  |
  v
System lists documents pending instructor's review
  |
  v
Instructor clicks a document
  |
  v
System auto-starts review: 'received' → 'under_review'
  |
  v
Instructor views document file
  |
  v
[Decision: action?]
  |--- Approve → Document completed (status = Submitted)
  |               Notify student
  |               [End of workflow]
  |
  |--- Forward → Move to next step (chairperson)
  |               Notify chairperson
  |               |
  |               v
  |         Chairperson reviews document
  |               |
  |               v
  |         [Decision: action?]
  |               |--- Approve → Completed
  |               |--- Return → Back to instructor
  |               [End of workflow]
  |
  |--- Return for Revision → Status = Missing
  |                           Notify student
  |                           [Cycle back to student upload]
  |
  [End]
```

### Activity: Attendance Check-In & Resolution

```
[Start]
  |
  v
Student arrives at company → Opens check-in page (public)
  |
  v
Browser requests GPS location permission
  |
  v
[Decision: GPS granted?]
  |--- NO → Set latitude/longitude = null
  |--- YES → Capture coordinates
  |
  v
Student enters attendance passcode (6 digits from dashboard)
  |
  v
System looks up student by passcode
  |
  v
[Decision: passcode valid?]
  |--- NO → Show "Invalid passcode" → retry
  |--- YES → Continue
  |
  v
[Decision: student has active deployment?]
  |--- NO → "No active deployment found"
  |--- YES → Get company coordinates & geofence radius
  |
  v
[Decision: already checked in today?]
  |--- YES → "Already checked in today"
  |--- NO → Continue
  |
  v
Compute distance using Haversine formula
  |
  v
[Decision: geofence status?]
  |--- GPS unavailable → location_unavailable, flag for review
  |--- Within radius → inside_pass, no review needed
  |--- Within buffer → near_boundary_review, flag for review
  |--- Outside → outside_flagged, flag for review
  |
  v
Create attendance record with all metadata
  |
  v
Auto-populate DTR entry
  |
  v
Show check-in confirmation to student
  |
  [End of check-in flow]
  |
  v
--- Later (end of day) ---
  |
  v
Student returns to check-in page → Clicks "Time Out"
  |
  v
System records time_out_at, computes total_minutes
  |
  v
Updates DTR entry with time_out data
  |
  v
Show time-out confirmation
  |
  [End of time-out flow]
  |
  v
--- Later (instructor review) ---
  |
  v
Instructor opens Attendance page
  |
  v
Filters by flagged records
  |
  v
Reviews each record's location data and distance
  |
  v
[Decision: resolution?]
  |--- Acknowledge valid → resolution_status = 'acknowledged'
  |--- Mark invalid → resolution_status = 'invalid'
  |
  v
Record is resolved
  |
  [End of resolution flow]
```

---

## SECTION 11: PROJECT TASK BREAKDOWN

### Phase 1: Requirements & Planning
1.1. Stakeholder interviews (students, instructors, chairperson, dean)
1.2. Requirements gathering and documentation
1.3. System analysis and specification
1.4. Scope definition and prioritization
1.5. Technology stack selection (Laravel, MySQL, Tailwind, Alpine.js)
1.6. Project planning and scheduling
1.7. Resource allocation

### Phase 2: Database Design
2.1. Entity-relationship modeling (all 27+ entities)
2.2. Schema design with proper relationships
2.3. Create migration files (77 migrations total)
2.4. Define indexes and unique constraints
2.5. Create seed data for testing
2.6. Set up MySQL database

### Phase 3: Backend — Core Infrastructure
3.1. Laravel project setup and configuration
3.2. Authentication system
3.3. User role management and InternshipRoles support class
3.4. Middleware: EnsureStudentSession, RoleMiddleware, CheckMaintenanceMode
3.5. Session management (30-min timeout, mutual exclusivity)
3.6. Error handling and logging
3.7. CSRF and security middleware

### Phase 4: Backend — Student & Company Management
4.1. Student model, controller, CRUD views
4.2. Student import (Excel + validation + account creation)
4.3. Company model, controller, CRUD views
4.4. Company import (Excel + geocoding integration)
4.5. Company industry management

### Phase 5: Backend — Deployment Management
5.1. Deployment model, controller, CRUD views
5.2. Section conflict validation
5.3. Auto-status computation (pending/active/completed)
5.4. Auto-activation on document completion
5.5. Deployment viewer routes for chairperson/dean

### Phase 6: Backend — Document Management & Workflow
6.1. Required document catalog (model + CRUD)
6.2. Workflow template and step models
6.3. DocumentWorkflowEngine service (initialize, autoStartReview, allowedActions, applyAction)
6.4. Student document submission (upload, validation, workflow init)
6.5. Workflow queue view (staff)
6.6. Workflow action endpoints (review, approve, forward, return, sign)
6.7. Email notification integration
6.8. Document forwarding (batch creation, release, acknowledgment)

### Phase 7: Backend — Attendance Monitoring
7.1. Attendance model and controller
7.2. Public check-in page (no auth required)
7.3. Passcode generation and validation
7.4. Geocoding service (OpenStreetMap integration)
7.5. Geofencing service (distance computation, status determination)
7.6. Time-out functionality
7.7. Flagged record resolution (instructor)
7.8. Daily Time Record auto-population

### Phase 8: Backend — Weekly Journals
8.1. WeeklyJournal model and controller
8.2. Auto-generation of week entries from deployment dates
8.3. Student journal index and show views (Alpine.js SPA)
8.4. AJAX auto-save for activities
8.5. AJAX inline file upload/delete
8.6. Journal submission with confirmation modal
8.7. Late flag computation (accessor)
8.8. Staff journal index (filterable list)
8.9. Staff journal show view (activities table, review panel)
8.10. Journal review action (mark as reviewed)

### Phase 9: Backend — DTR, Certificates, Evaluations
9.1. DailyTimeRecord model (auto-populated from attendance)
9.2. Monthly DTTR export functionality
9.3. Signed DTTR upload
9.4. Certificate CRUD
9.5. Certificate verification
9.6. Evaluation model and CRUD
9.7. HTE transaction link generation
9.8. External HTE evaluation submission (no login)
9.9. Student feedback evaluation

### Phase 10: Backend — Communication
10.1. Announcement model and CRUD
10.2. Role-based announcement visibility
10.3. Message thread system (threads, participants, messages)
10.4. Staff ↔ student messaging
10.5. Read/unread tracking
10.6. Thread archiving

### Phase 11: Backend — Dashboard & Reports
11.1. Dashboard controller with role-specific KPIs
11.2. Compliance summary logic
11.3. Section compliance breakdown
11.4. Evaluation averages
11.5. Deployed per company report
11.6. Missing documents report
11.7. Attendance export (CSV)
11.8. Settings management (campus geofence, semester)

### Phase 12: Frontend Development
12.1. Layout and navigation (sidebar, header)
12.2. Login and authentication pages
12.3. Student portal pages
12.4. Staff management pages (CRUD tables, forms)
12.5. Dashboard implementation with KPI cards
12.6. Report views and exports
12.7. Mobile-responsive check-in page
12.8. Alpine.js components (modals, auto-save, AJAX forms)
12.9. HTMX partials for search/filter

### Phase 13: Integration & Testing
13.1. Email queue setup
13.2. OpenStreetMap API integration testing
13.3. File storage symlink configuration
13.4. Unit testing (models, services)
13.5. Feature testing (controllers, workflows)
13.6. Integration testing (cross-module flows)
13.7. User acceptance testing
13.8. Performance testing (import, attendance)

### Phase 14: Deployment & Documentation
14.1. Server setup (XAMPP/Laravel requirements)
14.2. Environment configuration (.env, .env.testing)
14.3. Database migration and seeding
14.4. Queue worker configuration
14.5. SSL/HTTPS setup
14.6. System documentation
14.7. User manual
14.8. Staff training and student orientation

---

## SECTION 12: PERT ANALYSIS

### Task Estimates (in days)

| ID | Task | Predecessor | O | M | P | TE |
|---|---|---|---|---|---|---|
| A | Requirements Gathering | — | 5 | 10 | 15 | 10.0 |
| B | System Analysis | A | 3 | 5 | 10 | 5.5 |
| C | Database Design | B | 3 | 5 | 7 | 5.0 |
| D | UI/UX Design | B | 5 | 8 | 12 | 8.2 |
| E | Auth/Infrastructure | C | 3 | 5 | 8 | 5.2 |
| F | Student Management | C | 4 | 6 | 10 | 6.3 |
| G | Company Management | C | 3 | 5 | 8 | 5.2 |
| H | Deployment Management | F, G | 3 | 4 | 7 | 4.3 |
| I | Workflow Engine | C | 5 | 8 | 12 | 8.2 |
| J | Document Management | I, F | 4 | 6 | 10 | 6.3 |
| K | Attendance Monitoring | C, F | 6 | 10 | 15 | 10.2 |
| L | Weekly Journals | F, H | 4 | 6 | 10 | 6.3 |
| M | DTR Management | K | 3 | 5 | 8 | 5.2 |
| N | Certificates | F | 2 | 3 | 5 | 3.2 |
| O | Evaluations | F, H | 3 | 5 | 8 | 5.2 |
| P | Announcements | C | 1 | 2 | 3 | 2.0 |
| Q | Messaging | C | 4 | 7 | 10 | 7.0 |
| R | Doc Forwarding | J | 3 | 4 | 7 | 4.3 |
| S | Reports | F, G, H, K | 4 | 6 | 10 | 6.3 |
| T | Dashboard | S | 2 | 3 | 5 | 3.2 |
| U | Settings | C | 1 | 2 | 3 | 2.0 |
| V | Frontend Integration | D, E–U | 10 | 15 | 20 | 15.0 |
| W | Testing | V | 8 | 12 | 18 | 12.3 |
| X | Deployment | W | 2 | 3 | 5 | 3.2 |
| Y | Documentation | A–W | 5 | 8 | 12 | 8.2 |
| Z | Training | X, Y | 3 | 5 | 7 | 5.0 |

**TE Formula**: TE = (O + 4M + P) / 6

**Example (Task A)**: TE = (5 + 4(10) + 15) / 6 = 60 / 6 = 10.0 days

**Example (Task K)**: TE = (6 + 4(10) + 15) / 6 = 61 / 6 = 10.2 days

---

## SECTION 13: CPM ANALYSIS

### Activity List with TE Durations

| ID | Task | Predecessor | Duration (TE) |
|---|---|---|---|
| A | Requirements Gathering | — | 10 |
| B | System Analysis | A | 6 |
| C | Database Design | B | 5 |
| D | UI/UX Design | B | 8 |
| E | Auth/Infrastructure | C | 5 |
| F | Student Management | C | 6 |
| G | Company Management | C | 5 |
| H | Deployment Management | F, G | 4 |
| I | Workflow Engine | C | 8 |
| J | Document Management | I, F | 6 |
| K | Attendance Monitoring | C, F | 10 |
| L | Weekly Journals | F, H | 6 |
| M | DTR Management | K | 5 |
| N | Certificates | F | 3 |
| O | Evaluations | F, H | 5 |
| P | Announcements | C | 2 |
| Q | Messaging | C | 7 |
| R | Doc Forwarding | J | 4 |
| S | Reports | F, G, H, K | 6 |
| T | Dashboard | S | 3 |
| U | Settings | C | 2 |
| V | Frontend Integration | D, E, F, G, H, I, J, K, L, M, N, O, P, Q, R, S, T, U | 15 |
| W | Testing | V | 12 |
| X | Deployment | W | 3 |
| Y | Documentation | A through W (parallel) | 8 |
| Z | Training | X, Y | 5 |

### Critical Path Calculation

**Path 1**: A → B → C → F → H → L → V → W → X → Z
Duration: 10 + 6 + 5 + 6 + 4 + 6 + 15 + 12 + 3 + 5 = **72 days**

**Path 2**: A → B → C → F → J → R → V → W → X → Z
Duration: 10 + 6 + 5 + 6 + 6 + 4 + 15 + 12 + 3 + 5 = **72 days**

**Path 3**: A → B → C → F → K → M → V → W → X → Z
Duration: 10 + 6 + 5 + 6 + 10 + 5 + 15 + 12 + 3 + 5 = **77 days** ← CRITICAL PATH

**Path 4**: A → B → C → I → J → R → V → W → X → Z
Duration: 10 + 6 + 5 + 8 + 6 + 4 + 15 + 12 + 3 + 5 = **74 days**

**Path 5**: A → B → D → V → W → X → Z
Duration: 10 + 6 + 8 + 15 + 12 + 3 + 5 = **59 days**

### Critical Path: A → B → C → F → K → M → V → W → X → Z (77 days)

The **Attendance Monitoring** module (Task K, 10.2 days) is the most complex component due to geofence implementation, and combined with DTR (Task M, 5.2 days), this path drives the project timeline. These tasks depend on Student Management (Task F) and Database Design (Task C).

**Slack Analysis**: Tasks on the critical path have zero slack — any delay directly extends the project. Tasks D (UI/UX), P (Announcements), Q (Messaging), and U (Settings) have significant slack and can be developed in parallel with critical path tasks.

---

## SECTION 14: GANTT CHART PREPARATION

### Task Schedule (in days from project start)

| ID | Task | Duration | Start | End | Dependencies |
|---|---|---|---|---|---|
| A | Requirements Gathering | 10 | 0 | 10 | — |
| B | System Analysis | 6 | 10 | 16 | A |
| C | Database Design | 5 | 16 | 21 | B |
| D | UI/UX Design | 8 | 16 | 24 | B |
| E | Auth/Infrastructure | 5 | 21 | 26 | C |
| F | Student Management | 6 | 21 | 27 | C |
| G | Company Management | 5 | 21 | 26 | C |
| H | Deployment Management | 4 | 27 | 31 | F, G |
| I | Workflow Engine | 8 | 21 | 29 | C |
| J | Document Management | 6 | 29 | 35 | I, F |
| K | Attendance Monitoring | 10 | 27 | 37 | C, F |
| L | Weekly Journals | 6 | 31 | 37 | F, H |
| M | DTR Management | 5 | 37 | 42 | K |
| N | Certificates | 3 | 27 | 30 | F |
| O | Evaluations | 5 | 31 | 36 | F, H |
| P | Announcements | 2 | 21 | 23 | C |
| Q | Messaging | 7 | 21 | 28 | C |
| R | Doc Forwarding | 4 | 35 | 39 | J |
| S | Reports | 6 | 37 | 43 | F, G, H, K |
| T | Dashboard | 3 | 43 | 46 | S |
| U | Settings | 2 | 21 | 23 | C |
| V | Frontend Integration | 15 | 46 | 61 | D, E–U |
| W | Testing | 12 | 61 | 73 | V |
| X | Deployment | 3 | 73 | 76 | W |
| Y | Documentation | 8 | 10 | 18 (parallel) | A–W |
| Z | Training | 5 | 76 | 81 | X, Y |

### Milestones

| Milestone | Day | Description |
|---|---|---|
| M1 | Day 10 | Requirements approved |
| M2 | Day 21 | Database schema finalized |
| M3 | Day 27 | Core student/company modules complete |
| M4 | Day 37 | Attendance + journal modules complete |
| M5 | Day 46 | All backend modules integrated |
| M6 | Day 61 | Frontend integration complete |
| M7 | Day 73 | Testing complete |
| M8 | Day 76 | Deployment live |
| M9 | Day 81 | Training complete, project closed |

### Dependency Rationale

- **B → C**: System analysis outputs define entities, attributes, and relationships needed for schema design
- **C → All modules**: Every module depends on the database tables being defined first
- **F + G → H**: Deployments link students to companies, so both modules must be complete
- **I → J**: Document workflow engine must exist before implementing document submission routing
- **F + C → K**: Attendance needs student records and geofence settings in the database
- **K → M**: DTR auto-populates from attendance data
- **S → T**: Dashboard displays summary data computed by the reports module
- **V**: Cannot start until all backend modules provide their API endpoints and views
- **Y (Documentation)**: Runs in parallel from day 10, compiled throughout development

---

## SECTION 15: SYSTEM VALIDATION RULES

### Authentication & Access

| Rule | Field | Validation | Purpose |
|---|---|---|---|
| VR-01 | student_number | required, digits:8 | Ensure valid 8-digit student ID |
| VR-02 | contact_number | required, regex:/^09[0-9]{9}$/ | Philippine mobile format (09XXXXXXXXX) |
| VR-03 | email (student) | nullable, email, max:255 | Valid email if provided |
| VR-04 | email (staff) | required, email, max:255 | Staff login identifier |
| VR-05 | password | required, string, min:8 | Minimum password length |
| VR-06 | password_confirmation | required_with:password, same:password | Password match confirmation |

### File Uploads

| Rule | Field | Validation | Purpose |
|---|---|---|---|
| VR-07 | document upload | required, file, max:2048, mimes:pdf,doc,docx | Student document restrictions |
| VR-08 | import file | required, file, max:10240, mimes:xlsx,xls,csv | Bulk import restrictions |
| VR-09 | journal file | required, file, max:10240, mimes:pdf,doc,docx,jpg,jpeg,png | Journal attachment restrictions |
| VR-10 | document MIME | application/pdf, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document | Strict MIME type check (closure validation) |
| VR-11 | document extension | pdf, doc, docx | Extension check (closure validation) |

### Business Logic

| Rule | Validation | Purpose |
|---|---|---|
| VR-12 | student_number unique (students) | Prevent duplicate student records |
| VR-13 | student_id unique (student_accounts) | One account per student |
| VR-14 | attendance (student_id + date) unique | One check-in per day |
| VR-15 | daily_time_records (student_id + deployment_id + date) unique | One DTR entry per day |
| VR-16 | evaluation (student_id + deployment period + type) | One evaluation per type per deployment |
| VR-17 | section ∈ {A, B, C, D} | Valid section assignment |
| VR-18 | score ∈ [1, 100] | Valid evaluation score |
| VR-19 | geofence_radius ∈ [10, 5000] | Valid geofence radius in meters |
| VR-20 | attendance passcode = 6 digits | Valid passcode format |
| VR-21 | company name unique (LOWER TRIM) | Prevent duplicate company names |
| VR-22 | journal activities max 2000 chars/task | Prevent excessively long descriptions |
| VR-23 | review remarks max 1000 chars | Keep review notes concise |

### Workflow Validations

| Rule | Validation | Purpose |
|---|---|---|
| VR-24 | Action must be in allowedActions() list | Prevent invalid workflow transitions |
| VR-25 | Note required for return_for_revision | Ensure reason is provided for returns |
| VR-26 | Current holder must match actor role | Role-based access control |
| VR-27 | HTE token must be unused (used_at null) | One-time use enforcement |
| VR-28 | HTE token must not be expired (expires_at > now) | Time-limited access |

---

## SECTION 16: SECURITY ANALYSIS

### Authentication

| Component | Implementation |
|---|---|
| **Staff Login** | Laravel `Auth::attempt()` against `users` table. Bcrypt password hashing (cost 12). Email verification required. First-login flag forces password change. |
| **Student Login** | Custom authentication against `student_accounts` table. Student number + password validated with `Hash::check()`. OTP as second factor (6-digit code via email, session-stored, single-use). |
| **Session Management** | Staff: Laravel web guard with encrypted cookies. Student: Custom session with `student_account_id`. 30-minute idle timeout via `studentLatestActivity` timestamp. Sessions are mutually exclusive — staff login clears student session and vice versa. |
| **OTP** | Random 6-digit string stored in session. Sent via dedicated `StudentPortalOtpMail` mailable. Throttled to prevent brute force. Single-use per verification attempt. |

### Authorization

| Component | Implementation |
|---|---|
| **Role Middleware** | `RoleMiddleware` checks `auth()->user()->role` against comma-separated allowed roles. Aborts with 403 if unauthorized. |
| **Route Registration** | Routes grouped by role-based middleware. Instructor-only routes (import, CRUD), instructor+chairperson routes (announcements), institutional monitoring routes (instructor+chairperson+dean), student routes (student.auth prefix). |
| **Central Role Config** | `InternshipRoles` class defines role groupings. Single source of truth for: staffEmailRoles, programAdministratorRoles, institutionalMonitoringRoles, deploymentViewerRoles, etc. |
| **Sidebar Visibility** | `staffSidebarShows()` controls menu item visibility per role. Chairperson/dean have restricted sidebar (hidden menu items). |
| **Student Access** | `EnsureStudentSession` middleware checks `hasFullStudentPortalAccess()` on every request. Limited mode only allows documents + announcements routes. Staff role check on student routes. |

### Data Protection

| Measure | Implementation |
|---|---|
| **Password Hashing** | Bcrypt (cost 12) via Laravel's `Hash::make()` for both users and student_accounts. |
| **CSRF Protection** | `VerifyCsrfToken` middleware on all web routes. Required for all POST/PUT/DELETE requests. |
| **File Access Control** | Ownership checks on file downloads (student can only download their own documents/certificates). Storage on public disk with `storage` symlink. |
| **Input Validation** | All user input validated through `$request->validate()` with specific rules (format, length, type). |
| **SQL Injection** | Eloquent ORM with parameterized queries prevents SQL injection. Raw queries use `whereRaw` with bound parameters. |
| **XSS Prevention** | Blade `{{ }}` auto-escapes output. User-generated content (announcements, remarks) rendered through escaped templates. |
| **Throttling** | Login, OTP, HTE evaluation routes throttled (e.g., `throttle:30,1` for check-in, `throttle:20,1` for HTE). |
| **HTTPS** | Full HTTPS enforcement in production. |

### Audit Trails

| Area | Mechanism |
|---|---|
| **Document Workflow** | `student_document_actions` table records every action: actor, action type, from/to status, note, timestamp. |
| **Attendance Resolution** | `attendances` table tracks `resolved_by`, `resolved_at`, `resolution_note` for flagged records. |
| **Application Errors** | All exceptions logged to `storage/logs/laravel.log` with stack traces and user context (`userId` in context). |
| **Session Activity** | Student sessions tracked via `studentLatestActivity` session variable for idle timeout. |

### Backup Strategy

| Component | Approach |
|---|---|
| **Database** | Daily MySQL dump via cron or artisan command |
| **Uploaded Files** | `storage/app/public/` directory backup alongside database |
| **Configuration** | `.env`, `.env.testing`, config files backed up separately |

---

## SECTION 17: REPORTING AND ANALYTICS

### Dashboard KPIs (Role-Specific)

| Role | KPI Cards |
|---|---|
| Instructor | Total Students, Deployed, Completed, Not Deployed, Partner Companies, Deployments Recorded |
| Chairperson | Pending Your Review (workflow queue), Complete, In Progress, Needs Attention (compliance) |
| Dean | Complete, In Progress, Needs Attention (compliance) + Evaluation Averages (industry, school, student_feedback) |

### Report: Deployed Per Company
- **Purpose**: View which students are deployed to each partner company
- **Data Source**: deployments, companies, students
- **Filters**: (None, but section filter implied)
- **Output**: HTML table with company name, student count per section
- **Route**: `reports.deployed-per-company`

### Report: Missing Documents
- **Purpose**: Identify students who haven't submitted required mandatory documents
- **Data Source**: students, student_documents, required_documents
- **Filters**: Section
- **Output**: HTML table with student name, missing document names
- **Route**: `reports.missing-documents`

### Report: Compliance Summary
- **Purpose**: Overall document compliance statistics
- **Data Source**: students, student_documents, required_documents (is_mandatory = true)
- **Filters**: (None — dashboard-level)
- **Output**: HTML summary with compliant/partial/non-compliant counts
- **Route**: `reports.compliance-summary`

### Report: Deployment Locations
- **Purpose**: Visualize company locations on a map
- **Data Source**: companies (with latitude/longitude), deployments
- **Filters**: (None)
- **Output**: Map view with company markers
- **Route**: `reports.deployment-locations`

### Report: Attendance Export (CSV)
- **Purpose**: Export attendance records for external reporting
- **Data Source**: attendances, students
- **Filters**: Date range, section
- **Output**: CSV file download
- **Route**: `reports.attendance-export`

---

## SECTION 18: COMPLETE END-TO-END WORKFLOW

### Narrative Description

#### How the System Starts

The system begins with **zero data** — an empty MySQL database. An **Instructor (OJT Coordinator)** is the first user, logging in via the staff login page with their college email and password. Laravel's `Auth::attempt()` validates credentials against the `users` table. Upon successful authentication, the instructor lands on the **Dashboard**, which shows empty KPI cards: 0 Total Students, 0 Deployed, 0 Companies.

#### How Records Are Populated

The instructor imports student data from an **Excel file** obtained from the Registrar's Office. The file contains officially enrolled BSIS 4th-year students with their student numbers, names, sections, and contact details. The import processes each row: validates data (8-digit student number, valid phone number), creates `Student` records and `StudentAccount` records (with default password = student number, `first_login = true`). If the Excel includes company names and start dates, the system also creates `Company` and `Deployment` records automatically, linking students to their assigned companies.

Separately, the instructor imports **partner company data** from another Excel file. Each company's address is geocoded via the **OpenStreetMap Nominatim API** to obtain GPS coordinates — essential for the attendance geofencing feature.

#### How Students Access the System

Students receive their **student number** from the instructor. They navigate to the login page, enter "student number + default password", and the system sends a **6-digit OTP** to their registered email. Entering the correct OTP creates a custom session. On first login, the student is forced to **change their password**. After password change, the system checks `hasFullStudentPortalAccess()`:

- **Limited portal**: If the student doesn't yet have an active deployment or hasn't completed all mandatory documents, they can only access **Documents** and **Announcements**.
- **Full portal**: If the student has an active/completed deployment AND all mandatory documents are submitted, they gain access to **Dashboard, Weekly Journals, DTR, Certificates, Messaging, and Evaluations**.

#### How Document Compliance Works

The instructor defines a **Required Document Catalog** — a master checklist of everything students must submit (Resume, Application Letter, Medical Certificate, etc.). Each document can be mandatory or optional, assigned to a phase (Pre/Deployment/Monitoring/Post), and linked to a **Workflow Template** that defines the review chain (e.g., Instructor → Chairperson).

Students upload documents (PDF/DOC/DOCX) through their portal. Each upload:
1. Stores the file on the public disk
2. Creates a `StudentDocument` record
3. **Initializes the workflow**: Sets the first reviewer (typically Instructor), transitions status to 'received'
4. Sends an **email notification** to the reviewer
5. Checks if the student has now completed **all mandatory documents** → if yes, **activates** their deployment

The **Instructor** reviews documents in the Workflow Queue. They can:
- **Approve**: Final approval — document completed
- **Forward**: Send to Chairperson for additional review
- **Return for Revision**: Send back to student with notes

The **Chairperson** (if forwarded) can approve or return. Each action is logged in `student_document_actions` and triggers email notifications.

#### How Attendance Tracking Works

The system uses **geofence-based check-in**. Each company has GPS coordinates and a geofence radius (default 100m). When a student arrives at their company, they:
1. Open the public check-in page (no login needed — accessible from any phone)
2. Enter their **6-digit attendance passcode** (visible on their student dashboard)
3. The browser captures their **GPS location**
4. The system computes the **distance from the company** using the Haversine formula
5. **Geofence decision**:
   - Inside radius → `inside_pass` (clean)
   - Within buffer zone → `near_boundary_review` (flagged)
   - Outside → `outside_flagged` (flagged)
   - GPS unavailable → `location_unavailable` (flagged)

At end of day, the student checks out (time-out). The system records departure time, computes total hours, and auto-populates the **Daily Time Record**.

Flagged attendance records are reviewed by the **Instructor**, who can acknowledge them as valid or mark them as invalid.

#### How Weekly Journals Work

The system **auto-generates weekly journal entries** from the student's deployment start date to today. Each week becomes a draft journal. The student opens a journal and fills in **task descriptions for Monday through Saturday**. Changes are **auto-saved via AJAX** when the student clicks away from a field (no Save button). Supporting files can be uploaded per day.

When ready, the student clicks **Submit** — an Alpine modal confirms the action. The journal status changes to 'submitted' with a timestamp. If submitted after the week's end date, a **late flag** is shown to the instructor.

The **Instructor** reviews submitted journals, can see the activities table with file attachments, the late flag, and supervisor info. They click **"Mark as Reviewed"** (no reject/return option — student owns the journal), and the student receives an email notification.

#### How Evaluations Work

**Three evaluation types**:
1. **Industry** (Company Supervisor): Instructor generates an **HTE link** (one-time token, 7-day expiry) and emails it to the supervisor. The supervisor clicks the link (no login), evaluates the student (score 1-100 + comments), and submits. Token is invalidated after use.
2. **School** (Instructor): Instructor creates an evaluation record with score and comments.
3. **Student Feedback**: Student submits feedback about their internship experience (once per deployment period, duplicate prevention).

Each evaluation type has a **duplicate prevention** rule — one submission per deployment period per type.

#### How Notifications Flow

The system uses email-based notifications via `NotificationService`:
- **Document submitted** → Email to current workflow holder
- **Document reviewed/approved/returned** → Email to student or next reviewer
- **Weekly journal reviewed** → Email to student
- **OTP verification** → Email to student
- **HTE link** → Email to company supervisor
- **Staff password reset** → Email to staff

Emails use Laravel's **queue** system (database driver) for async delivery. Templates use the `NotificationMail` mailable class with structured event data.

#### How Reports Are Generated

Staff can access reports via the Reports module:
- **Deployed Per Company**: Lists students grouped by company
- **Missing Documents**: Students with incomplete mandatory submissions
- **Compliance Summary**: Section-by-section compliance breakdown
- **Deployment Locations**: Map view of company locations
- **Attendance Export**: CSV file export

The **Dashboard** displays real-time KPI cards based on the user's role, including student counts, compliance stats, pending reviews, and evaluation averages.

#### How the Process Ends

The internship lifecycle ends when:
1. All **mandatory documents** are submitted and approved
2. All **weekly journals** are reviewed
3. **Attendance** records complete
4. **Evaluations** (industry, school, student) are submitted
5. **Certificate** is issued and downloaded

The **Instructor** marks the deployment as 'completed' (or it auto-completes when the end_date passes). The student's portal access transitions to a read-only/completed state. Data remains in the system for archival, reporting, and accreditation purposes.

---

## APPENDIX: Entity Relationship Summary

```
users 1──* student_document_actions
users 1──* message_thread_participants
users 1──* announcements
users 1──* document_forwarding_batches
users 1──* remarks
users 1──* evaluations (as evaluator)
     │
students 1──* deployments
students 1──* student_documents
students 1──* weekly_journals
students 1──* certificates
students 1──* attendances
students 1──* daily_time_records
students 1──* evaluations
students 1──* remarks
students 1──* document_forwarding_items
students 1──* monthly_dttrs
students 1──1 student_accounts
     │
companies 1──* deployments
companies 1──* required_documents (nullable)
companies 1──* hte_transaction_links
companies 1──* evaluations (nullable)
companies *──1 company_industries
     │
deployments 1──* weekly_journals
deployments 1──* daily_time_records
deployments 1──* certificates
deployments 1──* monthly_dttrs
     │
required_documents 1──* student_documents
required_documents *──1 document_workflow_templates (nullable)
required_documents *──1 companies (nullable)
     │
document_workflow_templates 1──* document_workflow_steps
document_workflow_templates 1──* required_documents (nullable)
     │
student_documents 1──* student_document_actions
student_documents *──1 document_forwarding_items
     │
message_threads 1──* message_thread_participants
message_threads 1──* messages
     │
document_forwarding_batches 1──* document_forwarding_items
document_forwarding_batches 1──* transmittal_logs
```

---

*End of System Analysis Document. All 18 sections completed.*
