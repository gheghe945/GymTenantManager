<?php 
$title = 'Attendance Report - GymManager';
$includeCharts = true;
?>

<div class="card mb-4">
    <div class="card-header">
        <h2>Attendance Report</h2>
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
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?= $totalAttendance ?></h3>
                    <p class="stat-label">Total Check-ins</p>
                </div>
            </div>
        </div>
        
        <!-- Daily Attendance Chart -->
        <div class="chart-container mt-4">
            <h3 class="chart-title">Daily Attendance</h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="attendance-chart" 
                    data-labels="<?= htmlspecialchars(json_encode(array_column($dailyAttendance, 'date'))) ?>" 
                    data-values="<?= htmlspecialchars(json_encode(array_column($dailyAttendance, 'count'))) ?>">
                </canvas>
            </div>
        </div>
        
        <!-- Attendance By Day of Week -->
        <div class="chart-container mt-4">
            <h3 class="chart-title">Attendance by Day of Week</h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="attendance-by-day-chart" 
                    data-labels="<?= htmlspecialchars(json_encode(array_column($attendanceByDay, 'day_name'))) ?>" 
                    data-values="<?= htmlspecialchars(json_encode(array_column($attendanceByDay, 'count'))) ?>">
                </canvas>
            </div>
        </div>
        
        <!-- Course Popularity -->
        <div class="chart-container mt-4">
            <h3 class="chart-title">Course Popularity</h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="course-attendance-chart" 
                    data-labels="<?= htmlspecialchars(json_encode(array_column($attendanceByCourse, 'course_name'))) ?>" 
                    data-values="<?= htmlspecialchars(json_encode(array_column($attendanceByCourse, 'count'))) ?>">
                </canvas>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize charts if they exist
        if (typeof Chart !== 'undefined') {
            // Daily attendance chart
            const attendanceChartEl = document.getElementById('attendance-chart');
            if (attendanceChartEl) {
                const ctx = attendanceChartEl.getContext('2d');
                const labels = JSON.parse(attendanceChartEl.getAttribute('data-labels') || '[]');
                const values = JSON.parse(attendanceChartEl.getAttribute('data-values') || '[]');
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Daily Attendance',
                            data: values,
                            backgroundColor: 'rgba(46, 204, 113, 0.2)',
                            borderColor: 'rgba(46, 204, 113, 1)',
                            borderWidth: 2,
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
            
            // Attendance by day of week chart
            const dayChartEl = document.getElementById('attendance-by-day-chart');
            if (dayChartEl) {
                const ctx = dayChartEl.getContext('2d');
                const labels = JSON.parse(dayChartEl.getAttribute('data-labels') || '[]');
                const values = JSON.parse(dayChartEl.getAttribute('data-values') || '[]');
                
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Attendance Count',
                            data: values,
                            backgroundColor: 'rgba(52, 152, 219, 0.7)',
                            borderColor: 'rgba(52, 152, 219, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
            
            // Course popularity chart
            const courseChartEl = document.getElementById('course-attendance-chart');
            if (courseChartEl) {
                const ctx = courseChartEl.getContext('2d');
                const labels = JSON.parse(courseChartEl.getAttribute('data-labels') || '[]');
                const values = JSON.parse(courseChartEl.getAttribute('data-values') || '[]');
                
                new Chart(ctx, {
                    type: 'horizontalBar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Attendance Count',
                            data: values,
                            backgroundColor: 'rgba(155, 89, 182, 0.7)',
                            borderColor: 'rgba(155, 89, 182, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
        }
    });
</script>
