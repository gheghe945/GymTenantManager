<?php $title = 'Edit Gym - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Edit Gym</h2>
        <a href="<?= URLROOT ?>/tenants" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Gyms
        </a>
    </div>
    <div class="card-body">
        <form action="<?= URLROOT ?>/tenants/update/<?= $id ?>" method="post" data-validate>
            <div class="form-group">
                <label for="name">Gym Name</label>
                <input type="text" name="name" id="name" class="form-control <?= !empty($name_err) ? 'is-invalid' : '' ?>" value="<?= $name ?>" required>
                <?php if (!empty($name_err)): ?>
                    <div class="invalid-feedback"><?= $name_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="subdomain">Subdomain</label>
                <div class="flex align-items-center gap-1">
                    <input type="text" name="subdomain" id="subdomain" class="form-control <?= !empty($subdomain_err) ? 'is-invalid' : '' ?>" value="<?= $subdomain ?>" required>
                    <span>.yourdomain.com</span>
                </div>
                <small class="text-muted">Only lowercase letters, numbers, and hyphens allowed</small>
                <?php if (!empty($subdomain_err)): ?>
                    <div class="invalid-feedback"><?= $subdomain_err ?></div>
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
                <label for="phone">Phone</label>
                <input type="text" name="phone" id="phone" class="form-control" value="<?= $phone ?>">
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea name="address" id="address" class="form-control"><?= $address ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-check-label">
                    <input type="checkbox" name="is_active" class="form-check-input" <?= $is_active ? 'checked' : '' ?>> 
                    Active
                </label>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Gym</button>
            </div>
        </form>
    </div>
</div>
