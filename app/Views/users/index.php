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
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="<?= hasRole('SUPER_ADMIN') ? 7 : 6 ?>" class="text-center">No users found</td>
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
                                <td>
                                    <?php if (isset($user['is_active'])): ?>
                                        <?php if ($user['is_active']): ?>
                                            <span class="badge bg-success">Attivo</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Disabilitato</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-success">Attivo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <?php if ((hasRole('SUPER_ADMIN')) || 
                                                 (hasRole('GYM_ADMIN') && $user['role'] !== 'SUPER_ADMIN')): ?>
                                            <a href="<?= URLROOT ?>/users/edit/<?= $user['id'] ?>" class="btn btn-sm btn-primary" title="Modifica">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <!-- Reset Password -->
                                                <a href="<?= URLROOT ?>/users/resetPassword/<?= $user['id'] ?>" class="btn btn-sm btn-info" title="Reset Password">
                                                    <i class="fas fa-key"></i>
                                                </a>
                                                
                                                <!-- Enable/Disable User -->
                                                <?php if (isset($user['is_active']) && $user['is_active']): ?>
                                                    <form action="<?= URLROOT ?>/users/disable/<?= $user['id'] ?>" method="post" class="d-inline">
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Disabilita Utente">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form action="<?= URLROOT ?>/users/enable/<?= $user['id'] ?>" method="post" class="d-inline">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Abilita Utente">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <!-- Delete User -->
                                                <form action="<?= URLROOT ?>/users/delete/<?= $user['id'] ?>" method="post" class="d-inline">
                                                    <button type="submit" class="btn btn-sm btn-danger" data-delete-confirm="Sei sicuro di voler eliminare questo utente?" title="Elimina">
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
