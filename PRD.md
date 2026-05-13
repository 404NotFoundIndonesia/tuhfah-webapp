# Product Requirements Document

**Product:** Tuhfah Web Application  
**Version:** 1.0  
**Last Updated:** 2026-05-14  
**Author:** M. Iqbal Effendi  
**Status:** In Development

---

## 1. Overview

Tuhfah is a web-based information and management system for Islamic Education Parks (TPQ — Taman Pendidikan Al-Qur'an) and Islamic Education Centers (TPA — Taman Pendidikan Al-Qur'an). It centralizes administrative, academic, financial, and communication operations across one or more institutions into a single integrated platform.

### 1.1 Problem Statement

TPQ/TPA institutions typically manage students, teachers, attendance, payments, and communications through fragmented manual processes — paper records, spreadsheets, and verbal announcements. This results in:

- Data inconsistencies and loss of records
- Slow registration and enrollment processes
- Inability to monitor learning progress systematically
- Opaque financial records for guardians and administrators
- No structured communication channel between institution and guardians

### 1.2 Solution

A multi-tenant, role-based web application that provides each institution with tools for:

- Digital student registration and management
- Teacher and administrator user management
- Attendance tracking
- Learning progress monitoring
- Financial management with online payment support
- Inventory tracking
- Public announcement publishing

---

## 2. Goals

| # | Goal | Priority |
|---|------|----------|
| G1 | Digitize and centralize institution data management | High |
| G2 | Enable multiple TPQ/TPA institutions to use the system concurrently | High |
| G3 | Reduce administrative burden through automation | High |
| G4 | Provide guardians transparent access to their child's progress and payments | Medium |
| G5 | Support bilingual interface (Indonesian and English) | Medium |
| G6 | Be accessible on all devices (responsive design) | Medium |

---

## 3. Scope

### 3.1 In Scope

- User authentication and role-based access control
- Student registration and lifecycle management
- Teacher and staff management
- Attendance tracking (web)
- Learning progress monitoring
- Financial management (fees and honorariums)
- Inventory management
- Announcements (internal and public)
- Notification system
- Reporting and data analysis

### 3.2 Out of Scope

- Mobile application (Android/iOS) — separate product
- Integration with government databases (EMIS/SIMAS)
- Video conferencing or live class features
- Curriculum authoring tools

---

## 4. User Roles

| Role | Description | Permissions |
|------|-------------|-------------|
| **Owner** | Institution owner. Highest privilege. | Full access to all features. Manage headmasters, administrators, teachers, guardians, students. |
| **Headmaster** | Institutional head. Operational oversight. | Manage administrators, teachers, guardians, students. View all reports. |
| **Administrator** | Day-to-day admin operations. | Manage teachers, guardians, students. Handle finance, inventory, announcements. |
| **Teacher** | Instructional staff. | Input and monitor assigned students' learning progress. View own attendance. |
| **Student Guardian** | Parent or guardian of a student. | View child's progress, attendance, and payment status. |

---

## 5. Features

### 5.1 Authentication & Account Management

**Status:** Implemented

| ID | Requirement |
|----|-------------|
| AUTH-01 | Users register with name, email, and password. |
| AUTH-02 | Login via email and password. Rate-limited to 5 attempts before throttle. |
| AUTH-03 | Email verification required after registration. |
| AUTH-04 | Password reset via email link. |
| AUTH-05 | Authenticated users can change their own password. |
| AUTH-06 | Users can update profile: name, email, phone, address, gender, marital status, profile image. |
| AUTH-07 | Users can delete their account after password confirmation. |
| AUTH-08 | Locale preference (Indonesian / English) stored per user and applied on login. |
| AUTH-09 | Profile image auto-deleted from storage when account is deleted. |

---

### 5.2 User Management

**Status:** Implemented (Administrators, Teachers, Student Guardians)

| ID | Requirement |
|----|-------------|
| USR-01 | Owner and Headmaster can create, view, edit, and delete Administrator accounts. |
| USR-02 | Owner, Headmaster, and Administrator can create, view, edit, and delete Teacher accounts. |
| USR-03 | Owner, Headmaster, and Administrator can create, view, edit, and delete Student Guardian accounts. |
| USR-04 | New user accounts have default password `password` and must change on first login. |
| USR-05 | User lists rendered via server-side DataTables with search and pagination. |
| USR-06 | User profile images stored in `/storage` and auto-cleaned on account deletion. |

---

### 5.3 Student Management

**Status:** Implemented

| ID | Requirement |
|----|-------------|
| STD-01 | Authorized users can create, view, edit, and delete student records. |
| STD-02 | System auto-generates a unique student ID number using Hijri calendar date and gender prefix (`I` for male, `A` for female). |
| STD-03 | Student record includes: name, nickname, birthplace, birthdate, gender, profile image, admission date, departure date, and status. |
| STD-04 | Student statuses: Candidate, Active, Graduated, Expelled, On Leave, Quit. |
| STD-05 | Each student is linked to a Student Guardian user via foreign key. |
| STD-06 | Student list supports server-side filtering by gender and status. |
| STD-07 | Student profile images stored and auto-cleaned on record deletion. |

