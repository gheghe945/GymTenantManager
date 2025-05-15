<?php
/**
 * Role-based Access Control Middleware
 */
class RoleMiddleware implements MiddlewareInterface {
    /**
     * Handle the middleware request
     * 
     * Checks if user has appropriate role to access the resource
     *
     * @return void
     */
    public function handle() {
        // Check for specific pages that require admin roles
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        
        // If user is SUPER_ADMIN, allow access to everything
        if (hasRole('SUPER_ADMIN')) {
            return;
        }
        
        // Only SUPER_ADMIN can access tenant management
        if (strpos($uri, 'tenants') === 0) {
            $this->denyAccess();
        }
        
        // For certain routes, check if user has appropriate role
        $adminOnlyRoutes = [
            'users',
            'reports'
        ];
        
        foreach ($adminOnlyRoutes as $route) {
            if (strpos($uri, $route) === 0 && !hasRole('GYM_ADMIN')) {
                $this->denyAccess();
            }
        }
        
        // If user is a member, they can only view their own data
        if (hasRole('MEMBER')) {
            // Allow access to member-accessible pages
            $allowedMemberRoutes = [
                'dashboard',
                'courses',
                'memberships',
                'attendance',
                'payments'
            ];
            
            $isAllowed = false;
            foreach ($allowedMemberRoutes as $route) {
                if (strpos($uri, $route) === 0) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                $this->denyAccess();
            }
            
            // For edit/update/delete operations, check if the member is trying to access their own data
            $resourceIdPattern = '/\/(edit|update|delete)\/(\d+)/';
            if (preg_match($resourceIdPattern, $uri, $matches)) {
                // Deny access to edit/update/delete operations for members
                $this->denyAccess();
            }
        }
    }
    
    /**
     * Deny access and redirect to dashboard with error message
     *
     * @return void
     */
    private function denyAccess() {
        flash('access_denied', 'You do not have permission to access this resource', 'alert alert-danger');
        redirect('dashboard');
        exit;
    }
}
