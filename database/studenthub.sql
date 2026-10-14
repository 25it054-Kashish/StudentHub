-- =============================================================================
-- StudentHub Portal - Complete Normalized Database Schema & Seed Data
-- Subject: Web Development Frameworks (ITUE203) - Semester 3
-- Charotar University of Science and Technology (CHARUSAT)
-- Faculty of Technology and Engineering (FTE)
-- Practicals Covered: Practical 8, 9, 10, 11, 12, 13, 14
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `studenthub_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `studenthub_db`;

-- -----------------------------------------------------------------------------
-- 1. Users Table (Authentication, RBAC, Password Hashing, Remember-Me, Timeout)
-- Practicals: 8, 9, 10, 14
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `registrations`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `students`;
DROP TABLE IF EXISTS `remember_tokens`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_email` (`email`),
  INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. Remember-Me Tokens Table (Practical 10 Advanced)
-- -----------------------------------------------------------------------------
CREATE TABLE `remember_tokens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `token_hash` VARCHAR(255) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. Students Table (Student Management CRUD, Prepared Statements)
-- Practicals: 8, 9, 11
-- -----------------------------------------------------------------------------
CREATE TABLE `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `enrollment` VARCHAR(20) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `mobile` VARCHAR(15) NOT NULL,
  `course` VARCHAR(80) NOT NULL,
  `department` VARCHAR(50) NOT NULL,
  `year` VARCHAR(20) NOT NULL,
  `semester` INT NOT NULL DEFAULT 3,
  `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
  `gpa` DECIMAL(3,2) NOT NULL DEFAULT 8.00,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX `idx_students_enrollment` (`enrollment`),
  INDEX `idx_students_dept` (`department`),
  INDEX `idx_students_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. Events Table (Event Management CRUD, Poster Upload, Categories)
-- Practicals: 8, 12, 13
-- -----------------------------------------------------------------------------
CREATE TABLE `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `event_date` DATE NOT NULL,
  `event_time` VARCHAR(50) NOT NULL,
  `venue` VARCHAR(100) NOT NULL,
  `speaker` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `seats` INT NOT NULL DEFAULT 60,
  `available_seats` INT NOT NULL DEFAULT 60,
  `fee` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `poster_path` VARCHAR(255) DEFAULT 'images/event-web.svg',
  `status` ENUM('Open', 'Closed', 'Upcoming') NOT NULL DEFAULT 'Open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_events_date` (`event_date`),
  INDEX `idx_events_category` (`category`),
  INDEX `idx_events_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. Registrations Table (Event Passes & Many-to-Many Bridge)
-- Practicals: 8, 11, 14
-- -----------------------------------------------------------------------------
CREATE TABLE `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `event_id` INT NOT NULL,
  `pass_code` VARCHAR(50) NOT NULL UNIQUE,
  `remarks` VARCHAR(255) NULL,
  `attendance_status` ENUM('Registered', 'Attended', 'Absent') DEFAULT 'Registered',
  `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_student_event` (`student_id`, `event_id`),
  INDEX `idx_reg_student` (`student_id`),
  INDEX `idx_reg_event` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. Audit Logs Table (Admin Activity Logging & System Auditability)
-- Practicals: 8, 9, 10, 14
-- -----------------------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NOT NULL DEFAULT '127.0.0.1',
  `user_agent` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_audit_user` (`user_id`),
  INDEX `idx_audit_action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SEED DATA INSERTION
-- =============================================================================

-- Seed Users (Bcrypt hashed passwords verified in PHP 8)
-- Admin: admin@charusat.ac.in -> Admin@123
-- Student: student@charusat.ac.in -> Student@123
INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'admin', 'admin@charusat.ac.in', '$2y$10$C0o.BH8iqvN95OAqwg14gODVFO.dSqphJJQIj94MqCikBvFeor43u', 'admin', 'active'),
(2, '23ce001', 'student@charusat.ac.in', '$2y$10$NvRQ4bm52i4TxzbWLmBrfuiUEoQS7pz8BOPcVNDyhM0U0PUx5AOC6', 'student', 'active'),
(3, '23ce014', '23ce014@charusat.edu.in', '$2y$10$NvRQ4bm52i4TxzbWLmBrfuiUEoQS7pz8BOPcVNDyhM0U0PUx5AOC6', 'student', 'active');

