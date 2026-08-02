-- Forces Academy LMS — Week 2 sample data
-- Optional: import this in phpMyAdmin (forces_academy_lms > Import) to quickly
-- populate the courses and notices tables so courses.php and notices.php have
-- something to display while testing.

USE forces_academy_lms;

INSERT INTO courses (course_name, description, teacher_name) VALUES
('Full Stack Web Development', 'Learn PHP, MySQL, and modern front-end practices by building a real LMS from scratch.', 'Sir Ahmed Raza'),
('Introduction to Databases', 'Core relational database concepts: schema design, normalization, and SQL querying.', 'Ms. Sana Tariq'),
('UI/UX Fundamentals', 'Principles of usable, accessible interface design for web applications.', 'Sir Bilal Hussain');

INSERT INTO notices (title, content, posted_by) VALUES
('Week 2 task released', 'The Week 2 handout is live — build the dashboard sidebar, courses page, and notice board this week.', 'Code Saviours'),
('Submission deadline reminder', 'All Week 1 submissions are due by Friday. Late submissions may not be accepted.', 'Academy Admin'),
('Batch SI-26 orientation recap', 'Thank you to everyone who attended the orientation session. Slides have been shared in the batch group.', 'Code Saviours');
