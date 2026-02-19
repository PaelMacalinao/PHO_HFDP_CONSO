# Role-Based Access Control (RBAC) Implementation Summary

## Overview
A comprehensive Role-Based Access Control system has been implemented to restrict data access based on user roles (Admin vs Staff) and facility assignments.

## Database Changes

### Users Table Schema Update
```sql
ALTER TABLE users ADD COLUMN role ENUM('admin', 'staff') DEFAULT 'staff';
ALTER TABLE users ADD COLUMN assigned_facility VARCHAR(255);
ALTER TABLE users ADD INDEX idx_role (role);
```

**Changes:**
- Added `role` field: 'admin' (full access) or 'staff' (facility-restricted)
- Added `assigned_facility` field: Stores the facility name for staff users
- Added index on `role` for query optimization

## Backend Changes

### 1. Authentication (`includes/auth.php`)
**New Functions:**
- `isAdmin()` - Check if current user is an admin
- `isStaff()` - Check if current user is a staff member  
- `getAssignedFacility()` - Retrieve the assigned facility for current staff user

### 2. Login (`login.php`)
**Changes:**
- Updated table creation to include new `role` and `assigned_facility` columns
- Modified login query to fetch `role` and `assigned_facility`
- Updated default admin user creation to set role='admin'
- Store role and assigned_facility in session upon successful login

### 3. User Management (`user_management.php`)
**New Features:**
- Added Role dropdown (Admin or Staff)
- Added Assigned Facility dropdown (appears only when Staff is selected)
- Facility selection includes all major health facilities:
  - PHO PALAWAN
  - IWAHIG MEDICARE HOSPITAL
  - PALAWAN STATE UNIVERSITY MEDICAL CENTER
  - QUEZON MEDICARE HOSPITAL
  - ROXAS MEDICARE HOSPITAL  
  - RIZAL DISTRICT HOSPITAL
  - SOFRONIO ESPAÑOLA DISTRICT HOSPITAL
  - SAN VICENTE DISTRICT HOSPITAL
  - SOUTHERN PALAWAN PROVINCIAL HOSPITAL

**New Form Logic:**
- Dynamic facility field visibility: Hidden for Admin, visible/required for Staff
- JavaScript function `toggleFacilityField()` controls field visibility
- Updated user display table to show role and assigned facility

### 4. API Records Endpoint (`api/get_records.php`)
**Changes:**
- Added facility filtering for staff users
- When staff user requests records, automatic WHERE clause added: `concerned_office_facility = ?`
- Staff users cannot see records from other facilities regardless of filter selections

## Frontend Changes

### 1. Dashboard HTML (`index.php`)
**Changes:**
- Added `data-user-role` attribute to `<html>` element
- Included new `rbac.js` script for RBAC UI controls

### 2. New RBAC Control Script (`assets/js/rbac.js`)
**Features:**
- Detects user role from page data attribute
- For Staff users:
  - Shows informational notice about facility filtering
  - Disables facility selection filters if present
  - Displays message: "You are viewing records from your assigned facility only"

## Security Benefits

1. **Facility Isolation**: Staff users only see data from their assigned facility
2. **API-Level Enforcement**: Filtering happens at database query level
3. **UI Consistency**: Filters are disabled at UI level to prevent confusion
4. **Audit Trail**: Role and facility assignments are stored in database

## User Experience

### Admin Users:
- See all records from all facilities
- Can view and edit all data
- Access user management to create/configure staff accounts
- No facility restrictions

### Staff Users:
- See only records from their assigned facility
- Cannot change facility filter (disabled)
- See notification about facility restrictions
- Can still filter by Year, Cluster, Category, etc. within their facility

## How to Use

### Creating an Admin User:
1. Go to User Management
2. Select Role: "Admin (Full Access)"
3. Facility field will be hidden/optional
4. Create account

### Creating a Staff User:
1. Go to User Management
2. Select Role: "Staff (Facility-Restricted)"
3. Facility field appears and becomes required
4. Select the staff member's assigned facility
5. Create account

## Database Migration
Administrators should run the schema update to add new columns:
```bash
# Run this in phpMyAdmin or MySQL client:
ALTER TABLE users ADD COLUMN role ENUM('admin', 'staff') DEFAULT 'staff' COMMENT 'admin=Full access, staff=Facility-restricted access';
ALTER TABLE users ADD COLUMN assigned_facility VARCHAR(255) COMMENT 'Facility assignment for staff users';
ALTER TABLE users ADD INDEX idx_role (role);

# Update existing admin user to have admin role:
UPDATE users SET role = 'admin' WHERE username = 'phoadmin';
```

## Testing Checklist

- [ ] Admin user can see all records from all facilities
- [ ] Admin can create new admin and staff users
- [ ] Staff user sees only their assigned facility's records
- [ ] Staff user facility filter is disabled/grayed out
- [ ] Staff notification appears on dashboard
- [ ] Creating staff user without facility assignment shows error
- [ ] Facility dropdown only appears when Staff role is selected
- [ ] Logout and login with different roles works correctly
- [ ] API records endpoint respects facility filtering
