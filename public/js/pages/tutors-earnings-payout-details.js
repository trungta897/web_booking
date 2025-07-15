/**
 * Extracted from: tutors\earnings\payout-details.blade.php
 * Generated on: 2025-07-15 03:51:34
 */

document.addEventListener('DOMContentLoaded', function() {
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
});