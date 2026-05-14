# Task Breakdown — Tuhfah Web Application

Based on [PRD.md](PRD.md). Tasks ordered by dependency — complete each phase before starting the next. Each task includes description, implementation steps, Definition of Done (DoD), and required tests.

---

## Legend

- `[ ]` — Not started
- `[x]` — Done
- **Refs:** PRD requirement IDs this task fulfills
- **Depends on:** Task IDs that must be complete first

---

## Phase 1 — Foundation (Retroactive Testing)

> Phase 1 features are implemented. These tasks cover missing tests and factories for existing code. Complete before Phase 2 to ensure a stable base.

---

### T1.1 — User Management: Factories & Tests

**Description:** The existing `AdministratorController`, `TeacherController`, and `StudentGuardianController` have no feature tests. Write factories (if missing) and full CRUD test coverage for all three, including role-based authorization.

**Implementation:**
- Verify `UserFactory` covers all roles via `state()` methods (owner, headmaster, administrator, teacher, student_guardian)
- Create `tests/Feature/AdministratorTest.php`
- Create `tests/Feature/TeacherTest.php`
- Create `tests/Feature/StudentGuardianTest.php`

**Refs:** USR-01, USR-02, USR-03, USR-04, USR-05, USR-06

**Depends on:** —

- [x] Add role-based factory states to `UserFactory`
- [x] Write `AdministratorTest`: index renders, store creates user with ADMINISTRATOR role, update changes fields, destroy deletes user and image, unauthorized roles get 403
- [x] Write `TeacherTest`: same structure as above for TEACHER role
- [x] Write `StudentGuardianTest`: same structure for STUDENT_GUARDIAN role
- [x] All tests pass with `php artisan test --filter AdministratorTest,TeacherTest,StudentGuardianTest`

**DoD:**
- All CRUD actions have at least one happy-path test and one authorization-failure test per disallowed role
- `UserFactory` has states for all 5 roles
- No test fails

---

### T1.2 — Student Management: Factory & Tests

**Description:** `StudentController` has no feature tests. Write `StudentFactory` and test coverage for student CRUD, student ID auto-generation, guardian assignment, and status filtering.

**Implementation:**
- Create `database/factories/StudentFactory.php`
- Create `tests/Feature/StudentTest.php`

**Refs:** STD-01, STD-02, STD-03, STD-04, STD-05, STD-06, STD-07

**Depends on:** T1.1 (needs UserFactory states for guardian)

- [x] Create `StudentFactory` with all fields, random status, and linked `student_guardian_id`
- [x] Test: authorized user can view student index (DataTables JSON endpoint)
- [x] Test: store creates student record with auto-generated `student_id_number`
- [x] Test: male student ID has `I` prefix, female has `A` prefix
- [x] Test: student requires a valid `student_guardian_id`
- [x] Test: update changes student fields
- [x] Test: destroy deletes student and associated image
- [ ] Test: filtering by `gender` and `status` returns correct subset
- [x] Test: unauthorized role cannot create/update/delete students

**DoD:**
- `StudentFactory` produces valid student records
- All listed tests pass
- Student ID generation logic has dedicated unit test in `tests/Unit/StudentIdGenerationTest.php`

---

### T1.3 — Profile Management: Tests

**Description:** `ProfileController` has partial test coverage in `ProfileTest.php`. Verify image upload, image cleanup on deletion, and locale change are tested.

**Implementation:**
- Extend `tests/Feature/ProfileTest.php`

**Refs:** AUTH-06, AUTH-07, AUTH-08, AUTH-09

**Depends on:** —

- [x] Test: profile update with image upload stores image and updates `image` field
- [x] Test: profile update replaces old image file (old file deleted)
- [x] Test: account deletion removes profile image from storage
- [x] Test: locale change updates `locale` column and redirects correctly

**DoD:**
- All profile tests pass including image and locale scenarios

---

## Phase 2 — Attendance Management

> Depends on: Phase 1 complete (users and students exist with factories).

---

### T2.1 — Attendance: Enum, Migration & Model

**Description:** Create the database foundation for attendance. Attendance is polymorphic — it covers both students and teachers in a single table.

**Implementation:**
- Create `app/Enums/AttendanceStatus.php`
- Create migration `create_attendances_table`
- Create `app/Models/Attendance.php`
- Create `database/factories/AttendanceFactory.php`

**Refs:** ATT-01, ATT-02, ATT-03

**Depends on:** T1.1, T1.2

- [x] Create `AttendanceStatus` enum: `PRESENT`, `ABSENT`, `SICK`, `PERMITTED`
- [x] Create migration with columns: `id`, `attendable_type`, `attendable_id` (morphs), `date` (date), `status` (enum/string), `notes` (nullable text), `recorded_by` (FK → users.id), `timestamps`
- [x] Create `Attendance` model with `morphTo()` relation `attendable`, `belongsTo(User)` for `recordedBy`, fillable fields, and `$casts` for `status` → `AttendanceStatus` and `date` → `date`
- [x] Add `morphMany(Attendance)` to `Student` model
- [x] Add `morphMany(Attendance)` to `User` model (for teacher self-attendance)
- [x] Create `AttendanceFactory` covering both student and teacher morphs
- [x] Run migration without errors

