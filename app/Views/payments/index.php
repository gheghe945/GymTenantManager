<?php $title = 'Payments - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Payments</h2>
        
        <?php if ($isAdmin): ?>
        <a href="<?= URLROOT ?>/payments/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Payment
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
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Membership Type</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="<?= $isAdmin ? 6 : 5 ?>" class="text-center">No payments found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <?php if ($isAdmin): ?>
                                <td><?= $payment['user_name'] ?></td>
                                <?php endif; ?>
                                <td><?= number_format($payment['amount'], 2) ?></td>
                                <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                <td><?= $payment['payment_method'] ?></td>
                                <td><?= $payment['membership_type'] ?></td>
                                <td><?= $payment['notes'] ? $payment['notes'] : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
