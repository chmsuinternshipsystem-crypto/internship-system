<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;

class UserManualExportService
{
    private PhpWord $phpWord;
    private string $ssDir;
    private string $outputPath;

    public function __construct()
    {
        $this->ssDir = storage_path('app/manuals/screenshots');
        $this->outputPath = storage_path('app/manuals/CHMSU-Internship-System-Users-Manual.docx');
    }

    public function generate(): string
    {
        $this->phpWord = new PhpWord();
        $this->phpWord->setDefaultFontName('Arial');
        $this->phpWord->setDefaultFontSize(11);
        $this->phpWord->getCompatibility()->setOoxmlVersion(15);
        $this->phpWord->getDocInfo()->setTitle('CHMSU Internship System — Users Manual');
        $this->phpWord->getDocInfo()->setCreator('CHMSU Internship System');

        // Styles
        $this->phpWord->addTitleStyle(1, ['name' => 'Arial', 'size' => 18, 'bold' => true, 'color' => '1a56db'], ['spaceAfter' => 240]);
        $this->phpWord->addTitleStyle(2, ['name' => 'Arial', 'size' => 14, 'bold' => true, 'color' => '059669'], ['spaceAfter' => 200]);
        $this->phpWord->addTitleStyle(3, ['name' => 'Arial', 'size' => 12, 'bold' => true, 'color' => '1e293b'], ['spaceAfter' => 160]);
        $this->phpWord->addNumberingStyle('multilevel', [
            'type' => 'multilevel',
            'levels' => [
                ['format' => 'decimal', 'text' => '%1.', 'left' => 360, 'hanging' => 360, 'tab' => 360],
                ['format' => 'decimal', 'text' => '%1.%2.', 'left' => 720, 'hanging' => 360, 'tab' => 720],
                ['format' => 'decimal', 'text' => '%1.%2.%3.', 'left' => 1080, 'hanging' => 360, 'tab' => 1080],
            ]
        ]);

        $section = $this->phpWord->addSection([
            'paperSize' => 'Letter',
            'marginLeft' => Converter::inchToTwip(0.8),
            'marginRight' => Converter::inchToTwip(0.8),
            'marginTop' => Converter::inchToTwip(0.8),
            'marginBottom' => Converter::inchToTwip(0.8),
        ]);

        $this->addTitlePage($section);
        $section->addPageBreak();
        $this->addToc($section);
        $section->addPageBreak();

        // Section 1: Introduction
        $this->writeSection1($section);
        $section->addPageBreak();

        // Section 2: Getting Started
        $this->writeSection2($section);
        $section->addPageBreak();

        // Section 3: Student Portal
        $this->writeSection3($section);
        $section->addPageBreak();

        // Section 4: Instructor Guide
        $this->writeSection4($section);
        $section->addPageBreak();

        // Section 5: Chairperson Guide
        $this->writeSection5($section);
        $section->addPageBreak();

        // Section 6: Dean Guide
        $this->writeSection6($section);
        $section->addPageBreak();

        // Section 7: Common Features
        $this->writeSection7($section);
        $section->addPageBreak();

        // Section 8: Reports & Exports
        $this->writeSection8($section);
        $section->addPageBreak();

        // Section 9: Troubleshooting
        $this->writeSection9($section);

        $writer = IOFactory::createWriter($this->phpWord, 'Word2007');
        $writer->save($this->outputPath);

        return $this->outputPath;
    }