**DoD:**
- Migration runs clean via `php artisan migrate:fresh`
- `Attendance::create([...])` works for both student and teacher morph targets
- Unit test: `AttendanceStatus` enum has correct values and labels

---

### T2.2 — Student Attendance: Recording (Admin & Teacher)

**Description:** Administrators and Teachers can record daily attendance for all students. Provide a date-based form that lists all active students and captures their attendance status in one submission.

**Implementation:**
- Create `app/Http/Controllers/AttendanceController.php`
- Create `app/Http/Requests/StoreAttendanceRequest.php`
- Add routes in `routes/web.php`
- Create views: `resources/views/pages/attendance/index.blade.php`, `create.blade.php`

**Refs:** ATT-01, ATT-02

**Depends on:** T2.1

- [x] Route `GET /attendance` → list attendance records (server-side DataTable, filterable by date and status)
- [x] Route `GET /attendance/create` → show bulk attendance form for a selected date
- [x] Route `POST /attendance` → store bulk attendance records
- [x] Route `GET /attendance/{attendance}` → show single record detail
- [x] `StoreAttendanceRequest`: validate `date` is a valid date, `records` is array, each entry has valid `attendable_id` and `status`
- [x] Controller: only active students shown on create form; if attendance for date already exists, pre-fill the form
- [x] Gate: only `administrator` and `teacher` roles can access create/store; all authorized roles can view index
- [x] Add i18n keys to `lang/id` and `lang/en` for all new UI strings
- [x] Flash success/error notification after store

**DoD:**
- Admin and Teacher can access `/attendance/create`, select a date, mark all students, and submit
- Duplicate submission for same date updates existing records (upsert behavior)
- Unauthorized roles (guardian, student_guardian) get 403
- Feature test covers: happy path store, unauthorized access, validation failure on missing date

---

### T2.3 — Teacher Self-Attendance

**Description:** Teachers can record their own daily attendance through a dedicated interface separate from student attendance.

**Implementation:**
- Add routes and actions to `AttendanceController`
- Create view: `resources/views/pages/attendance/self.blade.php`

**Refs:** ATT-03

**Depends on:** T2.2

- [x] Route `GET /attendance/self` → show self-attendance form for teacher
- [x] Route `POST /attendance/self` → store teacher's own attendance record (morph to User)
- [x] Gate: only `teacher` role can access self-attendance routes
- [x] Form shows today's date pre-filled; teacher selects status and optional notes
- [x] Cannot submit duplicate self-attendance for the same date

**DoD:**
- Teacher can submit self-attendance; second submission on same date shows validation error
- Admin cannot access teacher self-attendance route (403)
- Feature test: teacher submits self-attendance → record created with correct `attendable_type = User`

---

### T2.4 — Guardian: View Child Attendance

**Description:** Student guardians can view a paginated history of their child's attendance records, filtered by month.

**Implementation:**
- Add `AttendanceController@guardianIndex` action
- Create view: `resources/views/pages/attendance/guardian.blade.php`

**Refs:** ATT-05

**Depends on:** T2.2

- [x] Route `GET /my-child/attendance` → accessible only by `student_guardian` role
- [x] Controller fetches attendance records for the guardian's linked student only
- [x] View shows attendance list with date, status badge, and notes; filterable by month/year
- [x] Gate: guardian cannot see attendance for students other than their own child

**DoD:**
- Guardian sees only their own child's records
- Accessing another child's attendance returns 403
- Feature test: guardian views child attendance, unauthorized role cannot access route

---

### T2.5 — Monthly Attendance Summary

**Description:** Generate a monthly attendance summary per student showing counts of Present, Absent, Sick, and Permitted days. Used in reporting and the guardian dashboard.

**Implementation:**
- Create `app/Services/AttendanceService.php` with `monthlySummary(Student $student, int $year, int $month): array`
- Expose via `AttendanceController@summary` action

**Refs:** ATT-06, ATT-07

**Depends on:** T2.2

- [x] `AttendanceService::monthlySummary()` returns array with keys `present`, `absent`, `sick`, `permitted`, `total_days`
- [x] Route `GET /attendance/summary?student_id=&year=&month=` → returns summary (accessible by admin, headmaster, teacher, guardian-for-own-child)
- [x] Unit test: `AttendanceService::monthlySummary()` with known fixture data returns correct counts
- [x] Feature test: endpoint returns correct JSON shape

**DoD:**
- Service method has 100% unit test coverage for normal and edge cases (no attendance records for month, all statuses present)
- Endpoint enforces role-based access

---

## Phase 3 — Learning Progress Monitoring

> Depends on: T2.1 (attendance foundation establishes pattern for polymorphic/related records).

---

### T3.1 — LearningProgress: Migration & Model

**Description:** Create the database and model layer for student learning progress records.

