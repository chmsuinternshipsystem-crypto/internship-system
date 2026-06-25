# Capstone Alignment README

## Purpose

This file aligns:

1. Capstone proposal scope (strict baseline)
2. Panel recommendations (apply practical items)
3. Current system users (staff + student portal)
4. Current implemented functionalities and workflows

This keeps delivery focused and avoids overbuilding.

---

## 1) Strict Baseline from Capstone Proposal

Required focus areas:

- Status management
- Deployment management
- Digital documentation compliance monitoring
- Real-time monitoring visibility
- Performance and evaluation tracking
- Communication support
- Student portal
- Summary reports

---

## 2) Panel Recommendations: Apply vs Defer

## Apply / Keep

- Evaluation types (`industry`, `school`)
- Added institutional roles with access controls
- Campus geofencing attendance
- Messaging communication hub
- Role-based page visibility and route access
- Document-specific workflow sequencing

## Defer / Backlog

- Semester/school year archiving
- Skills-based company matching

## Ignore for now

- Native in-system video conferencing engine

---

## 3) Current User Accounts

## Staff users

- `admin@chmsu.edu.ph` — admin
- `coordinator@chmsu.edu.ph` — coordinator
- `employer@chmsu.edu.ph` — employer
- `instructor@chmsu.edu.ph` — instructor
- `chairperson@chmsu.edu.ph` — chairperson
- `chair@chmsu.edu.ph` — chair (legacy duplicate naming)
- `dean@chmsu.edu.ph` — dean
- `vpaa@chmsu.edu.ph` — vpaa
- `cier@chmsu.edu.ph` — cier
- `legal@chmsu.edu.ph` — legal

## Student portal users

Students authenticate through `student_accounts` (separate from staff users).

---

## 4) Required Documents (Current)

- Memorandum of Agreement (mandatory)
- Parent Consent Form (mandatory)
- Endorsement Letter (mandatory)
- Medical Certificate (optional)
- Daily Time Record (mandatory)
- Test Document (currently mandatory)

If `Test Document` is only for testing, set it optional or remove it.

---

## 5) Communication Process

Thread-based messaging:

1. Create thread
2. Select recipients allowed by role
3. Send and reply in-thread
4. Unread counters shown in UI

---

## 6) Document Passing Process (Per Document)

Not all documents have the same sequence.

Templates in use:

- `MOA_LEGAL_FULL_CHAIN`  
  `Instructor -> CIER -> Legal -> Employer -> Chairperson -> Dean -> VPAA`
- `PRE_DEPLOYMENT_CHAIN`  
  `Instructor -> Chairperson -> Dean -> CIER`
- `MONITORING_CHAIN`  
  `Employer -> Instructor -> Chairperson`
- `COMPLETION_CHAIN`  
  `Employer -> Instructor -> Chairperson -> Dean -> CIER -> VPAA`

