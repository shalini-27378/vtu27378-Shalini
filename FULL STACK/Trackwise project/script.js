// Chart initialization
let expenseChart, categoryChart;

// Initialize charts when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupEventListeners();
    loadDashboardData();
});

function setupEventListeners() {
    // Date range picker for analytics
    const dateRange = document.getElementById('dateRange');
    if (dateRange) {
        dateRange.addEventListener('change', updateAnalytics);
    }
    
    // Form validation
    const expenseForm = document.getElementById('expenseForm');
    if (expenseForm) {
        expenseForm.addEventListener('submit', validateExpenseForm);
    }
}

function initializeCharts() {
    // Monthly expense trend chart
    const expenseCtx = document.getElementById('expenseChart');
    if (expenseCtx) {
        expenseChart = new Chart(expenseCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Monthly Expenses',
                    data: [],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Category distribution chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: [
                        '#4f46e5',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#ec4899'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}

async function loadDashboardData() {
    try {
        const response = await fetch('api/get_dashboard_data.php');
        const data = await response.json();
        
        updateDashboardStats(data.stats);
        updateCharts(data.charts);
    } catch (error) {
        console.error('Error loading dashboard data:', error);
    }
}

function updateDashboardStats(stats) {
    // Update stat cards
    document.querySelectorAll('.stat-number').forEach(el => {
        const statType = el.dataset.stat;
        if (stats[statType]) {
            el.textContent = '₹' + stats[statType].toFixed(2);
        }
    });
}

function updateCharts(chartData) {
    // Update expense chart
    if (expenseChart && chartData.monthly) {
        expenseChart.data.labels = chartData.monthly.labels;
        expenseChart.data.datasets[0].data = chartData.monthly.data;
        expenseChart.update();
    }
    
    // Update category chart
    if (categoryChart && chartData.categories) {
        categoryChart.data.labels = chartData.categories.labels;
        categoryChart.data.datasets[0].data = chartData.categories.data;
        categoryChart.update();
    }
}

function validateExpenseForm(e) {
    const amount = document.getElementById('amount').value;
    const date = document.getElementById('expense_date').value;
    const category = document.getElementById('category_id').value;
    
    let isValid = true;
    let errors = [];
    
    if (!amount || amount <= 0) {
        isValid = false;
        errors.push('Please enter a valid amount');
    }
    
    if (!date) {
        isValid = false;
        errors.push('Please select a date');
    }
    
    if (!category) {
        isValid = false;
        errors.push('Please select a category');
    }
    
    if (!isValid) {
        e.preventDefault();
        showAlert(errors.join('<br>'), 'danger');
    }
}

function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = message;
    
    const container = document.querySelector('.main-content');
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 2
    }).format(amount);
}

// Export to CSV
function exportToCSV(data, filename) {
    const csv = data.map(row => 
        Object.values(row).map(value => 
            typeof value === 'string' ? `"${value}"` : value
        ).join(',')
    ).join('\n');
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
}