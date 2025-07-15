/**
 * Extracted from: tutors\earnings\history.blade.php
 * Generated on: 2025-07-15 03:51:34
 */

function cancelPayout(payoutId) {
    if (confirm('{{ __("common.confirm_cancel_payout") }}')) {
        fetch(`/tutors/earnings/payout/${payoutId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || '{{ __("common.error_occurred") }}');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('{{ __("common.network_error") }}');
        });
    }
}

// Auto-submit form when filters change
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const fromDate = document.getElementById('from_date');
    const toDate = document.getElementById('to_date');

    // Auto-submit when status changes
    statusSelect.addEventListener('change', function() {
        if (this.value !== '') {
            this.form.submit();
        }
    });
});