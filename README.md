# FarmHub

A web application for booking activities at public farms in Saudi Arabia. Farm owners can list their farms and manage schedules, while visitors can browse farms, make reservations, leave reviews, and admins manage users, farms, and system activity.

=====================================

## Tech Stack

- PHP 8+
- MySQL / MariaDB
- HTML, CSS, JavaScript (vanilla, no frameworks)
- PDO for database access
- Session-based authentication

=====================================

## Installation

### 1. Install XAMPP

Install XAMPP with Apache and MySQL

### 2. Download FarmHub

Copy the FarmHub folder to `C:\xampp\htdocs\`

### 3. Create Database

1. Open phpMyAdmin at `http://localhost/phpmyadmin`
2. Create a new database named `farmhub`
3. Import the schema from `db/schema.sql`

### 4. Configure Database Connection

Create `config/database.php`:
```php
<?php
declare(strict_types=1);

return [
    'host' => 'localhost',
    'dbname' => 'farmhub',
    'user' => 'root',
    'password' => 'your_password_here',
];
```

### 5. Start Apache and MySQL

Open XAMPP Control Panel, start Apache and MySQL, then visit `http://localhost/FarmHub/public/`

=====================================

## Testing

Tested using Burp Suite Community Edition:
- CSRF Protection: Verified
- Session ID Validation: Verified
- XSS Prevention: Verified
- SQL Injection Prevention: Verified

=====================================

Built by Reoof Abahussain ★

