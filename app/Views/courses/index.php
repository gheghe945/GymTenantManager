<?php $title = 'Courses - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Courses</h2>
        
        <?php if ($isAdmin): ?>
        <a href="<?= URLROOT ?>/courses/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Course
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table sortable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Instructor</th>
                        <th>Schedule</th>
                        <th>Max Capacity</th>
                        <th>Enrolled</th>
                        <?php if ($isAdmin): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="<?= $isAdmin ? 6 : 5 ?>" class="text-center">No courses found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?= $course['name'] ?></td>
                                <td><?= $course['instructor'] ?></td>
                                <td><?= $course['schedule'] ?></td>
                                <td><?= $course['max_capacity'] ?></td>
                                <td>
                                    <?php 
                                        $enrolledCount = isset($course['enrolled_count']) ? $course['enrolled_count'] : 0;
                                        $capacityClass = 'badge-success';
                                        if ($enrolledCount >= $course['max_capacity']) {
                                            $capacityClass = 'badge-danger';
                                        } elseif ($enrolledCount >= $course['max_capacity'] * 0.8) {
                                            $capacityClass = 'badge-warning';
                                        }
                                    ?>
                                    <span class="badge <?= $capacityClass ?>">
                                        <?= $enrolledCount ?> / <?= $course['max_capacity'] ?>
                                    </span>
                                </td>
                                <?php if ($isAdmin): ?>
                                <td>
                                    <div class="flex gap-1">
                                        <a href="<?= URLROOT ?>/courses/edit/<?= $course['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= URLROOT ?>/courses/delete/<?= $course['id'] ?>" method="post" class="d-inline">
                                            <button type="submit" class="btn btn-sm btn-danger" data-delete-confirm="Are you sure you want to delete this course?">
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
