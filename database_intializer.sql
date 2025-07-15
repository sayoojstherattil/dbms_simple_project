-- Insert sample users
INSERT INTO users (username, email, password, role) VALUES 
('john_student', 'john@student.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('jane_faculty', 'jane@faculty.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty');

-- Insert sample student
INSERT INTO students (
    user_id, student_id, first_name, last_name, email, phone, date_of_birth, gender, 
    blood_group, nationality, address, program, specialization, current_semester, 
    admission_year, current_gpa, father_name, mother_name, guardian_phone, 
    guardian_email, emergency_contact
) VALUES (
    1, 'CS2023001', 'John', 'Smith', 'john@student.edu', '+91 9876543210', '2002-01-15', 
    'Male', 'O+', 'Indian', '123 Student Street, University City', 'Bachelor of Computer Science', 
    'Software Engineering', 6, 2021, 3.7, 'Robert Smith', 'Mary Smith', '+91 9876543211', 
    'robert.smith@email.com', '+91 9876543211'
);

-- Insert sample subjects
INSERT INTO subjects (subject_code, subject_name, credits, semester) VALUES 
('CS301', 'Computer Networks', 4, 6),
('CS302', 'Automata Theory', 3, 6),
('CS303', 'Architecture of IC', 4, 6),
('CS304', 'Database Systems', 4, 6),
('CS305', 'Software Engineering', 3, 6),
('CS306', 'Web Technologies', 3, 6);

-- Insert sample enrollments
INSERT INTO enrollments (student_id, subject_id, enrollment_date) VALUES 
(1, 1, '2023-06-01'),
(1, 2, '2023-06-01'),
(1, 3, '2023-06-01'),
(1, 4, '2023-06-01'),
(1, 5, '2023-06-01'),
(1, 6, '2023-06-01');

-- Insert sample faculty
INSERT INTO faculty (user_id, name, email, department, position, office_hours) VALUES 
(2, 'Dr. Jane Smith', 'jane@faculty.edu', 'Computer Science', 'Professor', 'Mon-Fri 10:00 AM - 4:00 PM'),
(NULL, 'Dr. John Doe', 'john.doe@university.edu', 'Computer Science', 'Dean', 'Mon-Fri 9:00 AM - 5:00 PM'),
(NULL, 'Dr. Bob Johnson', 'bob.johnson@university.edu', 'Science', 'Dean', 'Mon-Fri 8:00 AM - 6:00 PM');

-- Assign faculty to subjects
UPDATE subjects SET faculty_id = 1 WHERE id = 1;
UPDATE subjects SET faculty_id = 1 WHERE id = 2;
UPDATE subjects SET faculty_id = 2 WHERE id = 3;
UPDATE subjects SET faculty_id = 1 WHERE id = 4;
UPDATE subjects SET faculty_id = 2 WHERE id = 5;
UPDATE subjects SET faculty_id = 1 WHERE id = 6;

-- Insert sample attendance records
-- For Computer Networks (subject_id=1)
INSERT INTO attendance (student_id, subject_id, attendance_date, status) VALUES 
(1, 1, '2023-06-05', 'Present'),
(1, 1, '2023-06-07', 'Present'),
(1, 1, '2023-06-12', 'Present'),
(1, 1, '2023-06-14', 'Present'),
(1, 1, '2023-06-19', 'Present'),
(1, 1, '2023-06-21', 'Absent'),
(1, 1, '2023-06-26', 'Present'),
(1, 1, '2023-06-28', 'Present'),
(1, 1, '2023-07-03', 'Present'),
(1, 1, '2023-07-05', 'Present');

-- For Automata Theory (subject_id=2)
INSERT INTO attendance (student_id, subject_id, attendance_date, status) VALUES 
(1, 2, '2023-06-05', 'Present'),
(1, 2, '2023-06-07', 'Present'),
(1, 2, '2023-06-12', 'Present'),
(1, 2, '2023-06-14', 'Present'),
(1, 2, '2023-06-19', 'Absent'),
(1, 2, '2023-06-21', 'Present'),
(1, 2, '2023-06-26', 'Present'),
(1, 2, '2023-06-28', 'Present'),
(1, 2, '2023-07-03', 'Present'),
(1, 2, '2023-07-05', 'Present');

-- For Architecture of IC (subject_id=3)
INSERT INTO attendance (student_id, subject_id, attendance_date, status) VALUES 
(1, 3, '2023-06-05', 'Present'),
(1, 3, '2023-06-07', 'Present'),
(1, 3, '2023-06-12', 'Present'),
(1, 3, '2023-06-14', 'Absent'),
(1, 3, '2023-06-19', 'Present'),
(1, 3, '2023-06-21', 'Present'),
(1, 3, '2023-06-26', 'Present'),
(1, 3, '2023-06-28', 'Present'),
(1, 3, '2023-07-03', 'Present'),
(1, 3, '2023-07-05', 'Present');

-- For Database Systems (subject_id=4)
INSERT INTO attendance (student_id, subject_id, attendance_date, status) VALUES 
(1, 4, '2023-06-05', 'Present'),
(1, 4, '2023-06-07', 'Present'),
(1, 4, '2023-06-12', 'Present'),
(1, 4, '2023-06-14', 'Present'),
(1, 4, '2023-06-19', 'Present'),
(1, 4, '2023-06-21', 'Present'),
(1, 4, '2023-06-26', 'Absent'),
(1, 4, '2023-06-28', 'Present'),
(1, 4, '2023-07-03', 'Present'),
(1, 4, '2023-07-05', 'Present');

-- For Software Engineering (subject_id=5)
INSERT INTO attendance (student_id, subject_id, attendance_date, status) VALUES 
(1, 5, '2023-06-05', 'Present'),
(1, 5, '2023-06-07', 'Present'),
(1, 5, '2023-06-12', 'Present'),
(1, 5, '2023-06-14', 'Present'),
(1, 5, '2023-06-19', 'Present'),
(1, 5, '2023-06-21', 'Present'),
(1, 5, '2023-06-26', 'Present'),
(1, 5, '2023-06-28', 'Absent'),
(1, 5, '2023-07-03', 'Present'),
(1, 5, '2023-07-05', 'Present');

-- For Web Technologies (subject_id=6)
INSERT INTO attendance (student_id, subject_id, attendance_date, status) VALUES 
(1, 6, '2023-06-05', 'Present'),
(1, 6, '2023-06-07', 'Present'),
(1, 6, '2023-06-12', 'Absent'),
(1, 6, '2023-06-14', 'Present'),
(1, 6, '2023-06-19', 'Present'),
(1, 6, '2023-06-21', 'Present'),
(1, 6, '2023-06-26', 'Present'),
(1, 6, '2023-06-28', 'Present'),
(1, 6, '2023-07-03', 'Present'),
(1, 6, '2023-07-05', 'Present');

-- Insert sample exams
INSERT INTO exams (exam_name, exam_type, subject_id, max_marks, exam_date) VALUES 
('Series 1', 'Series1', 1, 50, '2023-06-15'),
('Series 1', 'Series1', 2, 50, '2023-06-16'),
('Series 1', 'Series1', 3, 50, '2023-06-17'),
('Series 2', 'Series2', 1, 50, '2023-07-10'),
('Series 2', 'Series2', 2, 50, '2023-07-11'),
('Series 2', 'Series2', 3, 50, '2023-07-12'),
('Model Exam', 'Model', 1, 50, '2023-08-05'),
('Model Exam', 'Model', 2, 50, '2023-08-06'),
('Model Exam', 'Model', 3, 50, '2023-08-07');

-- Insert sample results
INSERT INTO results (student_id, exam_id, marks_obtained, grade) VALUES 
(1, 1, 40, 'A'),
(1, 2, 35, 'B'),
(1, 3, 45, 'A'),
(1, 4, 38, 'B'),
(1, 5, 42, 'A'),
(1, 6, 37, 'B'),
(1, 7, 45, 'A'),
(1, 8, 40, 'A'),
(1, 9, 42, 'A');

-- Insert sample fee categories
INSERT INTO fee_categories (category_name, description, amount, due_date) VALUES 
('Tuition', 'Semester Academic Fees', 5000.00, '2023-09-01'),
('Hostel', 'Accommodation Charges', 1500.00, '2023-09-15'),
('Library', 'Annual Library Membership', 100.00, '2023-10-01'),
('Examination', 'Semester End Exam Fees', 250.00, '2023-11-15');

-- Insert sample fee payments
INSERT INTO fee_payments (student_id, fee_category_id, amount_paid, payment_date, payment_method, status) VALUES 
(1, 3, 100.00, '2023-09-01', 'Online Banking', 'Paid');
