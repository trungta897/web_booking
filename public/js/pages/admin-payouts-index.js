/**
 * Extracted from: admin\payouts\index.blade.php
 * Generated on: 2025-07-15 03:51:33
 */

document.addEventListener('DOMContentLoaded', function() {
function approvePayout(payoutId) {
    const notes = prompt('{{ __("admin.admin_notes_optional") }}:');
    if (notes !== null) {
        performPayoutAction(payoutId, 'approve', { admin_notes: notes });
    }
}

function rejectPayout(payoutId) {
    const reason = prompt('{{ __("admin.rejection_reason_required") }}:');
    if (reason && reason.trim()) {
        const notes = prompt('{{ __("admin.admin_notes_optional") }}:');
        performPayoutAction(payoutId, 'reject', {
            rejection_reason: reason.trim(),
            admin_notes: notes || ''
        });
    } else if (reason !== null) {
        alert('{{ __("admin.rejection_reason_required") }}');
    }
}

function completePayout(payoutId) {
    const transactionId = prompt('{{ __("admin.transaction_id_optional") }}:');
    if (transactionId !== null) {
        const notes = prompt('{{ __("admin.completion_notes_optional") }}:');
        performPayoutAction(payoutId, 'complete', {
            transaction_id: transactionId || '',
            completion_notes: notes || ''
        });
    }
}

function performPayoutAction(payoutId, action, data) {
    fetch(`/admin/payouts/${payoutId}/${action}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert(result.message || '{{ __("admin.error_occurred") }}');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('{{ __("admin.network_error") }}');
    });
}
});