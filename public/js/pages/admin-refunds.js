/**
 * Admin Refunds JavaScript
 * Handles refunds management page functionality including charts and data export
 */

// Global functions for refunds management
function refreshData() {
    window.location.reload();
}

function exportData() {
    // Create download link for CSV export
    const currentUrl = new URL(window.location);
    currentUrl.pathname = '/admin/refunds/export';
    window.location.href = currentUrl.toString();
}

function printData() {
    window.print();
}

// Trends Chart initialization
document.addEventListener('DOMContentLoaded', function() {
    // Wait a bit for layout to be fully ready
    setTimeout(function() {
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded!');
            return;
        }

        const chartElement = document.getElementById('refundTrendsChart');
        if (!chartElement) {
            console.error('Chart element not found!');
            return;
        }

        const ctx = chartElement.getContext('2d');
        const isDarkMode = document.documentElement.classList.contains('dark');

        // Get chart data from global variable or data attribute
        const chartData = window.refundTrendsData || [];

        // Always show chart with line, even with zero data
        try {
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.map(item => item.date || item.month),
                    datasets: [{
                        label: 'Refund Requests',
                        data: chartData.map(item => item.count || 0),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Refund Amount (1000 VND)',
                        data: chartData.map(item => (item.amount || 0) / 1000),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 3,
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Refund Trends Over Time',
                            color: isDarkMode ? '#e5e7eb' : '#374151',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                color: isDarkMode ? '#e5e7eb' : '#374151',
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: isDarkMode ? '#374151' : '#fff',
                            titleColor: isDarkMode ? '#e5e7eb' : '#374151',
                            bodyColor: isDarkMode ? '#e5e7eb' : '#374151',
                            borderColor: isDarkMode ? '#4b5563' : '#e5e7eb',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    if (context.datasetIndex === 0) {
                                        return `${context.dataset.label}: ${context.parsed.y} requests`;
                                    } else {
                                        return `${context.dataset.label}: ${context.parsed.y.toLocaleString()} thousand VND`;
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            beginAtZero: true,
                            ticks: {
                                color: isDarkMode ? '#9ca3af' : '#6b7280'
                            },
                            grid: {
                                color: isDarkMode ? '#374151' : '#e5e7eb',
                                drawBorder: false
                            },
                            title: {
                                display: true,
                                text: 'Number of Requests',
                                color: isDarkMode ? '#9ca3af' : '#6b7280'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            beginAtZero: true,
                            ticks: {
                                color: isDarkMode ? '#9ca3af' : '#6b7280'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            title: {
                                display: true,
                                text: 'Amount (1000 VND)',
                                color: isDarkMode ? '#9ca3af' : '#6b7280'
                            }
                        },
                        x: {
                            ticks: {
                                color: isDarkMode ? '#9ca3af' : '#6b7280'
                            },
                            grid: {
                                color: isDarkMode ? '#374151' : '#e5e7eb',
                                drawBorder: false
                            },
                            title: {
                                display: true,
                                text: 'Time Period',
                                color: isDarkMode ? '#9ca3af' : '#6b7280'
                            }
                        }
                    },
                    elements: {
                        line: {
                            borderWidth: 3
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });

        } catch (error) {
            console.error('Error creating chart:', error);
            // Show error message in chart container
            const container = chartElement.parentElement;
            container.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                        <p class="text-red-600 dark:text-red-400">Error loading chart</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Check browser console for details</p>
                    </div>
                </div>
            `;
        }

    }, 100); // 100ms delay to ensure layout is ready
});