**Implementation:**
- Create migration `create_learning_progress_table`
- Create `app/Models/LearningProgress.php`
- Create `database/factories/LearningProgressFactory.php`

**Refs:** LRN-01, LRN-02

**Depends on:** T1.2

- [x] Migration columns: `id`, `student_id` (FK → students.id), `teacher_id` (FK → users.id), `date` (date), `subject` (string), `milestone` (string), `score` (nullable float), `notes` (nullable text), `timestamps`
- [x] Model: `belongsTo(Student)`, `belongsTo(User, 'teacher_id')`, fillable, cast `date` to `date`
- [x] Add `hasMany(LearningProgress)` to `Student` model
- [x] Create `LearningProgressFactory`
- [x] Unit test: model relations resolve correctly

**DoD:**
- Migration runs clean
- Factory produces valid records linked to student and teacher

---

### T3.2 — Teacher: Input Progress Records

**Description:** Teachers can add, edit, and delete learning progress records for students they are responsible for.

**Implementation:**
- Create `app/Http/Controllers/LearningProgressController.php`
- Create `app/Http/Requests/StoreLearningProgressRequest.php`
- Create `app/Http/Requests/UpdateLearningProgressRequest.php`
- Add routes and views

**Refs:** LRN-01, LRN-02

**Depends on:** T3.1

- [x] RESTful routes under `/learning-progress` (index, create, store, show, edit, update, destroy)
- [x] Gate: `teacher` can create/edit/delete records for students; cannot edit other teachers' records
- [x] `StoreLearningProgressRequest`: validate `student_id` exists and is active, `date` is valid, `subject` and `milestone` are required strings
- [x] Teacher auto-assigned as `teacher_id` on store (not user-selectable)
- [x] Flash notifications on create/update/delete
- [x] Add i18n keys

**DoD:**
- Teacher can create a progress record and it saves with correct `teacher_id`
- Teacher cannot edit another teacher's progress record (403)
- Feature test covers: store, update, delete, authorization

---

### T3.3 — Admin & Headmaster: View All Progress

**Description:** Administrators and Headmasters can view learning progress for all students, with filtering by student, teacher, subject, and date range.

**Implementation:**
- Add `LearningProgressController@adminIndex` action
- Create view for admin progress list

**Refs:** LRN-03

**Depends on:** T3.2

- [x] Route `GET /learning-progress` for admin/headmaster shows all records with filters
- [x] Server-side DataTable with filters: student, teacher, subject, date range
- [x] Gate: teacher sees only own records on same route; admin/headmaster sees all
- [x] Feature test: admin sees all records; teacher sees only own

**DoD:**
- Admin can filter and view all progress records across all students
- Role-based scoping tested

---

### T3.4 — Guardian: View Child Progress

**Description:** Student guardians can view their child's full learning progress history ordered by date descending.

**Implementation:**
- Add `LearningProgressController@guardianIndex`
- Create guardian progress view

**Refs:** LRN-04

**Depends on:** T3.2

- [x] Route `GET /my-child/progress` → guardian only
- [x] Shows paginated progress records for linked student
- [x] Guardian cannot access another student's progress

**DoD:**
- Guardian sees only their child's records
- Feature test: access control and data scoping

---

### T3.5 — Progress Chart Visualization

**Description:** Display a line chart on the student's progress detail page showing score over time per subject, using ApexCharts (already in asset stack).

**Implementation:**
- Add chart data endpoint to `LearningProgressController`
- Add ApexCharts rendering to student progress view

**Refs:** LRN-05

**Depends on:** T3.3, T3.4

- [x] Route `GET /learning-progress/chart-data?student_id=&subject=` returns JSON array of `{date, score}`
- [x] ApexCharts line chart rendered in student progress page
- [x] Feature test: chart data endpoint returns correct JSON shape and respects authorization

**DoD:**
- Chart renders with real data for a student with multiple progress records
- Empty state shown when no records exist

---

## Phase 4 — Financial Management

> Depends on: Phase 2 and Phase 3 complete (student data is stable).

---

### T4.1 — Payment: Enum, Migration & Model

**Description:** Create the database foundation for student tuition fee payment records.

**Implementation:**
- Create `app/Enums/PaymentStatus.php`
- Create migration `create_payments_table`
- Create `app/Models/Payment.php`
- Create `database/factories/PaymentFactory.php`

**Refs:** FIN-01, FIN-03, FIN-05

**Depends on:** T1.2

- [x] `PaymentStatus` enum: `UNPAID`, `PAID`, `OVERDUE`
- [x] Migration columns: `id`, `student_id` (FK → students.id), `period` (string, e.g. "2025-01"), `amount` (decimal 10,2), `status` (string), `due_date` (date), `paid_at` (nullable datetime), `recorded_by` (FK → users.id), `timestamps`
- [x] Model: `belongsTo(Student)`, `belongsTo(User, 'recorded_by')`, cast `status` to `PaymentStatus`, cast `due_date` to `date`, cast `paid_at` to `datetime`
- [x] Add `hasMany(Payment)` to `Student` model
- [x] Unit test: `PaymentStatus` enum labels correct; Payment model casts work

