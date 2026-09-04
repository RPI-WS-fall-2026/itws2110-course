-- Runs once, on first initialization of an empty data directory.

CREATE DATABASE IF NOT EXISTS app;
USE app;

CREATE TABLE students (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  class_year INT          NOT NULL,
  created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
  id    INT AUTO_INCREMENT PRIMARY KEY,
  code  VARCHAR(16)  NOT NULL UNIQUE,
  title VARCHAR(120) NOT NULL
);

CREATE TABLE enrollments (
  student_id INT NOT NULL,
  course_id  INT NOT NULL,
  PRIMARY KEY (student_id, course_id),
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (course_id)  REFERENCES courses(id)
);

INSERT INTO students (name, class_year) VALUES
  ('Ada Lovelace', 2028),
  ('Grace Hopper', 2027),
  ('Alan Turing',  2029);

INSERT INTO courses (code, title) VALUES
  ('ITWS 1100', 'Introduction to ITWS'),
  ('ITWS 2110', 'Web Systems Development'),
  ('CSCI 1200', 'Data Structures');

INSERT INTO enrollments (student_id, course_id) VALUES
  (1, 1), (1, 2), (2, 2), (2, 3), (3, 3);
