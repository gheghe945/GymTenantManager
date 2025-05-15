<?php
/**
 * Course Controller
 */
class CourseController extends BaseController {
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
        'RoleMiddleware' => ['create', 'store', 'edit', 'update', 'delete']
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->courseModel = new Course();
    }
    
    /**
     * Display courses list
     *
     * @return void
     */
    public function index() {
        $tenantId = getCurrentTenantId();
        
        // Get courses by tenant
        $courses = $this->courseModel->getCoursesByTenantId($tenantId);
        
        $data = [
            'courses' => $courses,
            'isAdmin' => hasRole('SUPER_ADMIN') || hasRole('GYM_ADMIN')
        ];
        
        $this->render('courses/index', $data);
    }
    
    /**
     * Display course creation form
     *
     * @return void
     */
    public function create() {
        $data = [
            'name' => '',
            'description' => '',
            'schedule' => '',
            'instructor' => '',
            'max_capacity' => '',
            'name_err' => '',
            'description_err' => '',
            'schedule_err' => '',
            'instructor_err' => '',
            'max_capacity_err' => ''
        ];
        
        $this->render('courses/create', $data);
    }
    
    /**
     * Store new course
     *
     * @return void
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('courses');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Initialize data
        $data = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'schedule' => trim($_POST['schedule']),
            'instructor' => trim($_POST['instructor']),
            'max_capacity' => trim($_POST['max_capacity']),
            'tenant_id' => getCurrentTenantId(),
            'name_err' => '',
            'description_err' => '',
            'schedule_err' => '',
            'instructor_err' => '',
            'max_capacity_err' => ''
        ];
        
        // Validate name
        if (empty($data['name'])) {
            $data['name_err'] = 'Please enter course name';
        }
        
        // Validate description
        if (empty($data['description'])) {
            $data['description_err'] = 'Please enter description';
        }
        
        // Validate schedule
        if (empty($data['schedule'])) {
            $data['schedule_err'] = 'Please enter schedule';
        }
        
        // Validate instructor
        if (empty($data['instructor'])) {
            $data['instructor_err'] = 'Please enter instructor name';
        }
        
        // Validate max_capacity
        if (empty($data['max_capacity'])) {
            $data['max_capacity_err'] = 'Please enter maximum capacity';
        } elseif (!is_numeric($data['max_capacity']) || $data['max_capacity'] <= 0) {
            $data['max_capacity_err'] = 'Maximum capacity must be a positive number';
        }
        
        // Make sure no errors
        if (empty($data['name_err']) && empty($data['description_err']) && 
            empty($data['schedule_err']) && empty($data['instructor_err']) && 
            empty($data['max_capacity_err'])) {
            
            // Create course
            if ($this->courseModel->create($data)) {
                flash('course_message', 'Course created successfully');
                redirect('courses');
            } else {
                die('Something went wrong');
            }
        } else {
            // Load view with errors
            $this->render('courses/create', $data);
        }
    }
    
    /**
     * Display course edit form
     *
     * @param int $id Course ID
     * @return void
     */
    public function edit($id) {
        // Get course by ID with tenant check
        $course = $this->courseModel->getCourseById($id, getCurrentTenantId());
        
        // Check if course exists and belongs to current tenant
        if (!$course) {
            flash('course_message', 'Course not found or access denied', 'alert alert-danger');
            redirect('courses');
        }
        
        $data = [
            'id' => $course['id'],
            'name' => $course['name'],
            'description' => $course['description'],
            'schedule' => $course['schedule'],
            'instructor' => $course['instructor'],
            'max_capacity' => $course['max_capacity'],
            'name_err' => '',
            'description_err' => '',
            'schedule_err' => '',
            'instructor_err' => '',
            'max_capacity_err' => ''
        ];
        
        $this->render('courses/edit', $data);
    }
    
    /**
     * Update course
     *
     * @param int $id Course ID
     * @return void
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('courses');
        }
        
        // Get course to check ownership
        $course = $this->courseModel->getCourseById($id, getCurrentTenantId());
        
        // Check if course exists and belongs to current tenant
        if (!$course) {
            flash('course_message', 'Course not found or access denied', 'alert alert-danger');
            redirect('courses');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Initialize data
        $data = [
            'id' => $id,
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
            'schedule' => trim($_POST['schedule']),
            'instructor' => trim($_POST['instructor']),
            'max_capacity' => trim($_POST['max_capacity']),
            'tenant_id' => getCurrentTenantId(),
            'name_err' => '',
            'description_err' => '',
            'schedule_err' => '',
            'instructor_err' => '',
            'max_capacity_err' => ''
        ];
        
        // Validate name
        if (empty($data['name'])) {
            $data['name_err'] = 'Please enter course name';
        }
        
        // Validate description
        if (empty($data['description'])) {
            $data['description_err'] = 'Please enter description';
        }
        
        // Validate schedule
        if (empty($data['schedule'])) {
            $data['schedule_err'] = 'Please enter schedule';
        }
        
        // Validate instructor
        if (empty($data['instructor'])) {
            $data['instructor_err'] = 'Please enter instructor name';
        }
        
        // Validate max_capacity
        if (empty($data['max_capacity'])) {
            $data['max_capacity_err'] = 'Please enter maximum capacity';
        } elseif (!is_numeric($data['max_capacity']) || $data['max_capacity'] <= 0) {
            $data['max_capacity_err'] = 'Maximum capacity must be a positive number';
        }
        
        // Make sure no errors
        if (empty($data['name_err']) && empty($data['description_err']) && 
            empty($data['schedule_err']) && empty($data['instructor_err']) && 
            empty($data['max_capacity_err'])) {
            
            // Update course
            if ($this->courseModel->update($data)) {
                flash('course_message', 'Course updated successfully');
                redirect('courses');
            } else {
                die('Something went wrong');
            }
        } else {
            // Load view with errors
            $this->render('courses/edit', $data);
        }
    }
    
    /**
     * Delete course
     *
     * @param int $id Course ID
     * @return void
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('courses');
        }
        
        // Get course to check ownership
        $course = $this->courseModel->getCourseById($id, getCurrentTenantId());
        
        // Check if course exists and belongs to current tenant
        if (!$course) {
            flash('course_message', 'Course not found or access denied', 'alert alert-danger');
            redirect('courses');
        }
        
        // Delete course
        if ($this->courseModel->delete($id, getCurrentTenantId())) {
            flash('course_message', 'Course removed successfully');
        } else {
            flash('course_message', 'Something went wrong', 'alert alert-danger');
        }
        
        redirect('courses');
    }
}
