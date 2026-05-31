# Parent Dashboard Update Plan

## Goal

Bring the parent dashboard up to date with the newer student portal logic so parents can see a useful per-child overview instead of only basic summary cards.

The parent dashboard must remain scoped to the authenticated parent's own children only.

## Current Gap

`ParentPortalController` currently sends a shallow child payload:

- Basic child identity: id, name, code.
- Group count.
- Latest attendance status from the latest session.
- Latest posted exam result percentage/title.
- Student PDF URL.

The newer student portal logic now includes richer data that is not reflected on the parent dashboard:

- Saturday-Friday weekly session view.
- Upcoming sessions.
- Recent attendance with session, group, date, status, and notes.
- Upcoming and conducted exam buckets.
- Exam availability/status such as available, scheduled, submitted, closed, and in progress.
- Recent student notifications.

## Files To Modify

### Backend

- `Modules/Core/app/Http/Controllers/ParentPortalController.php`
  - Inject `BuildStudentWeekView`.
  - Inject `StudentExamService`.
  - Load each child with groups, attendance records, session/group relationships, and student user notifications.
  - Build a richer child dashboard payload.
  - Preserve parent ownership by starting from `$request->user()->children()`.

- `Modules/Core/app/Support/BuildStudentWeekView.php`
  - Reuse as-is if possible.
  - Only modify if parent dashboard needs small formatting additions that should also benefit student views.

- `Modules/Exams/app/Services/StudentExamService.php`
  - Reuse `indexDataForStudent()` for parent dashboard exam buckets.
  - Avoid exposing student-only action URLs to parent dashboard if parents should not open attempts.

### Frontend

- `resources/js/Pages/Parent/Dashboard.vue`
  - Replace the current simple card-only dashboard with a richer parent portal overview.
  - Keep child cards, but add expanded sections for:
    - This week's sessions.
    - Upcoming sessions.
    - Recent attendance.
    - Upcoming exams.
    - Latest/conducted exam results.
    - Recent notifications.
  - Preserve existing PDF download.
  - Keep links to full Attendance and Exams pages.

- `resources/js/Pages/Parent/Attendance.vue`
  - Optional follow-up: align this page with the same Saturday-Friday week view used by students.
  - Currently it shows the last 5 sessions per group, not the weekly view.

- `resources/js/Pages/Parent/Exams.vue`
  - Optional follow-up: update from result-only view to upcoming/conducted buckets.
  - Current page only uses `ExamResult` rows and does not show upcoming exams.

## Payload Shape Proposal

Each child in `ParentPortalController` should include:

```php
[
    'id' => $child->id,
    'name' => $child->name,
    'code' => $child->code,
    'phone' => $child->phone,
    'group_count' => $child->groups->count(),
    'groups' => [...],
    'week' => $weekView->build($child),
    'upcoming_sessions' => [...],
    'recent_attendance' => [...],
    'upcoming_exams' => [...],
    'conducted_exams' => [...],
    'latest_exam_percentage' => ...,
    'latest_exam_title' => ...,
    'recent_notifications' => [...],
    'pdf_url' => route('parent.child.pdf', $child->id),
]
```

Keep payload limits small:

- Upcoming sessions: 3-5 per child.
- Recent attendance: 5-6 per child.
- Upcoming exams: 3-4 per child.
- Conducted exams: 3-4 per child.
- Notifications: 3-5 per child.

## UI Plan

### Parent Dashboard Header

- Show a compact summary for the parent:
  - Number of linked children.
  - Total groups across children.
  - Upcoming sessions count.
  - Upcoming exams count.

### Child Overview Cards

For each child:

- Name, code, group count, PDF download.
- Latest attendance status.
- Latest exam percentage.
- Quick links to Attendance and Exams.

### Per-Child Detail Sections

Use dense, scannable panels rather than marketing-style sections:

- `This week`
  - Display Saturday-Friday days.
  - Show sessions under each day with time, group, subject, and attendance status.

- `Upcoming`
  - Next few sessions.
  - Next few exams.

- `Recent`
  - Attendance records.
  - Exam results.
  - Notifications.

If there are multiple children, keep each child section self-contained so parents do not mix data between children.

## Access And Safety Rules

- Never query students globally for the parent dashboard.
- Always start child data from `$request->user()->children()`.
- Do not expose student exam attempt URLs to parent users unless parent review access is explicitly added later.
- Do not expose other parents' notifications or children.
- Preserve the existing parent PDF authorization behavior.

## Tests To Add Or Update

### Feature Tests

- Update existing parent dashboard tests in `tests/Feature/ExampleTest.php` or add a focused test file such as:
  - `tests/Feature/ParentDashboardTest.php`

Test cases:

- Parent dashboard includes only the authenticated parent's children.
- Parent dashboard includes weekly sessions for a linked child.
- Parent dashboard includes upcoming sessions for a linked child.
- Parent dashboard includes recent attendance for a linked child.
- Parent dashboard includes upcoming/conducted exam data for a linked child.
- Parent dashboard does not include exam attempt URLs intended for student users.
- Parent dashboard includes recent parent/student-relevant notifications only if intended.

### Existing Tests To Watch

- `tests/Feature/ExampleTest.php`
- `tests/Feature/AdminNotificationsManagementTest.php`
- `tests/Feature/StudentDashboardWeekViewTest.php`
- `tests/Feature/StudentExamPortalTest.php`
- `tests/Feature/StudentQrAttendanceTest.php`

## Verification

Run after backend or frontend changes:

```bash
composer test
npm run build
```

If Blade/Tailwind/Vue UI is changed, also manually verify:

- Parent with one child.
- Parent with multiple children.
- Parent with no linked children.
- Child with no groups.
- Child with groups but no sessions.
- Child with upcoming sessions and attendance.
- Child with upcoming exams.
- Child with conducted exam results.

## Suggested Implementation Order

1. Update `ParentPortalController` payload using existing services.
2. Add or update feature tests for parent dashboard data and ownership scoping.
3. Update `Parent/Dashboard.vue` to render the richer payload.
4. Run `composer test`.
5. Run `npm run build`.
6. Decide whether to also update `Parent/Attendance.vue` and `Parent/Exams.vue` in the same pass or as follow-up work.

