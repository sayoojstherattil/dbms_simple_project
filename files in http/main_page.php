<?php
// main_page.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Get student data
$conn = getDBConnection();
$student_id = $_SESSION['user_id'];

// Fetch student information
$stmt = $conn->prepare("
    SELECT s.* 
    FROM students s
    JOIN users u ON s.user_id = u.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// Fetch attendance data
$attendance_stmt = $conn->prepare("
    SELECT sub.subject_name, 
           COUNT(CASE WHEN a.status = 'Present' THEN 1 END) as present_count,
           COUNT(*) as total_classes,
           ROUND((COUNT(CASE WHEN a.status = 'Present' THEN 1 END) / COUNT(*)) * 100) as percentage
    FROM attendance a
    JOIN subjects sub ON a.subject_id = sub.id
    JOIN enrollments e ON a.subject_id = e.subject_id AND e.student_id = ?
    GROUP BY sub.subject_name
");
$attendance_stmt->bind_param("i", $student['id']);
$attendance_stmt->execute();
$attendance_data = $attendance_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate overall attendance
$overall_attendance = 0;
if (!empty($attendance_data)) {
    $total_percentage = 0;
    foreach ($attendance_data as $subject) {
        $total_percentage += $subject['percentage'];
    }
    $overall_attendance = round($total_percentage / count($attendance_data));
}

// Fetch fee data
$fee_stmt = $conn->prepare("
    SELECT fc.category_name, fc.description, fc.amount, fc.due_date, 
           COALESCE(SUM(fp.amount_paid), 0) as paid_amount,
           CASE 
               WHEN COALESCE(SUM(fp.amount_paid), 0) >= fc.amount THEN 'Paid'
               WHEN fc.due_date < CURDATE() THEN 'Overdue'
               ELSE 'Pending'
           END as payment_status
    FROM fee_categories fc
    LEFT JOIN fee_payments fp ON fc.id = fp.fee_category_id AND fp.student_id = ?
    GROUP BY fc.id
");
$fee_stmt->bind_param("i", $student['id']);
$fee_stmt->execute();
$fee_data = $fee_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate fee summary
$total_fees = 0;
$total_paid = 0;
foreach ($fee_data as $fee) {
    $total_fees += $fee['amount'];
    $total_paid += $fee['paid_amount'];
}
$pending_fees = $total_fees - $total_paid;

// Fetch exam results
$results_stmt = $conn->prepare("
    SELECT e.exam_name, e.exam_type, sub.subject_name, r.marks_obtained, e.max_marks
    FROM results r
    JOIN exams e ON r.exam_id = e.id
    JOIN subjects sub ON e.subject_id = sub.id
    WHERE r.student_id = ?
    ORDER BY e.exam_type, sub.subject_name
");
$results_stmt->bind_param("i", $student['id']);
$results_stmt->execute();
$results_data = $results_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Organize results by exam type
$organized_results = [];
foreach ($results_data as $result) {
    $organized_results[$result['exam_type']][] = $result;
}

// Fetch faculty information
$faculty_stmt = $conn->prepare("SELECT * FROM faculty");
$faculty_stmt->execute();
$faculty_data = $faculty_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Content Display</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <div class="container">
        <input type="radio" name="content" id="content1" hidden checked>
        <input type="radio" name="content" id="content2" hidden>
        <input type="radio" name="content" id="content3" hidden>
        <input type="radio" name="content" id="content4" hidden>
        <input type="radio" name="content" id="content5" hidden>
       
        <div class="navigation">
            <input type="radio" name="content" id="nav1" hidden>
            <label for="content1">📊 Dashboard</label>
            <input type="radio" name="content" id="nav2" hidden>
            <label for="content2">👨‍🏫 Faculty</label>
            <input type="radio" name="content" id="nav3" hidden>
            <label for="content3">💰 Fee</label>
            <input type="radio" name="content" id="nav4" hidden>
            <label for="content4">📅 Attendance</label>
            <input type="radio" name="content" id="nav5" hidden>
            <label for="content5">👤 My Profile</label>
            <a href="logout.php" style="float: right; margin-right: 20px; color: #e74c3c;">Logout</a>
        </div>
       
        <div class="content-container">
            <!-- Dashboard Section -->
            <div id="section1" class="content">
                <h2>Dashboard</h2>
                <p>Welcome to your student portal dashboard. Here's an overview of your academic status.</p>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?= $overall_attendance ?>%</div>
                        <div class="stat-label">Overall Attendance</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $student['current_gpa'] ?? 'N/A' ?></div>
                        <div class="stat-label">Current GPA</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= count($attendance_data) ?></div>
                        <div class="stat-label">Courses Enrolled</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">$<?= number_format($pending_fees, 2) ?></div>
                        <div class="stat-label">Pending Fees</div>
                    </div>
                </div>

                <div class="dropdown-container">
                    <?php foreach (['Series1', 'Series2', 'Model'] as $exam_type): ?>
                        <?php if (isset($organized_results[$exam_type])): ?>
                            <div class="dropdown">
                                <input type="checkbox" id="<?= strtolower($exam_type) ?>" class="dropdown-toggle">
                                <label for="<?= strtolower($exam_type) ?>" class="dropdown-header"><?= $exam_type ?> Results</label>
                                <div class="dropdown-content">
                                    <?php foreach ($organized_results[$exam_type] as $result): ?>
                                        <div class="subject-item">
                                            <span><?= htmlspecialchars($result['subject_name']) ?></span>
                                            <span><?= $result['marks_obtained'] ?>/<?= $result['max_marks'] ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Faculty Section -->
            <div id="section2" class="content">
                <h2>Faculty Information</h2>
                <p>Meet your faculty members and their departments.</p>
                
                <div class="faculties">
                    <ul>
                        <?php foreach ($faculty_data as $faculty): ?>
                            <li>
                                <h3><?= htmlspecialchars($faculty['department']) ?></h3>
                                <p><strong>Name:</strong> <?= htmlspecialchars($faculty['name']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($faculty['email']) ?></p>
                                <p><strong>Position:</strong> <?= htmlspecialchars($faculty['position']) ?></p>
                                <p><strong>Office Hours:</strong> <?= htmlspecialchars($faculty['office_hours']) ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
           
            <!-- Fee Section -->
            <div id="section3" class="content">
                <h2>Fee Information</h2>
                <p>View your fee details and payment status.</p>
                
                <div class="fee-section">
                    <table class="fee-details-table">
                        <thead>
                            <tr>
                                <th>Fee Category</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Payment Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($fee_data as $fee): ?>
                                <tr>
                                    <td><?= htmlspecialchars($fee['category_name']) ?></td>
                                    <td><?= htmlspecialchars($fee['description']) ?></td>
                                    <td>$<?= number_format($fee['amount'], 2) ?></td>
                                    <td><?= date('F j, Y', strtotime($fee['due_date'])) ?></td>
                                    <td class="status-<?= strtolower($fee['payment_status']) ?>"><?= $fee['payment_status'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"><strong>Total Fees</strong></td>
                                <td colspan="3"><strong>$<?= number_format($total_fees, 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="fee-summary">
                        <h3>Fee Summary</h3>
                        <p><strong>Total Fees:</strong> $<?= number_format($total_fees, 2) ?></p>
                        <p><strong>Paid Amount:</strong> $<?= number_format($total_paid, 2) ?></p>
                        <p><strong>Remaining Balance:</strong> $<?= number_format($pending_fees, 2) ?></p>
                    </div>

                    <div class="payment-options">
                        <h3>Payment Methods</h3>
                        <ul>
                            <li>💳 Online Banking</li>
                            <li>💳 Credit Card</li>
                            <li>💳 Debit Card</li>
                            <li>💵 Cash Payment</li>
                            <li>📊 Installment Plan</li>
                        </ul>
                    </div>
                </div>
            </div>
           
            <!-- Attendance Section -->
            <div id="section4" class="content">
                <h2>Attendance Records</h2>
                <p>Track your attendance across all subjects and view detailed statistics.</p>
                
                <div class="attendance-box">
                    <div class="attendance-percentage"><?= $overall_attendance ?>%</div>
                    <div class="attendance-label">Overall Attendance</div>
                    <div class="attendance-status">Status: 
                        <span style="color: <?= $overall_attendance >= 85 ? '#2ecc71' : ($overall_attendance >= 75 ? '#f39c12' : '#e74c3c') ?>;">
                            <?= $overall_attendance >= 85 ? 'Good' : ($overall_attendance >= 75 ? 'Average' : 'Poor') ?>
                        </span>
                    </div>
                </div>

                <div class="attendance-grid">
                    <?php foreach ($attendance_data as $subject): ?>
                        <div class="subject-attendance">
                            <h3><?= htmlspecialchars($subject['subject_name']) ?></h3>
                            <div class="attendance-info">
                                <span>Classes Attended: <?= $subject['present_count'] ?></span>
                                <span>Total Classes: <?= $subject['total_classes'] ?></span>
                            </div>
                            <div class="attendance-bar">
                                <div class="attendance-fill 
                                    <?= $subject['percentage'] >= 85 ? 'good' : ($subject['percentage'] >= 75 ? 'average' : 'poor') ?>" 
                                    style="width: <?= $subject['percentage'] ?>%;">
                                </div>
                            </div>
                            <div style="text-align: center; font-weight: bold; 
                                color: <?= $subject['percentage'] >= 85 ? '#2ecc71' : ($subject['percentage'] >= 75 ? '#f39c12' : '#e74c3c') ?>;">
                                <?= $subject['percentage'] ?>%
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
           
            <!-- Profile Section -->
            <div id="section5" class="content">
                <h2>My Profile</h2>
                <div class="profile-container">
                    <div class="profile-header">
                        <div class="profile-image"><?= substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1) ?></div>
                        <div class="profile-info">
                            <h1><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h1>
                            <p><strong>Student ID:</strong> <?= htmlspecialchars($student['student_id']) ?></p>
                            <p><strong>Program:</strong> <?= htmlspecialchars($student['program']) ?></p>
                            <p><strong>Year:</strong> <?= $student['admission_year'] ? date('Y') - $student['admission_year'] + 1 : 'N/A' ?> Year</p>
                            <p><strong>Status:</strong> <?= $student['status'] ?? 'Active' ?></p>
                        </div>
                    </div>

                    <div class="profile-sections">
                        <div class="profile-section">
                            <h3>Personal Information</h3>
                            <div class="info-row">
                                <span class="info-label">Full Name:</span>
                                <span class="info-value"><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Date of Birth:</span>
                                <span class="info-value"><?= date('F j, Y', strtotime($student['date_of_birth'])) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Gender:</span>
                                <span class="info-value"><?= $student['gender'] ?? 'N/A' ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Blood Group:</span>
                                <span class="info-value"><?= $student['blood_group'] ?? 'N/A' ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Nationality:</span>
                                <span class="info-value"><?= $student['nationality'] ?? 'N/A' ?></span>
                            </div>
                        </div>

                        <div class="profile-section">
                            <h3>Contact Information</h3>
                            <div class="info-row">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?= htmlspecialchars($student['email']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Phone:</span>
                                <span class="info-value"><?= $student['phone'] ?? 'N/A' ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Address:</span>
                                <span class="info-value"><?= htmlspecialchars($student['address'] ?? 'N/A') ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Emergency Contact:</span>
                                <span class="info-value"><?= $student['emergency_contact'] ?? 'N/A' ?></span>
                            </div>
                        </div>

                        <div class="profile-section">
                            <h3>Academic Information</h3>
                            <div class="info-row">
                                <span class="info-label">Student ID:</span>
                                <span class="info-value"><?= htmlspecialchars($student['student_id']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Program:</span>
                                <span class="info-value"><?= htmlspecialchars($student['program']) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Specialization:</span>
                                <span class="info-value"><?= $student['specialization'] ?? 'N/A' ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Current Semester:</span>
                                <span class="info-value"><?= $student['current_semester'] ?? 'N/A' ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Admission Year:</span>
                                <span class="info-value"><?= $student['admission_year'] ?? 'N/A' ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Current GPA:</span>
                                <span class="info-value"><?= $student['current_gpa'] ?? 'N/A' ?>/4.0</span>
                            </div>
                        </div>

                        <div class="profile-section">
                            <h3>Guardian Information</h3>
                            <div class="info-row">
                                <span class="info-label">Father's Name:</span>
                                <span class="info-value"><?= $student['father_name'] ?? 'N/A' ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Mother's Name:</span>
                                <span class="info-value"><?= $student['mother_name'] ?? 'N/A' ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Guardian Phone:</span>
                                <span class="info-value"><?= $student['guardian_phone'] ?? 'N/A' ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Guardian Email:</span>
                                <span class="info-value"><?= $student['guardian_email'] ?? 'N/A' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
