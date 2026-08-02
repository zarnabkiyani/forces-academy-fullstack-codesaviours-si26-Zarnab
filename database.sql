-- Forces Academy LMS
-- Import this file in phpMyAdmin: Databases > forces_academy_lms > Import

CREATE DATABASE IF NOT EXISTS forces_academy_lms
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE forces_academy_lms;

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
