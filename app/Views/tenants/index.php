<?php $title = 'Gyms - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Gyms</h2>
        <a href="<?= URLROOT ?>/tenants/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Gym
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table sortable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Subdomain</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tenants)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No gyms found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tenants as $tenant): ?>
                            <tr>
                                <td><?= $tenant['name'] ?></td>
                                <td><?= $tenant['subdomain'] ?></td>
                                <td><?= $tenant['email'] ?></td>
                                <td><?= $tenant['phone'] ?></td>
                                <td>
                                    <?php if ($tenant['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($tenant['created_at'])) ?></td>
                                <td>
                                    <div class="flex gap-1">
                                        <a href="<?= URLROOT ?>/tenants/edit/<?= $tenant['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= URLROOT ?>/tenants/delete/<?= $tenant['id'] ?>" method="post" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-danger" data-delete-confirm="Are you sure you want to delete this gym? This action will delete ALL associated data and CANNOT be undone.">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
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
