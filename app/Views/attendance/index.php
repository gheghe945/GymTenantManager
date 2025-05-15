<?php $title = 'Attendance - GymManager'; ?>

<div class="card">
    <div class="card-header">
        <h2>Attendance Records</h2>
        
        <?php if (hasRole('SUPER_ADMIN') || hasRole('GYM_ADMIN')): ?>
        <a href="<?= URLROOT ?>/attendance/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Record Attendance
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (hasRole('SUPER_ADMIN') || hasRole('GYM_ADMIN')): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h4>Quick Check-In</h4>
                <form id="quick-check-in-form" action="<?= URLROOT ?>/attendance/store" method="post">
                    <div class="flex gap-2 flex-wrap">
                        <div style="flex: 2; min-width: 200px;">
                            <select id="quick-user-id" name="user_id" class="form-control" required>
                                <option value="">Select Member</option>
                                <?php 
                                    // Get the User model to fetch members
                                    $userModel = new User();
                                    $members = $userModel->getUsersByRoleAndTenant('MEMBER', getCurrentTenantId());
                                    
                                    foreach ($members as $member):
                                ?>
                                <option value="<?= $member['id'] ?>"><?= $member['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <input type="hidden" name="date" value="<?= date('Y-m-d') ?>">
                        <input type="hidden" name="time_in" value="<?= date('H:i') ?>">
                        <button type="submit" class="btn btn-primary">Check In</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table sortable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <?php if (hasRole('SUPER_ADMIN') || hasRole('GYM_ADMIN')): ?>
                        <th>Member</th>
                        <?php endif; ?>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Course</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendance)): ?>
                        <tr>
                            <td colspan="<?= (hasRole('SUPER_ADMIN') || hasRole('GYM_ADMIN')) ? 6 : 5 ?>" class="text-center">No attendance records found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendance as $record): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($record['date'])) ?></td>
                                <?php if (hasRole('SUPER_ADMIN') || hasRole('GYM_ADMIN')): ?>
                                <td><?= $record['user_name'] ?></td>
                                <?php endif; ?>
                                <td><?= $record['time_in'] ?></td>
                                <td><?= $record['time_out'] ? $record['time_out'] : '-' ?></td>
                                <td><?= $record['course_name'] ? $record['course_name'] : 'General' ?></td>
                                <td><?= $record['notes'] ? $record['notes'] : '-' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
