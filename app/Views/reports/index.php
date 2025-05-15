<?php 
$title = 'Reports - GymManager';
$includeCharts = true;
?>

<div class="card mb-4">
    <div class="card-header">
        <h2>Reports</h2>
    </div>
    <div class="card-body">
        <p>Select a report to view detailed statistics and analysis for your gym.</p>
        
        <div class="stats-cards">
            <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= URLROOT ?>/reports/members'">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">Members Report</h3>
                    <p class="stat-label">Membership statistics and trends</p>
                </div>
            </div>
            
            <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= URLROOT ?>/reports/attendance'">
                <div class="stat-icon green">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">Attendance Report</h3>
                    <p class="stat-label">Check-in patterns and class popularity</p>
                </div>
            </div>
            
            <div class="stat-card" style="cursor: pointer;" onclick="window.location.href='<?= URLROOT ?>/reports/revenue'">
                <div class="stat-icon orange">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value">Revenue Report</h3>
                    <p class="stat-label">Financial analysis and revenue streams</p>
                </div>
            </div>
        </div>
    </div>
</div>
