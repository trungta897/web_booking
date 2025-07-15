/**
 * Tutors Earnings Index JavaScript
 * Handles main earnings page with charts and statistics
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize earnings chart if Chart.js is available
    if (typeof Chart !== 'undefined') {
        const chartElement = document.getElementById('earningsChart');
        if (chartElement) {
            const ctx = chartElement.getContext('2d');
            
            // Get data from data attributes or global variables
            const monthlyData = window.chartData?.monthly_data || [];
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthlyData.map(item => item.month),
                    datasets: [{
                        label: 'Earnings',
                        data: monthlyData.map(item => item.earnings),
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.1
                    }, {
                        label: 'Revenue',
                        data: monthlyData.map(item => item.revenue),
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Monthly Earnings Overview'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('vi-VN').format(value) + ' VND';
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    
    // Quick stats refresh
    const refreshButton = document.getElementById('refreshStats');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            location.reload();
        });
    }
    
    // Export functionality
    const exportButton = document.getElementById('exportData');
    if (exportButton) {
        exportButton.addEventListener('click', function() {
            window.location.href = '/tutors/earnings/export';
        });
    }
});