<?php
// config/database.php
class Database {
    private $host = "localhost";
    private $db_name = "student_portal";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// ============================================
// AUTHENTICATION CLASS
// ============================================

class Auth {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // TODO: Implement login functionality
    /*
    public function login($username, $password) {
        // Your login implementation here
        // 1. Validate input
        // 2. Check user credentials
        // 3. Generate session token
        // 4. Return success/failure response
        
        Example structure:
        
        $query = "SELECT id, username, email, password, role FROM users WHERE username = :username OR email = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $user['password'])) {
                // Generate session token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
                
                // Insert session
                $session_query = "INSERT INTO sessions (user_id, session_token, expires_at) VALUES (:user_id, :token, :expires)";
                $session_stmt = $this->conn->prepare($session_query);
                $session_stmt->bindParam(':user_id', $user['id']);
                $session_stmt->bindParam(':token', $token);
                $session_stmt->bindParam(':expires', $expires);
                $session_stmt->execute();
                
                return [
                    'success' => true,
                    'token' => $token,
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ]
                ];
            }
        }
        
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    */
    
    // TODO: Implement logout functionality
    /*
    public function logout($token) {
        // Delete session token
        $query = "DELETE FROM sessions WHERE session_token = :token";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        return $stmt->execute();
    }
    */
    
    // TODO: Implement token validation
    /*
    public function validateToken($token) {
        $query = "SELECT u.id, u.username, u.email, u.role FROM users u 
                  JOIN sessions s ON u.id = s.user_id 
                  WHERE s.session_token = :token AND s.expires_at > NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
    */
    
    // Middleware to check authentication (you can implement this)
    public function requireAuth() {
        // For now, we'll skip authentication
        // TODO: Implement proper authentication check
        return true;
    }
}

// ============================================
// STUDENT CLASS
// ============================================

class Student {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get student profile
    public function getProfile($student_id) {
        $query = "SELECT * FROM students WHERE student_id = :student_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Update student profile
    public function updateProfile($student_id, $data) {
        $query = "UPDATE students SET 
                  first_name = :first_name,
                  last_name = :last_name,
                  email = :email,
                  phone = :phone,
                  address = :address,
                  emergency_contact = :emergency_contact
                  WHERE student_id = :student_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':emergency_contact', $data['emergency_contact']);
        
        return $stmt->execute();
    }
    
    // Get student dashboard data
    public function getDashboardData($student_id) {
        // Get student ID from database
        $student_query = "SELECT id FROM students WHERE student_id = :student_id";
        $stmt = $this->conn->prepare($student_query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            return false;
        }
        
        $student_db_id = $student['id'];
        
        // Get overall attendance
        $attendance_query = "SELECT 
                            COUNT(CASE WHEN status = 'Present' THEN 1 END) as present,
                            COUNT(*) as total
                            FROM attendance WHERE student_id = :student_id";
        $stmt = $this->conn->prepare($attendance_query);
        $stmt->bindParam(':student_id', $student_db_id);
        $stmt->execute();
        $attendance = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get current GPA
        $gpa_query = "SELECT current_gpa FROM students WHERE id = :student_id";
        $stmt = $this->conn->prepare($gpa_query);
        $stmt->bindParam(':student_id', $student_db_id);
        $stmt->execute();
        $gpa = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get enrolled courses count
        $courses_query = "SELECT COUNT(*) as course_count FROM enrollments WHERE student_id = :student_id AND status = 'Active'";
        $stmt = $this->conn->prepare($courses_query);
        $stmt->bindParam(':student_id', $student_db_id);
        $stmt->execute();
        $courses = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get pending fees
        $fees_query = "SELECT SUM(fc.amount - COALESCE(fp.amount_paid, 0)) as pending_fees
                       FROM fee_categories fc
                       LEFT JOIN fee_payments fp ON fc.id = fp.fee_category_id AND fp.student_id = :student_id
                       WHERE fp.status != 'Paid' OR fp.status IS NULL";
        $stmt = $this->conn->prepare($fees_query);
        $stmt->bindParam(':student_id', $student_db_id);
        $stmt->execute();
        $fees = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'attendance_percentage' => $attendance['total'] > 0 ? round(($attendance['present'] / $attendance['total']) * 100) : 0,
            'current_gpa' => $gpa['current_gpa'] ?? 0,
            'enrolled_courses' => $courses['course_count'] ?? 0,
            'pending_fees' => $fees['pending_fees'] ?? 0
        ];
    }
}