**DoD:**
- Migration runs clean
- Factory creates valid payment records with all statuses

---

### T4.2 — Admin: Manage Student Payments

**Description:** Administrators can create, view, and manually mark tuition payments as paid for any student.

**Implementation:**
- Create `app/Http/Controllers/PaymentController.php`
- Create form requests, views, routes

**Refs:** FIN-01, FIN-03, FIN-05

**Depends on:** T4.1

- [x] Routes: `GET /payment` (index), `GET /payment/create`, `POST /payment`, `GET /payment/{payment}`, `PATCH /payment/{payment}` (mark paid), `DELETE /payment/{payment}`
- [x] Gate: only `administrator`, `headmaster`, `owner` can manage payments
- [x] Index shows all payments with filters: student, period, status
- [x] Store validates: `student_id` is active student, `period` format is `YYYY-MM`, `amount` > 0, `due_date` is valid date
- [x] Mark-paid action sets `status = PAID`, `paid_at = now()`, `recorded_by = auth()->id()`
- [x] Flash notifications and i18n keys
- [x] Feature test: store payment, mark paid, unauthorized role gets 403

**DoD:**
- Admin can create a payment record and mark it paid
- Marking paid updates `paid_at` and `recorded_by`
- All validation rules tested

---

### T4.3 — Honorarium: Migration, Model & Management

**Description:** Administrators can record teacher honorarium payments with period, amount, and status tracking.

**Implementation:**
- Create migration `create_honorariums_table`
- Create `app/Models/Honorarium.php`
- Create `database/factories/HonorariumFactory.php`
- Create `app/Http/Controllers/HonorariumController.php`
- Add routes and views

**Refs:** FIN-02, FIN-03

**Depends on:** T4.1

- [x] Migration columns: `id`, `teacher_id` (FK → users.id), `period` (string), `amount` (decimal 10,2), `status` (string), `paid_at` (nullable datetime), `recorded_by` (FK → users.id), `timestamps`
- [x] Model: `belongsTo(User, 'teacher_id')`, `belongsTo(User, 'recorded_by')`, casts for status and dates
- [x] RESTful routes under `/honorarium`
- [x] Gate: `administrator`, `headmaster`, `owner` only
- [x] Same store/mark-paid pattern as PaymentController
- [x] Feature test: create honorarium, mark paid, authorization

**DoD:**
- Admin can create and mark honorarium paid
- Teacher field restricted to users with `teacher` role in the form dropdown

---

### T4.4 — Guardian: View Payment History

**Description:** Student guardians can view tuition fee history and outstanding payments for their linked child.

**Implementation:**
- Add `PaymentController@guardianIndex`
- Create guardian payment view

**Refs:** FIN-04

**Depends on:** T4.2

- [x] Route `GET /my-child/payments` → guardian only
- [x] Shows all payment records for linked student with status badges
- [x] Outstanding (UNPAID or OVERDUE) payments highlighted
- [x] Guardian cannot access payment records for other students (403)
- [x] Feature test: guardian sees only child's payments

**DoD:**
- Guardian view correctly scoped to linked student
- Overdue payments visually distinct from unpaid

---

### T4.5 — Overdue Payment Detection

**Description:** A scheduled command marks payments as OVERDUE when `due_date` has passed and status is still UNPAID.

**Implementation:**
- Create `app/Console/Commands/MarkOverduePayments.php`
- Register in `routes/console.php` or `bootstrap/app.php` schedule

**Refs:** FIN-03, FIN-08

**Depends on:** T4.2

- [x] Command `payments:mark-overdue` queries payments where `due_date < today` and `status = UNPAID`, updates status to `OVERDUE`
- [x] Command scheduled to run daily via `->daily()`
- [x] Unit test: command with fixture data correctly marks overdue payments and skips paid ones
- [x] Feature test: after running command, overdue payments have correct status

**DoD:**
- Command changes only UNPAID + past-due-date records to OVERDUE
- PAID records are never touched
- Unit test covers: no records, mixed records, all records already paid

---

### T4.6 — Payment Gateway Integration

**Description:** Integrate an online payment gateway so guardians can pay outstanding fees directly through the web app. Gateway TBD (see PRD OQ-01 — default to Midtrans as most common for Indonesian apps).

**Implementation:**
- Install payment gateway SDK (e.g. `composer require midtrans/midtrans-php`)
- Create `app/Services/PaymentGatewayService.php`
- Create `app/Http/Controllers/PaymentGatewayController.php` for redirect and webhook
- Add config entries in `config/services.php`
- Add environment variables to `.env.example`

**Refs:** FIN-06

**Depends on:** T4.4

- [x] Guardian can click "Pay Now" on an outstanding payment → redirected to gateway checkout
- [x] Gateway webhook endpoint `POST /payment/webhook` receives payment confirmation
- [x] On successful webhook: mark payment as PAID, store `paid_at`
- [x] Webhook endpoint validates gateway signature before processing
- [x] `.env.example` updated with gateway credential placeholders
- [x] Unit test: `PaymentGatewayService` creates correct payload structure
- [x] Feature test: webhook with valid signature marks payment paid; invalid signature returns 403

