<?php $title = __('Gyms') . ' - ' . __('GymManager'); ?>

<div class="card">
    <div class="card-header">
        <h2><?= __('Gyms') ?></h2>
        <a href="<?= URLROOT ?>/tenants/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> <?= __('Add Gym') ?>
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table sortable">
                <thead>
                    <tr>
                        <th><?= __('Name') ?></th>
                        <th><?= __('Subdomain') ?></th>
                        <th><?= __('Email') ?></th>
                        <th><?= __('Phone') ?></th>
                        <th><?= __('Status') ?></th>
                        <th><?= __('Created') ?></th>
                        <th><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tenants)): ?>
                        <tr>
                            <td colspan="7" class="text-center"><?= __('No gyms found') ?></td>
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
                                        <span class="badge badge-success"><?= __('Active') ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><?= __('Inactive') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d/m/Y', strtotime($tenant['created_at'])) ?></td>
                                <td>
                                    <div class="flex gap-1">
                                        <a href="<?= URLROOT ?>/tenants/edit/<?= $tenant['id'] ?>" class="btn btn-sm btn-primary" title="<?= __('Edit') ?>">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= URLROOT ?>/tenants/administrators/<?= $tenant['id'] ?>" class="btn btn-sm btn-info" title="<?= __('Manage Administrators') ?>">
                                            <i class="fas fa-user-shield"></i>
                                        </a>
                                        <form action="<?= URLROOT ?>/tenants/delete/<?= $tenant['id'] ?>" method="post" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-danger" title="<?= __('Delete') ?>" data-delete-confirm="<?= __('Are you sure you want to delete this gym? This action will delete ALL associated data and CANNOT be undone.') ?>">
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
