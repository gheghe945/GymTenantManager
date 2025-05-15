<?php $title = __('Manage Administrators') . ' - ' . __('GymManager'); ?>

<div class="card mb-4">
    <div class="card-header">
        <h2><?= __('Manage Administrators for') ?> <?= $tenant['name'] ?></h2>
        <a href="<?= URLROOT ?>/tenants" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> <?= __('Back to Gyms') ?>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3><?= __('Current Administrators') ?></h3>
            </div>
            <div class="card-body">
                <?php if (empty($administrators)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <?= __('No administrators assigned to this gym yet.') ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><?= __('Name') ?></th>
                                    <th><?= __('Email') ?></th>
                                    <th><?= __('Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($administrators as $admin): ?>
                                    <tr>
                                        <td><?= $admin['name'] ?></td>
                                        <td><?= $admin['email'] ?></td>
                                        <td>
                                            <form action="<?= URLROOT ?>/tenants/removeAdministrator/<?= $tenant['id'] ?>/<?= $admin['id'] ?>" method="post" class="d-inline">
                                                <button type="submit" class="btn btn-sm btn-danger" data-delete-confirm="<?= __('Are you sure you want to remove this administrator?') ?>">
                                                    <i class="fas fa-user-minus"></i> <?= __('Remove') ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <!-- Assign Existing User Tab -->
        <div class="card mb-4">
            <div class="card-header">
                <h3><?= __('Assign Existing User') ?></h3>
            </div>
            <div class="card-body">
                <?php if (empty($availableUsers)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <?= __('No available users to assign.') ?>
                    </div>
                <?php else: ?>
                    <form action="<?= URLROOT ?>/tenants/assignAdministrator/<?= $tenant['id'] ?>" method="post">
                        <div class="form-group">
                            <label for="user_id"><?= __('Select User') ?></label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value=""><?= __('Choose a user...') ?></option>
                                <?php foreach ($availableUsers as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= $user['name'] ?> (<?= $user['email'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> <?= __('Assign as Administrator') ?>
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Create New Administrator Tab -->
        <div class="card">
            <div class="card-header">
                <h3><?= __('Create New Administrator') ?></h3>
            </div>
            <div class="card-body">
                <form action="<?= URLROOT ?>/tenants/addAdministrator/<?= $tenant['id'] ?>" method="post" data-validate>
                    <div class="form-group">
                        <label for="admin_name"><?= __('Admin Name') ?></label>
                        <input type="text" name="admin_name" id="admin_name" class="form-control <?= !empty($admin_name_err) ? 'is-invalid' : '' ?>" value="<?= $admin_name ?>" required>
                        <?php if (!empty($admin_name_err)): ?>
                            <div class="invalid-feedback"><?= $admin_name_err ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email"><?= __('Admin Email') ?></label>
                        <input type="email" name="admin_email" id="admin_email" class="form-control <?= !empty($admin_email_err) ? 'is-invalid' : '' ?>" value="<?= $admin_email ?>" required>
                        <?php if (!empty($admin_email_err)): ?>
                            <div class="invalid-feedback"><?= $admin_email_err ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password"><?= __('Admin Password') ?></label>
                        <input type="password" name="admin_password" id="admin_password" class="form-control <?= !empty($admin_password_err) ? 'is-invalid' : '' ?>" required>
                        <?php if (!empty($admin_password_err)): ?>
                            <div class="invalid-feedback"><?= $admin_password_err ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_password_confirm"><?= __('Confirm Password') ?></label>
                        <input type="password" name="admin_password_confirm" id="admin_password_confirm" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-user-plus"></i> <?= __('Create Administrator') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>