// ============================================
// ATTENDANCE CLASS
// ============================================

class Attendance {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get attendance by student
    public function getStudentAttendance($student_id) {
        $query = "SELECT 
                    s.subject_name,
                    s.subject_code,
                    COUNT(CASE WHEN a.status = 'Present' THEN 1 END) as present_count,
                    COUNT(*) as total_classes,
                    ROUND((COUNT(CASE WHEN a.status = 'Present' THEN 1 END) / COUNT(*)) * 100, 2) as attendance_percentage
                  FROM attendance a
                  JOIN subjects s ON a.subject_id = s.id
                  JOIN students st ON a.student_id = st.id
                  WHERE st.student_id = :student_id
                  GROUP BY s.id, s.subject_name, s.subject_code
                  ORDER BY s.subject_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get overall attendance
    public function getOverallAttendance($student_id) {
        $query = "SELECT 
                    COUNT(CASE WHEN status = 'Present' THEN 1 END) as present,
                    COUNT(*) as total,
                    ROUND((COUNT(CASE WHEN status = 'Present' THEN 1 END) / COUNT(*)) * 100, 2) as percentage
                  FROM attendance a
                  JOIN students s ON a.student_id = s.id
                  WHERE s.student_id = :student_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Mark attendance
    public function markAttendance($student_id, $subject_id, $date, $status) {
        $query = "INSERT INTO attendance (student_id, subject_id, attendance_date, status)
                  VALUES (:student_id, :subject_id, :date, :status)
                  ON DUPLICATE KEY UPDATE status = :status";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':subject_id', $subject_id);
        $stmt->bindParam(':date', $date);
        $stmt->bindParam(':status', $status);
        
        return $stmt->execute();
    }
}

// ============================================
// RESULTS CLASS
// ============================================

class Results {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get results by exam type
    public function getResultsByExamType($student_id, $exam_type) {
        $query = "SELECT 
                    s.subject_name,
                    r.marks_obtained,
                    e.max_marks,
                    r.grade
                  FROM results r
                  JOIN exams e ON r.exam_id = e.id
                  JOIN subjects s ON e.subject_id = s.id
                  JOIN students st ON r.student_id = st.id
                  WHERE st.student_id = :student_id AND e.exam_type = :exam_type
                  ORDER BY s.subject_name";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':exam_type', $exam_type);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get all results for a student
    public function getAllResults($student_id) {
        $series1 = $this->getResultsByExamType($student_id, 'Series1');
        $series2 = $this->getResultsByExamType($student_id, 'Series2');
        $model = $this->getResultsByExamType($student_id, 'Model');
        
        return [
            'series1' => $series1,
            'series2' => $series2,
            'model' => $model
        ];
    }
}

// ============================================
// FEES CLASS
// ============================================

class Fees {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get fee details for a student
    public function getStudentFees($student_id) {
        $query = "SELECT 
                    fc.category_name,
                    fc.description,
                    fc.amount,
                    fc.due_date,
                    COALESCE(fp.amount_paid, 0) as amount_paid,
                    COALESCE(fp.status, 'Pending') as payment_status,
                    fp.payment_date,
                    fp.payment_method
                  FROM fee_categories fc
                  LEFT JOIN fee_payments fp ON fc.id = fp.fee_category_id 
                  AND fp.student_id = (SELECT id FROM students WHERE student_id = :student_id)
                  ORDER BY fc.due_date";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get fee summary
    public function getFeeSummary($student_id) {
        $query = "SELECT 
                    SUM(fc.amount) as total_fees,
                    SUM(COALESCE(fp.amount_paid, 0)) as paid_amount,
                    SUM(fc.amount - COALESCE(fp.amount_paid, 0)) as remaining_balance
                  FROM fee_categories fc
                  LEFT JOIN fee_payments fp ON fc.id = fp.fee_category_id 
                  AND fp.student_id = (SELECT id FROM students WHERE student_id = :student_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Process payment
    public function processPayment($student_id, $fee_category_id, $amount, $payment_method) {
        // Get student database ID
        $student_query = "SELECT id FROM students WHERE student_id = :student_id";
        $stmt = $this->conn->prepare($student_query);
        $stmt->bindParam(':student_id', $student_id);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            return false;
        }
        
        $transaction_id = 'TXN_' . uniqid();
        
        $query = "INSERT INTO fee_payments (student_id, fee_category_id, amount_paid, payment_date, payment_method, status, transaction_id)
                  VALUES (:student_id, :fee_category_id, :amount, CURDATE(), :payment_method, 'Paid', :transaction_id)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':student_id', $student['id']);
        $stmt->bindParam(':fee_category_id', $fee_category_id);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':transaction_id', $transaction_id);
        
        return $stmt->execute() ? $transaction_id : false;
    }
}

// ============================================
// FACULTY CLASS
// ============================================

class Faculty {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all faculty members
    public function getAllFaculty() {
        $query = "SELECT * FROM faculty ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get faculty by department
    public function getFacultyByDepartment($department) {
        $query = "SELECT * FROM faculty WHERE department = :department ORDER BY name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':department', $department);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// ============================================
// API ENDPOINTS
// ============================================

// Set headers for API response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Initialize classes
$auth = new Auth($db);
$student = new Student($db);
$attendance = new Attendance($db);
$results = new Results($db);
$fees = new Fees($db);
$faculty = new Faculty($db);

// Get request method and endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/api', '', $path); // Remove /api prefix if present

// Route handling
switch ($path) {
    case '/login':
        if ($method === 'POST') {
            // TODO: Implement login endpoint
            /*
            $data = json_decode(file_get_contents('php://input'), true);
            $result = $auth->login($data['username'], $data['password']);
            echo json_encode($result);
            */
            echo json_encode(['message' => 'Login endpoint - implement this yourself']);
        }
        break;
    
    case '/logout':
        if ($method === 'POST') {
            // TODO: Implement logout endpoint
            echo json_encode(['message' => 'Logout endpoint - implement this yourself']);
        }
        break;
    
    case '/dashboard':
        if ($method === 'GET') {
            if ($auth->requireAuth()) {
                $student_id = $_GET['student_id'] ?? 'CS2023001'; // Default for testing
                $data = $student->getDashboardData($student_id);
                echo json_encode($data);
            }
        }
        break;
    
    case '/profile':
        if ($method === 'GET') {
            if ($auth->requireAuth()) {
                $student_id = $_GET['student_id'] ?? 'CS2023001';
                $profile = $student->getProfile($student_id);
                echo json_encode($profile);
            }
        } elseif ($method === 'PUT') {
            if ($auth->requireAuth()) {
                $data = json_decode(file_get_contents('php://input'), true);
                $student_id = $data['student_id'];
                $result = $student->updateProfile($student_id, $data);
                echo json_encode(['success' => $result]);
            }
        }
        break;
    
    case '/attendance':
        if ($method === 'GET') {
            if ($auth->requireAuth()) {
                $student_id = $_GET['student_id'] ?? 'CS2023001';
                $attendance_data = $attendance->getStudentAttendance($student_id);
                $overall = $attendance->getOverallAttendance($student_id);
                echo json_encode([
                    'overall' => $overall,
                    'subjects' => $attendance_data
                ]);
            }
        }
        break;
    
    case '/results':
        if ($method === 'GET') {
            if ($auth->requireAuth()) {
                $student_id = $_GET['student_id'] ?? 'CS2023001';
                $results_data = $results->getAllResults($student_id);
                echo json_encode($results_data);
            }
        }
        break;
    
    case '/fees':
        if ($method === 'GET') {
            if ($auth->requireAuth()) {
                $student_id = $_GET['student_id'] ?? 'CS2023001';
                $fee_details = $fees->getStudentFees($student_id);
                $fee_summary = $fees->getFeeSummary($student_id);
                echo json_encode([
                    'details' => $fee_details,
                    'summary' => $fee_summary
                ]);
            }
        } elseif ($method === 'POST') {
            if ($auth->requireAuth()) {
                $data = json_decode(file_get_contents('php://input'), true);
                $transaction_id = $fees->processPayment(
                    $data['student_id'],
                    $data['fee_category_id'],
                    $data['amount'],
                    $data['payment_method']
                );
                echo json_encode([
                    'success' => $transaction_id !== false,
                    'transaction_id' => $transaction_id
                ]);
            }
        }
        break;
    
    case '/faculty':
        if ($method === 'GET') {
            if ($auth->requireAuth()) {
                $department = $_GET['department'] ?? null;
                if ($department) {
                    $faculty_data = $faculty->getFacultyByDepartment($department);
                } else {
                    $faculty_data = $faculty->getAllFaculty();
                }
                echo json_encode($faculty_data);
            }
        }
        break;
    
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?>
