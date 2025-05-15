/**
 * Main JavaScript for GymManager Application
 * Handles UI interactions, AJAX calls, and dynamic components
 */

// Execute when DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeSidebar();
    initializeTooltips();
    initializeDropdowns();
    initializeFormValidation();
    initializeCharts();
    initializeDataTables();
    initializeDeleteConfirmation();
    
    // Initialize module-specific functionality
    const currentPath = window.location.pathname;
    
    if (currentPath.includes('attendance')) {
        initializeAttendance();
    }
    
    if (currentPath.includes('reports')) {
        initializeReports();
    }
});

/**
 * Initialize sidebar toggle functionality
 */
function initializeSidebar() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarClose = document.getElementById('sidebar-close');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
            
            // On mobile, show/hide sidebar instead of collapsing
            if (window.innerWidth < 992) {
                sidebar.classList.toggle('show');
            }
        });
    }
    
    if (sidebarClose) {
        sidebarClose.addEventListener('click', function() {
            sidebar.classList.remove('show');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 992 && 
            sidebar && 
            sidebar.classList.contains('show') && 
            !sidebar.contains(event.target) && 
            event.target !== sidebarToggle) {
            sidebar.classList.remove('show');
        }
    });
    
    // Set active menu item based on current page
    const currentPath = window.location.pathname;
    const menuLinks = document.querySelectorAll('.sidebar-menu a');
    
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && currentPath.includes(href) && href !== '/') {
            link.classList.add('active');
        } else if (currentPath === '/' && href === '/') {
            link.classList.add('active');
        }
    });
}

/**
 * Initialize tooltips
 */
function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(tooltip => {
        tooltip.style.position = 'relative';
        
        tooltip.addEventListener('mouseenter', function() {
            const text = this.getAttribute('data-tooltip');
            const tooltipEl = document.createElement('div');
            
            tooltipEl.classList.add('tooltip');
            tooltipEl.textContent = text;
            tooltipEl.style.position = 'absolute';
            tooltipEl.style.backgroundColor = 'rgba(0, 0, 0, 0.8)';
            tooltipEl.style.color = '#fff';
            tooltipEl.style.padding = '5px 10px';
            tooltipEl.style.borderRadius = '3px';
            tooltipEl.style.fontSize = '12px';
            tooltipEl.style.bottom = '100%';
            tooltipEl.style.left = '50%';
            tooltipEl.style.transform = 'translateX(-50%)';
            tooltipEl.style.marginBottom = '5px';
            tooltipEl.style.zIndex = '1000';
            tooltipEl.style.whiteSpace = 'nowrap';
            
            this.appendChild(tooltipEl);
        });
        
        tooltip.addEventListener('mouseleave', function() {
            const tooltipEl = this.querySelector('.tooltip');
            if (tooltipEl) {
                tooltipEl.remove();
            }
        });
    });
}

/**
 * Initialize dropdown menus
 */
function initializeDropdowns() {
    // Already handled by CSS for simplicity,
    // but can be enhanced here if needed
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Get all required inputs
            const requiredInputs = form.querySelectorAll('[required]');
            
            requiredInputs.forEach(input => {
                // Remove existing error messages
                const existingError = input.parentNode.querySelector('.invalid-feedback');
                if (existingError) {
                    existingError.remove();
                }
                
                // Reset input style
                input.style.borderColor = '';
                
                // Check if input is empty
                if (!input.value.trim()) {
                    isValid = false;
                    
                    // Add error message
                    const errorMessage = document.createElement('div');
                    errorMessage.classList.add('invalid-feedback');
                    errorMessage.textContent = 'This field is required';
                    
                    input.parentNode.appendChild(errorMessage);
                    input.style.borderColor = '#e74c3c';
                }
                
                // Email validation
                if (input.type === 'email' && input.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value.trim())) {
                        isValid = false;
                        
                        // Add error message
                        const errorMessage = document.createElement('div');
                        errorMessage.classList.add('invalid-feedback');
                        errorMessage.textContent = 'Please enter a valid email address';
                        
                        input.parentNode.appendChild(errorMessage);
                        input.style.borderColor = '#e74c3c';
                    }
                }
                
                // Password validation for strength if it's a password field
                if (input.type === 'password' && input.value.trim() && input.getAttribute('data-validate-strength')) {
                    if (input.value.length < 6) {
                        isValid = false;
                        
                        // Add error message
                        const errorMessage = document.createElement('div');
                        errorMessage.classList.add('invalid-feedback');
                        errorMessage.textContent = 'Password must be at least 6 characters long';
                        
                        input.parentNode.appendChild(errorMessage);
                        input.style.borderColor = '#e74c3c';
                    }
                }
            });
            
            // Check password confirmation if exists
            const password = form.querySelector('input[name="password"]');
            const confirmPassword = form.querySelector('input[name="confirm_password"]');
            
            if (password && confirmPassword && password.value && confirmPassword.value) {
                if (password.value !== confirmPassword.value) {
                    isValid = false;
                    
                    // Remove existing error messages
                    const existingError = confirmPassword.parentNode.querySelector('.invalid-feedback');
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    // Add error message
                    const errorMessage = document.createElement('div');
                    errorMessage.classList.add('invalid-feedback');
                    errorMessage.textContent = 'Passwords do not match';
                    
                    confirmPassword.parentNode.appendChild(errorMessage);
                    confirmPassword.style.borderColor = '#e74c3c';
                }
            }
            
            // If the form is not valid, prevent submission
            if (!isValid) {
                event.preventDefault();
            }
        });
    });
}

/**
 * Initialize chart visualization if any charts are present
 */
