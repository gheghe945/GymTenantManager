<?php
/**
 * Tenant Middleware to detect and set the current tenant
 */
class TenantMiddleware implements MiddlewareInterface {
    /**
     * Handle the middleware request
     * 
     * Detects the current tenant from subdomain or URL parameter
     * and sets it in the session
     *
     * @return void
     */
    public function handle() {
        // Skip tenant detection for login page
        if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] === '/login') {
            return;
        }
        
        // Skip if user is not logged in
        if (!isLoggedIn()) {
            return;
        }
        
        // Skip tenant detection if user is a SUPER_ADMIN and no tenant is explicitly set
        if (hasRole('SUPER_ADMIN') && !isset($_GET['tenant_id']) && !isset($_SESSION['tenant_id'])) {
            return;
        }
        
        // If tenant is explicitly set in URL, use that
        if (isset($_GET['tenant_id'])) {
            $tenantId = (int)$_GET['tenant_id'];
            
            // Validate that this tenant exists
            $tenantModel = new Tenant();
            $tenant = $tenantModel->getTenantById($tenantId);
            
            if ($tenant) {
                $_SESSION['tenant_id'] = $tenantId;
                $_SESSION['tenant_name'] = $tenant['name'];
            }
            
            return;
        }
        
        // If tenant is already set in session, use that
        if (isset($_SESSION['tenant_id'])) {
            return;
        }
        
        // Try to detect tenant from subdomain
        $host = $_SERVER['HTTP_HOST'] ?? '';
        
        // Extract subdomain from host
        $parts = explode('.', $host);
        if (count($parts) > 2) {
            $subdomain = $parts[0];
            
            // Look up tenant by subdomain
            $tenantModel = new Tenant();
            $tenant = $tenantModel->findTenantBySubdomain($subdomain);
            
            if ($tenant) {
                $_SESSION['tenant_id'] = $tenant['id'];
                $_SESSION['tenant_name'] = $tenant['name'];
                return;
            }
        }
        
        // If user is not SUPER_ADMIN and no tenant is set, redirect to error page
        if (!hasRole('SUPER_ADMIN') && !isset($_SESSION['tenant_id'])) {
            // This is a fallback - should not happen in normal operation
            echo "Tenant not detected. Please access the site through your gym's subdomain.";
            exit;
        }
    }
}