**DoD:**
- Webhook is idempotent (duplicate callbacks do not double-mark)
- Gateway credentials never hardcoded — loaded from env
- Webhook signature validation tested with valid and invalid signatures

---

### T4.7 — Export Financial Records

**Description:** Administrators can export payment and honorarium records as PDF or Excel for a selected period.

**Implementation:**
- Install `maatwebsite/excel` for Excel export
- Install `barryvdh/laravel-dompdf` for PDF export
- Create `app/Exports/PaymentExport.php`
- Create `app/Exports/HonorariumExport.php`
- Add export routes and buttons to admin views

**Refs:** FIN-07

**Depends on:** T4.2, T4.3

- [x] Route `GET /payment/export?format=xlsx&period=YYYY-MM` → downloads Excel
- [x] Route `GET /payment/export?format=pdf&period=YYYY-MM` → downloads PDF
- [x] Same routes for `/honorarium/export`
- [x] Gate: `administrator`, `headmaster`, `owner` only
- [x] Feature test: export route returns response with correct `Content-Type` header and 200 status

**DoD:**
- Both Excel and PDF downloads work for payments and honorariums
- Export limited to one period at a time to avoid memory issues

---

## Phase 5 — Announcements

> Depends on: Phase 1 complete. Independent of Phase 2–4.

---

### T5.1 — Announcement: Migration & Model

**Description:** Create the database layer for announcements with public/internal scope and rich-text body.

**Implementation:**
- Create migration `create_announcements_table`
- Create `app/Models/Announcement.php`
- Create `database/factories/AnnouncementFactory.php`

**Refs:** ANN-01, ANN-02, ANN-05

**Depends on:** T1.1

- [ ] Create `app/Enums/AnnouncementScope.php`: `PUBLIC`, `INTERNAL`
- [ ] Migration columns: `id`, `title` (string), `body` (longText), `scope` (string), `published_at` (nullable datetime), `author_id` (FK → users.id), `timestamps`
- [ ] Model: `belongsTo(User, 'author_id')`, cast `scope` to `AnnouncementScope`, cast `published_at` to `datetime`
- [ ] Scope `published()`: `whereNotNull('published_at')->where('published_at', '<=', now())`
- [ ] Factory with both scopes
- [ ] Unit test: `published()` scope returns only published announcements

**DoD:**
- Migration runs clean
- `Announcement::published()->get()` returns only published records

---

### T5.2 — Admin: CRUD Announcements

**Description:** Administrators can create, publish, edit, and delete announcements. Body supports rich text via a simple HTML textarea (or lightweight editor).

**Implementation:**
- Create `app/Http/Controllers/AnnouncementController.php`
- Create form requests and views
- Add routes

**Refs:** ANN-01, ANN-02, ANN-05

**Depends on:** T5.1

- [ ] RESTful routes under `/announcement`
- [ ] Gate: only `administrator`, `headmaster`, `owner` can create/edit/delete
- [ ] Validation: `title` required max 255, `body` required, `scope` must be valid `AnnouncementScope` value, `published_at` nullable valid datetime
- [ ] Author auto-assigned as `author_id` on store
- [ ] "Publish Now" button on edit page sets `published_at = now()` if null
- [ ] Flash notifications and i18n keys
- [ ] Feature test: store, update, destroy, publish action, unauthorized access

**DoD:**
- Admin can draft (no `published_at`) and then publish an announcement
- Role authorization tested for all actions

---

### T5.3 — Public Announcements on Welcome Page

**Description:** Published public announcements appear on the public welcome page without requiring login.

**Implementation:**
- Modify `PageController@welcome` to pass public announcements to view
- Update `resources/views/welcome.blade.php`

**Refs:** ANN-03

**Depends on:** T5.2

- [ ] `welcome()` action queries `Announcement::published()->where('scope', AnnouncementScope::PUBLIC)->latest('published_at')->take(10)->get()`
- [ ] Welcome page displays announcement titles and truncated body with "Read more" toggle or link
- [ ] Unauthenticated user can see public announcements
- [ ] Feature test: public announcement appears on welcome page; internal announcement does not

**DoD:**
- Welcome page shows up to 10 latest public announcements
- Internal announcements are never shown on the public page

---

### T5.4 — Internal Announcements in Dashboard

**Description:** Authenticated users see internal (and public) announcements in the dashboard.

**Implementation:**
- Modify `PageController@dashboard` to pass recent announcements
- Update `resources/views/pages/dashboard.blade.php`

**Refs:** ANN-04

**Depends on:** T5.2

- [ ] Dashboard shows 5 most recent published announcements (all scopes)
- [ ] "View all" link to `/announcement` index for all authenticated users
- [ ] All authenticated roles can read announcements; only admin/headmaster/owner can manage
- [ ] Feature test: authenticated user sees internal announcement on dashboard

