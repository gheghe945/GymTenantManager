<?php
/**
 * Dashboard Controller
 */
class DashboardController extends BaseController {
    /**
     * Various model instances
     */
    private $userModel;
    private $courseModel;
    private $membershipModel;
    private $attendanceModel;
    private $paymentModel;
    
    /**
     * Middleware configuration
     */
    protected $middleware = [
        'AuthMiddleware' => ['*']
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->userModel = new User();
        $this->courseModel = new Course();
        $this->membershipModel = new Membership();
        $this->attendanceModel = new Attendance();
        $this->paymentModel = new Payment();
    }
    
    /**
     * Display the dashboard index page
     *
     * @return void
     */
    public function index() {
        // Get tenant ID (null for SUPER_ADMIN)
        $tenantId = getCurrentTenantId();
        
        // Different dashboard view based on role
        if (hasRole('SUPER_ADMIN')) {
            // Load Tenant model if needed by SUPER_ADMIN
            $tenantModel = new Tenant();
            
            // Get counts for SUPER_ADMIN dashboard
            $totalTenants = $tenantModel->countTenants();
            $totalUsers = $this->userModel->countUsers();
            
            $data = [
                'totalTenants' => $totalTenants,
                'totalUsers' => $totalUsers,
                'recentTenants' => $tenantModel->getRecentTenants(5)
            ];
            
            $this->render('dashboard/index', $data);
        } else if (hasRole('GYM_ADMIN')) {
            // Data for GYM_ADMIN dashboard
            $totalMembers = $this->userModel->countUsersByRole('MEMBER', $tenantId);
            $totalCourses = $this->courseModel->countCourses($tenantId);
            $activeMemberships = $this->membershipModel->countActiveMemberships($tenantId);
            $recentPayments = $this->paymentModel->getRecentPayments($tenantId, 5);
            $revenueThisMonth = $this->paymentModel->getTotalRevenueForMonth($tenantId, date('m'), date('Y'));
            $attendanceToday = $this->attendanceModel->countAttendanceForDay($tenantId, date('Y-m-d'));
            
            $data = [
                'totalMembers' => $totalMembers,
                'totalCourses' => $totalCourses,
                'activeMemberships' => $activeMemberships,
                'recentPayments' => $recentPayments,
                'revenueThisMonth' => $revenueThisMonth,
                'attendanceToday' => $attendanceToday
            ];
            
            $this->render('dashboard/index', $data);
        } else {
            // Data for MEMBER dashboard
            $userId = $_SESSION['user_id'];
            $userMemberships = $this->membershipModel->getMembershipsByUserId($userId, $tenantId);
            $userAttendance = $this->attendanceModel->getRecentAttendanceByUserId($userId, $tenantId, 10);
            $userCourses = $this->courseModel->getCoursesByUserId($userId, $tenantId);
            
            $data = [
                'memberships' => $userMemberships,
                'attendance' => $userAttendance,
                'courses' => $userCourses
            ];
            
            $this->render('dashboard/index', $data);
        }
    }
}
