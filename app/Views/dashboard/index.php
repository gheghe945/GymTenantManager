<?php 
    $title = 'Dashboard - GymManager';
    $includeCharts = true;
?>

<div class="card mb-4">
    <div class="card-header">
        <h2>
            <?php if (hasRole('SUPER_ADMIN')): ?>
                System Dashboard
            <?php elseif (hasRole('GYM_ADMIN')): ?>
                Gym Dashboard
            <?php else: ?>
                Member Dashboard
            <?php endif; ?>
        </h2>
    </div>
</div>

<?php if (hasRole('SUPER_ADMIN')): ?>
<!-- SUPER_ADMIN Dashboard -->
<div class="stats-cards">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-value"><?= $totalTenants ?></h3>
            <p class="stat-label">Total Gyms</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-value"><?= $totalUsers ?></h3>
            <p class="stat-label">Total Users</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recent Gyms</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Subdomain</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentTenants)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No gyms found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentTenants as $tenant): ?>
                            <tr>
                                <td><?= $tenant['name'] ?></td>
                                <td><?= $tenant['subdomain'] ?></td>
                                <td><?= $tenant['email'] ?></td>
                                <td>
                                    <?php if ($tenant['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M d, Y', strtotime($tenant['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <a href="<?= URLROOT ?>/tenants" class="btn btn-primary">View All Gyms</a>
    </div>
</div>

<?php elseif (hasRole('GYM_ADMIN')): ?>
<!-- GYM_ADMIN Dashboard -->
<div class="stats-cards">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-value"><?= $totalMembers ?></h3>
            <p class="stat-label">Total Members</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-running"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-value"><?= $totalCourses ?></h3>
            <p class="stat-label">Active Courses</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-id-card"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-value"><?= $activeMemberships ?></h3>
            <p class="stat-label">Active Memberships</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-content">
            <h3 class="stat-value"><?= number_format($revenueThisMonth, 2) ?></h3>
            <p class="stat-label">Revenue This Month</p>
        </div>
    </div>
</div>

<div class="flex gap-3 flex-wrap">
    <div class="chart-container" style="flex: 1; min-width: 300px;">
        <h3 class="chart-title">Today's Attendance: <?= $attendanceToday ?></h3>
        <a href="<?= URLROOT ?>/attendance/create" class="btn btn-primary">Record Attendance</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recent Payments</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentPayments)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No payments found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentPayments as $payment): ?>
                            <tr>
                                <td><?= $payment['user_name'] ?></td>
                                <td><?= number_format($payment['amount'], 2) ?></td>
                                <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                <td><?= $payment['payment_method'] ?></td>
                                <td><?= $payment['membership_type'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <a href="<?= URLROOT ?>/payments" class="btn btn-primary">View All Payments</a>
    </div>
</div>

<?php else: ?>
<!-- MEMBER Dashboard -->
<div class="card mb-4">
    <div class="card-header">
        <h3>My Memberships</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($memberships)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No memberships found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($memberships as $membership): ?>
                            <tr>
                                <td><?= $membership['type'] ?></td>
                                <td><?= date('M d, Y', strtotime($membership['start_date'])) ?></td>
                                <td><?= date('M d, Y', strtotime($membership['end_date'])) ?></td>
                                <td>
                                    <?php if ($membership['status'] === 'active' && strtotime($membership['end_date']) >= time()): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php elseif ($membership['status'] === 'cancelled'): ?>
                                        <span class="badge badge-danger">Cancelled</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Expired</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h3>My Courses</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Instructor</th>
                        <th>Schedule</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr>
                            <td colspan="3" class="text-center">No courses found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?= $course['name'] ?></td>
                                <td><?= $course['instructor'] ?></td>
                                <td><?= $course['schedule'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Recent Attendance</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Course</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendance)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No attendance records found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendance as $record): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($record['date'])) ?></td>
                                <td><?= $record['time_in'] ?></td>
                                <td><?= $record['time_out'] ? $record['time_out'] : '-' ?></td>
                                <td><?= $record['course_name'] ? $record['course_name'] : 'General' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        <a href="<?= URLROOT ?>/attendance" class="btn btn-primary">View All Attendance</a>
    </div>
</div>
<?php endif; ?>
