<?php
/**
 * Membership Controller
 */
class MembershipController extends BaseController {
    /**
     * Membership model instance
     *
     * @var Membership
     */
    private $membershipModel;
    
    /**
     * User model instance
     *
     * @var User
     */
    private $userModel;
    
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
        $this->membershipModel = new Membership();
        $this->userModel = new User();
    }
    
    /**
     * Display memberships list
     *
     * @return void
     */
    public function index() {
        $tenantId = getCurrentTenantId();
        
        // Get memberships by tenant or by user if MEMBER
        if (hasRole('MEMBER')) {
            $memberships = $this->membershipModel->getMembershipsByUserId($_SESSION['user_id'], $tenantId);
        } else {
            $memberships = $this->membershipModel->getMembershipsByTenantId($tenantId);
        }
        
        $data = [
            'memberships' => $memberships,
            'isAdmin' => hasRole('SUPER_ADMIN') || hasRole('GYM_ADMIN')
        ];
        
        $this->render('memberships/index', $data);
    }
    
    /**
     * Display membership creation form
     *
     * @return void
     */
    public function create() {
        $tenantId = getCurrentTenantId();
        
        // Get all members for this tenant
        $members = $this->userModel->getUsersByRoleAndTenant('MEMBER', $tenantId);
        
        $data = [
            'user_id' => '',
            'type' => '',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 month')),
            'price' => '',
            'status' => 'active',
            'notes' => '',
            'members' => $members,
            'user_id_err' => '',
            'type_err' => '',
            'start_date_err' => '',
            'end_date_err' => '',
            'price_err' => '',
            'status_err' => ''
        ];
        
        $this->render('memberships/create', $data);
    }
    
    /**
     * Store new membership
     *
     * @return void
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('memberships');
        }
        
        $tenantId = getCurrentTenantId();
        
        // Get all members for this tenant (for form redisplay if needed)
        $members = $this->userModel->getUsersByRoleAndTenant('MEMBER', $tenantId);
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Initialize data
        $data = [
            'user_id' => trim($_POST['user_id']),
            'type' => trim($_POST['type']),
            'start_date' => trim($_POST['start_date']),
            'end_date' => trim($_POST['end_date']),
            'price' => trim($_POST['price']),
            'status' => trim($_POST['status']),
            'notes' => trim($_POST['notes']),
            'tenant_id' => $tenantId,
            'members' => $members,
            'user_id_err' => '',
            'type_err' => '',
            'start_date_err' => '',
            'end_date_err' => '',
            'price_err' => '',
            'status_err' => ''
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
        
        // Validate type
        if (empty($data['type'])) {
            $data['type_err'] = 'Please enter membership type';
        }
        
        // Validate start_date
        if (empty($data['start_date'])) {
            $data['start_date_err'] = 'Please enter start date';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['start_date'])) {
            $data['start_date_err'] = 'Invalid date format (YYYY-MM-DD)';
        }
        
        // Validate end_date
        if (empty($data['end_date'])) {
            $data['end_date_err'] = 'Please enter end date';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['end_date'])) {
            $data['end_date_err'] = 'Invalid date format (YYYY-MM-DD)';
        } elseif (!empty($data['start_date']) && $data['end_date'] < $data['start_date']) {
            $data['end_date_err'] = 'End date must be after start date';
        }
        
        // Validate price
        if (empty($data['price'])) {
            $data['price_err'] = 'Please enter price';
        } elseif (!is_numeric($data['price']) || $data['price'] < 0) {
            $data['price_err'] = 'Price must be a non-negative number';
        }
        
        // Validate status
        if (empty($data['status'])) {
            $data['status_err'] = 'Please select status';
        } elseif (!in_array($data['status'], ['active', 'expired', 'cancelled'])) {
            $data['status_err'] = 'Invalid status';
        }
        
        // Make sure no errors
        if (empty($data['user_id_err']) && empty($data['type_err']) && 
            empty($data['start_date_err']) && empty($data['end_date_err']) && 
            empty($data['price_err']) && empty($data['status_err'])) {
            
            // Create membership
            if ($this->membershipModel->create($data)) {
                flash('membership_message', 'Membership created successfully');
                redirect('memberships');
            } else {
                die('Something went wrong');
            }
        } else {
            // Load view with errors
            $this->render('memberships/create', $data);
        }
    }
    
    /**
     * Display membership edit form
     *
     * @param int $id Membership ID
     * @return void
     */
    public function edit($id) {
        $tenantId = getCurrentTenantId();
        
        // Get membership by ID with tenant check
        $membership = $this->membershipModel->getMembershipById($id, $tenantId);
        
        // Check if membership exists and belongs to current tenant
        if (!$membership) {
            flash('membership_message', 'Membership not found or access denied', 'alert alert-danger');
            redirect('memberships');
        }
        
        // Get all members for this tenant
        $members = $this->userModel->getUsersByRoleAndTenant('MEMBER', $tenantId);
        
        $data = [
            'id' => $membership['id'],
            'user_id' => $membership['user_id'],
            'type' => $membership['type'],
            'start_date' => $membership['start_date'],
            'end_date' => $membership['end_date'],
            'price' => $membership['price'],
            'status' => $membership['status'],
            'notes' => $membership['notes'],
            'members' => $members,
            'user_id_err' => '',
            'type_err' => '',
            'start_date_err' => '',
            'end_date_err' => '',
            'price_err' => '',
            'status_err' => ''
        ];
        
        $this->render('memberships/edit', $data);
    }
    
    /**
     * Update membership
     *
     * @param int $id Membership ID
     * @return void
     */
    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('memberships');
        }
        
        $tenantId = getCurrentTenantId();
        
        // Get membership to check ownership
        $membership = $this->membershipModel->getMembershipById($id, $tenantId);
        
        // Check if membership exists and belongs to current tenant
        if (!$membership) {
            flash('membership_message', 'Membership not found or access denied', 'alert alert-danger');
            redirect('memberships');
        }
        
        // Get all members for this tenant (for form redisplay if needed)
        $members = $this->userModel->getUsersByRoleAndTenant('MEMBER', $tenantId);
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Initialize data
        $data = [
            'id' => $id,
            'user_id' => trim($_POST['user_id']),
            'type' => trim($_POST['type']),
            'start_date' => trim($_POST['start_date']),
            'end_date' => trim($_POST['end_date']),
            'price' => trim($_POST['price']),
            'status' => trim($_POST['status']),
            'notes' => trim($_POST['notes']),
            'tenant_id' => $tenantId,
            'members' => $members,
            'user_id_err' => '',
            'type_err' => '',
            'start_date_err' => '',
            'end_date_err' => '',
            'price_err' => '',
            'status_err' => ''
        ];
        
        // Validation (same as create)
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
        
        // Validate type
        if (empty($data['type'])) {
            $data['type_err'] = 'Please enter membership type';
        }
        
        // Validate start_date
        if (empty($data['start_date'])) {
            $data['start_date_err'] = 'Please enter start date';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['start_date'])) {
            $data['start_date_err'] = 'Invalid date format (YYYY-MM-DD)';
        }
        
        // Validate end_date
        if (empty($data['end_date'])) {
            $data['end_date_err'] = 'Please enter end date';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['end_date'])) {
            $data['end_date_err'] = 'Invalid date format (YYYY-MM-DD)';
        } elseif (!empty($data['start_date']) && $data['end_date'] < $data['start_date']) {
            $data['end_date_err'] = 'End date must be after start date';
        }
        
        // Validate price
        if (empty($data['price'])) {
            $data['price_err'] = 'Please enter price';
        } elseif (!is_numeric($data['price']) || $data['price'] < 0) {
            $data['price_err'] = 'Price must be a non-negative number';
        }
        
        // Validate status
        if (empty($data['status'])) {
            $data['status_err'] = 'Please select status';
        } elseif (!in_array($data['status'], ['active', 'expired', 'cancelled'])) {
            $data['status_err'] = 'Invalid status';
        }
        
        // Make sure no errors
        if (empty($data['user_id_err']) && empty($data['type_err']) && 
            empty($data['start_date_err']) && empty($data['end_date_err']) && 
            empty($data['price_err']) && empty($data['status_err'])) {
            
            // Update membership
            if ($this->membershipModel->update($data)) {
                flash('membership_message', 'Membership updated successfully');
                redirect('memberships');
            } else {
                die('Something went wrong');
            }
        } else {
            // Load view with errors
            $this->render('memberships/edit', $data);
        }
    }
    
    /**
     * Delete membership
     *
     * @param int $id Membership ID
     * @return void
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('memberships');
        }
        
        $tenantId = getCurrentTenantId();
        
        // Get membership to check ownership
        $membership = $this->membershipModel->getMembershipById($id, $tenantId);
        
        // Check if membership exists and belongs to current tenant
        if (!$membership) {
            flash('membership_message', 'Membership not found or access denied', 'alert alert-danger');
            redirect('memberships');
        }
        
        // Delete membership
        if ($this->membershipModel->delete($id, $tenantId)) {
            flash('membership_message', 'Membership removed successfully');
        } else {
            flash('membership_message', 'Something went wrong', 'alert alert-danger');
        }
        
        redirect('memberships');
    }
}