-- Seed Students (20 seeded records as required by Practical 11)
INSERT INTO `students` (`id`, `user_id`, `enrollment`, `full_name`, `email`, `mobile`, `course`, `department`, `year`, `semester`, `gender`, `gpa`, `status`) VALUES
(1, 2, '23CE001', 'Kashish Patel', '23ce001@charusat.edu.in', '9876543210', 'B.Tech Computer Engineering', 'CSPIT-CE', '2nd Year', 3, 'Female', 9.42, 'Active'),
(2, 3, '23CE014', 'Aarav Sharma', '23ce014@charusat.edu.in', '9898123456', 'B.Tech Computer Engineering', 'CSPIT-CE', '2nd Year', 3, 'Male', 8.85, 'Active'),
(3, NULL, '23IT023', 'Diya Joshi', '23it023@charusat.edu.in', '9723456789', 'B.Tech Information Technology', 'CSPIT-IT', '2nd Year', 3, 'Female', 9.10, 'Active'),
(4, NULL, '23AIML008', 'Rohan Mehta', '23aiml008@charusat.edu.in', '9825123987', 'B.Tech Artificial Intelligence & ML', 'CSPIT-AIML', '2nd Year', 3, 'Male', 9.30, 'Active'),
(5, NULL, '23CSE045', 'Ananya Desai', '23cse045@charusat.edu.in', '9712398456', 'B.Tech Computer Science & Engg', 'DEPSTAR-CSE', '2nd Year', 3, 'Female', 8.95, 'Active'),
(6, NULL, '22CE055', 'Devansh Shah', '22ce055@charusat.edu.in', '9426578912', 'B.Tech Computer Engineering', 'CSPIT-CE', '3rd Year', 5, 'Male', 8.70, 'Active'),
(7, NULL, '22IT031', 'Pooja Trivedi', '22it031@charusat.edu.in', '9909988776', 'B.Tech Information Technology', 'CSPIT-IT', '3rd Year', 5, 'Female', 9.05, 'Active'),
(8, NULL, '22AIML019', 'Siddharth Verma', '22aiml019@charusat.edu.in', '9879012345', 'B.Tech Artificial Intelligence & ML', 'CSPIT-AIML', '3rd Year', 5, 'Male', 8.65, 'Active'),
(9, NULL, '21CE089', 'Krisha Dave', '21ce089@charusat.edu.in', '9408123456', 'B.Tech Computer Engineering', 'CSPIT-CE', '4th Year', 7, 'Female', 9.60, 'Active'),
(10, NULL, '21IT072', 'Harshil Vora', '21it072@charusat.edu.in', '9898765432', 'B.Tech Information Technology', 'CSPIT-IT', '4th Year', 7, 'Male', 8.40, 'Active'),
(11, NULL, '23EC012', 'Riya Panchal', '23ec012@charusat.edu.in', '9727123456', 'B.Tech Electronics & Comm', 'CSPIT-EC', '2nd Year', 3, 'Female', 8.50, 'Active'),
(12, NULL, '23ME005', 'Jayraj Solanki', '23me005@charusat.edu.in', '9428987654', 'B.Tech Mechanical Engineering', 'CSPIT-ME', '2nd Year', 3, 'Male', 7.90, 'Active'),
(13, NULL, '24CE022', 'Tanvi Soni', '24ce022@charusat.edu.in', '9824054321', 'B.Tech Computer Engineering', 'CSPIT-CE', '1st Year', 1, 'Female', 9.20, 'Active'),
(14, NULL, '24AIML015', 'Neel Prajapati', '24aiml015@charusat.edu.in', '9979112233', 'B.Tech Artificial Intelligence & ML', 'CSPIT-AIML', '1st Year', 1, 'Male', 8.75, 'Active'),
(15, NULL, '23DEP034', 'Isha Choksi', '23dep034@charusat.edu.in', '9714556677', 'B.Tech Information Technology', 'DEPSTAR-IT', '2nd Year', 3, 'Female', 9.15, 'Active'),
(16, NULL, '22CSE067', 'Manan Bhavsar', '22cse067@charusat.edu.in', '9898001122', 'B.Tech Computer Science & Engg', 'DEPSTAR-CSE', '3rd Year', 5, 'Male', 8.35, 'Active'),
(17, NULL, '21AIML040', 'Vidhi Raval', '21aiml040@charusat.edu.in', '9409887766', 'B.Tech Artificial Intelligence & ML', 'CSPIT-AIML', '4th Year', 7, 'Female', 9.55, 'Active'),
(18, NULL, '23CL018', 'Karan Thakkar', '23cl018@charusat.edu.in', '9876009988', 'B.Tech Civil Engineering', 'CSPIT-CL', '2nd Year', 3, 'Male', 7.80, 'Active'),
(19, NULL, '22CE102', 'Jhanvi Parekh', '22ce102@charusat.edu.in', '9725443322', 'B.Tech Computer Engineering', 'CSPIT-CE', '3rd Year', 5, 'Female', 9.00, 'Active'),
(20, NULL, '23IT088', 'Aditya Chauhan', '23it088@charusat.edu.in', '9825332211', 'B.Tech Information Technology', 'CSPIT-IT', '2nd Year', 3, 'Male', 8.60, 'Active');

