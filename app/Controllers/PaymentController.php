<?php
/**
 * Payment Controller
 */
class PaymentController extends BaseController {
    /**
     * Payment model instance
     *
     * @var Payment
     */
    private $paymentModel;
    
    /**
     * User model instance
     *
     * @var User
     */
    private $userModel;
    
    /**
     * Membership model instance
     *
     * @var Membership
     */
    private $membershipModel;
    
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
        $this->paymentModel = new Payment();
        $this->userModel = new User();
        $this->membershipModel = new Membership();
    }
    
    /**
     * Display payments list
     *
     * @return void
     */
    public function index() {
        $tenantId = getCurrentTenantId();
        
        // Get payments based on role
        if (hasRole('MEMBER')) {
            $payments = $this->paymentModel->getPaymentsByUserId($_SESSION['user_id'], $tenantId);
        } else {
            $payments = $this->paymentModel->getPaymentsByTenantId($tenantId);
        }
        
        $data = [
            'payments' => $payments,
            'isAdmin' => hasRole('SUPER_ADMIN') || hasRole('GYM_ADMIN')
        ];
        
        $this->render('payments/index', $data);
    }
    
    /**
     * Display payment creation form
     *
     * @return void
     */
    public function create() {
        $tenantId = getCurrentTenantId();
        
        // Get all members for this tenant
        $members = $this->userModel->getUsersByRoleAndTenant('MEMBER', $tenantId);
        
        // Get all memberships for this tenant
        $memberships = $this->membershipModel->getMembershipsByTenantId($tenantId);
        
        $data = [
            'user_id' => '',
            'membership_id' => '',
            'amount' => '',
            'payment_date' => date('Y-m-d'),
            'payment_method' => '',
            'notes' => '',
            'members' => $members,
            'memberships' => $memberships,
            'user_id_err' => '',
            'amount_err' => '',
            'payment_date_err' => '',
            'payment_method_err' => ''
        ];
        
        $this->render('payments/create', $data);
    }
    
    /**
     * Store new payment
     *
     * @return void
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('payments');
        }
        
        $tenantId = getCurrentTenantId();
        
        // Get all members for this tenant (for form redisplay if needed)
        $members = $this->userModel->getUsersByRoleAndTenant('MEMBER', $tenantId);
        
        // Get all memberships for this tenant (for form redisplay if needed)
        $memberships = $this->membershipModel->getMembershipsByTenantId($tenantId);
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Initialize data
        $data = [
            'user_id' => trim($_POST['user_id']),
            'membership_id' => isset($_POST['membership_id']) ? trim($_POST['membership_id']) : null,
            'amount' => trim($_POST['amount']),
            'payment_date' => trim($_POST['payment_date']),
            'payment_method' => trim($_POST['payment_method']),
            'notes' => trim($_POST['notes']),
            'tenant_id' => $tenantId,
            'members' => $members,
            'memberships' => $memberships,
            'user_id_err' => '',
            'amount_err' => '',
            'payment_date_err' => '',
            'payment_method_err' => ''
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
        
        // Validate membership_id if provided
        if (!empty($data['membership_id'])) {
            // Check if membership exists, belongs to this tenant, and to this user
            $membership = $this->membershipModel->getMembershipById($data['membership_id'], $tenantId);
            if (!$membership || $membership['user_id'] != $data['user_id']) {
                $data['membership_id_err'] = 'Invalid membership selected';
            }
        }
        
        // Validate amount
        if (empty($data['amount'])) {
            $data['amount_err'] = 'Please enter amount';
        } elseif (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            $data['amount_err'] = 'Amount must be a positive number';
        }
        
        // Validate payment_date
        if (empty($data['payment_date'])) {
            $data['payment_date_err'] = 'Please enter payment date';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['payment_date'])) {
            $data['payment_date_err'] = 'Invalid date format (YYYY-MM-DD)';
        }
        
        // Validate payment_method
        if (empty($data['payment_method'])) {
            $data['payment_method_err'] = 'Please enter payment method';
        }
        
        // Make sure no errors
        if (empty($data['user_id_err']) && empty($data['amount_err']) && 
            empty($data['payment_date_err']) && empty($data['payment_method_err'])) {
            
            // Create payment
            if ($this->paymentModel->create($data)) {
                // If payment associated with a membership, update membership status to active
                if (!empty($data['membership_id'])) {
                    $this->membershipModel->updateStatus($data['membership_id'], 'active');
                }
                
                flash('payment_message', 'Payment recorded successfully');
                redirect('payments');
            } else {
                die('Something went wrong');
            }
        } else {
            // Load view with errors
            $this->render('payments/create', $data);
        }
    }
}
