-- =====================================================================
-- Forces Academy LMS — Week 6 addition: Timetable table
-- =====================================================================
-- Run this AFTER database_full_export.sql (or database.sql) has already
-- been imported. In phpMyAdmin: select your database > Import > choose
-- this file > Go.
-- =====================================================================

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