    private function addTitlePage($section): void
    {
        $this->img($section, public_path('images/CHMSU Header.png'), 400);
        $section->addTextBreak(4);

        $section->addText(
            'INTERNSHIP DEPLOYMENT MANAGEMENT',
            ['name' => 'Arial', 'size' => 20, 'bold' => true, 'color' => '1a56db'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        $section->addText(
            'AND DIGITAL DOCUMENTATION',
            ['name' => 'Arial', 'size' => 20, 'bold' => true, 'color' => '1a56db'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        $section->addText(
            'COMPLIANCE MONITORING SYSTEM',
            ['name' => 'Arial', 'size' => 20, 'bold' => true, 'color' => '1a56db'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        $section->addTextBreak(1);
        $section->addText(
            'USERS MANUAL',
            ['name' => 'Arial', 'size' => 16, 'bold' => true, 'color' => '059669'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        $section->addTextBreak(4);
        $section->addText(
            'CHMSU-Talisay BSIS Program',
            ['name' => 'Arial', 'size' => 13, 'color' => '475569'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        $section->addText(
            'Version 2.1 — July 2026',
            ['name' => 'Arial', 'size' => 12, 'color' => '94a3b8'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0]
        );
        $section->addTextBreak(6);
        $section->addText(
            'Four User Roles: Student | Instructor (OJT Coordinator) | Chairperson | Dean',
            ['name' => 'Arial', 'size' => 11, 'italic' => true, 'color' => '64748b'],
            ['alignment' => Jc::CENTER]
        );
    }

    private function addToc($section): void
    {
        $section->addTitle('Table of Contents', 1);
        $section->addTextBreak(1);

        $toc = [
            '1. Introduction' => '3',
            '   1.1 About the System' => '3',
            '   1.2 System Requirements' => '3',
            '   1.3 Login Credentials' => '3',
            '2. Getting Started' => '4',
            '   2.1 Staff Login' => '4',
            '   2.2 Student Login' => '4',
            '   2.3 First-Login Password Change' => '5',
            '3. Student Portal' => '6',
            '   3.1 Dashboard Overview' => '6',
            '   3.2 My Documents' => '7',
            '   3.3 Attendance / Clock In-Out' => '8',
            '   3.4 Weekly Journals' => '9',
            '   3.5 Daily Time Records' => '10',
            '   3.6 Messages' => '11',
            '   3.7 Certificates' => '11',
            '   3.8 Profile Settings' => '12',
            '4. Instructor (OJT Coordinator) Guide' => '13',
            '   4.1 Dashboard' => '13',
            '   4.2 Student Management' => '14',
            '   4.3 Company Management' => '15',
            '   4.4 Deployments' => '16',
            '   4.5 Document Queue' => '17',
            '   4.6 Attendance Monitoring' => '17',
            '   4.7 Evaluations & HTE Links' => '18',
            '   4.8 Weekly Journals Review' => '19',
            '   4.9 DTR Monitoring' => '19',
            '   4.10 Certificates' => '20',
            '   4.11 Reports' => '20',
            '   4.12 Campus Settings' => '21',
            '   4.13 Messages & Announcements' => '21',
            '5. Chairperson Guide' => '22',
            '6. Dean Guide' => '23',
            '7. Common Features' => '24',
            '8. Reports & Exports' => '25',
            '9. Troubleshooting' => '26',
        ];

        foreach ($toc as $entry => $page) {
            $isHeading = !str_starts_with($entry, '   ');
            $section->addText(
                $entry . str_repeat('.', 60 - strlen($entry)) . ' ' . $page,
                ['name' => 'Arial', 'size' => $isHeading ? 11 : 10, 'bold' => $isHeading],
                ['spaceAfter' => 40]
            );
        }
    }

    // ================================================================
    // SECTION 1: INTRODUCTION
    // ================================================================
    private function writeSection1($section): void
    {
        $section->addTitle('1. Introduction', 1);
        $section->addTitle('1.1 About the System', 2);
        $section->addText('The Internship Deployment Management and Digital Documentation Compliance Monitoring System is a web-based platform designed to manage the entire lifecycle of student internships for the BSIS program at Carlos Hilado Memorial State University — Talisay. It replaces manual paperwork and fragmented communication with a centralized digital system that handles student deployment, document compliance, attendance monitoring, weekly journaling, performance evaluation, and reporting.');
        $section->addTextBreak(1);
        $section->addText('The system supports four user roles, each with permissions tailored to their responsibilities:');
        $roles = ['Student — submits documents, clocks in/out, writes journals, views grades', 'Instructor (OJT Coordinator) — manages students, companies, documents, attendance', 'Chairperson — oversees program compliance, reviews forwarded documents', 'Dean — high-level oversight of program performance'];
        foreach ($roles as $r) {
            $section->addText('• ' . $r, ['name' => 'Arial', 'size' => 11], ['spaceAfter' => 40]);
        }

        $section->addTitle('1.2 System Requirements', 2);
        $reqs = [
            'Web Browser: Google Chrome 120+, Mozilla Firefox 115+, or Microsoft Edge 120+',
            'Internet: Broadband connection (minimum 1 Mbps)',
            'Screen Resolution: 1024 × 768 or higher recommended',
            'Mobile: Android 10+ or iOS 14+ for student check-in features',
            'JavaScript must be enabled in the browser',
        ];
        foreach ($reqs as $r) {
            $section->addText('• ' . $r, ['name' => 'Arial', 'size' => 11], ['spaceAfter' => 40]);
        }

        $section->addTitle('1.3 Login Credentials', 2);
        $section->addText('Staff users log in using their CHMSU email address and password. Students log in using their student number (default password is also their student number). On first login, students verify via OTP sent to their registered email, then are prompted to change their password.');
        $this->addScreenshot($section, 'shared/login-page', 'The CHMSU Internship System login page — choose between Student and Faculty & Staff portals.');
    }

    // ================================================================
    // SECTION 2: GETTING STARTED
    // ================================================================
    private function writeSection2($section): void
    {
        $section->addTitle('2. Getting Started', 1);

        $section->addTitle('2.1 Staff Login', 2);
        $section->addText('① On the login page, click the "Faculty & Staff" button.');
        $section->addText('② Select your role from the dropdown (Instructor, Chairperson, or Dean).');
        $section->addText('③ Enter your CHMSU email address and password.');
        $section->addText('④ Click "Sign in" to access your personalized dashboard.');
        $this->addScreenshot($section, 'shared/login-filled', 'The staff login form with role selector and credentials filled.');
        $section->addPageBreak();

        $section->addTitle('2.2 Student Login', 2);
        $section->addText('① On the login page, click the "Student Internship Portal" button.');
        $section->addText('② Enter your student number (e.g., 20230001).');
        $section->addText('③ Enter your password (default: your student number).');
        $section->addText('④ Click "Sign in". An OTP code will be sent to your registered email.');
        $section->addText('⑤ Enter the 6-digit OTP code to verify your identity.');
        $section->addText('⑥ If this is your first login, set a new password.');
        $this->addScreenshot($section, 'shared/student-login-filled', 'Student login form with student number and password fields.');

        $section->addTitle('2.3 First-Login Password Change', 2);
        $section->addText('After OTP verification, first-time users are required to set a new password. Choose a password with at least 8 characters. Your new password will be used for all subsequent logins.');
        $this->addScreenshot($section, 'student/dashboard', 'The student dashboard showing quick actions, document progress, and announcements.');
    }

    // ================================================================
    // SECTION 3: STUDENT PORTAL
    // ================================================================
    private function writeSection3($section): void
    {
        $section->addTitle('3. Student Portal', 1);

        $section->addTitle('3.1 Dashboard Overview', 2);
        $section->addText('The student dashboard is your home base. It displays:');
        $items = ['Quick Clock In/Out buttons (AM/PM sessions)', 'Document progress summary (Pre/Monitoring/Post phases)', 'Deployment status and grade card', 'Recent announcements', 'Quick action buttons for Documents, DTR, Journal, Messages'];
        foreach ($items as $i) {
            $section->addText('• ' . $i, ['name' => 'Arial', 'size' => 11], ['spaceAfter' => 40]);
        }
        $section->addTextBreak(1);
        $section->addText('Note: Students with a pending deployment see a limited portal (documents + announcements only). Full access is granted when all Pre-deployment documents are approved and the deployment is activated.');
        $this->addScreenshot($section, 'student/documents', 'My Documents page showing the 14 required documents organized by phase.');
        $section->addPageBreak();

        $section->addTitle('3.2 My Documents', 2);
        $section->addText('The My Documents page shows all required internship documents organized by phase:');
        $phases = ['Pre-Deployment (8 documents) — Pledge of Good Conduct, MOA, Internship Agreement, Parent Consent, Application Letter, Endorsement Letter, Resume, Acceptance Letter', 'Monitoring (4 documents) — Enrolment Form, NBI Clearance (optional), Medical Certificate, Training Plan', 'Post-Deployment (2 documents) — Signed DTTR, Signed Weekly Journal'];
        foreach ($phases as $p) {
            $section->addText('• ' . $p, ['name' => 'Arial', 'size' => 11], ['spaceAfter' => 40]);
        }
        $section->addTextBreak(1);
        $section->addText('To upload a document: Select the file (PDF, DOC, DOCX; max 2MB), then click Upload. Documents flow through a review process: Submitted → In Review → Approved or Returned.');
        $this->addScreenshot($section, 'student/documents-missing', 'The Missing Documents tab helps students quickly identify what still needs to be submitted.');

        $section->addTitle('3.3 Attendance / Clock In-Out', 2);
        $section->addText('The system uses a 4-session attendance model:');
        $sessions = ['AM Check-In (morning arrival)', 'AM Check-Out (lunch break)', 'PM Check-In (afternoon return)', 'PM Check-Out (end of day)'];
        foreach ($sessions as $s) {
            $section->addText('• ' . $s, ['name' => 'Arial', 'size' => 11], ['spaceAfter' => 40]);
        }
        $section->addTextBreak(1);
        $section->addText('Students can clock in/out from the dashboard (1-click, no passcode needed for authenticated sessions) or via the public check-in page using their student number and attendance passcode. A 10-meter geofence validates that the student is at their assigned company location.');
        $this->addScreenshot($section, 'student/attendance-checkin', 'The public attendance check-in page with student number and passcode fields.');
        $section->addPageBreak();

        $section->addTitle('3.4 Weekly Journals', 2);
        $section->addText('Weekly journals are auto-generated from the deployment start date. Each week shows Monday through Saturday with activity fields for each day. The Alpine.js inline editor auto-saves your entries every 600ms of inactivity. You can attach files to each day\'s entry. Once all days are filled and the week ends, the journal auto-submits.');
        $this->addScreenshot($section, 'student/weekly-journals', 'The weekly journal editor with inline activity fields and file uploads.');

        $section->addTitle('3.5 Daily Time Records', 2);
        $section->addText('The DTR is auto-populated from your attendance check-ins. View it in a month calendar format. You can add task descriptions for each day (editable for the current date only) and download DTTR reports as DOCX.');
        $this->addScreenshot($section, 'student/dtr-calendar', 'The DTR calendar view showing daily attendance summaries.');

        $section->addTitle('3.6 Messages', 2);
        $section->addText('Internal messaging allows you to communicate with instructors and staff. The WhatsApp-style interface shows your conversation list on the left and the active conversation on the right. Messages support read/unread tracking and archiving.');
        $this->addScreenshot($section, 'student/messages', 'Student messaging interface showing inbox and conversation panels.');
        $this->addScreenshot($section, 'student/messages-create', 'The new message form with participant selector.');

        $section->addTitle('3.7 Certificates', 2);
        $section->addText('Upon completing 600 hours and all required documents, your instructor will upload a completion certificate. You can view and download it from the Certificates page.');
        $this->addScreenshot($section, 'student/certificates', 'Certificates page showing available certificates for download.');

        $section->addTitle('3.8 Profile Settings', 2);
        $section->addText('Edit your email (used for OTP and notifications), contact number (Philippine mobile format), or password. Your name, student number, and section are read-only — these come from registrar data.');
        $this->addScreenshot($section, 'student/profile', 'Profile settings page showing the edit form with helper text.');
    }

    // ================================================================
    // SECTION 4: INSTRUCTOR GUIDE
    // ================================================================
    private function writeSection4($section): void
    {
        $section->addTitle('4. Instructor (OJT Coordinator) Guide', 1);

        $section->addTitle('4.1 Dashboard', 2);
        $section->addText('The instructor dashboard provides at-a-glance KPIs: total students, deployed count, pending documents, at-risk alerts, and section compliance. The supervised students card shows your assignees with pending action counts. KPIs auto-refresh every 60 seconds.');
        $this->addScreenshot($section, 'instructor/dashboard', 'The instructor dashboard with KPI cards, at-risk alert, and supervised students panel.');
        $section->addPageBreak();

        $section->addTitle('4.2 Student Management', 2);
        $section->addText('The Students page lists all BSIS interns. Use the search bar, section filter, and deployment status toggle (All/Pending/Deployed) to find students. The "My Students" checkbox filters to your assigned students only. Batch operations allow deploying or assigning instructors to multiple students at once.');
        $this->addScreenshot($section, 'instructor/students-list', 'Students list with search, filters, and batch action bar.');
        $this->addScreenshot($section, 'instructor/students-batch-bar', 'The batch operations bar at the bottom of the students table.');

        $section->addTextBreak(1);
        $section->addText('Creating a new student: Fill in personal information, select their section, assigned instructor, and OJT type (Unplaced/Internal/External). For External OJT, select the partner company. The system automatically creates a pending deployment and a student account with the student number as the default password.');
        $this->addScreenshot($section, 'instructor/student-create', 'Student creation form with OJT type selection and company dropdown.');

        $section->addTextBreak(1);
        $section->addText('Importing students from Excel: Use the Import page to upload an Excel file with student data. The system validates each row, creates accounts, and reports the results. Duplicate student numbers are detected and updated rather than duplicated.');
        $this->addScreenshot($section, 'instructor/student-import', 'Student import page with file upload and format instructions.');

        $section->addTextBreak(1);
        $section->addText('The tabbed student profile provides a comprehensive view: Profile info, Documents, Journals, DTR, Attendance, and Certificates — all in one page with URL hash persistence.');
        $this->addScreenshot($section, 'instructor/student-show-tabbed', 'The tabbed student profile view with 6 content tabs.');
        $section->addPageBreak();

        $section->addTitle('4.3 Company Management', 2);
        $section->addText('Maintain your directory of partner companies. Each company record includes name, industry classification, complete address (with PSGC cascading dropdown for province/city/barangay), contact person details, and geofence coordinates for attendance validation.');
        $this->addScreenshot($section, 'instructor/companies-list', 'Companies list with geofence badge and search filters.');
        $this->addScreenshot($section, 'instructor/company-create', 'Company creation form with address cascading dropdown.');
        $this->addScreenshot($section, 'instructor/company-show', 'Company detail page showing information, assigned students, and geofence status.');
        $this->addScreenshot($section, 'instructor/company-edit', 'Company edit page with student assignment hub for batch-assigning students.');

        $section->addTitle('4.4 Deployments', 2);
        $section->addText('Deployments link students to companies. Status is auto-computed: Pending → Active (when all Pre-docs approved) → Completed (at 600 hours or past end_date). Filter by status and search by student name.');
        $this->addScreenshot($section, 'instructor/deployments-list', 'Deployments list with status filter and auto-computed status badges.');
        $section->addPageBreak();

        $section->addTitle('4.5 Document Queue', 2);
        $section->addText('The Document Queue is your central review hub. Documents are grouped by student (expandable accordion). Review submitted documents — approve them to advance the workflow, or return them with a note for revision. When all 8 Pre-deployment documents are approved, the system auto-activates the student\'s deployment.');
        $this->addScreenshot($section, 'instructor/document-queue', 'Document Queue with grouped-by-student accordion layout.');
        $this->addScreenshot($section, 'instructor/queue-grouped-by-student', 'Expanded student group showing individual documents with Review buttons.');

        $section->addTitle('4.6 Attendance Monitoring', 2);
        $section->addText('Monitor student attendance with geofence status indicators. Each attendance record shows the 4 sessions (AM In/Out, PM In/Out), geofence status (inside/near/outside), and resolution status. Use the batch resolve feature to clear flagged entries with a resolution note.');
        $this->addScreenshot($section, 'instructor/attendance', 'Attendance monitoring page with filters and batch resolve bar.');

        $section->addTitle('4.7 Evaluations & HTE Links', 2);
        $section->addText('Create evaluations for students and send one-time HTE (Host Training Establishment) links to company supervisors. The HTE form includes 18 criteria across 3 categories (Work Habits, Work Skills, Social Skills) rated 1-5. Supervisors can submit without logging in. You can also view evaluation scores and export completed evaluations as DOCX.');
        $this->addScreenshot($section, 'instructor/evaluations-list', 'Evaluations list with section filter.');
        $this->addScreenshot($section, 'instructor/send-hte-link', 'Send HTE evaluation link form with company supervisor details.');
        $section->addPageBreak();

        $section->addTitle('4.8 Weekly Journals Review', 2);
        $section->addText('Review submitted weekly journals organized by student. The grouped accordion shows pending review counts per student. Mark journals as Reviewed when satisfied. Late submissions are flagged with a red badge. Download individual journals as DOCX.');
        $this->addScreenshot($section, 'instructor/weekly-journals', 'Weekly journals review page with student-grouped accordion and status filters.');

        $section->addTitle('4.9 DTR Monitoring', 2);
        $section->addText('View student Daily Time Records in a calendar format. The dual-panel layout shows the student list on one side and the selected student\'s DTR table on the other. Each row shows AM/PM check-in/out times, total hours, and task descriptions.');
        $this->addScreenshot($section, 'instructor/dtr-calendar', 'DTR monitoring page with scrollable student list and daily records table.');

        $section->addTitle('4.10 Certificates', 2);
        $section->addText('Upload completion certificates for students who have finished their internship. Certificates can be verified (bulk verify available) and students receive notification. Students can view and download certificates from their portal.');
        $this->addScreenshot($section, 'instructor/certificates-list', 'Certificates list showing uploaded certificates with verify actions.');
        $this->addScreenshot($section, 'instructor/certificate-create', 'Certificate upload form.');

        $section->addTitle('4.11 Reports', 2);
        $section->addText('Access four report tabs: Deployed per Company, Missing Documents, Compliance Summary, and Attendance Export. Each tab supports filters (search, section, My Students) and export options (PDF, CSV). The Deployed tab shows which students are at which companies. The Missing Documents tab shows students with incomplete requirements.');
        $this->addScreenshot($section, 'instructor/reports', 'Reports dashboard with tabbed navigation and HTMX-powered filtering.');
        $section->addPageBreak();

        $section->addTitle('4.12 Campus Settings', 2);
        $section->addText('Configure the campus geofence (lat/lng coordinates, radius), attendance time windows (AM: 7:00-12:00, PM: 1:00-6:00 with grace period), semester/academic year tracking, and instructor management. Use the Leaflet map to visually set the campus boundary.');
        $this->addScreenshot($section, 'instructor/campus-settings', 'Campus settings page with geofence configuration and time window editors.');

        $section->addTitle('4.13 Messages & Announcements', 2);
        $section->addText('Use the WhatsApp-style messaging panel to communicate with students and staff. The left panel shows conversation threads; clicking one opens the conversation on the right. Create announcements to broadcast information to all students, staff, or specific roles.');
        $this->addScreenshot($section, 'instructor/messages-inbox', 'Instructor messaging interface with split-panel layout.');
        $this->addScreenshot($section, 'instructor/messages-create', 'New message form with student/recipient selector.');
        $this->addScreenshot($section, 'instructor/announcements-list', 'Announcements list with create/edit/delete actions.');

        // Extra instructor pages
        $this->addScreenshot($section, 'instructor/archive-list', 'Archive page showing archived students, companies, and deployments with school year filter.');
        $this->addScreenshot($section, 'instructor/company-industries', 'Company industry classification management.');
        $this->addScreenshot($section, 'instructor/evaluation-criteria', 'Evaluation criteria settings page with category-grouped criteria management.');
        $this->addScreenshot($section, 'instructor/document-forwarding', 'Document forwarding batch management for physical document transmittal.');
        $this->addScreenshot($section, 'instructor/notifications', 'In-app notification inbox showing all system notifications.');
        $this->addScreenshot($section, 'instructor/required-documents', 'Required documents master list with phase assignments and template uploads.');
    }

    // ================================================================
    // SECTION 5: CHAIRPERSON GUIDE
    // ================================================================
    private function writeSection5($section): void
    {
        $section->addTitle('5. Chairperson Guide', 1);

        $section->addTitle('5.1 Dashboard', 2);
        $section->addText('The chairperson dashboard provides a high-level overview of program health: total students, deployed count, pending documents, and section compliance rates. Use this to identify which sections or students need attention.');
        $this->addScreenshot($section, 'chairperson/dashboard', 'Chairperson dashboard with KPI cards and section compliance widget.');

        $section->addTitle('5.2 Student Viewing', 2);
        $section->addText('View student profiles and progress. The student list shows document and journal progress percentages. You can drill into individual students to see their complete profile, documents, and compliance status.');
        $this->addScreenshot($section, 'chairperson/students-view', 'Students list as seen by chairperson (read-only view).');

        $section->addTitle('5.3 Compliance Monitoring', 2);
        $section->addText('The Compliance page shows a comprehensive view of document completion across all students. Filter by status (compliant/partial/non-compliant), section, or risk flags. At-risk students are flagged with warning indicators.');
        $this->addScreenshot($section, 'chairperson/compliance', 'Compliance overview page with status filters and risk flags.');

        $section->addTitle('5.4 Reports', 2);
        $section->addText('Access the deployed per company, missing documents, and compliance summary reports. Export data as PDF or CSV for your reporting needs.');
        $this->addScreenshot($section, 'chairperson/reports', 'Reports page with tabbed navigation and export options.');

        $section->addTitle('5.5 Announcements & Messages', 2);
        $section->addText('Create and manage announcements visible to students and staff. Use the messaging system to communicate with instructors and deans.');
        $this->addScreenshot($section, 'chairperson/announcements', 'Announcements page with create button.');
        $this->addScreenshot($section, 'chairperson/messages', 'Messaging interface for chairperson communications.');
    }

    // ================================================================
    // SECTION 6: DEAN GUIDE
    // ================================================================
    private function writeSection6($section): void
    {
        $section->addTitle('6. Dean Guide', 1);

        $section->addTitle('6.1 Dashboard', 2);
        $section->addText('The dean dashboard shows executive-level KPIs: total students, deployed count, evaluation completion rate, and average evaluation scores. This provides a quick pulse check on program health.');
        $this->addScreenshot($section, 'dean/dashboard', 'Dean dashboard with executive KPI cards and evaluation overviews.');

        $section->addTitle('6.2 Student Viewing', 2);
        $section->addText('Browse the student registry to view student profiles and their document progress.');
        $this->addScreenshot($section, 'dean/students-view', 'Student list as seen by dean (view-only).');

        $section->addTitle('6.3 Compliance Overview', 2);
        $section->addText('Monitor overall compliance rates across all sections. Filter and drill into the data to identify areas needing improvement.');
        $this->addScreenshot($section, 'dean/compliance', 'Compliance overview for dean-level monitoring.');

        $section->addTitle('6.4 Executive Summary Report', 2);
        $section->addText('The Executive Summary report provides a dean-specific view of program KPIs, including pipeline metrics, company satisfaction data, document compliance rates, and evaluation feedback counts.');
        $this->addScreenshot($section, 'dean/executive-summary', 'Executive Summary report with high-level KPIs and trend data.');

        $section->addTitle('6.5 Announcements & Messages', 2);
        $section->addText('View announcements and communicate via the messaging system.');
        $this->addScreenshot($section, 'dean/announcements', 'Announcements page (view-only for dean).');
        $this->addScreenshot($section, 'dean/messages', 'Dean messaging interface.');
    }

    // ================================================================
    // SECTION 7: COMMON FEATURES
    // ================================================================
    private function writeSection7($section): void
    {
        $section->addTitle('7. Common Features', 1);

        $section->addTitle('7.1 Navigation', 2);
        $section->addText('The sidebar navigation is role-aware — each role sees only the menu items they have permission to access. On mobile, a bottom navigation bar provides quick access to the most-used pages. Use the Cmd+K (or Ctrl+K) keyboard shortcut to open the global command palette for searching pages, students, and companies.');
        $section->addTextBreak(1);
        $section->addText('Desktop sidebar shows: Companies, Students, Deployments, Required Documents, Compliance, Document Queue, Attendance, Evaluations, Weekly Journals, DTR, Certificates, Announcements, Messages, Reports, Archive, and Settings — role-permitting.');

        $section->addTitle('7.2 Notification Bell', 2);
        $section->addText('The notification bell in the top bar shows unread count. Click it to see recent notifications. The bell polls every 30 seconds for new notifications and dispatches events to refresh visible page content. Key notification triggers include: document submissions, deployment activations, new messages, certificate uploads, risk flags, and automated reminders.');

        $section->addTitle('7.3 Floating Action Button (FAB)', 2);
        $section->addText('The floating "+" button in the bottom-right corner provides quick access to role-specific actions: Create Student, Add Company, New Evaluation, Send Message, or New Announcement.');

        $section->addTitle('7.4 Search & Filter Controls', 2);
        $section->addText('All list pages feature HTMX-powered search and filter controls. Filters auto-submit via HTMX for instant partial page updates. Key filters include: section dropdown, My Students checkbox, deployment status, risk flag toggle, and document status. The "Select All Matching" checkbox enables batch operations across paginated results.');

        $section->addTitle('7.5 Archive', 2);
        $section->addText('Instead of permanent deletion, students, companies, and deployments can be archived. Archived records are viewable in the Archive page with school year filtering. Restore records from the archive to bring them back to active status.');
    }

    // ================================================================
    // SECTION 8: REPORTS & EXPORTS
    // ================================================================
    private function writeSection8($section): void
    {
        $section->addTitle('8. Reports & Exports', 1);

        $section->addTitle('8.1 Report Types', 2);
        $reports = [
            'Deployed per Company — Shows which students are assigned to which companies with deployment dates and status',
            'Missing Documents — Lists students with incomplete document requirements, filtered by document phase',
            'Compliance Summary — Per-student compliance status with section grouping',
            'Attendance Export — Attendance records with geofence status, resolution status, and date filters',
            'Executive Summary (Dean only) — High-level KPIs including pipeline metrics and evaluation scores',
        ];
        foreach ($reports as $r) {
            $section->addText('• ' . $r, ['name' => 'Arial', 'size' => 11], ['spaceAfter' => 40]);
        }

        $section->addTitle('8.2 Export Formats', 2);
        $section->addText('Reports can be exported in the following formats:');
        $formats = ['PDF — via DomPDF, for printing and distribution', 'CSV — Attendance data export for spreadsheet analysis', 'DOCX — DTR, Weekly Journal, and Evaluation exports with CHMSU branding'];
        foreach ($formats as $f) {
            $section->addText('• ' . $f, ['name' => 'Arial', 'size' => 11], ['spaceAfter' => 40]);
        }
        $this->addScreenshot($section, 'instructor/reports', 'Report dashboard with 4 tabbed report views.');

        $section->addTitle('8.3 Print View', 2);
        $section->addText('All pages include print CSS that hides the sidebar and header for clean printing. Use your browser\'s Print function (Ctrl+P) or the built-in print buttons on report pages.');
    }

    // ================================================================
    // SECTION 9: TROUBLESHOOTING
    // ================================================================
    private function writeSection9($section): void
    {
        $section->addTitle('9. Troubleshooting', 1);

        $faqs = [
            ['Cannot log in — "Invalid credentials"', 'Verify your email/student number and password. Staff: ensure you selected the correct role. Students: use "Forgot Password" if needed. If still unable, contact the system administrator.'],
            ['OTP not received', 'Check your email spam folder. Ensure your registered email is correct (update in Profile). If using Gmail SMTP, check that the app password is valid. Contact your instructor if the issue persists.'],
            ['Cannot upload document', 'Verify the file is PDF, DOC, or DOCX format. Maximum file size is 2MB. Check that the document phase is currently active for you. If monitoring docs are not visible, ensure your Pre-deployment docs are all approved.'],
            ['Attendance check-in not working', 'Ensure your browser location services are enabled. You must be within 10 meters of the company location (geofence). The check-in is available only during your campus time windows (AM: 7:00-12:00, PM: 1:00-6:00).'],
            ['Page not loading / blank screen', 'Clear your browser cache and cookies. Disable browser extensions that might interfere. Ensure JavaScript is enabled. Try a different browser (Chrome recommended).'],
            ['Session timed out', 'Staff sessions have a standard timeout. Students have a 2-hour idle timeout. Simply log in again. Use "Trust this browser" on login to reduce OTP prompts.'],
            ['No data in reports', 'Ensure filters are correctly applied. Check that there are students/deployments matching your filter criteria. Reports pages use HTMX — try refreshing the page if data doesn\'t load.'],
            ['Email notifications not sending', 'Verify SMTP configuration in .env file. Check that the queue worker is running (`php artisan queue:work`). Test with MAIL_MAILER=log to verify email content.'],
        ];

        foreach ($faqs as $i => $faq) {
            $section->addText(($i + 1) . '. ' . $faq[0], ['name' => 'Arial', 'size' => 11, 'bold' => true], ['spaceAfter' => 40]);
            $section->addText($faq[1], ['name' => 'Arial', 'size' => 11], ['spaceAfter' => 120, 'indentation' => ['left' => 360]]);
        }
    }

    // ================================================================
    // HELPERS
    // ================================================================
    private function addScreenshot($section, string $key, string $caption): void
    {
        $path = $this->ssDir . '/' . $key . '.png';
        if (!file_exists($path)) {
            $section->addText("[Screenshot missing: $key]", ['name' => 'Arial', 'size' => 9, 'italic' => true, 'color' => 'dc2626']);
            return;
        }
        $section->addTextBreak(1);
        $this->img($section, $path, 540);
        $section->addText(
            'Figure: ' . $caption,
            ['name' => 'Arial', 'size' => 9, 'italic' => true, 'color' => '64748b'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 120]
        );
    }

    private function img($section, string $path, int $width): void
    {
        if (file_exists($path)) {
            $section->addImage($path, [
                'width' => $width,
                'alignment' => Jc::CENTER,
                'spaceAfter' => 0,
                'spaceBefore' => 0,
            ]);
        }
    }
}
