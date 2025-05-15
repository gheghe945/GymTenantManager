<?php $title = 'Users - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Users</h2>
        
        <?php if (hasRole('SUPER_ADMIN') || hasRole('GYM_ADMIN')): ?>
        <a href="<?= URLROOT ?>/users/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add User
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table sortable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <?php if (hasRole('SUPER_ADMIN')): ?>
                        <th>Gym</th>
                        <?php endif; ?>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="<?= hasRole('SUPER_ADMIN') ? 6 : 5 ?>" class="text-center">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= $user['name'] ?></td>
                                <td><?= $user['email'] ?></td>
                                <td>
                                    <?php if ($user['role'] === 'SUPER_ADMIN'): ?>
                                        <span class="badge badge-danger">Super Admin</span>
                                    <?php elseif ($user['role'] === 'GYM_ADMIN'): ?>
                                        <span class="badge badge-primary">Gym Admin</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Member</span>
                                    <?php endif; ?>
                                </td>
                                <?php if (hasRole('SUPER_ADMIN')): ?>
                                <td>
                                    <?php if ($user['role'] === 'SUPER_ADMIN'): ?>
                                        <span class="text-muted">N/A</span>
                                    <?php else: ?>
                                        <?= $user['tenant_name'] ?? '-' ?>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="flex gap-1">
                                        <?php if ((hasRole('SUPER_ADMIN')) || 
                                                 (hasRole('GYM_ADMIN') && $user['role'] !== 'SUPER_ADMIN')): ?>
                                            <a href="<?= URLROOT ?>/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <form action="<?= URLROOT ?>/users/delete/<?= $user['id'] ?>" method="post" class="d-inline">
                                                    <button type="submit" class="btn btn-sm btn-danger" data-delete-confirm="Are you sure you want to delete this user?">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
