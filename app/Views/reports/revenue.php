<?php 
$title = 'Revenue Report - GymManager';
$includeCharts = true;
?>

<div class="card mb-4">
    <div class="card-header">
        <h2>Revenue Report</h2>
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
                <div class="stat-icon orange">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <h3 class="stat-value"><?= number_format($totalRevenue, 2) ?></h3>
                    <p class="stat-label">Total Revenue</p>
                </div>
            </div>
        </div>
        
        <!-- Monthly Revenue Chart -->
        <div class="chart-container mt-4">
            <h3 class="chart-title">Monthly Revenue</h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="revenue-chart" 
                    data-labels="<?= htmlspecialchars(json_encode(array_column($monthlyRevenue, 'month'))) ?>" 
                    data-values="<?= htmlspecialchars(json_encode(array_column($monthlyRevenue, 'total'))) ?>">
                </canvas>
            </div>
        </div>
        
        <!-- Revenue by Payment Method -->
        <div class="chart-container mt-4">
            <h3 class="chart-title">Revenue by Payment Method</h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="payment-method-chart" 
                    data-labels="<?= htmlspecialchars(json_encode(array_column($revenueByMethod, 'payment_method'))) ?>" 
                    data-values="<?= htmlspecialchars(json_encode(array_column($revenueByMethod, 'total'))) ?>">
                </canvas>
            </div>
        </div>
        
        <!-- Revenue by Membership Type -->
        <div class="chart-container mt-4">
            <h3 class="chart-title">Revenue by Membership Type</h3>
            <div style="position: relative; height: 300px; width: 100%;">
                <canvas id="membership-revenue-chart" 
                    data-labels="<?= htmlspecialchars(json_encode(array_column($revenueByMembershipType, 'membership_type'))) ?>" 
                    data-values="<?= htmlspecialchars(json_encode(array_column($revenueByMembershipType, 'total'))) ?>">
                </canvas>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize charts if they exist
        if (typeof Chart !== 'undefined') {
            // Monthly revenue chart
            const revenueChartEl = document.getElementById('revenue-chart');
            if (revenueChartEl) {
                const ctx = revenueChartEl.getContext('2d');
                const labels = JSON.parse(revenueChartEl.getAttribute('data-labels') || '[]');
                const values = JSON.parse(revenueChartEl.getAttribute('data-values') || '[]');
                
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Revenue',
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
                                    callback: function(value) {
                                        return '$' + value;
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Payment method chart
            const methodChartEl = document.getElementById('payment-method-chart');
            if (methodChartEl) {
                const ctx = methodChartEl.getContext('2d');
                const labels = JSON.parse(methodChartEl.getAttribute('data-labels') || '[]');
                const values = JSON.parse(methodChartEl.getAttribute('data-values') || '[]');
                
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: [
                                'rgba(52, 152, 219, 0.7)',
                                'rgba(46, 204, 113, 0.7)',
                                'rgba(155, 89, 182, 0.7)',
                                'rgba(243, 156, 18, 0.7)',
                                'rgba(231, 76, 60, 0.7)',
                                'rgba(149, 165, 166, 0.7)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        return label + ': $' + value.toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Membership type revenue chart
            const membershipRevenueChartEl = document.getElementById('membership-revenue-chart');
            if (membershipRevenueChartEl) {
                const ctx = membershipRevenueChartEl.getContext('2d');
                const labels = JSON.parse(membershipRevenueChartEl.getAttribute('data-labels') || '[]');
                const values = JSON.parse(membershipRevenueChartEl.getAttribute('data-values') || '[]');
                
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: [
                                'rgba(46, 204, 113, 0.7)',
                                'rgba(52, 152, 219, 0.7)',
                                'rgba(155, 89, 182, 0.7)',
                                'rgba(243, 156, 18, 0.7)',
                                'rgba(231, 76, 60, 0.7)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.parsed || 0;
                                        return label + ': $' + value.toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    });
</script>
