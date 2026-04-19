# FarmHub (PHP + MVC + MySQL)

This is a starter implementation of **FarmHub** with:
- MVC structure (controllers/models/views)
- Auth (register/login/logout) with password hashing + sessions
- Farm browsing + pagination
- Owner dashboard + basic farm CRUD pages (create/edit/delete)

## Requirements
- PHP 8+
- MySQL / MariaDB
- Apache with `mod_rewrite` enabled (XAMPP recommended)

## Install
1. Copy the project into your web root (already in `c:\\xampp2\\htdocs\\FarmHub`).
2. Create the database:
   - Open phpMyAdmin (or MySQL CLI)
   - Run: `db/schema.sql` (recommended: drop/recreate the database if you already imported an older version)
3. Configure DB credentials:
   - Edit `config/database.php` if you use a DB user/password other than the defaults.
4. Ensure Apache rewrite is enabled:
   - The project includes `public/.htaccess` so routes like `/login` work.
5. Open the app in your browser:
   - `http://localhost/FarmHub/public/`

## Test quickly
1. Register a user as role **Farm Owner**.
2. Go to `Dashboard -> Manage Farms`.
3. Create a farm.
4. Log out and browse the farms from the main page.

