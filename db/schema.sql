-- FarmHub database schema (MySQL / MariaDB)
-- Import into an empty database (recommended DB name: farmhub).
SET FOREIGN_KEY_CHECKS = 0 ;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

 CREATE DATABASE IF NOT EXISTS farmhub
 DEFAULT CHARACTER SET utf8mb4
DEFAULT COLLATE utf8mb4_unicode_ci;

USE farmhub;

-- ----------------------------
-- roles
-- ----------------------------
CREATE TABLE IF NOT EXISTS roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name ENUM('visitor','owner','admin') NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO roles (id, name) VALUES
  (1, 'visitor'),
  (2, 'owner'),
  (3, 'admin')
ON DUPLICATE KEY UPDATE
  name = VALUES(name);

-- ----------------------------
-- users
-- ----------------------------
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(191) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role_id INT UNSIGNED NOT NULL DEFAULT 1,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE users
  ADD CONSTRAINT fk_users_role
  FOREIGN KEY (role_id) REFERENCES roles(id)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

-- ----------------------------
-- farms
-- ----------------------------
CREATE TABLE IF NOT EXISTS farms (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  owner_id INT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  location VARCHAR(150) NOT NULL,
  description TEXT NOT NULL,
  main_image VARCHAR(255) NULL,
  -- Coordinates used for "Sort by Distance" on the visitor farm listing.
  latitude DECIMAL(10,8) NULL,
  longitude DECIMAL(11,8) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  CONSTRAINT fk_farms_owner
    FOREIGN KEY (owner_id) REFERENCES users(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- For existing installs, add coordinate columns if they don't already exist.
ALTER TABLE farms
  ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,8) NULL AFTER main_image,
  ADD COLUMN IF NOT EXISTS longitude DECIMAL(11,8) NULL AFTER latitude;

-- Optional indexes
CREATE INDEX IF NOT EXISTS idx_farms_owner_id ON farms(owner_id);
CREATE INDEX IF NOT EXISTS idx_farms_is_active ON farms(is_active);
CREATE INDEX IF NOT EXISTS idx_farms_approval_status ON farms(approval_status);

-- ----------------------------
-- activities
-- ----------------------------
CREATE TABLE IF NOT EXISTS activities (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  farm_id INT UNSIGNED NOT NULL,
  name VARCHAR(150) NOT NULL,
  activity_type VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_activities_farm_id (farm_id),
  CONSTRAINT fk_activities_farm
    FOREIGN KEY (farm_id) REFERENCES farms(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- schedules
-- ----------------------------
CREATE TABLE IF NOT EXISTS schedules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  activity_id INT UNSIGNED NOT NULL,
  schedule_date DATE NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  capacity INT UNSIGNED NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  UNIQUE KEY uq_schedule (activity_id, schedule_date, start_time),
  INDEX idx_schedules_activity_date (activity_id, schedule_date),
  CONSTRAINT fk_schedules_activity
    FOREIGN KEY (activity_id) REFERENCES activities(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- booking_status
-- ----------------------------
CREATE TABLE IF NOT EXISTS booking_status (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO booking_status (id, name) VALUES
  (1, 'Pending'),
  (2, 'Approved'),
  (3, 'Cancelled'),
  (4, 'Completed')
ON DUPLICATE KEY UPDATE
  name = VALUES(name);

-- ----------------------------
-- bookings
-- ----------------------------
CREATE TABLE IF NOT EXISTS bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  visitor_id INT UNSIGNED NOT NULL,
  schedule_id INT UNSIGNED NOT NULL,
  party_size INT UNSIGNED NOT NULL DEFAULT 1,
  status_id INT UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL,
  cancelled_at TIMESTAMP NULL DEFAULT NULL,
  notes TEXT NULL,
  INDEX idx_bookings_visitor (visitor_id),
  INDEX idx_bookings_schedule (schedule_id),
  INDEX idx_bookings_status (status_id),
  CONSTRAINT fk_bookings_visitor
    FOREIGN KEY (visitor_id) REFERENCES users(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_bookings_schedule
    FOREIGN KEY (schedule_id) REFERENCES schedules(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_bookings_status
    FOREIGN KEY (status_id) REFERENCES booking_status(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Helpful indexes for owner/admin booking views
CREATE INDEX IF NOT EXISTS idx_bookings_created_at ON bookings(created_at);

-- ----------------------------
-- Seed data (sample users, farm, activity, schedule)
-- NOTE: All users below use password 'password' (bcrypt hash).
--       For production, change passwords immediately.

INSERT INTO users (id, name, email, password, role_id, is_active, created_at) VALUES
  (1, 'Admin User', 'admin@farmhub.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 3, 1, NOW()),
  (2, 'Owner User', 'owner@farmhub.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 2, 1, NOW()),
  (3, 'Visitor User', 'visitor@farmhub.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1, NOW())
ON DUPLICATE KEY UPDATE
  email = VALUES(email);

INSERT INTO farms (id, owner_id, name, location, description, main_image, is_active, approval_status, created_at) VALUES
  (1, 2, 'Green Valley Farm', 'Countryside', 'Sample educational farm with tours and workshops.', NULL, 1, 'approved', NOW())
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  location = VALUES(location),
  description = VALUES(description),
  approval_status = VALUES(approval_status);

INSERT INTO activities (id, farm_id, name, activity_type, description, is_active, created_at) VALUES
  (1, 1, 'Guided Farm Tour', 'Tour', 'Walkthrough of fields, barns, and equipment.', 1, NOW())
ON DUPLICATE KEY UPDATE
  name = VALUES(name),
  activity_type = VALUES(activity_type),
  description = VALUES(description),
  is_active = VALUES(is_active);

INSERT INTO schedules (id, activity_id, schedule_date, start_time, end_time, capacity, price, is_active, created_at) VALUES
  (1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', '11:00:00', 20, 25.00, 1, NOW())
ON DUPLICATE KEY UPDATE
  schedule_date = VALUES(schedule_date),
  start_time = VALUES(start_time),
  end_time = VALUES(end_time),
  capacity = VALUES(capacity),
  price = VALUES(price),
  is_active = VALUES(is_active);



-- ----------------------------
-- notifications
-- ----------------------------
CREATE TABLE IF NOT EXISTS notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('booking','reminder','owner_alert','system') NOT NULL DEFAULT 'booking',
  message TEXT NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  read_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_notifications_user (user_id),
  CONSTRAINT fk_notifications_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO notifications (user_id, type, message, is_read, created_at) VALUES
  (1, 'system', 'New farm submissions may require approval review.', 0, NOW()),
  (1, 'system', 'Daily system summary is available in Reports.', 0, NOW());

-- ----------------------------
-- reports (minimal placeholder for admin analytics)
-- ----------------------------
CREATE TABLE IF NOT EXISTS reports (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id INT UNSIGNED NOT NULL,
  report_type VARCHAR(100) NOT NULL,
  payload JSON NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_reports_admin (admin_id),
  CONSTRAINT fk_reports_admin
    FOREIGN KEY (admin_id) REFERENCES users(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

