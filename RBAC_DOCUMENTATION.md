# DIGISKUL - Role-Based Access Control (RBAC) Documentation

## Overview

DIGISKUL implements a comprehensive Role-Based Access Control (RBAC) system with tenant isolation, ensuring each user sees only what they need and does only what they're permitted to do.

---

## User Roles & Access Summary

| Role | Read | Write | Approve | Global Access |
|------|------|-------|---------|---------------|
| Super Admin | ✔ | ✔ | ✔ | ✔ |
| School Admin | ✔ | ✔ | ✔ | ✖ |
| Class Teacher | ✔ | ✔ | ✔ (class only) | ✖ |
| Teacher | ✔ | ✔ | ✖ | ✖ |
| Student | ✔ | ✖ | ✖ | ✖ |
| Parent | ✔ | ✖ | ✖ | ✖ |
| Bursar | ✔ | ✔ (fees) | ✖ | ✖ |

---

## 1. Super Admin (System Owner)

**Panel Access:** System-wide administration panel

**Capabilities:**
- Manage all schools (create, update, suspend, delete)
- View system-wide analytics and usage
- Manage licenses and subscriptions
- Configure system settings
- Access all schools' data
- View activity logs across all schools
- Enable/disable features per school

**Panel Features:**
- `/admin/schools` - School management
- `/admin/licenses` - License management
- `/admin/system-settings` - System configuration
- `/admin/activity-logs` - Audit trail
- `/admin/analytics` - System analytics

---

## 2. School Admin / Principal

**Panel Access:** School-level administration panel

**Capabilities:**
- Manage teachers, staff, students, parents
- Create and manage classes and subjects
- Assign teachers to classes/subjects
- Approve timetables and duty rosters
- Manage academic terms and sessions
- View all school reports and analytics
- Publish announcements
- Archive academic data

**Panel Features:**
- `/users` - User management
- `/teacher-assignments` - Teacher to class/subject mapping
- `/classes`, `/subjects` - Academic structure
- `/students` - Full student management
- `/settings` - School configuration
- `/archive` - Session archiving

---

## 3. Teacher

**Panel Access:** Teacher panel (limited to assigned classes/subjects)

**Capabilities:**
- View students in assigned classes only
- Mark attendance for assigned classes
- Record grades for assigned subjects only
- View personal timetable and duty roster
- Add comments to report cards
- View class performance analytics

**Data Restrictions:**
- Cannot view other teachers' data
- Cannot access administrative settings
- Cannot see students from non-assigned classes

**Panel Features:**
- `/dashboard` - Personal teaching overview
- `/attendance` - Mark for assigned classes
- `/grades` - Record for assigned subjects
- `/timetable` - View personal schedule
- `/duties` - View own duty assignments

---

## 4. Class Teacher (Extended Teacher)

**Panel Access:** Enhanced teacher panel with class oversight

**Capabilities:**
- All teacher capabilities, plus:
- Full access to assigned class data
- Coordinate with subject teachers
- Approve grades for their class
- Generate and compile report cards
- View class-wide attendance summary
- Add behavioral records

**Panel Features:**
- All teacher features, plus:
- `/report-cards` - Generate and manage
- `/reports` - Class summary reports
- Class performance analytics

---

## 5. Student

**Panel Access:** Student portal (read-only)

**Capabilities:**
- View personal profile
- View own attendance records
- View own grades and results
- View personal timetable
- Download report cards
- View school announcements

**Data Restrictions:**
- Read-only access
- Cannot view other students' data
- Cannot modify any records

**Panel Features:**
- `/student/dashboard` - Personal overview
- `/student/profile` - Personal information
- `/student/grades` - View assessment scores
- `/student/attendance` - Attendance history
- `/student/timetable` - Class schedule

---

## 6. Parent / Guardian

**Panel Access:** Parent portal (read-only for linked children)

**Capabilities:**
- View linked children's profiles
- Monitor children's attendance
- View children's grades
- Download children's report cards
- View fee balances
- Read school announcements

**Data Restrictions:**
- Can only see linked children
- Read-only access
- Cannot access other students' data

**Panel Features:**
- `/parent/dashboard` - Children overview
- `/parent/children` - List of linked children
- `/parent/child/:id` - Individual child details

---

## 7. Bursar / Accountant

**Panel Access:** Financial management panel

**Capabilities:**
- Manage fee structures
- Record student payments
- Generate payment receipts
- View debtors list
- Send payment reminders
- Export financial reports

**Data Restrictions:**
- Cannot access academic data (grades)
- Cannot mark attendance
- Fee-related functions only

**Panel Features:**
- `/bursar/dashboard` - Financial overview
- `/fees` - Fee management
- `/payments` - Payment recording
- `/fees/debtors` - Debtor management

---

## 8. Non-Academic Staff

### Librarian
- View students for book issuing
- Manage library inventory
- Track book borrowing/returns

### ICT Officer
- Provide user support
- Basic user management
- System troubleshooting access

---

## Technical Implementation

### Backend (Laravel)

#### Middleware
- `CheckRole` - Verifies user has required role(s)
- `CheckPermission` - Verifies user has required permission(s)
- `EnsureSchoolAccess` - Tenant isolation enforcement
- `LogActivity` - NDPR-compliant activity logging

#### Permission System
Using Spatie Laravel Permission with 100+ granular permissions organized by module:
- `students.view`, `students.create`, `students.update`, `students.delete`
- `attendance.mark`, `attendance.view`, `attendance.mark_own_class`
- `grades.record`, `grades.approve`, `grades.view_own_subject`
- And many more...

### Frontend (Vue.js)

#### Auth Store
- `hasRole(roles)` - Check if user has specific role(s)
- `hasMinRole(minRole)` - Check if user has minimum role level
- `canAccess(feature)` - Feature-based permission checking
- Role-based dashboard redirects

#### Router Guards
- Route-level role requirements in meta
- Automatic redirects for unauthorized access
- Role-specific navigation menus

---

## Database Tables

### RBAC Enhancement Tables
- `teacher_assignments` - Teacher to class/subject mapping
- `parent_student_links` - Parent to student relationships
- `activity_logs` - Audit trail
- `announcements` - Role-targeted notices
- `school_features` - Per-school feature toggles

---

## Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@digiskul.app | password |
| School Admin | admin@nurlight.digiskul.app | password |
| Teacher | teacher@nurlight.digiskul.app | password |
| Class Teacher | classteacher@nurlight.digiskul.app | password |
| Student | student@nurlight.digiskul.app | password |
| Parent | parent@nurlight.digiskul.app | password |
| Bursar | bursar@nurlight.digiskul.app | password |
| ICT Officer | ict@nurlight.digiskul.app | password |

---

## Setup Commands

```bash
# Run migrations for new RBAC tables
php artisan migrate

# Seed roles, permissions, and test users
php artisan db:seed

# Clear permission cache after changes
php artisan permission:cache-reset
```

---

## Security Features

1. **Role-Based Access Control (RBAC)** - Spatie Laravel Permission
2. **School-level tenant isolation** - Users can only access their school's data
3. **Encrypted authentication** - Laravel Sanctum tokens
4. **Activity logging per user** - NDPR-compliant audit trail
5. **License validation** - Block access for expired schools
6. **Feature toggles** - Disable features per school

---

## Conclusion

The DIGISKUL RBAC system ensures:
- **Clear separation of responsibilities** between all user types
- **Data privacy** through tenant isolation
- **Audit compliance** with comprehensive activity logging
- **Flexible administration** with granular permissions
- **Scalable architecture** for multi-school deployments
