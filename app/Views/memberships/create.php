<?php $title = 'Add Membership - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Add Membership</h2>
        <a href="<?= URLROOT ?>/memberships" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Memberships
        </a>
    </div>
    <div class="card-body">
        <form action="<?= URLROOT ?>/memberships/store" method="post" data-validate>
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
                <label for="type">Membership Type</label>
                <select name="type" id="type" class="form-control <?= !empty($type_err) ? 'is-invalid' : '' ?>" required>
                    <option value="">Select Type</option>
                    <option value="Monthly" <?= $type === 'Monthly' ? 'selected' : '' ?>>Monthly</option>
                    <option value="Quarterly" <?= $type === 'Quarterly' ? 'selected' : '' ?>>Quarterly</option>
                    <option value="Semi-Annual" <?= $type === 'Semi-Annual' ? 'selected' : '' ?>>Semi-Annual</option>
                    <option value="Annual" <?= $type === 'Annual' ? 'selected' : '' ?>>Annual</option>
                    <option value="Premium" <?= $type === 'Premium' ? 'selected' : '' ?>>Premium</option>
                </select>
                <?php if (!empty($type_err)): ?>
                    <div class="invalid-feedback"><?= $type_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control <?= !empty($start_date_err) ? 'is-invalid' : '' ?>" value="<?= $start_date ?>" required>
                <?php if (!empty($start_date_err)): ?>
                    <div class="invalid-feedback"><?= $start_date_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-control <?= !empty($end_date_err) ? 'is-invalid' : '' ?>" value="<?= $end_date ?>" required>
                <?php if (!empty($end_date_err)): ?>
                    <div class="invalid-feedback"><?= $end_date_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" name="price" id="price" class="form-control <?= !empty($price_err) ? 'is-invalid' : '' ?>" value="<?= $price ?>" step="0.01" min="0" required>
                <?php if (!empty($price_err)): ?>
                    <div class="invalid-feedback"><?= $price_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control <?= !empty($status_err) ? 'is-invalid' : '' ?>" required>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="expired" <?= $status === 'expired' ? 'selected' : '' ?>>Expired</option>
                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <?php if (!empty($status_err)): ?>
                    <div class="invalid-feedback"><?= $status_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" class="form-control"><?= $notes ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Create Membership</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('type');
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const priceInput = document.getElementById('price');
        
        // Set default membership durations and prices
        typeSelect.addEventListener('change', function() {
            const startDate = new Date(startDateInput.value);
            let endDate = new Date(startDate);
            let price = 0;
            
            switch(this.value) {
                case 'Monthly':
                    endDate.setMonth(endDate.getMonth() + 1);
                    price = 50;
                    break;
                case 'Quarterly':
                    endDate.setMonth(endDate.getMonth() + 3);
                    price = 135;
                    break;
                case 'Semi-Annual':
                    endDate.setMonth(endDate.getMonth() + 6);
                    price = 250;
                    break;
                case 'Annual':
                    endDate.setFullYear(endDate.getFullYear() + 1);
                    price = 450;
                    break;
                case 'Premium':
                    endDate.setFullYear(endDate.getFullYear() + 1);
                    price = 600;
                    break;
            }
            
            if (this.value) {
                endDateInput.value = endDate.toISOString().substr(0, 10);
                priceInput.value = price;
            }
        });
        
        // Update end date when start date changes
        startDateInput.addEventListener('change', function() {
            if (typeSelect.value) {
                typeSelect.dispatchEvent(new Event('change'));
            }
        });
    });
</script>
