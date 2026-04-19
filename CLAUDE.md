# FarmHub — Project Constitution

## What This Project Is

FarmHub is a PHP 8+ MVC web application for agritourism farm booking. Visitors browse farms and book activities, owners manage their farms/activities/schedules, and admins oversee the platform. It runs on XAMPP (Apache + mod_rewrite + MySQL) with no external PHP dependencies.

**Stack:** PHP 8+, MySQL/MariaDB, Bootstrap 5.3, vanilla JS, PDO

---

## Architecture

```
public/index.php      ← entry point + all 39 route definitions
core/
  Router.php          ← dispatches URI → Controller@method
  BaseController.php  ← view(), redirect(), requireRole(), user()
  BaseModel.php       ← abstract base with PDO $db
  Database.php        ← PDO singleton
  Security.php        ← CSRF token generation/validation
  helpers.php         ← base_url(), e(), csrf_token()
config/
  config.php          ← app name, env, debug, base_url
  database.php        ← host, dbname, user, password
app/
  controllers/        ← one controller per feature area
  models/             ← one model per DB table
  views/              ← grouped by role/feature
db/schema.sql         ← canonical schema + seed data
public/assets/
  css/style.css       ← custom styles (Bootstrap overrides + .fh-* classes)
  js/app.js           ← AJAX farm search + booking UI
```

### MVC Rules
- **Models:** all business logic, queries, and data validation live here
- **Controllers:** thin — validate input, call model methods, call `$this->view()` or `$this->redirect()`
- **Views:** display only — no DB calls, no business logic, minimal PHP (`foreach`, `if`, `echo`)

---

## Architecture Rules

- New routes go in `public/index.php` only — never instantiate controllers directly elsewhere
- Always use `BaseController` helpers: `requireRole()`, `view()`, `redirect()`, `user()`, `isLoggedIn()`
- Always use `$this->db` (inherited from `BaseModel`) — never create a new PDO connection
- CSRF protection is mandatory on every POST: use `csrf_token()` in forms + `Security::requireCsrfToken()` at the top of POST handlers
- Always use the `e()` helper to escape any user-supplied value in views

---

## Database Rules

- **Soft deletes only** for user-visible data — set `is_active = 0`, never `DELETE` from `farms`, `activities`, `schedules`, or `bookings`
- Always use PDO prepared statements with bound parameters — never interpolate variables into SQL strings
- Add indexes for new foreign key columns and any column used in WHERE/ORDER BY
- All schema changes (new tables, columns, indexes) go in `db/schema.sql` with a comment

---

## Security Rules

- Never `echo` unescaped user input — always `<?= e($value) ?>`
- Never bypass `requireRole()` or ownership verification queries
- Sessions only (`$_SESSION`) — no extra cookies
- No `eval()`, `exec()`, `shell_exec()`, `system()`, or any shell function
- Admin self-lockout prevention must be preserved — never remove that check in `AdminUserController`

---

## Coding Style

- **PHP:** PSR-12, 4-space indent, `camelCase` methods, `PascalCase` classes, `snake_case` DB columns
- **HTML/Views:** 2-space indent, Bootstrap 5.3 components first
- **CSS:** add to `public/assets/css/style.css` — no inline `style=""` attributes; use existing `.fh-*` utility classes before adding new ones
- **JS:** vanilla only, no libraries — keep additions in `public/assets/js/app.js`

---

## File Organization

- One controller per feature area (`OwnerFarmController`, `AdminReportController`, etc.)
- One model per DB table (`Farm`, `Booking`, `Notification`, etc.)
- Views grouped: `views/owner/`, `views/admin/`, `views/dashboard/visitor/`, `views/dashboard/owner/`, `views/auth/`
- Core framework files in `core/` — do not add application logic here

---

## What NOT To Do

- Do not add Composer packages or external PHP libraries without discussion
- Do not hard-delete `farms`, `activities`, `schedules`, or `bookings` records
- Do not put SQL queries or business logic in view files
- Do not create global state outside of `$_SESSION`
- Do not skip CSRF validation on any POST handler
- Do not bypass ownership checks — always verify the acting user owns the resource before mutating it

---

## Roles & Permissions

| Action | Visitor | Owner | Admin |
|--------|---------|-------|-------|
| Browse farms | Yes | Yes | Yes |
| Book activities | Yes | No | No |
| Cancel own booking | Yes | No | No |
| Approve/complete bookings | No | Own farms only | All |
| Cancel any booking | No | Own farms only | All |
| Manage farms/activities/schedules | No | Own only | No |
| Approve/reject farms | No | No | Yes |
| Manage users | No | No | Yes |
| Generate reports | No | No | Yes |

---

## Manual Testing Checklist

Before marking any feature complete, verify:

1. Register as **visitor**, **owner**, and **admin** (separate accounts)
2. Owner creates a farm → confirm it appears as "pending" in admin panel
3. Admin approves farm → confirm it appears in public farm listing
4. Owner adds activity + schedule to the farm
5. Visitor books a slot → confirm Pending booking appears in owner dashboard
6. Owner approves booking → visitor sees Approved status
7. Test cancellation from both visitor and owner sides
8. Confirm notifications fire at each booking status change
9. Admin generates a report → confirm it saves and displays correctly
10. Test that role restrictions block unauthorized access (e.g., visitor cannot reach `/owner/farms`)
