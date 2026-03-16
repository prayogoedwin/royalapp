# UI Screenshots Guide

## Navigation

### Sidebar Menu
The sidebar now includes a "User Management" section with three submenu items:
- **Users** (icon: user) - Manage system users
- **Roles** (icon: shield) - Manage roles
- **Permissions** (icon: key) - Manage permissions

The menu item expands/collapses and highlights the active page.

---

## Roles Management

### `/roles` - Roles List
**Page Elements:**
- Breadcrumb: Dashboard > Roles
- Page Title: "Roles" with subtitle "Manage roles and their permissions"
- "Download Excel" button (secondary/gray) - exports to Excel/CSV
- "Create Role" button (top right, blue/primary)
- **DataTables server-side table** with features:
  - Search box (real-time search)
  - Show entries dropdown (10, 25, 50, 100)
  - Columns:
    - Name (role name)
    - Users (count of users with this role)
    - Permissions (count of permissions assigned)
    - Created (formatted date)
    - Actions (View, Edit, Delete)
  - Server-side pagination
  - Sorting on columns
  - "Processing..." indicator during AJAX load
- Empty state message if no roles exist

### `/roles/create` - Create Role
**Page Elements:**
- Breadcrumb: Dashboard > Roles > Create
- Page Title: "Create Role"
- Form with:
  - Name input field (required)
  - Permissions section with grid of checkboxes (2 columns on desktop)
    - Each permission as a checkbox with label
  - Create button (primary/blue)
  - Cancel button (secondary/gray) - links back to roles list
- Validation errors display below each field
- All in a white card with border and shadow

### `/roles/{id}/edit` - Edit Role
Same as Create, but:
- Breadcrumb ends with "Edit"
- Page title: "Edit Role"
- Form pre-filled with role data
- Permissions pre-checked based on role's current permissions
- Button says "Update" instead of "Create"

### `/roles/{id}` - View Role (Show)
**Page Elements:**
- Breadcrumb: Dashboard > Roles > View
- Page Title: "Role Details"
- Read-only display card showing:
  - Role name
  - Created date
  - Last updated date
- Permissions section showing all assigned permissions as badges
- Users section showing all users with this role
- "Edit" and "Back to List" buttons

---

## Permissions Management

### `/permissions` - Permissions List
**Page Elements:**
- Breadcrumb: Dashboard > Permissions
- Page Title: "Permissions" with subtitle "Manage system permissions"
- "Download Excel" button (secondary/gray) - exports to Excel/CSV
- "Create Permission" button (top right, blue/primary)
- **DataTables server-side table** with features:
  - Search box (real-time search)
  - Show entries dropdown (10, 25, 50, 100)
  - Columns:
    - Name (permission name)
    - Roles (count of roles that have this permission)
    - Created (formatted date)
    - Actions (View, Edit, Delete)
  - Server-side pagination
  - Sorting on columns
  - "Processing..." indicator during AJAX load
- Empty state message if no permissions exist

### `/permissions/create` - Create Permission
**Page Elements:**
- Breadcrumb: Dashboard > Permissions > Create
- Page Title: "Create Permission"
- Form with:
  - Name input field (required)
  - Helper text: "e.g., create-posts, edit-users, delete-comments"
  - Create button (primary/blue)
  - Cancel button (secondary/gray)
- Validation errors display below field
- Compact, simple form (only one field)

### `/permissions/{id}/edit` - Edit Permission
Same as Create, but:
- Breadcrumb ends with "Edit"
- Page title: "Edit Permission"
- Form pre-filled with permission name
- Button says "Update" instead of "Create"

### `/permissions/{id}` - View Permission (Show)
**Page Elements:**
- Breadcrumb: Dashboard > Permissions > View
- Page Title: "Permission Details"
- Read-only display card showing:
  - Permission name
  - Created date
  - Last updated date
- Roles section showing all roles that have this permission as badges
- "Edit" and "Back to List" buttons

---

## Users Management

