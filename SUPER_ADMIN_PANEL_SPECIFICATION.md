# DIGISKUL Super Admin (System Owner) Panel Specification

> **Document Version:** 1.0  
> **Last Updated:** January 7, 2026  
> **Classification:** Product Architecture Document  
> **Scope:** Super Admin Panel Only

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [Primary Role Definition](#2-primary-role-definition)
3. [Duties & Responsibilities](#3-duties--responsibilities)
4. [Privileges & Override Powers](#4-privileges--override-powers)
5. [Access Rights & Visibility Scope](#5-access-rights--visibility-scope)
6. [Panel Features & Modules](#6-panel-features--modules)
7. [Technical Implementation](#7-technical-implementation)
8. [Security Framework](#8-security-framework)
9. [UI/UX Design Reference](#9-uiux-design-reference)

---

## 1. Executive Summary

The **Super Admin (System Owner)** is the highest authority within the DIGISKUL multi-tenant platform. This role operates at the **platform level**, transcending individual school (tenant) boundaries while maintaining strict data isolation principles.

### Key Characteristics

| Attribute | Description |
|-----------|-------------|
| **Role Level** | Platform-wide (cross-tenant) |
| **Access Scope** | Unrestricted system access |
| **Override Authority** | Full override of lower-level restrictions |
| **Data Visibility** | All tenants, all modules, all records |
| **Panel Type** | Centralized System Administration Console |

### Core Authority Areas

```
┌─────────────────────────────────────────────────────────────────────┐
│                    SUPER ADMIN AUTHORITY MATRIX                     │
├─────────────────────────────────────────────────────────────────────┤
│  🏫 TENANT GOVERNANCE    │ Onboard, configure, suspend, delete     │
│  ⚙️ SYSTEM CONFIGURATION │ Global defaults, feature toggles        │
│  📜 LICENSE MANAGEMENT   │ Subscriptions, billing, quotas          │
│  📊 PLATFORM ANALYTICS   │ Usage, health, performance metrics      │
│  🔐 SECURITY & AUDIT     │ Logs, compliance, access control        │
│  🔄 OPERATIONS           │ Backup, recovery, maintenance           │
│  🎚️ FEATURE CONTROL      │ Module enablement per school            │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 2. Primary Role Definition

### 2.1 Role Identity

| Property | Value |
|----------|-------|
| **Role Name** | Super Admin |
| **Alias** | System Owner / Platform Administrator |
| **Role Code** | `SUPER_ADMIN` |
| **Role Priority** | 100 (Highest) |
| **Tenant Scope** | Global (All Tenants) |
| **Data Boundary** | Unrestricted |

### 2.2 Role Context

The Super Admin exists **outside the tenant hierarchy** and operates from a centralized control plane. Unlike school-level administrators who are constrained to their respective tenant boundaries, the Super Admin:

- **Owns** the entire DIGISKUL platform infrastructure
- **Governs** all schools as independent tenants
- **Configures** system-wide rules that cascade to all tenants
- **Monitors** aggregate platform health and individual school activity
- **Enforces** compliance, licensing, and operational policies

### 2.3 Role Hierarchy Position

```
                    ┌───────────────────┐
                    │    SUPER ADMIN    │  ← Platform Level
                    │  (System Owner)   │
                    └─────────┬─────────┘
                              │
        ┌─────────────────────┼─────────────────────┐
        │                     │                     │
   ┌────▼────┐          ┌────▼────┐          ┌────▼────┐
   │ School A │          │ School B │          │ School N │
   │ (Tenant) │          │ (Tenant) │          │ (Tenant) │
   └────┬────┘          └────┬────┘          └────┬────┘
        │                     │                     │
   [School Admin]        [School Admin]        [School Admin]
        │                     │                     │
   [All Other Roles]     [All Other Roles]     [All Other Roles]
```

---

## 3. Duties & Responsibilities

### 3.1 Tenant Lifecycle Management

| Duty | Description | Frequency |
|------|-------------|-----------|
| **School Onboarding** | Register new schools, configure initial settings, assign licenses | On-demand |
| **Tenant Configuration** | Set school-specific parameters, branding, feature access | Initial + Updates |
| **Subscription Management** | Monitor, renew, upgrade/downgrade school subscriptions | Monthly/Annually |
| **School Suspension** | Temporarily disable access for policy violations or non-payment | As needed |
| **School Termination** | Permanently deactivate schools and archive/delete data | Rare |

### 3.2 System Configuration & Governance

| Duty | Description | Frequency |
|------|-------------|-----------|
| **Global Default Settings** | Define platform-wide defaults (grading scales, attendance rules, etc.) | Initial + Periodic |
| **System Rule Definition** | Establish policies that apply across all tenants | As needed |
| **Feature Flag Management** | Enable/disable modules system-wide or per-school | Ongoing |
| **Template Management** | Create and maintain report card templates, certificate templates | Periodic |
| **Integration Configuration** | Configure third-party integrations (payment gateways, SMS, email) | Initial + Updates |

### 3.3 Platform Monitoring & Oversight

| Duty | Description | Frequency |
|------|-------------|-----------|
| **Health Monitoring** | Track system uptime, API response times, error rates | Continuous |
| **Usage Analytics** | Review platform-wide and per-school usage metrics | Daily/Weekly |
| **Capacity Planning** | Monitor storage, database, and compute resource utilization | Weekly/Monthly |
| **Compliance Auditing** | Review activity logs, access patterns, data handling | Weekly/Monthly |
| **Performance Optimization** | Identify and resolve system bottlenecks | Ongoing |

### 3.4 Security & Access Control

| Duty | Description | Frequency |
|------|-------------|-----------|
| **Global Role Management** | Define and update system-wide role templates | As needed |
| **Permission Architecture** | Manage the permission matrix and access policies | Periodic |
| **Audit Trail Review** | Examine cross-school activity logs for anomalies | Weekly |
| **Security Incident Response** | Investigate and address security breaches | As needed |
| **Data Protection Compliance** | Ensure NDPR/GDPR compliance across all tenants | Ongoing |

### 3.5 Operations & Maintenance

| Duty | Description | Frequency |
|------|-------------|-----------|
| **Backup Oversight** | Verify automated backups, initiate manual backups | Daily verification |
| **Disaster Recovery** | Execute recovery procedures when needed | Emergency |
| **System Updates** | Deploy platform updates and patches | Scheduled |
| **Database Maintenance** | Optimize, clean, and maintain database health | Weekly/Monthly |
| **Archive Management** | Manage historical data retention and archival | Quarterly/Annually |

---

## 4. Privileges & Override Powers

### 4.1 Complete Privilege Matrix

The Super Admin possesses **unrestricted privileges** across all system modules:

| Module | Read | Create | Update | Delete | Approve | Override | Configure |
|--------|------|--------|--------|--------|---------|----------|-----------|
| Schools/Tenants | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Users (All Schools) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Students (All Schools) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Classes/Subjects | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Attendance Records | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Grades/Results | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Report Cards | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Fee/Payments | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Announcements | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| System Settings | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Licenses | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Activity Logs | ✅ | ❌ | ❌ | ❌* | ❌ | ❌ | ✅ |
| Backups | ✅ | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ |

> *Activity logs are immutable for audit integrity. Only archival/purge after retention period.

### 4.2 Override Powers

The Super Admin has the authority to **override** lower-level restrictions:

#### 4.2.1 Access Overrides

| Override Type | Description |
|---------------|-------------|
| **Tenant Bypass** | Access any school's data without school-level permissions |
| **Role Elevation** | Temporarily grant elevated permissions to any user |
| **Lock Override** | Unlock records locked by school admins |
| **Approval Bypass** | Approve/reject items pending lower-level approval |
| **Session Override** | Force logout any user, terminate active sessions |

#### 4.2.2 Restriction Overrides

| Override Type | Description |
|---------------|-------------|
| **Feature Enforcement** | Enable features for a school regardless of license tier |
| **Quota Override** | Temporarily increase limits (students, users, storage) |
| **Deadline Extension** | Extend system-enforced deadlines for data entry |
| **Suspension Lift** | Reinstate suspended schools or users |
| **Data Retention Override** | Retain data beyond standard retention periods |

#### 4.2.3 Emergency Powers

| Power | Description | Trigger |
|-------|-------------|---------|
| **System Lockdown** | Disable all non-admin access platform-wide | Major security incident |
| **School Isolation** | Completely isolate a school's access | Policy violation/breach |
| **Data Freeze** | Prevent all write operations to specific data | Legal/compliance hold |
| **Emergency Backup** | Initiate immediate full system backup | Pre-incident capture |
| **Failover Activation** | Switch to disaster recovery infrastructure | System failure |

### 4.3 Exclusions & Constraints

Even the Super Admin has certain operational constraints:

| Constraint | Rationale |
|------------|-----------|
| Cannot edit immutable audit logs | Legal compliance, audit integrity |
| Cannot delete own account without transfer | System continuity |
| All actions are logged | Accountability, compliance |
| Cannot bypass MFA for own login | Security best practice |
| Cannot bulk-delete production data without confirmation chain | Safety mechanism |

---

## 5. Access Rights & Visibility Scope

### 5.1 Data Visibility Matrix

| Data Category | Visibility Level | Access Details |
|---------------|------------------|----------------|
| **School Profiles** | Full | All registration details, configurations, statuses |
| **User Accounts** | Full | All users across all schools, credentials (masked) |
| **Student Records** | Full | Academic, attendance, behavioral, financial records |
| **Academic Data** | Full | Grades, results, report cards for all schools |
| **Financial Data** | Full | Fee structures, payments, balances across all schools |
| **System Logs** | Full | API logs, error logs, activity logs, security logs |
| **Analytics** | Full | Platform metrics, school metrics, user metrics |
| **Configurations** | Full | All system and school-level settings |
| **Backups** | Full | Backup manifests, restore points, storage usage |

### 5.2 Cross-Tenant Access Rights

```
┌────────────────────────────────────────────────────────────────────────┐
│                    SUPER ADMIN CROSS-TENANT ACCESS                     │
├────────────────────────────────────────────────────────────────────────┤
│                                                                        │
│   ┌──────────────┐     ┌──────────────┐     ┌──────────────┐          │
│   │   SCHOOL A   │     │   SCHOOL B   │     │   SCHOOL C   │          │
│   │   (Tenant)   │     │   (Tenant)   │     │   (Tenant)   │          │
│   ├──────────────┤     ├──────────────┤     ├──────────────┤          │
│   │ • Users      │     │ • Users      │     │ • Users      │          │
│   │ • Students   │ ◄─► │ • Students   │ ◄─► │ • Students   │          │
│   │ • Grades     │     │ • Grades     │     │ • Grades     │          │
│   │ • Finance    │     │ • Finance    │     │ • Finance    │          │
│   │ • Settings   │     │ • Settings   │     │ • Settings   │          │
│   └──────────────┘     └──────────────┘     └──────────────┘          │
│           ▲                   ▲                   ▲                   │
│           │                   │                   │                   │
│           └───────────────────┼───────────────────┘                   │
│                               │                                        │
│                    ┌──────────▼──────────┐                            │
│                    │    SUPER ADMIN      │                            │
│                    │   (Full Access)     │                            │
│                    └─────────────────────┘                            │
│                                                                        │
└────────────────────────────────────────────────────────────────────────┘
```

### 5.3 Masked/Protected Data

Certain data is masked or requires additional confirmation:

| Data Type | Protection Level | Access Method |
|-----------|------------------|---------------|
| User Passwords | Hashed (never visible) | Reset only |
| Payment Card Details | Masked (last 4 digits) | Integration-level access |
| Personal Identification | Masked by default | Explicit view action |
| Medical Records | Encrypted | Requires unlock justification |
| Exported Backup Files | Encrypted | Decryption key required |

---

## 6. Panel Features & Modules

### 6.1 Dashboard Overview

**Route:** `/super-admin/dashboard`

The Super Admin dashboard provides a bird's-eye view of the entire platform:

#### Key Metrics Cards
- **Total Schools** (Active / Suspended / Trial)
- **Total Users** (Aggregate across all schools)
- **Total Students** (Enrollment figures)
- **Active Sessions** (Real-time logged-in users)
- **System Health Score** (0-100)
- **License Compliance** (Schools with valid/expired licenses)

#### Dashboard Widgets
- Platform-wide activity feed (real-time)
- School status breakdown chart
- License expiration timeline
- System resource utilization gauge
- Critical alerts panel
- Recent signups and onboardings

---

### 6.2 School Management

**Route:** `/super-admin/schools`

#### 6.2.1 School Directory

| Feature | Description |
|---------|-------------|
| **School List** | Paginated, filterable list of all schools |
| **Search & Filter** | By name, status, license type, region, creation date |
| **Bulk Actions** | Bulk suspend, bulk email, bulk export |
| **Quick View** | Inline preview of school details |

#### 6.2.2 School Onboarding

**Route:** `/super-admin/schools/create`

| Step | Fields/Actions |
|------|----------------|
| **Basic Info** | School name, code, registration number, address |
| **Contact Details** | Admin email, phone, emergency contact |
| **Configuration** | Academic year, term structure, grading system |
| **License Assignment** | Plan selection, quota allocation |
| **Feature Selection** | Module enablement |
| **Admin Setup** | Create initial School Admin account |
| **Finalization** | Review and activate |

#### 6.2.3 School Profile Management

**Route:** `/super-admin/schools/:id`

| Section | Capabilities |
|---------|--------------|
| **Overview Tab** | School summary, key metrics, status |
| **Configuration Tab** | Edit all school settings |
| **Users Tab** | View/manage all users in school |
| **Students Tab** | View student enrollment |
| **Features Tab** | Toggle features for this school |
| **License Tab** | View/modify license details |
| **Logs Tab** | Activity logs for this school |
| **Actions** | Suspend, Activate, Delete, Clone configuration |

#### 6.2.4 School Status Management

| Status | Description | Actions Available |
|--------|-------------|-------------------|
| **Active** | Fully operational | Suspend, Configure |
| **Trial** | Time-limited trial access | Extend, Convert to Paid, Suspend |
| **Suspended** | Access blocked | Activate, Delete |
| **Pending** | Awaiting setup completion | Complete Setup, Delete |
| **Archived** | Historical, read-only | Restore, Purge |

---

### 6.3 License & Subscription Management

**Route:** `/super-admin/licenses`

#### 6.3.1 License Plans

| Feature | Description |
|---------|-------------|
| **Plan Management** | Create, edit, delete license plans |
| **Plan Configuration** | Define features, quotas, pricing per plan |
| **Plan Comparison** | Side-by-side feature comparison matrix |

#### 6.3.2 Plan Attributes

| Attribute | Description |
|-----------|-------------|
| **Plan Name** | e.g., Starter, Standard, Premium, Enterprise |
| **Duration** | Monthly, Quarterly, Annually |
| **User Limit** | Maximum staff users |
| **Student Limit** | Maximum enrolled students |
| **Storage Quota** | File storage allocation |
| **Feature Set** | Enabled modules/features |
| **Price** | Cost per period |
| **Trial Period** | Days of free trial |

#### 6.3.3 Subscription Management

**Route:** `/super-admin/licenses/subscriptions`

| Feature | Description |
|---------|-------------|
| **Active Subscriptions** | All current subscriptions |
| **Expiring Soon** | Subscriptions expiring within 30 days |
| **Expired** | Schools with expired subscriptions |
| **Renewal Actions** | Manual renewal, grace period extension |
| **Upgrade/Downgrade** | Change school's plan |
| **Payment History** | View subscription payment records |

---

### 6.4 System Configuration

**Route:** `/super-admin/settings`

#### 6.4.1 Global Settings

| Category | Settings |
|----------|----------|
| **Platform Identity** | Platform name, logo, favicon, copyright text |
| **Default Values** | Default grading scale, attendance rules, academic structure |
| **Email Configuration** | SMTP settings, sender addresses, templates |
| **SMS Configuration** | SMS gateway settings, sender ID |
| **Storage Configuration** | Cloud storage settings, backup locations |
| **Security Settings** | Password policies, session timeouts, MFA requirements |

#### 6.4.2 Feature Registry

**Route:** `/super-admin/settings/features`

| Feature | Description |
|---------|-------------|
| **Feature List** | All available platform features/modules |
| **Global Toggle** | Enable/disable features platform-wide |
| **Description Management** | Update feature descriptions for school selection |
| **Dependency Mapping** | Define feature dependencies |

#### 6.4.3 Integration Settings

**Route:** `/super-admin/settings/integrations`

| Integration | Configuration |
|-------------|---------------|
| **Payment Gateways** | Paystack, Flutterwave, Stripe configuration |
| **Email Services** | Mailgun, SendGrid, AWS SES |
| **SMS Providers** | Twilio, Termii, Africa's Talking |
| **AI Services** | OpenAI, Gemini API keys |
| **Cloud Storage** | AWS S3, Google Cloud Storage, Azure Blob |
| **Analytics** | Google Analytics, Mixpanel |

---

### 6.5 Analytics & Reporting

**Route:** `/super-admin/analytics`

#### 6.5.1 Platform Analytics

| Metric Category | Metrics |
|-----------------|---------|
| **Adoption** | New schools per period, activation rate, churn rate |
| **Usage** | DAU/MAU, feature usage heatmap, peak hours |
| **Engagement** | Average session duration, screens per session |
| **Performance** | API response times, error rates, uptime |
| **Growth** | Student enrollment trends, user growth |
| **Revenue** | MRR, ARR, ARPU (if applicable) |

#### 6.5.2 School Comparison Reports

| Report | Description |
|--------|-------------|
| **Usage Ranking** | Schools ranked by platform usage |
| **Engagement Score** | Schools ranked by engagement metrics |
| **Feature Adoption** | Which features are used most by schools |
| **Compliance Score** | Schools ranked by data completeness |

#### 6.5.3 Export & Scheduling

| Feature | Description |
|---------|-------------|
| **Manual Export** | Export any report to PDF/Excel |
| **Scheduled Reports** | Configure automated report generation |
| **Email Distribution** | Auto-email reports to stakeholders |

---

### 6.6 Audit & Activity Logs

**Route:** `/super-admin/logs`

#### 6.6.1 Activity Log Viewer

| Feature | Description |
|---------|-------------|
| **Global Activity Feed** | All actions across all schools |
| **Filters** | By school, user, action type, date range, IP |
| **Search** | Full-text search within log entries |
| **Export** | Export logs for external analysis |

#### 6.6.2 Log Categories

| Category | Example Actions |
|----------|-----------------|
| **Authentication** | Login success/failure, password resets, MFA events |
| **CRUD Operations** | Create, update, delete actions on any record |
| **Administrative** | Role changes, permission modifications |
| **System Events** | Backup completions, maintenance actions |
| **Security Events** | Suspicious access attempts, policy violations |

#### 6.6.3 Security Audit

**Route:** `/super-admin/logs/security`

| Feature | Description |
|---------|-------------|
| **Failed Logins** | Repeated failed authentication attempts |
| **Unusual Access** | Access from new locations/devices |
| **Permission Escalation** | Attempts to access unauthorized resources |
| **Data Export** | Bulk data export events |

---

### 6.7 User Management (Cross-Tenant)

**Route:** `/super-admin/users`

#### 6.7.1 Global User Directory

| Feature | Description |
|---------|-------------|
| **All Users** | Paginated list of all users across schools |
| **Filter by School** | View users of specific school |
| **Filter by Role** | View all teachers, all admins, etc. |
| **User Search** | Search by name, email, phone |
| **User Status** | Active, suspended, pending verification |

#### 6.7.2 User Actions

| Action | Description |
|--------|-------------|
| **View Profile** | Full user details (respecting protection) |
| **Impersonate** | Login as user for troubleshooting (logged) |
| **Reset Password** | Force password reset |
| **Suspend** | Block user access |
| **Transfer** | Move user to different school |
| **Delete** | Remove user (with data handling options) |

---

### 6.8 Role & Permission Management

**Route:** `/super-admin/roles`

#### 6.8.1 Global Role Templates

| Feature | Description |
|---------|-------------|
| **Role List** | All defined roles in the system |
| **Role Editor** | Add, modify, delete roles |
| **Permission Assignment** | Attach permissions to roles |
| **Role Hierarchy** | Define role priority levels |

#### 6.8.2 Permission Registry

**Route:** `/super-admin/roles/permissions`

| Feature | Description |
|---------|-------------|
| **Permission List** | All granular permissions in the system |
| **Permission Groups** | Organize permissions by module |
| **Permission Editor** | Add new permissions for new features |
| **Dependency Mapping** | Define permission prerequisites |

---

### 6.9 Backup & Recovery

**Route:** `/super-admin/backups`

#### 6.9.1 Backup Management

| Feature | Description |
|---------|-------------|
| **Backup Schedule** | View/configure automated backup schedule |
| **Manual Backup** | Trigger immediate backup |
| **Backup List** | All available backup points |
| **Backup Details** | Size, duration, status, contents |
| **Download** | Download backup files (encrypted) |
| **Delete Old Backups** | Remove backups beyond retention period |

#### 6.9.2 Recovery Operations

| Feature | Description |
|---------|-------------|
| **Full Restore** | Restore entire system from backup |
| **Selective Restore** | Restore specific school/data |
| **Point-in-Time** | Restore to specific timestamp |
| **Restore Preview** | Preview data before restoration |
| **Restore Log** | History of all restoration operations |

#### 6.9.3 Disaster Recovery

| Feature | Description |
|---------|-------------|
| **DR Status** | Current disaster recovery readiness |
| **Failover Test** | Test DR procedures |
| **Failover Activate** | Switch to DR infrastructure |
| **Recovery Runbook** | Step-by-step recovery procedures |

---

### 6.10 Announcements & Communication

**Route:** `/super-admin/announcements`

#### 6.10.1 Platform Announcements

| Feature | Description |
|---------|-------------|
| **Create Announcement** | System-wide announcement |
| **Target Schools** | Select specific schools or all |
| **Target Roles** | Target specific user roles |
| **Schedule** | Immediate or scheduled publish |
| **Priority Level** | Normal, Important, Critical |
| **Expiration** | Auto-expire announcement |

#### 6.10.2 Direct Messaging

| Feature | Description |
|---------|-------------|
| **Message School Admin** | Direct communication to school admins |
| **Broadcast Email** | Mass email to selected recipients |
| **Notification Center** | Manage in-app notifications |

---

### 6.11 System Health & Monitoring

**Route:** `/super-admin/health`

#### 6.11.1 Real-time Monitoring

| Metric | Description |
|--------|-------------|
| **API Status** | Endpoint health and response times |
| **Database Status** | Connection pool, query performance |
| **Queue Status** | Job queue length, processing rate |
| **Storage Status** | Disk usage, availability |
| **Memory/CPU** | Server resource utilization |

#### 6.11.2 Alerting

| Feature | Description |
|---------|-------------|
| **Alert Rules** | Configure threshold-based alerts |
| **Alert Channels** | Email, SMS, Slack notifications |
| **Alert History** | Past alerts and resolutions |
| **Incident Log** | Track system incidents |

---

### 6.12 Support & Maintenance

**Route:** `/super-admin/support`

#### 6.12.1 Support Tools

| Feature | Description |
|---------|-------------|
| **Impersonation** | Login as any user (fully logged) |
| **Debug Mode** | Enable verbose logging for troubleshooting |
| **Cache Management** | Clear system/school caches |
| **Session Management** | View/terminate active sessions |

#### 6.12.2 Maintenance Mode

| Feature | Description |
|---------|-------------|
| **Enable Maintenance** | Put system in maintenance mode |
| **Maintenance Message** | Custom message for users |
| **Bypass List** | IPs/users allowed during maintenance |
| **Schedule Maintenance** | Plan future maintenance windows |

---

## 7. Technical Implementation

### 7.1 API Routes

```php
// Super Admin API Routes (Laravel)
Route::prefix('super-admin')->middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard/stats', 'SuperAdmin\DashboardController@stats');
    Route::get('/dashboard/activity', 'SuperAdmin\DashboardController@recentActivity');
    
    // Schools
    Route::apiResource('/schools', 'SuperAdmin\SchoolController');
    Route::post('/schools/{school}/suspend', 'SuperAdmin\SchoolController@suspend');
    Route::post('/schools/{school}/activate', 'SuperAdmin\SchoolController@activate');
    Route::get('/schools/{school}/users', 'SuperAdmin\SchoolController@users');
    Route::get('/schools/{school}/logs', 'SuperAdmin\SchoolController@logs');
    
    // Licenses
    Route::apiResource('/licenses/plans', 'SuperAdmin\LicensePlanController');
    Route::apiResource('/licenses/subscriptions', 'SuperAdmin\SubscriptionController');
    Route::post('/licenses/subscriptions/{subscription}/renew', 'SuperAdmin\SubscriptionController@renew');
    
    // Settings
    Route::get('/settings', 'SuperAdmin\SettingsController@index');
    Route::put('/settings', 'SuperAdmin\SettingsController@update');
    Route::get('/settings/features', 'SuperAdmin\FeatureController@index');
    Route::put('/settings/features/{feature}', 'SuperAdmin\FeatureController@update');
    
    // Analytics
    Route::get('/analytics/platform', 'SuperAdmin\AnalyticsController@platform');
    Route::get('/analytics/schools', 'SuperAdmin\AnalyticsController@schools');
    Route::get('/analytics/usage', 'SuperAdmin\AnalyticsController@usage');
    
    // Logs
    Route::get('/logs/activity', 'SuperAdmin\LogController@activity');
    Route::get('/logs/security', 'SuperAdmin\LogController@security');
    Route::get('/logs/system', 'SuperAdmin\LogController@system');
    
    // Users (Cross-Tenant)
    Route::get('/users', 'SuperAdmin\UserController@index');
    Route::get('/users/{user}', 'SuperAdmin\UserController@show');
    Route::post('/users/{user}/impersonate', 'SuperAdmin\UserController@impersonate');
    Route::post('/users/{user}/suspend', 'SuperAdmin\UserController@suspend');
    
    // Roles & Permissions
    Route::apiResource('/roles', 'SuperAdmin\RoleController');
    Route::get('/permissions', 'SuperAdmin\PermissionController@index');
    
    // Backups
    Route::get('/backups', 'SuperAdmin\BackupController@index');
    Route::post('/backups/create', 'SuperAdmin\BackupController@create');
    Route::post('/backups/{backup}/restore', 'SuperAdmin\BackupController@restore');
    Route::delete('/backups/{backup}', 'SuperAdmin\BackupController@destroy');
    
    // Health & Monitoring
    Route::get('/health', 'SuperAdmin\HealthController@index');
    Route::get('/health/metrics', 'SuperAdmin\HealthController@metrics');
    
    // Announcements
    Route::apiResource('/announcements', 'SuperAdmin\AnnouncementController');
    
    // Maintenance
    Route::post('/maintenance/enable', 'SuperAdmin\MaintenanceController@enable');
    Route::post('/maintenance/disable', 'SuperAdmin\MaintenanceController@disable');
});
```

### 7.2 Frontend Routes (Vue.js)

```javascript
// Super Admin Routes
const superAdminRoutes = [
  {
    path: '/super-admin',
    component: SuperAdminLayout,
    meta: { requiresAuth: true, roles: ['super_admin'] },
    children: [
      { path: '', redirect: 'dashboard' },
      { path: 'dashboard', name: 'SuperAdminDashboard', component: () => import('@/views/super-admin/Dashboard.vue') },
      
      // Schools
      { path: 'schools', name: 'SchoolsIndex', component: () => import('@/views/super-admin/schools/Index.vue') },
      { path: 'schools/create', name: 'SchoolsCreate', component: () => import('@/views/super-admin/schools/Create.vue') },
      { path: 'schools/:id', name: 'SchoolsShow', component: () => import('@/views/super-admin/schools/Show.vue') },
      { path: 'schools/:id/edit', name: 'SchoolsEdit', component: () => import('@/views/super-admin/schools/Edit.vue') },
      
      // Licenses
      { path: 'licenses', name: 'LicensesIndex', component: () => import('@/views/super-admin/licenses/Index.vue') },
      { path: 'licenses/plans', name: 'LicensePlans', component: () => import('@/views/super-admin/licenses/Plans.vue') },
      { path: 'licenses/subscriptions', name: 'Subscriptions', component: () => import('@/views/super-admin/licenses/Subscriptions.vue') },
      
      // Settings
      { path: 'settings', name: 'SystemSettings', component: () => import('@/views/super-admin/Settings.vue') },
      { path: 'settings/features', name: 'FeatureManagement', component: () => import('@/views/super-admin/settings/Features.vue') },
      { path: 'settings/integrations', name: 'Integrations', component: () => import('@/views/super-admin/settings/Integrations.vue') },
      
      // Analytics
      { path: 'analytics', name: 'Analytics', component: () => import('@/views/super-admin/analytics/Index.vue') },
      
      // Logs
      { path: 'logs', name: 'ActivityLogs', component: () => import('@/views/super-admin/logs/Activity.vue') },
      { path: 'logs/security', name: 'SecurityLogs', component: () => import('@/views/super-admin/logs/Security.vue') },
      
      // Users
      { path: 'users', name: 'GlobalUsers', component: () => import('@/views/super-admin/users/Index.vue') },
      { path: 'users/:id', name: 'UserDetail', component: () => import('@/views/super-admin/users/Show.vue') },
      
      // Roles
      { path: 'roles', name: 'RoleManagement', component: () => import('@/views/super-admin/roles/Index.vue') },
      { path: 'roles/permissions', name: 'Permissions', component: () => import('@/views/super-admin/roles/Permissions.vue') },
      
      // Backups
      { path: 'backups', name: 'Backups', component: () => import('@/views/super-admin/backups/Index.vue') },
      
      // Health
      { path: 'health', name: 'SystemHealth', component: () => import('@/views/super-admin/health/Index.vue') },
      
      // Announcements
      { path: 'announcements', name: 'SystemAnnouncements', component: () => import('@/views/super-admin/announcements/Index.vue') },
      
      // Support
      { path: 'support', name: 'SupportTools', component: () => import('@/views/super-admin/support/Index.vue') },
      { path: 'maintenance', name: 'Maintenance', component: () => import('@/views/super-admin/support/Maintenance.vue') },
    ]
  }
];
```

### 7.3 Database Tables

```sql
-- Super Admin Specific Tables

-- License Plans
CREATE TABLE license_plans (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    duration_months INT NOT NULL,
    user_limit INT,
    student_limit INT,
    storage_gb DECIMAL(10,2),
    price DECIMAL(10,2),
    features JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- School Subscriptions
CREATE TABLE school_subscriptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT NOT NULL,
    license_plan_id BIGINT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled', 'grace_period') DEFAULT 'active',
    auto_renew BOOLEAN DEFAULT FALSE,
    payment_reference VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (license_plan_id) REFERENCES license_plans(id)
);

-- Platform Settings
CREATE TABLE platform_settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    key_name VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    category VARCHAR(50),
    is_encrypted BOOLEAN DEFAULT FALSE,
    updated_by BIGINT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- School Features (Feature Toggles per School)
CREATE TABLE school_features (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT NOT NULL,
    feature_code VARCHAR(100) NOT NULL,
    is_enabled BOOLEAN DEFAULT TRUE,
    enabled_at TIMESTAMP,
    disabled_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_school_feature (school_id, feature_code),
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
);

-- System Backups
CREATE TABLE system_backups (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    backup_type ENUM('full', 'incremental', 'school_only') DEFAULT 'full',
    school_id BIGINT NULL,
    file_path VARCHAR(500),
    file_size_bytes BIGINT,
    status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_by BIGINT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- System Announcements
CREATE TABLE system_announcements (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('normal', 'important', 'critical') DEFAULT 'normal',
    target_type ENUM('all', 'specific_schools', 'specific_roles') DEFAULT 'all',
    target_ids JSON,
    is_published BOOLEAN DEFAULT FALSE,
    publish_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Impersonation Log (Security Audit)
CREATE TABLE impersonation_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    super_admin_id BIGINT NOT NULL,
    impersonated_user_id BIGINT NOT NULL,
    reason TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    started_at TIMESTAMP NOT NULL,
    ended_at TIMESTAMP NULL,
    FOREIGN KEY (super_admin_id) REFERENCES users(id),
    FOREIGN KEY (impersonated_user_id) REFERENCES users(id)
);
```

### 7.4 Super Admin Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->hasRole('super_admin')) {
            return response()->json([
                'message' => 'Unauthorized. Super Admin access required.',
                'error' => 'insufficient_permissions'
            ], 403);
        }

        // Log all super admin actions
        activity()
            ->causedBy($request->user())
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'route' => $request->path(),
                'method' => $request->method(),
            ])
            ->log('super_admin_access');

        return $next($request);
    }
}
```

---

## 8. Security Framework

### 8.1 Authentication Requirements

| Requirement | Implementation |
|-------------|----------------|
| **Multi-Factor Authentication** | Mandatory for Super Admin accounts |
| **Session Duration** | 8 hours maximum, 30 minutes idle timeout |
| **IP Whitelisting** | Optional, recommended for production |
| **Device Binding** | Track and optionally limit authorized devices |
| **Login Notifications** | Email/SMS alert on each login |

### 8.2 Audit Trail Requirements

All Super Admin actions are logged with:

| Field | Description |
|-------|-------------|
| `action_type` | Type of action performed |
| `target_model` | Model/entity affected |
| `target_id` | ID of affected record |
| `before_data` | State before change (JSON) |
| `after_data` | State after change (JSON) |
| `ip_address` | Source IP address |
| `user_agent` | Browser/client information |
| `timestamp` | Precise action time |
| `session_id` | Current session identifier |

### 8.3 Data Protection

| Measure | Description |
|---------|-------------|
| **Encryption at Rest** | All database backups encrypted |
| **Encryption in Transit** | TLS 1.3 for all connections |
| **PII Masking** | Automatic masking in logs |
| **Access Justification** | Required for sensitive data access |
| **Data Retention Policies** | Configurable per data type |

---

## 9. UI/UX Design Reference

### 9.1 Panel Layout Structure

```
┌─────────────────────────────────────────────────────────────────────────┐
│  [DIGISKUL Logo]    SUPER ADMIN CONSOLE         [Alerts] [Profile] [⚙] │
├──────────────┬──────────────────────────────────────────────────────────┤
│              │                                                          │
│  📊 Dashboard  │                    MAIN CONTENT AREA                    │
│              │                                                          │
│  🏫 Schools   │  ┌─────────────────────────────────────────────────┐    │
│    └ All     │  │                                                   │    │
│    └ Pending │  │   Dynamic content based on selected menu item     │    │
│    └ Trial   │  │                                                   │    │
│              │  │   - Cards, Tables, Forms, Charts                  │    │
│  📜 Licenses  │  │                                                   │    │
│    └ Plans   │  │                                                   │    │
│    └ Subs    │  │                                                   │    │
│              │  └─────────────────────────────────────────────────┘    │
│  ⚙️ Settings  │                                                          │
│              │                                                          │
│  📈 Analytics │                                                          │
│              │                                                          │
│  📋 Logs      │                                                          │
│    └ Activity│                                                          │
│    └ Security│                                                          │
│              │                                                          │
│  👥 Users     │                                                          │
│              │                                                          │
│  🔑 Roles     │                                                          │
│              │                                                          │
│  💾 Backups   │                                                          │
│              │                                                          │
│  ❤️ Health    │                                                          │
│              │                                                          │
│  📢 Announce  │                                                          │
│              │                                                          │
│  🔧 Support   │                                                          │
│              │                                                          │
├──────────────┴──────────────────────────────────────────────────────────┤
│  © 2026 DIGISKUL Platform  │  v1.0.0  │  System Time: 2026-01-07 11:52  │
└─────────────────────────────────────────────────────────────────────────┘
```

### 9.2 Color Scheme

| Element | Color | Hex |
|---------|-------|-----|
| **Primary** | Deep Blue | `#1E3A5F` |
| **Secondary** | Teal | `#0D9488` |
| **Accent** | Gold | `#F59E0B` |
| **Success** | Green | `#10B981` |
| **Warning** | Orange | `#F97316` |
| **Danger** | Red | `#EF4444` |
| **Background** | Dark Gray | `#111827` |
| **Surface** | Gray | `#1F2937` |
| **Text Primary** | White | `#FFFFFF` |
| **Text Secondary** | Light Gray | `#9CA3AF` |

### 9.3 Key UI Components

| Component | Purpose |
|-----------|---------|
| **Stat Cards** | Display key metrics with trend indicators |
| **Data Tables** | Paginated, sortable, filterable lists |
| **Action Modals** | Confirmation dialogs for critical actions |
| **Toast Notifications** | Non-intrusive success/error messages |
| **Status Badges** | Visual indicators for record states |
| **Charts** | Line, bar, pie charts for analytics |
| **Timeline** | Activity feed visualization |
| **Command Palette** | Quick navigation (Ctrl/Cmd + K) |

---

## Appendix A: Permission Codes

```
SUPER_ADMIN_PERMISSIONS = [
    // Schools
    'schools.view',
    'schools.create',
    'schools.update',
    'schools.delete',
    'schools.suspend',
    'schools.activate',
    'schools.impersonate',
    
    // Licenses
    'licenses.plans.view',
    'licenses.plans.manage',
    'licenses.subscriptions.view',
    'licenses.subscriptions.manage',
    
    // Settings
    'settings.view',
    'settings.update',
    'settings.features.manage',
    'settings.integrations.manage',
    
    // Analytics
    'analytics.platform.view',
    'analytics.schools.view',
    'analytics.export',
    
    // Logs
    'logs.activity.view',
    'logs.security.view',
    'logs.system.view',
    'logs.export',
    
    // Users (Cross-Tenant)
    'users.global.view',
    'users.global.manage',
    'users.impersonate',
    
    // Roles
    'roles.view',
    'roles.manage',
    'permissions.view',
    'permissions.manage',
    
    // Backups
    'backups.view',
    'backups.create',
    'backups.restore',
    'backups.delete',
    
    // Health
    'health.view',
    'health.alerts.manage',
    
    // Announcements
    'announcements.system.create',
    'announcements.system.manage',
    
    // Maintenance
    'maintenance.enable',
    'maintenance.disable',
];
```

---

## Appendix B: Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl/Cmd + K` | Open command palette |
| `Ctrl/Cmd + /` | Toggle sidebar |
| `Ctrl/Cmd + B` | Navigate to backups |
| `Ctrl/Cmd + S` | Navigate to schools |
| `Ctrl/Cmd + L` | Navigate to logs |
| `Escape` | Close modal/drawer |
| `G then D` | Go to dashboard |
| `G then S` | Go to settings |

---

**End of Super Admin Panel Specification**

*This document defines the complete scope of the Super Admin (System Owner) role within DIGISKUL. No other user roles, panels, or access levels are defined or implied in this specification.*
