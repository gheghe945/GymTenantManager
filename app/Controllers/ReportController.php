<?php
/**
 * Report Controller
 */
class ReportController extends BaseController {
    /**
     * Various model instances
     */
    private $userModel;
    private $membershipModel;
    private $attendanceModel;
    private $paymentModel;
    
    /**
     * Middleware configuration
     */
    protected $middleware = [
        'AuthMiddleware' => ['*'],
        'RoleMiddleware' => ['*']
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->userModel = new User();
        $this->membershipModel = new Membership();
        $this->attendanceModel = new Attendance();
        $this->paymentModel = new Payment();
    }
    
    /**
     * Display report index page
     *
     * @return void
     */
    public function index() {
        // Only admins can access reports
        if (!hasRole('SUPER_ADMIN') && !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        $data = [
            'title' => 'Reports Dashboard'
        ];
        
        $this->render('reports/index', $data);
    }
    
    /**
     * Display members report
     *
     * @return void
     */
    public function members() {
        // Only admins can access reports
        if (!hasRole('SUPER_ADMIN') && !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        $tenantId = getCurrentTenantId();
        
        // Filter options
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        
        // Get members data
        $totalMembers = $this->userModel->countUsersByRole('MEMBER', $tenantId);
        $newMembers = $this->userModel->countNewMembers($tenantId, $startDate, $endDate);
        $activeMemberships = $this->membershipModel->countActiveMemberships($tenantId);
        $expiredMemberships = $this->membershipModel->countExpiredMemberships($tenantId);
        $membersByType = $this->membershipModel->countMembershipsByType($tenantId);
        
        $data = [
            'title' => 'Members Report',
            'totalMembers' => $totalMembers,
            'newMembers' => $newMembers,
            'activeMemberships' => $activeMemberships,
            'expiredMemberships' => $expiredMemberships,
            'membersByType' => $membersByType,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
        
        $this->render('reports/members', $data);
    }
    
    /**
     * Display attendance report
     *
     * @return void
     */
    public function attendance() {
        // Only admins can access reports
        if (!hasRole('SUPER_ADMIN') && !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        $tenantId = getCurrentTenantId();
        
        // Filter options
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        
        // Get attendance data
        $totalAttendance = $this->attendanceModel->countAttendance($tenantId, $startDate, $endDate);
        $dailyAttendance = $this->attendanceModel->getDailyAttendanceCounts($tenantId, $startDate, $endDate);
        $attendanceByDay = $this->attendanceModel->getAttendanceByDayOfWeek($tenantId, $startDate, $endDate);
        $attendanceByCourse = $this->attendanceModel->getAttendanceCountByCourse($tenantId, $startDate, $endDate);
        
        $data = [
            'title' => 'Attendance Report',
            'totalAttendance' => $totalAttendance,
            'dailyAttendance' => $dailyAttendance,
            'attendanceByDay' => $attendanceByDay,
            'attendanceByCourse' => $attendanceByCourse,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
        
        $this->render('reports/attendance', $data);
    }
    
    /**
     * Display revenue report
     *
     * @return void
     */
    public function revenue() {
        // Only admins can access reports
        if (!hasRole('SUPER_ADMIN') && !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        $tenantId = getCurrentTenantId();
        
        // Filter options
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // First day of current month
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t'); // Last day of current month
        
        // Get revenue data
        $totalRevenue = $this->paymentModel->getTotalRevenue($tenantId, $startDate, $endDate);
        $monthlyRevenue = $this->paymentModel->getMonthlyRevenue($tenantId, $startDate, $endDate);
        $revenueByMethod = $this->paymentModel->getRevenueByPaymentMethod($tenantId, $startDate, $endDate);
        $revenueByMembershipType = $this->paymentModel->getRevenueByMembershipType($tenantId, $startDate, $endDate);
        
        $data = [
            'title' => 'Revenue Report',
            'totalRevenue' => $totalRevenue,
            'monthlyRevenue' => $monthlyRevenue,
            'revenueByMethod' => $revenueByMethod,
            'revenueByMembershipType' => $revenueByMembershipType,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
        
        $this->render('reports/revenue', $data);
    }
}
