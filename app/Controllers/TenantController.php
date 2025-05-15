<?php
/**
 * Tenant Controller
 */
class TenantController extends BaseController {
    /**
     * Tenant model instance
     *
     * @var Tenant
     */
    private $tenantModel;
    
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
        $this->tenantModel = new Tenant();
        $this->userModel = new User();
    }
    
    /**
     * Display tenants list
     *
     * @return void
     */
    public function index() {
        // Only SUPER_ADMIN can access this
        if (!hasRole('SUPER_ADMIN')) {
            redirect('dashboard');
        }
        
        // Get all tenants
        $tenants = $this->tenantModel->getAllTenants();
        
        $data = [
            'tenants' => $tenants
        ];
        
        $this->render('tenants/index', $data);
    }
    
    /**
     * Display tenant creation form
     *
     * @return void
     */
    public function create() {
        // Only SUPER_ADMIN can access this
        if (!hasRole('SUPER_ADMIN')) {
            redirect('dashboard');
        }
        
        $data = [
            'name' => '',
            'subdomain' => '',
            'address' => '',
            'phone' => '',
            'email' => '',
            'is_active' => true,
            'name_err' => '',
            'subdomain_err' => '',
            'email_err' => '',
            'admin_name' => '',
            'admin_email' => '',
            'admin_password' => '',
            'admin_name_err' => '',
            'admin_email_err' => '',
            'admin_password_err' => ''
        ];
        
        $this->render('tenants/create', $data);
    }
    
    /**
     * Store new tenant
     *
     * @return void
     */
    public function store() {
        // Only SUPER_ADMIN can access this
        if (!hasRole('SUPER_ADMIN')) {
            redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tenants');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Initialize data
        $data = [
            'name' => trim($_POST['name']),
            'subdomain' => trim($_POST['subdomain']),
            'address' => trim($_POST['address']),
            'phone' => trim($_POST['phone']),
            'email' => trim($_POST['email']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'admin_name' => isset($_POST['admin_name']) ? trim($_POST['admin_name']) : '',
            'admin_email' => isset($_POST['admin_email']) ? trim($_POST['admin_email']) : '',
            'admin_password' => isset($_POST['admin_password']) ? $_POST['admin_password'] : '',
            'admin_password_confirm' => isset($_POST['admin_password_confirm']) ? $_POST['admin_password_confirm'] : '',
            'name_err' => '',
            'subdomain_err' => '',
            'email_err' => '',
            'admin_name_err' => '',
            'admin_email_err' => '',
            'admin_password_err' => ''
        ];
        
        // Validate name
        if (empty($data['name'])) {
            $data['name_err'] = __('Please enter gym name');
        }
        
        // Validate subdomain
        if (empty($data['subdomain'])) {
            $data['subdomain_err'] = __('Please enter subdomain');
        } elseif (!preg_match('/^[a-z0-9-]+$/', $data['subdomain'])) {
            $data['subdomain_err'] = __('Subdomain can only contain lowercase letters, numbers, and hyphens');
        } elseif ($this->tenantModel->findTenantBySubdomain($data['subdomain'])) {
            $data['subdomain_err'] = __('Subdomain is already taken');
        }
        
        // Validate email
        if (empty($data['email'])) {
            $data['email_err'] = __('Please enter email');
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $data['email_err'] = __('Please enter a valid email');
        }
        
        // Validate admin fields if they're filled
        if (!empty($data['admin_name']) || !empty($data['admin_email']) || !empty($data['admin_password'])) {
            // If any admin field is filled, all are required
            if (empty($data['admin_name'])) {
                $data['admin_name_err'] = __('Please enter admin name');
            }
            
            if (empty($data['admin_email'])) {
                $data['admin_email_err'] = __('Please enter admin email');
            } elseif (!filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)) {
                $data['admin_email_err'] = __('Please enter a valid email');
            } elseif ($this->userModel->findUserByEmail($data['admin_email'])) {
                $data['admin_email_err'] = __('Email is already registered');
            }
            
            if (empty($data['admin_password'])) {
                $data['admin_password_err'] = __('Please enter password');
            } elseif (strlen($data['admin_password']) < 6) {
                $data['admin_password_err'] = __('Password must be at least 6 characters');
            } elseif ($data['admin_password'] !== $data['admin_password_confirm']) {
                $data['admin_password_err'] = __('Passwords do not match');
            }
        }
        
        // Make sure no errors
        if (empty($data['name_err']) && empty($data['subdomain_err']) && empty($data['email_err']) &&
            empty($data['admin_name_err']) && empty($data['admin_email_err']) && empty($data['admin_password_err'])) {
            
            // Use transaction to ensure data consistency
            $db = getDbConnection();
            $db->beginTransaction();
            
            try {
                // Create tenant
                $tenantId = $this->tenantModel->create($data);
                
                if (!$tenantId) {
                    throw new Exception(__('Error creating gym'));
                }
                
                // Create admin user if admin details are provided
                if (!empty($data['admin_name']) && !empty($data['admin_email']) && !empty($data['admin_password'])) {
                    $adminData = [
                        'tenant_id' => $tenantId,
                        'name' => $data['admin_name'],
                        'email' => $data['admin_email'],
                        'password' => password_hash($data['admin_password'], PASSWORD_DEFAULT),
                        'role' => 'GYM_ADMIN'
                    ];
                    
                    if (!$this->userModel->create($adminData)) {
                        throw new Exception(__('Error creating admin user'));
                    }
                }
                
                // Commit transaction
                $db->commit();
                
                flash('tenant_message', __('Gym added successfully with admin user'));
                redirect('tenants');
            } catch (Exception $e) {
                // Rollback transaction on error
                $db->rollBack();
                die($e->getMessage());
            }
        } else {
            // Load view with errors
            $this->render('tenants/create', $data);
        }
    }
    
    /**
     * Display tenant edit form
     *
     * @param int $id Tenant ID
     * @return void
     */
    public function edit($id) {
        // Only SUPER_ADMIN can access this
        if (!hasRole('SUPER_ADMIN')) {
            redirect('dashboard');
        }
        
        // Get tenant by ID
        $tenant = $this->tenantModel->getTenantById($id);
        
        // Check if tenant exists
        if (!$tenant) {
            redirect('tenants');
        }
        
        $data = [
            'id' => $tenant['id'],
            'name' => $tenant['name'],
            'subdomain' => $tenant['subdomain'],
            'address' => $tenant['address'],
            'phone' => $tenant['phone'],
            'email' => $tenant['email'],
            'is_active' => $tenant['is_active'],
            'name_err' => '',
            'subdomain_err' => '',
            'email_err' => ''
        ];
        
        $this->render('tenants/edit', $data);
    }
    
    /**
     * Update tenant
     *
     * @param int $id Tenant ID
     * @return void
     */
    public function update($id) {
        // Only SUPER_ADMIN can access this
        if (!hasRole('SUPER_ADMIN')) {
            redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tenants');
        }
        
        // Get tenant to check existence
        $tenant = $this->tenantModel->getTenantById($id);
        
        // Check if tenant exists
        if (!$tenant) {
            redirect('tenants');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Initialize data
        $data = [
            'id' => $id,
            'name' => trim($_POST['name']),
            'subdomain' => trim($_POST['subdomain']),
            'address' => trim($_POST['address']),
            'phone' => trim($_POST['phone']),
            'email' => trim($_POST['email']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'name_err' => '',
            'subdomain_err' => '',
            'email_err' => ''
        ];
        
        // Validate name
        if (empty($data['name'])) {
            $data['name_err'] = 'Please enter gym name';
        }
        
        // Validate subdomain
        if (empty($data['subdomain'])) {
            $data['subdomain_err'] = 'Please enter subdomain';
        } elseif (!preg_match('/^[a-z0-9-]+$/', $data['subdomain'])) {
            $data['subdomain_err'] = 'Subdomain can only contain lowercase letters, numbers, and hyphens';
        } elseif ($data['subdomain'] !== $tenant['subdomain'] && $this->tenantModel->findTenantBySubdomain($data['subdomain'])) {
            $data['subdomain_err'] = 'Subdomain is already taken';
        }
        
        // Validate email
        if (empty($data['email'])) {
            $data['email_err'] = 'Please enter email';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $data['email_err'] = 'Please enter a valid email';
        }
        
        // Make sure no errors
        if (empty($data['name_err']) && empty($data['subdomain_err']) && empty($data['email_err'])) {
            // Update tenant
            if ($this->tenantModel->update($data)) {
                flash('tenant_message', 'Gym updated successfully');
                redirect('tenants');
            } else {
                die('Something went wrong');
            }
        } else {
            // Load view with errors
            $this->render('tenants/edit', $data);
        }
    }
    
    /**
     * Delete tenant
     *
     * @param int $id Tenant ID
     * @return void
     */
    public function delete($id) {
        // Only SUPER_ADMIN can access this
        if (!hasRole('SUPER_ADMIN')) {
            redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('tenants');
        }
        
        // Get tenant to check existence
        $tenant = $this->tenantModel->getTenantById($id);
        
        // Check if tenant exists
        if (!$tenant) {
            redirect('tenants');
        }
        
        // Delete tenant
        if ($this->tenantModel->delete($id)) {
            flash('tenant_message', 'Gym removed successfully');
        } else {
            flash('tenant_message', 'Something went wrong', 'alert alert-danger');
        }
        
        redirect('tenants');
    }
}