### `/users` - Users List
**Page Elements:**
- Breadcrumb: Dashboard > Users
- Page Title: "Users" with subtitle "Manage users and their roles"
- "Download Excel" button (secondary/gray) - exports to Excel/CSV
- "Create User" button (top right, blue/primary)
- **DataTables server-side table** with features:
  - Search box (real-time search)
  - Show entries dropdown (10, 25, 50, 100)
  - Columns:
    - Name (user's full name)
    - Email (user's email address)
    - Roles (badges showing role names in blue)
      - Multiple role badges if user has multiple roles
      - "No roles" text if user has no roles
    - Created (formatted date)
    - Actions (View, Edit, Delete)
  - Server-side pagination
  - Sorting on columns
  - "Processing..." indicator during AJAX load
- Empty state message if no users exist

**Role Badges:** Small blue rounded badges with role names

### `/users/create` - Create User
**Page Elements:**
- Breadcrumb: Dashboard > Users > Create
- Page Title: "Create User" with subtitle "Create a new user and assign roles"
- Form with:
  - Name input field (required)
  - Email input field (required, email validation)
  - Password input field (required, password strength rules)
  - Confirm Password input field (required, must match)
  - Roles section with grid of checkboxes (2 columns on desktop)
    - Each role as a checkbox with label
    - Empty state if no roles available
  - Create button (primary/blue)
  - Cancel button (secondary/gray)
- Validation errors display below each field
- All in a white card with border and shadow

### `/users/{id}/edit` - Edit User
Same as Create, but:
- Breadcrumb ends with "Edit"
- Page title: "Edit User" with subtitle "Update user details and roles"
- Form pre-filled with user data
- Password fields:
  - Not required
  - Helper text: "Leave blank to keep current password"
- Roles pre-checked based on user's current roles
- Button says "Update" instead of "Create"

### `/users/{id}` - View User (Show)
**Page Elements:**
- Breadcrumb: Dashboard > Users > View
- Page Title: "User Details"
- Read-only display card showing:
  - User name
  - Email address
  - Created date
  - Last updated date
- Roles section showing all assigned roles as badges
- Permissions section showing all effective permissions from roles
- "Edit" and "Back to List" buttons

---

## Design Features (All Pages)

### Color Scheme
- **Light mode:** White backgrounds, gray borders, dark text
- **Dark mode:** Dark gray backgrounds, lighter borders, white text
- **Primary action:** Blue buttons (#3B82F6)
- **Danger action:** Red text/buttons (#DC2626)
- **Success message:** Green background (#10B981)

### Typography
- Page titles: Large, bold (2xl)
- Subtitles: Medium, gray
- Table headers: Small, uppercase, gray
- Body text: Regular size, good contrast

### Interactive Elements
- **Buttons:** Rounded, with hover effects
- **Links:** Blue color, underline on hover
- **Table rows:** Hover effect (background changes)
- **Forms:** Clear labels, validation styling
- **Checkboxes:** Custom styled, aligned with labels

### Responsive Design
- **Desktop:** Full layout with sidebar
- **Tablet:** Collapsible sidebar
- **Mobile:** Stacked table cells, full-width forms

### Success Messages
- Green background banner at top of content
- Shows after successful create/update/delete
- Includes close button (X)
- Auto-fades with animation

### Delete Confirmations
- JavaScript confirm dialog
- Message: "Are you sure you want to delete this [role/permission/user]?"
- Prevents accidental deletions

### Empty States
- Centered message in table
- Gray text
- Friendly message like "No roles found."

### Pagination
- **DataTables server-side pagination** for all listing tables (Users, Roles, Permissions)
- Real-time search with debounce
- Page length selector (10, 25, 50, 100 entries)
- Shows page numbers and previous/next buttons
- Displays entry information (e.g., "Showing 1 to 10 of 25 roles")
- Optimized for large datasets with AJAX loading
- "Processing..." indicator during data fetch

---

## Consistent Patterns

All pages follow these LaravelDaily patterns:
1. Breadcrumb navigation at top
2. Page header with title and optional action button
3. White card container with subtle shadow
4. Clean tables with alternating hover states
5. Form fields with consistent spacing
6. Action buttons grouped together
7. Success messages shown after actions
8. Responsive design for all screen sizes
9. Dark mode support throughout
10. FontAwesome icons in sidebar and actions
11. **DataTables integration** for all listing tables with server-side processing
12. Export functionality for downloading data as Excel/CSV
13. "Show" pages for read-only detail views