-- Seed Events (16 campus events for Practical 12)
INSERT INTO `events` (`id`, `title`, `category`, `event_date`, `event_time`, `venue`, `speaker`, `description`, `seats`, `available_seats`, `fee`, `poster_path`, `status`) VALUES
(1, 'Full-Stack Web Development Bootcamp', 'Workshop', '2026-10-25', '10:00 AM - 01:00 PM', 'CSPIT Seminar Hall 1', 'Prof. Martin Parmar (CSPIT-CE)', 'Comprehensive training on HTML5, CSS Grid, Modern JS, PHP 8 and REST APIs with live deployment.', 120, 24, 0.00, 'images/event-web.svg', 'Open'),
(2, 'AI & Generative Deep Learning Summit', 'Conference', '2026-11-12', '09:30 AM - 04:30 PM', 'FTE Central Auditorium', 'Dr. Jalpesh Vasa (CSPIT-AIML)', 'Deep dive into Large Language Models, prompt engineering, and diffusion models.', 250, 55, 150.00, 'images/event-ai.svg', 'Open'),
(3, 'Charusat Innovate 36-Hr Hackathon', 'Hackathon', '2026-11-28', '08:00 AM Onwards', 'Central Innovation Complex', 'Dr. Mrugendra Rahevar (CSPIT-CE)', '36-hour codefest solving problem statements in Agritech, Edtech, and Healthcare. Prizes ₹1,00,000+.', 80, 12, 0.00, 'images/event-hackathon.svg', 'Open'),
(4, 'Capture The Flag (CTF) Cybersecurity Quest', 'Competition', '2026-12-05', '11:00 AM - 05:00 PM', 'Cyber Defense Lab 404', 'Mr. Dhaval Patel (CSPIT-IT)', 'Ethical hacking, binary exploitation, and cryptography challenge tournament.', 60, 8, 0.00, 'images/event-cyber.svg', 'Open'),
(5, 'Autonomous RoboRace & Drone Rodeo', 'Robotics', '2026-12-18', '10:00 AM - 04:00 PM', 'Mechanical Arena Grounds', 'Mr. Vaibhav Matalia (DEPSTAR-CSE)', 'Design and race obstacle-avoiding rovers and FPV acrobatic drones.', 40, 15, 200.00, 'images/event-robotics.svg', 'Open'),
(6, 'Cloud Native Architecture & Kubernetes', 'Workshop', '2027-01-08', '02:00 PM - 05:30 PM', 'Lab 202', 'Ms. Srushti Gajjar (CSPIT-CSE)', 'Containerizing microservices using Docker and orchestrating with Kubernetes.', 100, 32, 0.00, 'images/event-web.svg', 'Open');

-- Seed Registrations (Event Passes)
INSERT INTO `registrations` (`id`, `student_id`, `event_id`, `pass_code`, `remarks`, `attendance_status`) VALUES
(1, 1, 1, 'PASS-WEB-23CE001-984', 'Bringing personal laptop, Linux OS', 'Registered'),
(2, 1, 3, 'PASS-HACK-23CE001-441', 'Team CharusatDevs Lead', 'Registered'),
(3, 2, 1, 'PASS-WEB-23CE014-112', 'Interested in PHP 8 APIs', 'Registered');

-- Seed Audit Logs (Practical 14)
INSERT INTO `audit_logs` (`user_id`, `action`, `details`, `ip_address`, `user_agent`) VALUES
(1, 'SYSTEM_INIT', 'StudentHub database schema deployed successfully', '127.0.0.1', 'CLI Seed Script'),
(2, 'USER_LOGIN', 'Student authentication successful for 23CE001', '127.0.0.1', 'Mozilla/5.0'),
(2, 'EVENT_REGISTER', 'Registered for Full-Stack Web Development Bootcamp', '127.0.0.1', 'Mozilla/5.0');