**DoD:**
- Internal announcements visible only to logged-in users
- Dashboard announcement section tested for all roles

---

## Phase 6 — Notification System

> Depends on: T2.2 (attendance), T3.2 (progress), T4.2 (payments), T5.1 (announcements).

---

### T6.1 — Laravel Notifications Setup

**Description:** Set up Laravel's database notification channel as the foundation for the in-app notification system.

**Implementation:**
- Run `php artisan notifications:table` to publish migration
- Create base notification structure

**Refs:** NOT-03

**Depends on:** T1.1

- [ ] Run `php artisan notifications:table && php artisan migrate`
- [ ] Verify `notifications` table has: `id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`
- [ ] Add `HasNotifications` (Laravel built-in `Notifiable` trait) is already on `User` model — verify
- [ ] Create shared notification bell component: `resources/views/components/notification-bell.blade.php` showing unread count
- [ ] Route `GET /notifications` → list all notifications for authenticated user (paginated)
- [ ] Route `PATCH /notifications/{id}/read` → mark single notification read
- [ ] Route `PATCH /notifications/read-all` → mark all read
- [ ] Feature test: unread count updates after sending notification; mark-read clears it

**DoD:**
- Notification bell renders in navbar with live unread count
- Mark-read and mark-all-read work correctly

---

### T6.2 — Attendance Notifications for Guardians

**Description:** When a student is marked Absent, Sick, or Permitted, their guardian receives an in-app notification.

**Implementation:**
- Create `app/Notifications/StudentAbsentNotification.php`

**Refs:** NOT-01

**Depends on:** T2.2, T6.1

- [ ] Notification sent via `database` channel to the student's guardian user
- [ ] Notification data: student name, date, status, recorded_by name
- [ ] Dispatch notification in `AttendanceController@store` after successful save
- [ ] Unit test: `StudentAbsentNotification` formats `toArray()` data correctly
- [ ] Feature test: after submitting attendance with absent student, guardian has unread notification

**DoD:**
- Guardian receives notification for absent/sick/permitted status only (not for PRESENT)
- Notification content includes student name and date

---

### T6.3 — Payment Notifications

**Description:** Guardians receive an in-app notification when a payment becomes overdue. Admins receive notifications for overdue payments.

**Implementation:**
- Create `app/Notifications/PaymentOverdueNotification.php`
- Dispatch from `MarkOverduePayments` command

**Refs:** NOT-01, FIN-08

**Depends on:** T4.5, T6.1

- [ ] `PaymentOverdueNotification` sent to guardian of the student with overdue payment
- [ ] Copy notification sent to all `administrator` role users
- [ ] `MarkOverduePayments` command dispatches notification for each newly overdue payment
- [ ] Unit test: notification formats correctly; command sends correct number of notifications
- [ ] Feature test: after command runs, guardian and admins have unread notifications

**DoD:**
- Notifications only sent when status transitions from UNPAID → OVERDUE (not re-sent on subsequent runs)
- Uses a `notified_at` column or equivalent to prevent duplicate notifications

---

### T6.4 — Learning Progress Notifications

**Description:** Guardian receives an in-app notification when a new learning progress record is added for their child.

**Implementation:**
- Create `app/Notifications/NewLearningProgressNotification.php`
- Dispatch from `LearningProgressController@store`

**Refs:** NOT-01

**Depends on:** T3.2, T6.1

- [ ] Notification sent to student's guardian after new progress record saved
- [ ] Notification data: student name, subject, milestone, teacher name, date
- [ ] Unit test: notification data array has correct keys
- [ ] Feature test: guardian receives notification after teacher creates progress record

**DoD:**
- Notification dispatched only on create, not on update
- Guardian can see progress summary in notification data

---

### T6.5 — Email Notification Channel

**Description:** All in-app notifications also optionally send via email if the user has email notifications enabled.

**Implementation:**
- Add `email_notifications` boolean column to `users` table (default true)
- Update all notification classes to include `mail` channel when user opts in

**Refs:** NOT-04

**Depends on:** T6.2, T6.3, T6.4

- [ ] Migration: `add_email_notifications_to_users_table` adds nullable boolean `email_notifications` (default `true`)
- [ ] Profile page: add toggle for email notifications preference
- [ ] Each notification class: `via()` returns `['database', 'mail']` if `$notifiable->email_notifications`, else `['database']` only
- [ ] `toMail()` method on each notification class with proper subject and body
- [ ] Feature test: user with email_notifications=false does not send mail; user with true sends mail (use `Mail::fake()`)

**DoD:**
- Users can opt out of email notifications from their profile
- All notification classes respect the preference

---

## Phase 7 — Inventory Management

> Depends on: Phase 1 complete. Independent of Phases 2–6.

---

### T7.1 — Inventory: Migration & Model

**Description:** Create the database layer for physical asset inventory tracking.

**Implementation:**
- Create `app/Enums/ItemCondition.php`
- Create migration `create_inventories_table`
- Create `app/Models/Inventory.php`
- Create `database/factories/InventoryFactory.php`

