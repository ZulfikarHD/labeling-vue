# Sprint 2: Authentication & User Management

## Overview
Implement login system, user management (admin), and profile settings.

---

## Story 2.1: Login System

**As a** user  
**I want** to login with my NP and password  
**So that** I can access the system

**Acceptance Criteria:**
- [ ] Login page with NP and password fields
- [ ] NP converted to UPPERCASE
- [ ] Validate credentials
- [ ] Check if user is active
- [ ] Redirect to dashboard on success
- [ ] Show error on failure

### Login Form Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| np | text | required, max:5 | Employee number |
| password | password | required | User password |
| remember | checkbox | optional | Remember me |

### Error Messages

| Scenario | Message |
|----------|---------|
| Invalid credentials | "NP atau password salah" |
| Inactive user | "Akun tidak aktif. Hubungi administrator" |
| Empty fields | Field-specific validation errors |

---

## Story 2.2: Logout

**As a** logged-in user  
**I want** to logout  
**So that** I can end my session securely

**Acceptance Criteria:**
- [ ] Logout button in navigation
- [ ] Clear session on logout
- [ ] Redirect to login page

---

## Story 2.3: Auth Middleware

**As a** developer  
**I want** route protection  
**So that** only authorized users can access features

**Acceptance Criteria:**
- [ ] `auth` middleware for all protected routes
- [ ] `admin` middleware for admin-only routes (Role 1)
- [ ] Redirect to login if not authenticated
- [ ] 403 error for unauthorized access

### Role Definitions

| Role | Value | Access |
|------|-------|--------|
| Admin | 1 | Full access |
| Operator | 2 | Limited access |

---

## Story 2.4: User Management - List Users (Admin)

**As an** admin  
**I want** to see all users  
**So that** I can manage user accounts

**Acceptance Criteria:**
- [ ] List all users
- [ ] Show NP, role, team, status
- [ ] Search by NP
- [ ] Filter by role
- [ ] Filter by status

### Page Components

| Component | Description |
|-----------|-------------|
| SearchInput | Search by NP |
| RoleFilter | Filter by role |
| StatusFilter | Filter by active/inactive |
| UserTable | User list |
| ActionButtons | Edit, Delete |

### User Table Columns

| Column | Description |
|--------|-------------|
| NP | Employee number |
| Role | Admin/Operator badge |
| Team | Assigned workstation |
| Status | Active/Inactive badge |
| Actions | Edit, Delete buttons |

---

## Story 2.5: Create User (Admin)

**As an** admin  
**I want** to create new users  
**So that** new employees can access the system

**Acceptance Criteria:**
- [ ] Form to create user
- [ ] Default password option (Peruri + NP)
- [ ] Assign role
- [ ] Assign team
- [ ] Validation

### Create User Form Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| np | text | required, unique, max:5 | Employee number |
| password | password | required, min:6 | User password |
| use_default | checkbox | - | Use default password (Peruri + NP) |
| role | select | required | Admin (1) or Operator (2) |
| team | select | required | Workstation assignment |

---

## Story 2.6: Change Password (Admin)

**As an** admin  
**I want** to change user passwords  
**So that** I can help users who forgot their password

**Acceptance Criteria:**
- [ ] Select user
- [ ] Enter new password
- [ ] Confirm password
- [ ] Option to use default password

### Change Password Form Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| user | select | required | User to change |
| new_password | password | required, min:6 | New password |
| confirm_password | password | required, same | Confirm password |
| use_default | checkbox | - | Reset to default |

---

## Story 2.7: Profile Settings

**As a** user  
**I want** to manage my profile  
**So that** I can update my information

**Acceptance Criteria:**
- [ ] View my profile info
- [ ] Change my password
- [ ] Cannot change NP (readonly)

### Profile Page Sections

| Section | Description |
|---------|-------------|
| Profile Info | View NP, role, team |
| Update Password | Change own password |
| Delete Account | Delete own account (optional) |

### Update Password Form Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| current_password | password | required | Current password |
| new_password | password | required, min:6 | New password |
| confirm_password | password | required, same | Confirm new password |

---

## Story 2.8: Workstation Management (Admin)

**As an** admin  
**I want** to manage workstations/teams  
**So that** I can organize work areas

**Acceptance Criteria:**
- [ ] List all workstations
- [ ] Create new workstation
- [ ] Edit workstation name
- [ ] Activate/deactivate workstation

### Workstation Form Fields

| Field | Type | Validation | Description |
|-------|------|------------|-------------|
| name | text | required, max:50 | Workstation name |
| is_active | checkbox | default:true | Active status |

---

## Routes Summary

```php
// Auth
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

// Profile
Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

// Admin - User Management
Route::middleware('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/change-password', [ChangePasswordController::class, 'index'])->name('password.change');
    Route::post('/change-password', [ChangePasswordController::class, 'store']);
    
    // Workstations
    Route::resource('workstations', WorkstationController::class);
});
```

---

## Database Seeder

```php
// Default admin user
User::create([
    'np' => 'ADMIN',
    'password' => bcrypt('password'),
    'role' => 1, // Admin
    'workstation_id' => 1,
]);

// Default workstations
Workstation::create(['name' => 'Team 1', 'is_active' => true]);
Workstation::create(['name' => 'Team 2', 'is_active' => true]);
Workstation::create(['name' => 'Team 3', 'is_active' => true]);
```

---

## Definition of Done (Sprint 2)

- [ ] Can login with NP + password
- [ ] Can logout
- [ ] Routes protected by auth middleware
- [ ] Admin can manage users
- [ ] Admin can create users
- [ ] Admin can change passwords
- [ ] Users can update own profile
- [ ] Admin can manage workstations
- [ ] Default admin seeded

---

## Sprint 2 Checklist

```
[ ] 2.1 Login System
    [ ] Login page
    [ ] NP uppercase conversion
    [ ] Validation
    [ ] Error messages
    [ ] Redirect on success

[ ] 2.2 Logout
    [ ] Logout route
    [ ] Session clear
    [ ] Redirect to login

[ ] 2.3 Auth Middleware
    [ ] auth middleware
    [ ] admin middleware
    [ ] Route protection

[ ] 2.4 User List
    [ ] Index page
    [ ] Search
    [ ] Filters
    [ ] User table

[ ] 2.5 Create User
    [ ] Create form
    [ ] Default password option
    [ ] Role selection
    [ ] Team assignment

[ ] 2.6 Change Password (Admin)
    [ ] Change password page
    [ ] User selection
    [ ] Default password option

[ ] 2.7 Profile Settings
    [ ] Profile page
    [ ] Update password form
    [ ] Delete account (optional)

[ ] 2.8 Workstation Management
    [ ] Workstation CRUD
    [ ] Active/inactive toggle

[ ] Seeder
    [ ] Default admin
    [ ] Default workstations
```