---

### 5.4 Attendance Management

**Status:** Planned

| ID | Requirement |
|----|-------------|
| ATT-01 | Administrators and Teachers can record daily attendance for students. |
| ATT-02 | Attendance statuses: Present, Absent, Sick, Permitted. |
| ATT-03 | Teachers can record their own attendance. |
| ATT-04 | Administrators can view attendance records for all students and teachers. |
| ATT-05 | Guardians can view their child's attendance history. |
| ATT-06 | System generates monthly attendance summaries per student. |
| ATT-07 | Attendance data feeds into reporting module. |

---

### 5.5 Learning Progress Monitoring

**Status:** Planned

| ID | Requirement |
|----|-------------|
| LRN-01 | Teachers can input learning progress records per student per session (e.g., Qur'an surah/juz reached, memorization score). |
| LRN-02 | Progress record includes: date, subject, achievement/milestone, notes. |
| LRN-03 | Administrators and Headmasters can view all students' progress. |
| LRN-04 | Guardians can view their child's progress history. |
| LRN-05 | System visualizes progress over time (chart). |

---

### 5.6 Financial Management

**Status:** Planned

| ID | Requirement |
|----|-------------|
| FIN-01 | Administrators can create tuition fee records per student per billing period. |
| FIN-02 | Administrators can record teacher honorarium payments. |
| FIN-03 | Payment statuses: Unpaid, Paid, Overdue. |
| FIN-04 | Guardians can view payment history and outstanding fees for their child. |
| FIN-05 | System supports manual payment recording by admins. |
| FIN-06 | System integrates with at least one online payment gateway for guardian self-service payments. |
| FIN-07 | Financial records are exportable as reports (PDF or Excel). |
| FIN-08 | Administrators receive notifications for overdue payments. |

---

### 5.7 Inventory Management

**Status:** Planned

| ID | Requirement |
|----|-------------|
| INV-01 | Administrators can add, edit, and delete inventory items (physical assets). |
| INV-02 | Each item record includes: name, quantity, condition, acquisition date, notes. |
| INV-03 | Administrators can record item usage or disposal. |
| INV-04 | Inventory list is viewable by Owner, Headmaster, and Administrator roles. |

---

### 5.8 Announcements

**Status:** Planned

| ID | Requirement |
|----|-------------|
| ANN-01 | Administrators can create, publish, edit, and delete announcements. |
| ANN-02 | Announcements can be scoped: public (visible on welcome page) or internal (authenticated users only). |
| ANN-03 | Public announcements are displayed on the institution's public-facing welcome page without login. |
| ANN-04 | Internal announcements are visible to all authenticated users in the dashboard. |
| ANN-05 | Announcements support title, body (rich text), publication date, and author. |

---

### 5.9 Notification System

**Status:** Planned

| ID | Requirement |
|----|-------------|
| NOT-01 | Guardians receive notifications when: attendance is recorded for their child, a payment is due or overdue, new learning progress is added. |
| NOT-02 | Teachers receive notifications for: new assignments, attendance reminders. |
| NOT-03 | Notifications delivered in-app (bell icon in navbar). |
| NOT-04 | Notifications optionally delivered via email. |

---

### 5.10 Reporting & Analytics

**Status:** Planned

| ID | Requirement |
|----|-------------|
| RPT-01 | Dashboard shows summary statistics: total students by status, attendance rate, recent payments. |
| RPT-02 | Administrators can generate attendance reports per period (weekly, monthly). |
| RPT-03 | Administrators can generate financial summary reports per period. |
| RPT-04 | Learning progress reports exportable per student. |
| RPT-05 | Reports exportable as PDF or Excel. |

---

## 6. Non-Functional Requirements

### 6.1 Performance

| ID | Requirement |
|----|-------------|
| NFR-01 | All server-side DataTables use AJAX to avoid full-page reload on pagination/search. |
| NFR-02 | Image uploads are validated for size and MIME type before storage. |
| NFR-03 | Pages load within 3 seconds on standard broadband connection. |

### 6.2 Security

| ID | Requirement |
|----|-------------|
| NFR-04 | All routes behind authentication except: welcome, login, password reset, public announcements. |
| NFR-05 | Authorization enforced at controller level — users cannot access resources outside their role. |
| NFR-06 | CSRF protection on all state-changing requests. |
| NFR-07 | Input validation on all form submissions (server-side). |
| NFR-08 | Passwords hashed using bcrypt. |
| NFR-09 | Rate limiting on login endpoint. |

### 6.3 Usability

| ID | Requirement |
|----|-------------|
| NFR-10 | UI is responsive and usable on desktop, tablet, and mobile browsers. |
| NFR-11 | Application supports Indonesian (default) and English languages. |
| NFR-12 | Flash notifications confirm success or failure on all create/update/delete actions. |
| NFR-13 | Form validation errors displayed inline next to relevant fields. |

### 6.4 Maintainability

| ID | Requirement |
|----|-------------|
| NFR-14 | Code formatted with Laravel Pint (PSR-12). |
| NFR-15 | Feature tests written with PHPUnit for critical flows. |
| NFR-16 | Seeders and factories provided for local development and testing. |

---

## 7. Technical Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2+, Laravel 11 |
| Database | MySQL |
| Frontend | HTML, CSS, JavaScript; Bootstrap |
| Asset Pipeline | Vite 5 |
| Auth Scaffolding | Laravel Breeze |
| Tables | Yajra Laravel DataTables (server-side) |
| Calendar | pharaonic/laravel-hijri (Hijri date support) |
| Code Style | Laravel Pint (PSR-12) |
| Testing | PHPUnit 11 |
| Dev Environment | Laravel Sail (Docker) |

---

## 8. Data Model Summary

### Core Entities

```
users
  id, name, email, password, role, locale
  phone, address, gender, marital_status, image
  email_verified_at, remember_token, timestamps

students
  id, student_id_number, name, nickname
  birthplace, birthdate, gender, image
  status, admission_date, departure_date
  student_guardian_id (→ users.id)
  timestamps
```

### Planned Entities

```
attendances
  id, attendable_type, attendable_id (polymorphic: student/teacher)
  date, status, notes, recorded_by (→ users.id), timestamps

learning_progress
  id, student_id (→ students.id), teacher_id (→ users.id)
  date, subject, milestone, score, notes, timestamps

payments
  id, student_id (→ students.id), period, amount
  status, due_date, paid_at, recorded_by (→ users.id), timestamps

honorariums
  id, teacher_id (→ users.id), period, amount
  status, paid_at, recorded_by (→ users.id), timestamps

inventories
  id, name, quantity, condition, acquisition_date, notes, timestamps

announcements
  id, title, body, scope (public/internal), published_at
  author_id (→ users.id), timestamps

notifications
  id, notifiable_type, notifiable_id, type, data, read_at, timestamps
```

---

## 9. User Flows

### 9.1 Student Registration Flow

1. Administrator opens **Student → Create**.
2. Fills in student details and selects a guardian from the existing Student Guardian list.
3. Submits form. System auto-generates student ID number (Hijri-based, gender-prefixed).
4. Student record created with status `Candidate`.
5. Administrator manually updates status to `Active` after confirmation.

### 9.2 Guardian Views Child Progress

1. Guardian logs in.
2. Dashboard shows summary of child's attendance and latest progress entry.
3. Guardian navigates to **Progress** — views full history ordered by date.
4. Guardian navigates to **Payments** — views outstanding fees with payment option.

### 9.3 Teacher Records Attendance

1. Teacher logs in and navigates to **Attendance**.
2. Selects class/group and date.
3. Marks each student Present / Absent / Sick / Permitted.
4. Submits. Guardians of absent students receive notification.

---

## 10. Milestones

| Phase | Features | Status |
|-------|----------|--------|
| **Phase 1 — Foundation** | Auth, User Management, Student Management | Done |
| **Phase 2 — Academic** | Attendance, Learning Progress | Planned |
| **Phase 3 — Finance** | Financial Management, Honorariums | Planned |
| **Phase 4 — Engagement** | Announcements, Notifications | Planned |
| **Phase 5 — Insight** | Inventory, Reporting & Analytics | Planned |

---

## 11. Open Questions

| # | Question | Owner |
|---|----------|-------|
| OQ-01 | Which payment gateway(s) will be integrated first (Midtrans, Xendit, DOKU)? | Product |
| OQ-02 | Will attendance support QR code or RFID scanning in Phase 2, or web-only? | Product |
| OQ-03 | Is multi-tenancy implemented at the database level (tenant_id columns) or at schema/database level? | Engineering |
| OQ-04 | What is the student ID number format beyond gender prefix and Hijri date — include institution code? | Product |
| OQ-05 | Is the Teacher role expected to manage their own profile image independently? | Product |

---

## 12. References

- Effendi, M. Iqbal dan Nafila Fayruz. (2021). *Sistem Informasi dan Manajemen Taman Pendidikan Al-Qur'an Imam Syafi'i Banjarmasin Berbasis Web dan Aplikasi Android.* Tugas Akhir Diploma 3. Politeknik Negeri Banjarmasin.
- Fitri dkk. (2022). Sistem Informasi dan Manajemen Taman Pendidikan Al-Qur'an Imam Syafi'i Banjarmasin Berbasis Web. *Jurnal Impact*, 4(1), 4–11.