**Refs:** INV-01, INV-02

**Depends on:** T1.1

- [ ] `ItemCondition` enum: `GOOD`, `DAMAGED`, `LOST`
- [ ] Migration columns: `id`, `name` (string), `quantity` (integer), `condition` (string), `acquisition_date` (date), `notes` (nullable text), `timestamps`
- [ ] Model: fillable, cast `condition` to `ItemCondition`, cast `acquisition_date` to `date`
- [ ] Factory with all condition states
- [ ] Unit test: `ItemCondition` enum has correct values

**DoD:**
- Migration runs clean
- Factory produces valid inventory records

---

### T7.2 — Admin: CRUD Inventory Items

**Description:** Administrators can add, view, edit, and delete inventory items.

**Implementation:**
- Create `app/Http/Controllers/InventoryController.php`
- Create form requests and views
- Add routes

**Refs:** INV-01, INV-02, INV-04

**Depends on:** T7.1

- [ ] RESTful routes under `/inventory`
- [ ] Gate: `owner`, `headmaster`, `administrator` can view; `administrator` can create/update/delete
- [ ] Validation: `name` required, `quantity` required integer ≥ 0, `condition` valid enum value, `acquisition_date` valid date
- [ ] Index page: server-side DataTable filterable by condition
- [ ] Flash notifications and i18n keys
- [ ] Feature test: CRUD operations, authorization, filtering

**DoD:**
- All CRUD operations work and are tested
- Headmaster/Owner can view but not modify

---

### T7.3 — Record Item Usage or Disposal

**Description:** Administrators can reduce inventory quantity via a usage/disposal record, with a reason field.

**Implementation:**
- Create migration `create_inventory_logs_table`
- Create `app/Models/InventoryLog.php`
- Add `usage` action to `InventoryController`

**Refs:** INV-03

**Depends on:** T7.2

- [ ] `inventory_logs` table: `id`, `inventory_id` (FK), `type` (usage/disposal), `quantity_changed` (integer), `reason` (string), `recorded_by` (FK → users.id), `timestamps`
- [ ] Route `POST /inventory/{inventory}/log` → creates log entry and decrements `inventories.quantity`
- [ ] Validation: `quantity_changed` must not exceed current `quantity`
- [ ] Gate: `administrator` only
- [ ] Feature test: logging usage decrements quantity; over-quantity triggers validation error

**DoD:**
- Quantity cannot go negative
- Each usage/disposal traceable by user and reason

---

## Phase 8 — Reporting & Analytics

> Depends on: T2.5 (attendance summary), T3.3 (progress data), T4.2 (payment data), T4.7 (export infrastructure).

---

### T8.1 — Dashboard: Summary Statistics

**Description:** The dashboard shows live summary statistics relevant to the logged-in role: total students by status, today's attendance rate, and recent payment activity.

**Implementation:**
- Create `app/Services/DashboardService.php`
- Update `PageController@dashboard`
- Update dashboard view with stat cards

**Refs:** RPT-01

**Depends on:** T2.5, T4.2

- [ ] `DashboardService::stats(User $user): array` returns role-appropriate data:
  - All roles: total active students
  - Admin/Headmaster/Owner: attendance rate today, total unpaid payments, total overdue payments
  - Teacher: total students they recorded progress for this month
  - Guardian: child's attendance rate this month, outstanding payment count
- [ ] Dashboard view: stat cards using Bootstrap grid
- [ ] Unit test: `DashboardService::stats()` returns correct shape for each role
- [ ] Stats use efficient aggregate queries (no N+1)

**DoD:**
- Dashboard loads in < 1 second for typical dataset (< 500 students)
- Guardian sees only their child's stats, not institution-wide data

---

### T8.2 — Attendance Reports

**Description:** Administrators can generate and view attendance reports for a selected period (weekly or monthly), per student or all students.

**Implementation:**
- Create `app/Http/Controllers/ReportController.php`
- Create attendance report view

**Refs:** RPT-02, RPT-05

**Depends on:** T2.5, T4.7

- [ ] Route `GET /report/attendance?period=weekly|monthly&date=YYYY-MM-DD` → paginated attendance report
- [ ] Report shows each student with Present/Absent/Sick/Permitted counts and attendance percentage
- [ ] Export to PDF and Excel via `GET /report/attendance/export?format=xlsx|pdf&...`
- [ ] Gate: `owner`, `headmaster`, `administrator` only
- [ ] Feature test: report returns correct data; export returns correct Content-Type

**DoD:**
- Report includes all active students even if they have zero attendance records
- Export tested for both formats

---

### T8.3 — Financial Reports

**Description:** Administrators can generate financial summary reports showing total collected, total outstanding, and total overdue fees per period.

**Implementation:**
- Add financial report action to `ReportController`
- Create financial report view

**Refs:** RPT-03, RPT-05

**Depends on:** T4.2, T4.3, T4.7

