<?php $title = __('Create New Gym') . ' - ' . __('GymManager'); ?>

<div class="card">
    <div class="card-header">
        <h2><?= __('Create New Gym') ?></h2>
        <a href="<?= URLROOT ?>/tenants" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> <?= __('Back to Gyms') ?>
        </a>
    </div>
    <div class="card-body">
        <form action="<?= URLROOT ?>/tenants/store" method="post" data-validate>
            <div class="form-tabs mb-4">
                <div class="tab active" data-tab="gym-info"><?= __('Gym Information') ?></div>
                <div class="tab" data-tab="admin-info"><?= __('Admin Account') ?></div>
            </div>

            <div class="tab-content" id="gym-info">
                <div class="form-group">
                    <label for="name"><?= __('Gym Name') ?></label>
                    <input type="text" name="name" id="name" class="form-control <?= !empty($name_err) ? 'is-invalid' : '' ?>" value="<?= $name ?>" required>
                    <?php if (!empty($name_err)): ?>
                        <div class="invalid-feedback"><?= __($name_err) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="subdomain"><?= __('Subdomain') ?></label>
                    <div class="flex align-items-center gap-1">
                        <input type="text" name="subdomain" id="subdomain" class="form-control <?= !empty($subdomain_err) ? 'is-invalid' : '' ?>" value="<?= $subdomain ?>" required>
                        <span>.yourdomain.com</span>
                    </div>
                    <small class="text-muted"><?= __('Only lowercase letters, numbers, and hyphens allowed') ?></small>
                    <?php if (!empty($subdomain_err)): ?>
                        <div class="invalid-feedback"><?= __($subdomain_err) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="email"><?= __('Email') ?></label>
                    <input type="email" name="email" id="email" class="form-control <?= !empty($email_err) ? 'is-invalid' : '' ?>" value="<?= $email ?>" required>
                    <?php if (!empty($email_err)): ?>
                        <div class="invalid-feedback"><?= __($email_err) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="phone"><?= __('Phone') ?></label>
                    <input type="text" name="phone" id="phone" class="form-control" value="<?= $phone ?>">
                </div>
                
                <div class="form-group">
                    <label for="address"><?= __('Address') ?></label>
                    <textarea name="address" id="address" class="form-control"><?= $address ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-check-label">
                        <input type="checkbox" name="is_active" class="form-check-input" <?= $is_active ? 'checked' : '' ?>> 
                        <?= __('Is Active') ?>
                    </label>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-primary" id="next-tab"><?= __('Next') ?> <i class="fas fa-arrow-right"></i></button>
                </div>
            </div>
            
            <div class="tab-content" id="admin-info" style="display: none;">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> <?= __('Create an admin user for this gym. This user will have permissions to manage all aspects of this gym.') ?>
                </div>
                
                <div class="form-group">
                    <label for="admin_name"><?= __('Admin Name') ?></label>
                    <input type="text" name="admin_name" id="admin_name" class="form-control <?= !empty($admin_name_err) ? 'is-invalid' : '' ?>" value="<?= $admin_name ?? '' ?>">
                    <?php if (!empty($admin_name_err)): ?>
                        <div class="invalid-feedback"><?= __($admin_name_err) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="admin_email"><?= __('Admin Email') ?></label>
                    <input type="email" name="admin_email" id="admin_email" class="form-control <?= !empty($admin_email_err) ? 'is-invalid' : '' ?>" value="<?= $admin_email ?? '' ?>">
                    <?php if (!empty($admin_email_err)): ?>
                        <div class="invalid-feedback"><?= __($admin_email_err) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="admin_password"><?= __('Admin Password') ?></label>
                    <input type="password" name="admin_password" id="admin_password" class="form-control <?= !empty($admin_password_err) ? 'is-invalid' : '' ?>">
                    <?php if (!empty($admin_password_err)): ?>
                        <div class="invalid-feedback"><?= __($admin_password_err) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="admin_password_confirm"><?= __('Confirm Password') ?></label>
                    <input type="password" name="admin_password_confirm" id="admin_password_confirm" class="form-control">
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" id="prev-tab"><i class="fas fa-arrow-left"></i> <?= __('Back') ?></button>
                    <button type="submit" class="btn btn-success"><?= __('Create Gym with Admin') ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form tab navigation
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        const nextTabBtn = document.getElementById('next-tab');
        const prevTabBtn = document.getElementById('prev-tab');
        
        // Switch tab function
        function switchTab(tabId) {
            tabs.forEach(tab => {
                if (tab.getAttribute('data-tab') === tabId) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });
            
            tabContents.forEach(content => {
                if (content.id === tabId) {
                    content.style.display = 'block';
                } else {
                    content.style.display = 'none';
                }
            });
        }
        
        // Tab click events
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                switchTab(this.getAttribute('data-tab'));
            });
        });
        
        // Next button click
        if (nextTabBtn) {
            nextTabBtn.addEventListener('click', function() {
                switchTab('admin-info');
            });
        }
        
        // Previous button click
        if (prevTabBtn) {
            prevTabBtn.addEventListener('click', function() {
                switchTab('gym-info');
            });
        }
        
        // Auto-populate admin email based on gym email
        const gymEmailInput = document.getElementById('email');
        const adminEmailInput = document.getElementById('admin_email');
        
        if (gymEmailInput && adminEmailInput) {
            gymEmailInput.addEventListener('input', function() {
                if (!adminEmailInput.value || adminEmailInput._wasAutoGenerated) {
                    adminEmailInput.value = this.value;
                    adminEmailInput._wasAutoGenerated = true;
                }
            });
            
            adminEmailInput.addEventListener('input', function() {
                this._wasAutoGenerated = false;
            });
        }
        
        // Auto-generate subdomain from name
        const nameInput = document.getElementById('name');
        const subdomainInput = document.getElementById('subdomain');
        
        if (nameInput && subdomainInput) {
            nameInput.addEventListener('input', function() {
                // Only auto-generate if subdomain is empty or was auto-generated before
                if (!subdomainInput.value || subdomainInput._wasAutoGenerated) {
                    const subdomain = this.value.toLowerCase()
                        .replace(/\s+/g, '-')           // Replace spaces with hyphens
                        .replace(/[^a-z0-9-]/g, '')     // Remove non-alphanumeric chars except hyphens
                        .replace(/-+/g, '-')            // Replace multiple hyphens with single one
                        .replace(/^-+|-+$/g, '');       // Trim hyphens from start and end
                    
                    subdomainInput.value = subdomain;
                    subdomainInput._wasAutoGenerated = true;
                }
            });
            
            // When user manually changes subdomain, turn off auto-generation
            subdomainInput.addEventListener('input', function() {
                this._wasAutoGenerated = false;
            });
        }
    });
</script>
