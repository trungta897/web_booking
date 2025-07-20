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

// Date validation and formatting functions
function isValidDateFormat(dateString) {
    const regex = /^(\d{2})\/(\d{2})\/(\d{2})$/;
    const match = dateString.match(regex);
    if (!match) return false;

    const day = parseInt(match[1]);
    const month = parseInt(match[2]);
    const year = parseInt(match[3]) + 2000; // Convert to 4-digit year

    if (month < 1 || month > 12) return false;
    if (day < 1 || day > 31) return false;
    if (year < 2000 || year > 2100) return false;

    // Check if date is valid
    const date = new Date(year, month - 1, day);
    return date.getFullYear() === year &&
           date.getMonth() === (month - 1) &&
           date.getDate() === day;
}

// Trends Chart initialization
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date inputs
    const fromDateInput = document.getElementById('from_date');
    const toDateInput = document.getElementById('to_date');

    if (fromDateInput) {
        fromDateInput.addEventListener('blur', function() {
            if (this.value && !isValidDateFormat(this.value)) {
                this.classList.add('border-red-500');
                // Show error message
                let errorMsg = this.parentNode.querySelector('.date-error');
                if (!errorMsg) {
                    errorMsg = document.createElement('p');
                    errorMsg.className = 'date-error mt-1 text-sm text-red-600';
                    errorMsg.textContent = 'Định dạng ngày không hợp lệ. Vui lòng sử dụng dd/mm/yy';
                    this.parentNode.appendChild(errorMsg);
                }
            } else {
                this.classList.remove('border-red-500');
                const errorMsg = this.parentNode.querySelector('.date-error');
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
        });

        fromDateInput.addEventListener('input', function() {
            this.classList.remove('border-red-500');
            const errorMsg = this.parentNode.querySelector('.date-error');
            if (errorMsg) {
                errorMsg.remove();
            }
        });
    }

    if (toDateInput) {
        toDateInput.addEventListener('blur', function() {
            if (this.value && !isValidDateFormat(this.value)) {
                this.classList.add('border-red-500');
                // Show error message
                let errorMsg = this.parentNode.querySelector('.date-error');
                if (!errorMsg) {
                    errorMsg = document.createElement('p');
                    errorMsg.className = 'date-error mt-1 text-sm text-red-600';
                    errorMsg.textContent = 'Định dạng ngày không hợp lệ. Vui lòng sử dụng dd/mm/yy';
                    this.parentNode.appendChild(errorMsg);
                }
            } else {
                this.classList.remove('border-red-500');
                const errorMsg = this.parentNode.querySelector('.date-error');
                if (errorMsg) {
                    errorMsg.remove();
                }
            }
        });

        toDateInput.addEventListener('input', function() {
            this.classList.remove('border-red-500');
            const errorMsg = this.parentNode.querySelector('.date-error');
            if (errorMsg) {
                errorMsg.remove();
            }
        });
    }

    // Form validation before submit
    const filterForm = document.querySelector('form[action*="admin.refunds"]');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            let isValid = true;

            if (fromDateInput && fromDateInput.value && !isValidDateFormat(fromDateInput.value)) {
                fromDateInput.classList.add('border-red-500');
                isValid = false;
            }

            if (toDateInput && toDateInput.value && !isValidDateFormat(toDateInput.value)) {
                toDateInput.classList.add('border-red-500');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng nhập đúng định dạng ngày: dd/mm/yy');
                return false;
            }
        });
    }

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
                        label: 'Yêu cầu hoàn tiền',
                        data: chartData.map(item => item.count || 0),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Số tiền hoàn (nghìn VND)',
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
                            text: 'Xu hướng hoàn tiền theo thời gian',
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
                                color: isDarkMode ? '#9ca3af' : '#6b7280'
                            },
                            grid: {
                                color: isDarkMode ? '#374151' : '#e5e7eb',
                                drawBorder: false
                            },
                            title: {
                                display: true,
                                text: 'Số lượng yêu cầu',
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
                                text: 'Thời gian',
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
                        <p class="text-red-600 dark:text-red-400">Lỗi tải biểu đồ</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kiểm tra console để xem chi tiết</p>
                    </div>
                </div>
            `;
        }

    }, 100); // 100ms delay to ensure layout is ready
});
