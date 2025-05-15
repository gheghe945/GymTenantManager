<?php
/**
 * Attendance Controller
 */
class AttendanceController extends BaseController {
    /**
     * Attendance model instance
     *
     * @var Attendance
     */
    private $attendanceModel;
    
    /**
     * User model instance
     *
     * @var User
     */
    private $userModel;
    
    /**
     * Course model instance
     *
     * @var Course
     */
    private $courseModel;
    
    /**
     * Middleware configuration
     */
    protected $middleware = [
        'AuthMiddleware' => ['*'],
        'RoleMiddleware' => ['create', 'store']
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->attendanceModel = new Attendance();
        $this->userModel = new User();
        $this->courseModel = new Course();
    }
    
    /**
     * Display attendance list
     *
     * @return void
     */
    public function index() {
        $tenantId = getCurrentTenantId();
        
        // Get attendance data based on role
        if (hasRole('MEMBER')) {
            $attendance = $this->attendanceModel->getAttendanceByUserId($_SESSION['user_id'], $tenantId);
        } else {
            $attendance = $this->attendanceModel->getAttendanceByTenantId($tenantId);
        }
        
        $data = [
            'attendance' => $attendance,
            'isAdmin' => hasRole('SUPER_ADMIN') || hasRole('GYM_ADMIN')
        ];
        
        $this->render('attendance/index', $data);
    }
    
    /**
     * Display attendance creation form
     *
     * @return void
     */
    public function create() {
        $tenantId = getCurrentTenantId();
        
        // Get all members for this tenant
        $members = $this->userModel->getUsersByRoleAndTenant('MEMBER', $tenantId);
        
        // Get all courses for this tenant
        $courses = $this->courseModel->getCoursesByTenantId($tenantId);
        
        $data = [
            'user_id' => '',
            'course_id' => '',
            'date' => date('Y-m-d'),
            'time_in' => date('H:i'),
            'time_out' => '',
            'notes' => '',
            'members' => $members,
            'courses' => $courses,
            'user_id_err' => '',
            'course_id_err' => '',
            'date_err' => ''
        ];
        
        $this->render('attendance/create', $data);
    }
    
    /**
     * Store new attendance record
     *
     * @return void
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('attendance');
        }
        
        $tenantId = getCurrentTenantId();
        
        // Get all members for this tenant (for form redisplay if needed)
        $members = $this->userModel->getUsersByRoleAndTenant('MEMBER', $tenantId);
        
        // Get all courses for this tenant (for form redisplay if needed)
        $courses = $this->courseModel->getCoursesByTenantId($tenantId);
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Initialize data
        $data = [
            'user_id' => trim($_POST['user_id']),
            'course_id' => isset($_POST['course_id']) ? trim($_POST['course_id']) : null,
            'date' => trim($_POST['date']),
            'time_in' => trim($_POST['time_in']),
            'time_out' => trim($_POST['time_out']),
            'notes' => trim($_POST['notes']),
            'tenant_id' => $tenantId,
            'members' => $members,
            'courses' => $courses,
            'user_id_err' => '',
            'course_id_err' => '',
            'date_err' => ''
        ];
        
        // Validate user_id
        if (empty($data['user_id'])) {
            $data['user_id_err'] = 'Please select a member';
        } else {
            // Check if user exists and belongs to this tenant
            $user = $this->userModel->getUserById($data['user_id']);
            if (!$user || $user['tenant_id'] != $tenantId) {
                $data['user_id_err'] = 'Invalid member selected';
            }
        }
        
        // Validate course_id if provided
        if (!empty($data['course_id'])) {
            // Check if course exists and belongs to this tenant
            $course = $this->courseModel->getCourseById($data['course_id'], $tenantId);
            if (!$course) {
                $data['course_id_err'] = 'Invalid course selected';
            }
        }
        
        // Validate date
        if (empty($data['date'])) {
            $data['date_err'] = 'Please enter date';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
            $data['date_err'] = 'Invalid date format (YYYY-MM-DD)';
        }
        
        // Make sure no errors
        if (empty($data['user_id_err']) && empty($data['course_id_err']) && empty($data['date_err'])) {
            // Create attendance record
            if ($this->attendanceModel->create($data)) {
                flash('attendance_message', 'Attendance recorded successfully');
                redirect('attendance');
            } else {
                die('Something went wrong');
            }
        } else {
            // Load view with errors
            $this->render('attendance/create', $data);
        }
    }
}
