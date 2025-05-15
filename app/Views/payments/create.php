<?php $title = 'Add Payment - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Add Payment</h2>
        <a href="<?= URLROOT ?>/payments" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Payments
        </a>
    </div>
    <div class="card-body">
        <form action="<?= URLROOT ?>/payments/store" method="post" data-validate>
            <div class="form-group">
                <label for="user_id">Member</label>
                <select name="user_id" id="user_id" class="form-control <?= !empty($user_id_err) ? 'is-invalid' : '' ?>" required>
                    <option value="">Select Member</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?= $member['id'] ?>" <?= $user_id == $member['id'] ? 'selected' : '' ?>>
                            <?= $member['name'] ?> (<?= $member['email'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($user_id_err)): ?>
                    <div class="invalid-feedback"><?= $user_id_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="membership_id">Membership (Optional)</label>
                <select name="membership_id" id="membership_id" class="form-control">
                    <option value="">Select Membership</option>
                    <?php if (!empty($memberships)): ?>
                        <?php foreach ($memberships as $membership): ?>
                            <?php if (isset($user_id) && $membership['user_id'] == $user_id): ?>
                                <option value="<?= $membership['id'] ?>" <?= isset($membership_id) && $membership_id == $membership['id'] ? 'selected' : '' ?>>
                                    <?= $membership['type'] ?> (<?= date('M d, Y', strtotime($membership['start_date'])) ?> - <?= date('M d, Y', strtotime($membership['end_date'])) ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="amount">Amount</label>
                <input type="number" name="amount" id="amount" class="form-control <?= !empty($amount_err) ? 'is-invalid' : '' ?>" value="<?= $amount ?>" step="0.01" min="0" required>
                <?php if (!empty($amount_err)): ?>
                    <div class="invalid-feedback"><?= $amount_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="payment_date">Payment Date</label>
                <input type="date" name="payment_date" id="payment_date" class="form-control <?= !empty($payment_date_err) ? 'is-invalid' : '' ?>" value="<?= $payment_date ?>" required>
                <?php if (!empty($payment_date_err)): ?>
                    <div class="invalid-feedback"><?= $payment_date_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select name="payment_method" id="payment_method" class="form-control <?= !empty($payment_method_err) ? 'is-invalid' : '' ?>" required>
                    <option value="">Select Payment Method</option>
                    <option value="Cash" <?= $payment_method === 'Cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="Credit Card" <?= $payment_method === 'Credit Card' ? 'selected' : '' ?>>Credit Card</option>
                    <option value="Debit Card" <?= $payment_method === 'Debit Card' ? 'selected' : '' ?>>Debit Card</option>
                    <option value="Bank Transfer" <?= $payment_method === 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                    <option value="PayPal" <?= $payment_method === 'PayPal' ? 'selected' : '' ?>>PayPal</option>
                    <option value="Other" <?= $payment_method === 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
                <?php if (!empty($payment_method_err)): ?>
                    <div class="invalid-feedback"><?= $payment_method_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="notes">Notes (Optional)</label>
                <textarea name="notes" id="notes" class="form-control"><?= $notes ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Record Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default date to today if not set
        const dateInput = document.getElementById('payment_date');
        if (!dateInput.value) {
            dateInput.value = new Date().toISOString().substr(0, 10);
        }
        
        // Handle user selection to filter memberships
        const userSelect = document.getElementById('user_id');
        const membershipSelect = document.getElementById('membership_id');
        
        userSelect.addEventListener('change', function() {
            // Clear and disable membership select if no user selected
            if (!this.value) {
                membershipSelect.innerHTML = '<option value="">Select Membership</option>';
                return;
            }
            
            // Filter memberships for selected user
            const userId = this.value;
            membershipSelect.innerHTML = '<option value="">Select Membership</option>';
            
            // Find memberships for this user
            <?php if (!empty($memberships)): ?>
                <?php foreach ($memberships as $membership): ?>
                if (<?= $membership['user_id'] ?> == userId) {
                    const option = document.createElement('option');
                    option.value = <?= $membership['id'] ?>;
                    option.textContent = '<?= $membership['type'] ?> (<?= date('M d, Y', strtotime($membership['start_date'])) ?> - <?= date('M d, Y', strtotime($membership['end_date'])) ?>)';
                    membershipSelect.appendChild(option);
                }
                <?php endforeach; ?>
            <?php endif; ?>
        });
    });
</script>
