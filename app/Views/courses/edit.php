<?php $title = 'Edit Course - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Edit Course</h2>
        <a href="<?= URLROOT ?>/courses" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
    </div>
    <div class="card-body">
        <form action="<?= URLROOT ?>/courses/update/<?= $id ?>" method="post" data-validate>
            <div class="form-group">
                <label for="name">Course Name</label>
                <input type="text" name="name" id="name" class="form-control <?= !empty($name_err) ? 'is-invalid' : '' ?>" value="<?= $name ?>" required>
                <?php if (!empty($name_err)): ?>
                    <div class="invalid-feedback"><?= $name_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" class="form-control <?= !empty($description_err) ? 'is-invalid' : '' ?>" required><?= $description ?></textarea>
                <?php if (!empty($description_err)): ?>
                    <div class="invalid-feedback"><?= $description_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="instructor">Instructor</label>
                <input type="text" name="instructor" id="instructor" class="form-control <?= !empty($instructor_err) ? 'is-invalid' : '' ?>" value="<?= $instructor ?>" required>
                <?php if (!empty($instructor_err)): ?>
                    <div class="invalid-feedback"><?= $instructor_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="schedule">Schedule</label>
                <input type="text" name="schedule" id="schedule" class="form-control <?= !empty($schedule_err) ? 'is-invalid' : '' ?>" value="<?= $schedule ?>" placeholder="e.g. Mon/Wed/Fri 6:00-7:00 PM" required>
                <?php if (!empty($schedule_err)): ?>
                    <div class="invalid-feedback"><?= $schedule_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="max_capacity">Maximum Capacity</label>
                <input type="number" name="max_capacity" id="max_capacity" class="form-control <?= !empty($max_capacity_err) ? 'is-invalid' : '' ?>" value="<?= $max_capacity ?>" min="1" required>
                <?php if (!empty($max_capacity_err)): ?>
                    <div class="invalid-feedback"><?= $max_capacity_err ?></div>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Course</button>
            </div>
        </form>
    </div>
</div>