- [ ] Route `GET /report/finance?period=YYYY-MM` → financial summary
- [ ] Shows: total payments by status, total honorariums paid, net income for period
- [ ] Export to PDF and Excel
- [ ] Gate: `owner`, `headmaster`, `administrator`
- [ ] Feature test: report totals match fixture data

**DoD:**
- Financial totals are computed via SQL aggregates, not PHP loops
- Report correctly handles periods with no records (shows zeros, not errors)

---

### T8.4 — Learning Progress Reports

**Description:** Administrators can generate a learning progress report per student, exportable as PDF, showing all progress entries for a period.

**Implementation:**
- Add progress report action to `ReportController`

**Refs:** RPT-04, RPT-05

**Depends on:** T3.3, T4.7

- [ ] Route `GET /report/progress?student_id=&from=YYYY-MM-DD&to=YYYY-MM-DD` → progress report for student
- [ ] Export to PDF via `GET /report/progress/export?format=pdf&...`
- [ ] Gate: `owner`, `headmaster`, `administrator` (teacher can access for own students only)
- [ ] Feature test: report scoped correctly by date range; teacher scoping tested

**DoD:**
- PDF export renders all progress entries ordered by date
- Teacher cannot export another teacher's students' reports (403)

---

## Cross-Cutting Tasks

> These tasks apply across all phases and should be done as features are built.

---

### TX.1 — i18n: Translate All New Strings

**Description:** All new UI strings added in Phases 2–8 must have translations in both `lang/id/` and `lang/en/`.

**Depends on:** Each phase task that adds UI

- [ ] Every new label, button, error message, and notification text has an entry in `lang/id/*.php` and `lang/en/*.php`
- [ ] No hardcoded Indonesian or English strings in Blade views — all use `__('key')` or `@lang`

**DoD:** Application is fully operable in both languages with no missing translation keys (testable via `php artisan lang:publish` + manual review or automated missing-key detection)

---

### TX.2 — Sidebar Navigation Updates

**Description:** As new modules are added, the sidebar must reflect them with role-based visibility.

**Depends on:** Each phase that adds a new module

- [ ] Sidebar shows Attendance link for admin and teacher roles (Phase 2)
- [ ] Sidebar shows Learning Progress link for teacher, admin, headmaster (Phase 3)
- [ ] Sidebar shows Finance link for admin, headmaster, owner (Phase 4)
- [ ] Sidebar shows Announcements link for all roles (Phase 5)
- [ ] Sidebar shows Inventory link for admin, headmaster, owner (Phase 7)
- [ ] Sidebar shows Reports link for admin, headmaster, owner (Phase 8)
- [ ] Guardian sidebar shows: My Child's Attendance, My Child's Progress, My Child's Payments (Phases 2, 3, 4)

**DoD:** No sidebar link appears for a role that does not have permission to access the route

---

### TX.3 — Code Style: Pint on All New Files

**Description:** All new PHP files must pass Laravel Pint before merge.

- [ ] Run `./vendor/bin/pint` after completing each task
- [ ] CI/CD or pre-commit check runs Pint

**DoD:** `./vendor/bin/pint --test` exits with code 0 on all new files

---

### TX.4 — Seed Data for All New Modules

**Description:** Database seeders must cover all new entities so developers can run `php artisan db:seed` and get a fully populated local environment.

**Depends on:** Each phase's factory tasks

- [ ] `AttendanceSeeder`: seed 30 days of attendance for all seeded students
- [ ] `LearningProgressSeeder`: seed 3 months of weekly progress records per student
- [ ] `PaymentSeeder`: seed 6 months of payments per student with mixed statuses
- [ ] `HonorariumSeeder`: seed 6 months of honorariums per teacher
- [ ] `AnnouncementSeeder`: seed 5 public and 5 internal announcements
- [ ] `InventorySeeder`: seed 10 inventory items with mixed conditions
- [ ] Update `DatabaseSeeder` to call all new seeders in correct order

**DoD:** `php artisan migrate:fresh --seed` completes without errors and populates all tables

---

## Task Summary

| Phase | Tasks | Status |
|-------|-------|--------|
| Phase 1 — Foundation (Retroactive Testing) | T1.1, T1.2, T1.3 | Pending |
| Phase 2 — Attendance | T2.1, T2.2, T2.3, T2.4, T2.5 | Done |
| Phase 3 — Learning Progress | T3.1, T3.2, T3.3, T3.4, T3.5 | Done |
| Phase 4 — Financial Management | T4.1, T4.2, T4.3, T4.4, T4.5, T4.6, T4.7 | Done |
| Phase 5 — Announcements | T5.1, T5.2, T5.3, T5.4 | Pending |
| Phase 6 — Notifications | T6.1, T6.2, T6.3, T6.4, T6.5 | Pending |
| Phase 7 — Inventory | T7.1, T7.2, T7.3 | Pending |
| Phase 8 — Reporting & Analytics | T8.1, T8.2, T8.3, T8.4 | Pending |
| Cross-Cutting | TX.1, TX.2, TX.3, TX.4 | Ongoing |
| **Total** | **34 tasks** | **0 / 34 done** |
