<?php $title = 'Memberships - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Memberships</h2>
        
        <?php if ($isAdmin): ?>
        <a href="<?= URLROOT ?>/memberships/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Membership
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table sortable">
                <thead>
                    <tr>
                        <?php if ($isAdmin): ?>
                        <th>Member</th>
                        <?php endif; ?>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Price</th>
                        <th>Status</th>
                        <?php if ($isAdmin): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($memberships)): ?>
                        <tr>
                            <td colspan="<?= $isAdmin ? 7 : 5 ?>" class="text-center">No memberships found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($memberships as $membership): ?>
                            <tr>
                                <?php if ($isAdmin): ?>
                                <td><?= $membership['user_name'] ?></td>
                                <?php endif; ?>
                                <td><?= $membership['type'] ?></td>
                                <td><?= date('M d, Y', strtotime($membership['start_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($membership['end_date'])) ?></td>
                                <td><?= number_format($membership['price'], 2) ?></td>
                                <td>
                                    <?php 
                                        $status = $membership['status'];
                                        $statusBadgeClass = 'badge-secondary';
                                        
                                        if ($status === 'active') {
                                            if (strtotime($membership['end_date']) < time()) {
                                                $status = 'expired';
                                                $statusBadgeClass = 'badge-warning';
                                            } else {
                                                $statusBadgeClass = 'badge-success';
                                            }
                                        } elseif ($status === 'cancelled') {
                                            $statusBadgeClass = 'badge-danger';
                                        } elseif ($status === 'expired') {
                                            $statusBadgeClass = 'badge-warning';
                                        }
                                    ?>
                                    <span class="badge <?= $statusBadgeClass ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                </td>
                                <?php if ($isAdmin): ?>
                                <td>
                                    <div class="flex gap-1">
                                        <a href="<?= URLROOT ?>/memberships/edit/<?= $membership['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= URLROOT ?>/memberships/delete/<?= $membership['id'] ?>" method="post" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-danger" data-delete-confirm="Are you sure you want to delete this membership?">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
