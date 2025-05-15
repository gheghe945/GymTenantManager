<?php $title = 'Add User - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Add User</h2>
        <a href="<?= URLROOT ?>/users" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
    <div class="card-body">
        <form action="<?= URLROOT ?>/users/store" method="post" data-validate>
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control <?= !empty($name_err) ? 'is-invalid' : '' ?>" value="<?= $name ?>" required>
                <?php if (!empty($name_err)): ?>
                    <div class="invalid-feedback"><?= $name_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control <?= !empty($email_err) ? 'is-invalid' : '' ?>" value="<?= $email ?>" required>
                <?php if (!empty($email_err)): ?>
                    <div class="invalid-feedback"><?= $email_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control <?= !empty($password_err) ? 'is-invalid' : '' ?>" required data-validate-strength>
                <?php if (!empty($password_err)): ?>
                    <div class="invalid-feedback"><?= $password_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role" class="form-control <?= !empty($role_err) ? 'is-invalid' : '' ?>" <?= $isSuperAdmin ? '' : 'disabled' ?> required>
                    <?php if ($isSuperAdmin): ?>
                        <option value="SUPER_ADMIN" <?= $role === 'SUPER_ADMIN' ? 'selected' : '' ?>>Super Admin</option>
                        <option value="GYM_ADMIN" <?= $role === 'GYM_ADMIN' ? 'selected' : '' ?>>Gym Admin</option>
                        <option value="MEMBER" <?= $role === 'MEMBER' ? 'selected' : '' ?>>Member</option>
                    <?php else: ?>
                        <option value="MEMBER" selected>Member</option>
                    <?php endif; ?>
                </select>
                <?php if (!empty($role_err)): ?>
                    <div class="invalid-feedback"><?= $role_err ?></div>
                <?php endif; ?>
            </div>
            
            <?php if ($isSuperAdmin): ?>
            <div class="form-group" id="tenant-group">
                <label for="tenant_id">Gym</label>
                <select name="tenant_id" id="tenant_id" class="form-control <?= !empty($tenant_id_err) ? 'is-invalid' : '' ?>" required>
                    <option value="">Select Gym</option>
                    <?php foreach ($tenants as $tenant): ?>
                        <option value="<?= $tenant['id'] ?>" <?= $tenant_id == $tenant['id'] ? 'selected' : '' ?>>
                            <?= $tenant['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($tenant_id_err)): ?>
                    <div class="invalid-feedback"><?= $tenant_id_err ?></div>
                <?php endif; ?>
            </div>
            <?php else: ?>
                <input type="hidden" name="role" value="MEMBER">
                <input type="hidden" name="tenant_id" value="<?= $tenant_id ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

<?php if ($isSuperAdmin): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role');
        const tenantGroup = document.getElementById('tenant-group');
        
        roleSelect.addEventListener('change', function() {
            if (this.value === 'SUPER_ADMIN') {
                tenantGroup.style.display = 'none';
            } else {
                tenantGroup.style.display = 'block';
            }
        });
        
        // Initial check
        if (roleSelect.value === 'SUPER_ADMIN') {
            tenantGroup.style.display = 'none';
        } else {
            tenantGroup.style.display = 'block';
        }
    });
</script>
<?php endif; ?>
