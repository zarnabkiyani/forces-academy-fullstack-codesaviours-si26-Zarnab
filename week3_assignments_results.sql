-- Forces Academy LMS — Week 3 migration
-- Import this file in phpMyAdmin: Databases > forces_academy_lms > Import
-- (Run this AFTER database.sql / sample_data.sql from Week 2 — it depends on
-- the students and courses tables already existing.)

USE forces_academy_lms;

-- ---------------------------------------------------------------------
-- Step 1: Assignments table
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

-- ---------------------------------------------------------------------
-- Submissions table
-- ---------------------------------------------------------------------
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

-- ---------------------------------------------------------------------
-- Step 4: Results table
-- ---------------------------------------------------------------------
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
-- Sample assignments (tied to the Week 2 sample courses)
-- ---------------------------------------------------------------------
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
-- Step 4.15: Sample results for a test student
-- This targets whichever student was registered first (lowest id).
-- If you want a specific student instead, replace the subquery below
-- with that student's exact id, e.g. student_id = 3.
-- ---------------------------------------------------------------------
INSERT INTO results (student_id, course_id, subject, marks, total_marks, grade, exam_type) VALUES
((SELECT id FROM students ORDER BY id ASC LIMIT 1),
 (SELECT id FROM courses WHERE course_name = 'Full Stack Web Development' LIMIT 1),
 'PHP & MySQL', 42, 50, 'A', 'Midterm'),
((SELECT id FROM students ORDER BY id ASC LIMIT 1),
 (SELECT id FROM courses WHERE course_name = 'Introduction to Databases' LIMIT 1),
 'Database Design', 35, 50, 'B', 'Midterm'),
((SELECT id FROM students ORDER BY id ASC LIMIT 1),
 (SELECT id FROM courses WHERE course_name = 'UI/UX Fundamentals' LIMIT 1),
 'Interface Design', 47, 50, 'A', 'Final'),
((SELECT id FROM students ORDER BY id ASC LIMIT 1),
 (SELECT id FROM courses WHERE course_name = 'Full Stack Web Development' LIMIT 1),
 'JavaScript Basics', 28, 50, 'C', 'Quiz');
