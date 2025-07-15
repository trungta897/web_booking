/**
 * Extracted from: admin\refunds.blade.php
 * Generated on: 2025-07-15 03:51:33
 */

// Refresh data function
function refreshData() {
    window.location.reload();
}

// Export data function
function exportData() {
    // Implementation for data export
    alert('{{ __("admin.export") }} feature will be implemented');
}

// Print data function
function printData() {
    window.print();
}

// Trends Chart
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

    const chartData = {!! json_encode($stats['daily_trends'] ?? []) !!};

    // Always show chart with line, even with zero data
    const labels = chartData && chartData.length > 0 ? chartData.map(item => item.date) : ['01/2024', '02/2024', '03/2024'];
    const countData = chartData && chartData.length > 0 ? chartData.map(item => item.count) : [0, 0, 0];
    const amountData = chartData && chartData.length > 0 ? chartData.map(item => item.amount / 1000) : [0, 0, 0]; // Convert to thousands

        try {
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                label: 'Số yêu cầu hoàn tiền',
                data: countData,
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.3,
                fill: false,
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                yAxisID: 'y'
            }, {
                label: 'Tổng tiền hoàn (nghìn VND)',
                data: amountData,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.3,
                fill: false,
                pointBackgroundColor: 'rgb(34, 197, 94)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                yAxisID: 'y1'
            }]
        },
                options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
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
                                return `${context.dataset.label}: ${context.parsed.y} yêu cầu`;
                            } else {
                                return `${context.dataset.label}: ${context.parsed.y.toLocaleString()} nghìn VND`;
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
                        color: isDarkMode ? '#9ca3af' : '#6b7280',
                        stepSize: 1
                    },
                    grid: {
                        color: isDarkMode ? '#374151' : '#e5e7eb',
                        drawBorder: false
                    },
                    title: {
                        display: true,
                        text: 'Số yêu cầu hoàn tiền',
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
                        text: 'Số tiền (nghìn VND)',
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
                        text: 'Thời gian (tháng/năm)',
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

    // Chart is always displayed - no overlay needed
    }, 100); // 100ms delay to ensure layout is ready
});