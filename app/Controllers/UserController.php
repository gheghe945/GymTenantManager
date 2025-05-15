<?php
/**
 * User Controller
 */
class UserController extends BaseController {
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
        'RoleMiddleware' => ['*']
    ];
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Display users list
     *
     * @return void
     */
    public function index() {
        // Check role-based permissions
        if (!hasRole('SUPER_ADMIN') && !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        $tenantId = getCurrentTenantId();
        
        // Get users based on role
        if (hasRole('SUPER_ADMIN')) {
            $users = $this->userModel->getAllUsers();
        } else {
            $users = $this->userModel->getUsersByTenantId($tenantId);
        }
        
        $data = [
            'users' => $users,
            'isSuperAdmin' => hasRole('SUPER_ADMIN')
        ];
        
        $this->render('users/index', $data);
    }
    
    /**
     * Display user creation form
     *
     * @return void
     */
    public function create() {
        // Check role-based permissions
        if (!hasRole('SUPER_ADMIN') && !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        // Get tenants list if SUPER_ADMIN
        $tenants = [];
        if (hasRole('SUPER_ADMIN')) {
            $tenantModel = new Tenant();
            $tenants = $tenantModel->getAllTenants();
        }
        
        $data = [
            'name' => '',
            'email' => '',
            'role' => 'MEMBER', // Default role
            'tenant_id' => getCurrentTenantId(), // Default tenant
            'name_err' => '',
            'email_err' => '',
            'password_err' => '',
            'role_err' => '',
            'tenant_id_err' => '',
            'tenants' => $tenants,
            'isSuperAdmin' => hasRole('SUPER_ADMIN')
        ];
        
        $this->render('users/create', $data);
    }
    
    /**
     * Store new user
     *
     * @return void
     */
    public function store() {
        // Check role-based permissions
        if (!hasRole('SUPER_ADMIN') && !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('users');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Get tenants list if SUPER_ADMIN
        $tenants = [];
        if (hasRole('SUPER_ADMIN')) {
            $tenantModel = new Tenant();
            $tenants = $tenantModel->getAllTenants();
        }
        
        // Initialize data
        $data = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'password' => trim($_POST['password']),
            'role' => hasRole('SUPER_ADMIN') ? trim($_POST['role']) : 'MEMBER',
            'tenant_id' => hasRole('SUPER_ADMIN') ? (int)$_POST['tenant_id'] : getCurrentTenantId(),
            'name_err' => '',
            'email_err' => '',
            'password_err' => '',
            'role_err' => '',
            'tenant_id_err' => '',
            'tenants' => $tenants,
            'isSuperAdmin' => hasRole('SUPER_ADMIN')
        ];
        
        // Validate name
        if (empty($data['name'])) {
            $data['name_err'] = 'Please enter name';
        }
        
        // Validate email
        if (empty($data['email'])) {
            $data['email_err'] = 'Please enter email';
        } elseif ($this->userModel->findUserByEmail($data['email'])) {
            $data['email_err'] = 'Email is already taken';
        }
        
        // Validate password
        if (empty($data['password'])) {
            $data['password_err'] = 'Please enter password';
        } elseif (strlen($data['password']) < 6) {
            $data['password_err'] = 'Password must be at least 6 characters';
        }
        
        // Validate role
        if (empty($data['role'])) {
            $data['role_err'] = 'Please select role';
        } elseif (!in_array($data['role'], ['SUPER_ADMIN', 'GYM_ADMIN', 'MEMBER'])) {
            $data['role_err'] = 'Invalid role';
        }
        
        // Super Admin can only create SUPER_ADMIN users
        if (hasRole('SUPER_ADMIN') && $data['role'] === 'SUPER_ADMIN') {
            $data['tenant_id'] = null; // SUPER_ADMIN has no tenant
        } 
        // Validate tenant_id
        elseif (hasRole('SUPER_ADMIN') && empty($data['tenant_id'])) {
            $data['tenant_id_err'] = 'Please select a gym';
        }
        
        // Make sure no errors
        if (empty($data['name_err']) && empty($data['email_err']) && 
            empty($data['password_err']) && empty($data['role_err']) && 
            empty($data['tenant_id_err'])) {
            
            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Create user
            if ($this->userModel->create($data)) {
                flash('user_message', 'User created successfully');
                redirect('users');
            } else {
                die('Something went wrong');
            }
        } else {
            // Load view with errors
            $this->render('users/create', $data);
        }
    }
    
    /**
     * Display user edit form
     *
     * @param int $id User ID
     * @return void
     */
    public function edit($id) {
        // Check role-based permissions
        if (!hasRole('SUPER_ADMIN') && !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        // Get user by ID
        $user = $this->userModel->getUserById($id);
        
        // Check if user exists
        if (!$user) {
            redirect('users');
        }
        
        // Check if GYM_ADMIN is trying to edit user from another tenant
        if (hasRole('GYM_ADMIN') && $user['tenant_id'] != getCurrentTenantId()) {
            flash('user_message', 'Unauthorized action', 'alert alert-danger');
            redirect('users');
        }
        
        // Get tenants list if SUPER_ADMIN
        $tenants = [];
        if (hasRole('SUPER_ADMIN')) {
            $tenantModel = new Tenant();
            $tenants = $tenantModel->getAllTenants();
        }
        
        $data = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'tenant_id' => $user['tenant_id'],
            'name_err' => '',
            'email_err' => '',
            'password_err' => '',
            'role_err' => '',
            'tenant_id_err' => '',
            'tenants' => $tenants,
            'isSuperAdmin' => hasRole('SUPER_ADMIN')
        ];
        
        $this->render('users/edit', $data);
    }
    
    /**
     * Update user
     *
     * @param int $id User ID
     * @return void
     */
    public function update($id) {
        // Check role-based permissions
        if (!hasRole('SUPER_ADMIN') && !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('users');
        }
        
        // Get existing user
        $user = $this->userModel->getUserById($id);
        
        // Check if user exists
        if (!$user) {
            redirect('users');
        }
        
        // Check if GYM_ADMIN is trying to edit user from another tenant
        if (hasRole('GYM_ADMIN') && $user['tenant_id'] != getCurrentTenantId()) {
            flash('user_message', 'Unauthorized action', 'alert alert-danger');
            redirect('users');
        }
        
        // Sanitize POST data
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        
        // Get tenants list if SUPER_ADMIN
        $tenants = [];
        if (hasRole('SUPER_ADMIN')) {
            $tenantModel = new Tenant();
            $tenants = $tenantModel->getAllTenants();
        }
        
        // Initialize data
        $data = [
            'id' => $id,
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'password' => trim($_POST['password']),
            'role' => hasRole('SUPER_ADMIN') ? trim($_POST['role']) : $user['role'],
            'tenant_id' => hasRole('SUPER_ADMIN') ? (int)$_POST['tenant_id'] : getCurrentTenantId(),
            'name_err' => '',
            'email_err' => '',
            'password_err' => '',
            'role_err' => '',
            'tenant_id_err' => '',
            'tenants' => $tenants,
            'isSuperAdmin' => hasRole('SUPER_ADMIN')
        ];
        
        // Validate name
        if (empty($data['name'])) {
            $data['name_err'] = 'Please enter name';
        }
        
        // Validate email
        if (empty($data['email'])) {
            $data['email_err'] = 'Please enter email';
        } elseif ($data['email'] !== $user['email'] && $this->userModel->findUserByEmail($data['email'])) {
            $data['email_err'] = 'Email is already taken';
        }
        
        // Validate password only if provided (optional in update)
        if (!empty($data['password']) && strlen($data['password']) < 6) {
            $data['password_err'] = 'Password must be at least 6 characters';
        }
        
        // Validate role
        if (empty($data['role'])) {
            $data['role_err'] = 'Please select role';
        } elseif (!in_array($data['role'], ['SUPER_ADMIN', 'GYM_ADMIN', 'MEMBER'])) {
            $data['role_err'] = 'Invalid role';
        }
        
        // Super Admin can only create SUPER_ADMIN users
        if (hasRole('SUPER_ADMIN') && $data['role'] === 'SUPER_ADMIN') {
            $data['tenant_id'] = null; // SUPER_ADMIN has no tenant
        } 
        // Validate tenant_id
        elseif (hasRole('SUPER_ADMIN') && empty($data['tenant_id'])) {
            $data['tenant_id_err'] = 'Please select a gym';
        }
        
        // Make sure no errors
        if (empty($data['name_err']) && empty($data['email_err']) && 
            empty($data['password_err']) && empty($data['role_err']) && 
            empty($data['tenant_id_err'])) {
            
            // Hash password if provided
            if (!empty($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            } else {
                // Keep old password
                unset($data['password']);
            }
            
            // Update user
            if ($this->userModel->update($data)) {
                flash('user_message', 'User updated successfully');
                redirect('users');
            } else {
                die('Something went wrong');
            }
        } else {
            // Load view with errors
            $this->render('users/edit', $data);
        }
    }
    
    /**
     * Delete user
     *
     * @param int $id User ID
     * @return void
     */
    public function delete($id) {
        // Check role-based permissions
        if (!hasRole('SUPER_ADMIN') && !hasRole('GYM_ADMIN')) {
            redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('users');
        }
        
        // Get user by ID
        $user = $this->userModel->getUserById($id);
        
        // Check if user exists
        if (!$user) {
            redirect('users');
        }
        
        // Check if GYM_ADMIN is trying to delete user from another tenant
        if (hasRole('GYM_ADMIN') && $user['tenant_id'] != getCurrentTenantId()) {
            flash('user_message', 'Unauthorized action', 'alert alert-danger');
            redirect('users');
        }
        
        // Prevent deleting yourself
        if ($user['id'] == $_SESSION['user_id']) {
            flash('user_message', 'You cannot delete your own account', 'alert alert-danger');
            redirect('users');
        }
        
        // Delete user
        if ($this->userModel->delete($id)) {
            flash('user_message', 'User removed successfully');
        } else {
            flash('user_message', 'Something went wrong', 'alert alert-danger');
        }
        
        redirect('users');
    }
}
