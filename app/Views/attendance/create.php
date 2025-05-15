<?php $title = 'Record Attendance - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Record Attendance</h2>
        <a href="<?= URLROOT ?>/attendance" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Attendance
        </a>
    </div>
    <div class="card-body">
        <form action="<?= URLROOT ?>/attendance/store" method="post" data-validate>
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
                <label for="course_id">Course (Optional)</label>
                <select name="course_id" id="course_id" class="form-control <?= !empty($course_id_err) ? 'is-invalid' : '' ?>">
                    <option value="">General Attendance</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id'] ?>" <?= $course_id == $course['id'] ? 'selected' : '' ?>>
                            <?= $course['name'] ?> (<?= $course['schedule'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($course_id_err)): ?>
                    <div class="invalid-feedback"><?= $course_id_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" name="date" id="date" class="form-control <?= !empty($date_err) ? 'is-invalid' : '' ?>" value="<?= $date ?>" required>
                <?php if (!empty($date_err)): ?>
                    <div class="invalid-feedback"><?= $date_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="time_in">Time In</label>
                <input type="time" name="time_in" id="time_in" class="form-control" value="<?= $time_in ?>">
            </div>
            
            <div class="form-group">
                <label for="time_out">Time Out (Optional)</label>
                <input type="time" name="time_out" id="time_out" class="form-control" value="<?= $time_out ?>">
            </div>
            
            <div class="form-group">
                <label for="notes">Notes (Optional)</label>
                <textarea name="notes" id="notes" class="form-control"><?= $notes ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Record Attendance</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default date to today if not set
        const dateInput = document.getElementById('date');
        if (!dateInput.value) {
            dateInput.value = new Date().toISOString().substr(0, 10);
        }
        
        // Set default time to current time if not set
        const timeInInput = document.getElementById('time_in');
        if (!timeInInput.value) {
            const now = new Date();
            timeInInput.value = now.getHours().toString().padStart(2, '0') + ':' + 
                                now.getMinutes().toString().padStart(2, '0');
        }
    });
</script>
