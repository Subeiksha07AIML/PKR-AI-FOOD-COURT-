CREATE DATABASE IF NOT EXISTS pkr_food_court;
USE pkr_food_court;

DROP TABLE IF EXISTS students;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    register_number VARCHAR(50) UNIQUE NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    dob DATE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO students (register_number, student_name, dob, password) VALUES
('241AI002', 'Anuviruntha sri. B', '2006-08-18', '2006-08-18'),
('241AI003', 'Dhanusya.S.S', '2007-02-19', '2007-02-19'),
('241AI004', 'Dharani.S', '2006-06-13', '2006-06-13'),
('241AI005', 'Dharnika.K', '2007-05-12', '2007-05-12'),
('241AI006', 'Gopika.G', '2006-10-14', '2006-10-14'),
('241AI007', 'Gopika. M', '2007-04-27', '2007-04-27'),
('241AI008', 'Harini.A.G', '2006-10-13', '2006-10-13'),
('241AI009', 'Harini.S', '2006-08-21', '2006-08-21'),
('241AI010', 'Haritha.S', '2007-04-23', '2007-04-23'),
('241AI012', 'Janani. V', '2006-07-11', '2006-07-11'),
('241AI013', 'Kanimozhi.S', '2006-10-08', '2006-10-08'),
('241AI014', 'Kaviya.R', '2006-10-22', '2006-10-22'),
('241AI015', 'Mohithavani.S', '2005-11-24', '2005-11-24'),
('241AI016', 'Nadhiya.S', '2007-02-12', '2007-02-12'),
('241AI018', 'Sathya.P', '2006-11-04', '2006-11-04'),
('241AI019', 'Selvabharathi.D', '2007-05-17', '2007-05-17'),
('241AI020', 'Shreeja. A', '2006-11-30', '2006-11-30'),
('241AI021', 'Sowbarnika.S', '2007-01-18', '2007-01-18'),
('241AI022', 'Sri dharshini.R', '2007-09-27', '2007-09-27'),
('241AI023', 'Sripriya.C', '2006-04-18', '2006-04-18'),
('241AI024', 'Subeiksha.S.A', '2006-12-07', '2006-12-07'),
('241AI025', 'Sujitha Sri.M', '2006-10-04', '2006-10-04'),
('241AI026', 'Thiriloshini.S', '2005-06-27', '2005-06-27'),
('241AI027', 'Vibha.B.B', '2007-04-20', '2007-04-20'),
('241AI028', 'Abarna Sri.R', '2005-10-20', '2005-10-20');
