-- =====================================================================
-- Forces Academy LMS — FULL DATABASE EXPORT (Week 5 Deployment)
-- =====================================================================
-- This single file combines, in the correct order:
--   1. database.sql                    (core tables: students, admins, courses, notices)
--   2. sample_data.sql                 (sample courses + notices)
--   3. week3_assignments_results.sql   (assignments, submissions, results tables + sample data)
--   4. week4_admin_seed.sql            (default admin login account)
--
-- HOW TO USE ON INFINITYFREE:
--   1. Log in to your InfinityFree control panel
--   2. Go to MySQL Databases > create a new database (note its full name,
--      InfinityFree usually prefixes it, e.g. epiz_12345678_forces_academy_lms)
--   3. Open phpMyAdmin from the control panel, select that database
--   4. Go to Import > Choose this file (database_full_export.sql) > Go
--   5. Update config/db.php with the new host, database name, username,
--      and password shown in your InfinityFree MySQL Databases page
-- =====================================================================

CREATE DATABASE IF NOT EXISTS forces_academy_lms
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE forces_academy_lms;

-- ---------------------------------------------------------------------
-- Core tables (from database.sql)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
  id            INT(11) NOT NULL AUTO_INCREMENT,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(100) NOT NULL,
  password      VARCHAR(255) NOT NULL,
  roll_number   VARCHAR(20) NOT NULL,
  class         VARCHAR(50) NOT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY email (email),
  UNIQUE KEY roll_number (roll_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admins (
  id        INT(11) NOT NULL AUTO_INCREMENT,
  username  VARCHAR(50) NOT NULL,
  password  VARCHAR(255) NOT NULL,
  email     VARCHAR(100) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS courses (
  id            INT(11) NOT NULL AUTO_INCREMENT,
  course_name   VARCHAR(100) NOT NULL,
  description   TEXT,
  teacher_name  VARCHAR(100) DEFAULT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notices (
  id          INT(11) NOT NULL AUTO_INCREMENT,
  title       VARCHAR(200) NOT NULL,
  content     TEXT,
  posted_by   VARCHAR(100) DEFAULT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Assignments / Submissions / Results tables (from week3_assignments_results.sql)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS assignments (
  id           INT(11) NOT NULL AUTO_INCREMENT,
  title        VARCHAR(200) NOT NULL,
  description  TEXT,
  course_id    INT(11) NOT NULL,
  due_date     DATE NOT NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY course_id (course_id),
  CONSTRAINT fk_assignments_course
    FOREIGN KEY (course_id) REFERENCES courses (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS submissions (
  id             INT(11) NOT NULL AUTO_INCREMENT,
  assignment_id  INT(11) NOT NULL,
  student_id     INT(11) NOT NULL,
  file_path      VARCHAR(255) NOT NULL,
  submitted_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status         ENUM('submitted', 'graded') NOT NULL DEFAULT 'submitted',
  PRIMARY KEY (id),
  UNIQUE KEY uniq_assignment_student (assignment_id, student_id),
  KEY assignment_id (assignment_id),
  KEY student_id (student_id),
  CONSTRAINT fk_submissions_assignment
    FOREIGN KEY (assignment_id) REFERENCES assignments (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_submissions_student
    FOREIGN KEY (student_id) REFERENCES students (id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS results (
  id           INT(11) NOT NULL AUTO_INCREMENT,
  student_id   INT(11) NOT NULL,
  course_id    INT(11) DEFAULT NULL,
  subject      VARCHAR(100) NOT NULL,
  marks        INT(11) NOT NULL,
  total_marks  INT(11) NOT NULL,
  grade        VARCHAR(5) NOT NULL,
  exam_type    VARCHAR(50) NOT NULL,
  created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY student_id (student_id),
  KEY course_id (course_id),
  CONSTRAINT fk_results_student
    FOREIGN KEY (student_id) REFERENCES students (id)
    ON DELETE CASCADE,
  CONSTRAINT fk_results_course
    FOREIGN KEY (course_id) REFERENCES courses (id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- Sample data (from sample_data.sql) — optional but recommended so
-- courses.php / notices.php / assignments.php have something to show
-- ---------------------------------------------------------------------
INSERT INTO courses (course_name, description, teacher_name) VALUES
('Full Stack Web Development', 'Learn PHP, MySQL, and modern front-end practices by building a real LMS from scratch.', 'Sir Ahmed Raza'),
('Introduction to Databases', 'Core relational database concepts: schema design, normalization, and SQL querying.', 'Ms. Sana Tariq'),
('UI/UX Fundamentals', 'Principles of usable, accessible interface design for web applications.', 'Sir Bilal Hussain');

INSERT INTO notices (title, content, posted_by) VALUES
('Week 2 task released', 'The Week 2 handout is live — build the dashboard sidebar, courses page, and notice board this week.', 'Code Saviours'),
('Submission deadline reminder', 'All Week 1 submissions are due by Friday. Late submissions may not be accepted.', 'Academy Admin'),
('Batch SI-26 orientation recap', 'Thank you to everyone who attended the orientation session. Slides have been shared in the batch group.', 'Code Saviours');

-- Sample assignments (tied to the sample courses above)
INSERT INTO assignments (title, description, course_id, due_date) VALUES
('Build a Login Form',
 'Create a working login form with server-side validation and session handling.',
 (SELECT id FROM courses WHERE course_name = 'Full Stack Web Development' LIMIT 1),
 DATE_ADD(CURDATE(), INTERVAL 7 DAY)),
('Database Schema Design',
 'Design a normalized schema for a small library management system and submit the ER diagram.',
 (SELECT id FROM courses WHERE course_name = 'Introduction to Databases' LIMIT 1),
 DATE_ADD(CURDATE(), INTERVAL 10 DAY)),
('Wireframe a Dashboard',
 'Sketch and submit low-fidelity wireframes for a student dashboard screen.',
 (SELECT id FROM courses WHERE course_name = 'UI/UX Fundamentals' LIMIT 1),
 DATE_ADD(CURDATE(), INTERVAL 5 DAY));

-- ---------------------------------------------------------------------
-- Default admin account (from week4_admin_seed.sql)
--   Username: admin
--   Password: admin123
-- IMPORTANT: change this password after your first login on the live site.
-- ---------------------------------------------------------------------
INSERT INTO admins (username, password, email) VALUES
('admin', '$2y$10$tuO70mGYSDXPVxDIXbTf..PWYFB3TNaS4QhRAEyt7REl72xSkvAuG', 'admin@forcesacademy.test');

-- ---------------------------------------------------------------------
-- Week 6 addition: timetable table
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS timetable (
  id          INT(11) NOT NULL AUTO_INCREMENT,
  class       VARCHAR(50) NOT NULL,
  day         VARCHAR(20) NOT NULL,
  time_slot   VARCHAR(50) NOT NULL,
  subject     VARCHAR(100) NOT NULL,
  teacher     VARCHAR(100) NOT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
