<?php 
$title = 'Members Report - GymManager';
$includeCharts = true;
?>

<div class="card mb-4">
    <div class="card-header">
        <h2>Members Report</h2>
        <div class="flex gap-2">
            <a href="<?= URLROOT ?>/reports" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
            <button class="btn btn-secondary print-report-btn">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Date Range Filter -->
        <form id="date-range-form" class="mb-4" method="get">
            <div class="flex gap-2 flex-wrap align-items-center">
                <div>
                    <label for="start_date">From:</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?= $startDate ?>">
                </div>
                <div>
                    <label for="end_date">To:</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?= $endDate ?>">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary mt-1">Apply Filter</button>
                </div>
            </div>
        </form>
        
        <!-- Summary Stats -->
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
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?= $newMembers ?></h3>
                    <p class="stat-label">New Members</p>
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
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?= $expiredMemberships ?></h3>
                    <p class="stat-label">Expired Memberships</p>
                </div>
            </div>
        </div>
        
        <!-- Membership Types Chart -->
        <div class="chart-container mt-4">
            <h3 class="chart-title">Membership Types Distribution</h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="membership-chart" 
                    data-labels="<?= htmlspecialchars(json_encode(array_column($membersByType, 'type'))) ?>" 
                    data-values="<?= htmlspecialchars(json_encode(array_column($membersByType, 'count'))) ?>">
                </canvas>
            </div>
        </div>
    </div>
</div>