function initializeCharts() {
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js is not loaded');
        return;
    }
    
    // Revenue chart (monthly)
    const revenueChartEl = document.getElementById('revenue-chart');
    if (revenueChartEl) {
        const ctx = revenueChartEl.getContext('2d');
        
        // Get data from the element's data attributes
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
    
    // Attendance chart (daily)
    const attendanceChartEl = document.getElementById('attendance-chart');
    if (attendanceChartEl) {
        const ctx = attendanceChartEl.getContext('2d');
        
        // Get data from the element's data attributes
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
    
    // Membership types pie chart
    const membershipChartEl = document.getElementById('membership-chart');
    if (membershipChartEl) {
        const ctx = membershipChartEl.getContext('2d');
        
        // Get data from the element's data attributes
        const labels = JSON.parse(membershipChartEl.getAttribute('data-labels') || '[]');
        const values = JSON.parse(membershipChartEl.getAttribute('data-values') || '[]');
        
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
                        'rgba(231, 76, 60, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    }
}

/**
 * Initialize DataTables functionality if present
 */
function initializeDataTables() {
    // This is a minimal implementation
    // In a real app, would use DataTables library
    
    const tables = document.querySelectorAll('table.sortable');
    
    tables.forEach(table => {
        // Add simple search filtering
        const tableContainer = table.parentNode;
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search...';
        searchInput.classList.add('form-control', 'mb-2');
        searchInput.style.maxWidth = '300px';
        
        tableContainer.insertBefore(searchInput, table);
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // Add sorting functionality to headers
        const headers = table.querySelectorAll('th');
        
        headers.forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(table, index);
            });
        });
    });
}

/**
 * Sort table by column
 * @param {HTMLTableElement} table - Table to sort
 * @param {number} columnIndex - Index of the column to sort by
 */
function sortTable(table, columnIndex) {
    const isNumeric = table.rows[1] && !isNaN(parseFloat(table.rows[1].cells[columnIndex].textContent));
    const direction = table.getAttribute('data-sort-dir') === 'asc' ? -1 : 1;
    const newDirection = direction === 1 ? 'asc' : 'desc';
    
    table.setAttribute('data-sort-dir', newDirection);
    
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.rows);
    
    // Sort the rows
    rows.sort((a, b) => {
        let aValue = a.cells[columnIndex].textContent.trim();
        let bValue = b.cells[columnIndex].textContent.trim();
        
        if (isNumeric) {
            return direction * (parseFloat(aValue) - parseFloat(bValue));
        } else {
            return direction * aValue.localeCompare(bValue);
        }
    });
    
    // Reappend rows in new order
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Initialize delete confirmation dialogs
 */
function initializeDeleteConfirmation() {
    const deleteButtons = document.querySelectorAll('[data-delete-confirm]');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const confirmMessage = this.getAttribute('data-delete-confirm') || 'Are you sure you want to delete this item?';
            
            if (!confirm(confirmMessage)) {
                event.preventDefault();
            }
        });
    });
}

/**
 * Initialize attendance-specific functionality
 */
function initializeAttendance() {
    // Quick check-in functionality
    const quickCheckInForm = document.getElementById('quick-check-in-form');
    
    if (quickCheckInForm) {
        quickCheckInForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const userId = document.getElementById('quick-user-id').value;
            if (!userId) {
                alert('Please select a member');
                return;
            }
            
            const formData = new FormData(this);
            
            // Send AJAX request
            fetch('/attendance/store', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert('Attendance recorded successfully');
                    
                    // Reset form
                    quickCheckInForm.reset();
                    
                    // Reload page to show new attendance
                    location.reload();
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while recording attendance');
            });
        });
    }
    
    // User selection cascade (when applicable)
    const userSelect = document.getElementById('user_id');
    const membershipSelect = document.getElementById('membership_id');
    
    if (userSelect && membershipSelect) {
        userSelect.addEventListener('change', function() {
            const userId = this.value;
            
            if (!userId) {
                // Clear membership options
                membershipSelect.innerHTML = '<option value="">Select membership</option>';
                return;
            }
            
            // Fetch memberships for this user
            fetch(`/memberships/user/${userId}`)
            .then(response => response.json())
            .then(data => {
                // Clear existing options
                membershipSelect.innerHTML = '<option value="">Select membership</option>';
                
                // Add new options
                data.memberships.forEach(membership => {
                    const option = document.createElement('option');
                    option.value = membership.id;
                    option.textContent = `${membership.type} (${membership.start_date} to ${membership.end_date})`;
                    membershipSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while loading membership data');
            });
        });
    }
}

/**
 * Initialize reports-specific functionality
 */
function initializeReports() {
    // Handle date range filtering
    const dateRangeForm = document.getElementById('date-range-form');
    
    if (dateRangeForm) {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        
        if (startDateInput && endDateInput) {
            // Set max date to today
            const today = new Date().toISOString().split('T')[0];
            startDateInput.max = today;
            endDateInput.max = today;
            
            // Ensure end date is not before start date
            startDateInput.addEventListener('change', function() {
                endDateInput.min = this.value;
                if (endDateInput.value && endDateInput.value < this.value) {
                    endDateInput.value = this.value;
                }
            });
            
            // Ensure start date is not after end date
            endDateInput.addEventListener('change', function() {
                startDateInput.max = this.value;
                if (startDateInput.value && startDateInput.value > this.value) {
                    startDateInput.value = this.value;
                }
            });
        }
    }
    
    // Print report functionality
    const printButton = document.querySelector('.print-report-btn');
    
    if (printButton) {
        printButton.addEventListener('click', function() {
            window.print();
        });
    }
}
