/**
 * Admin Payouts Index Page JavaScript
 * Handles payout actions from the main index page
 */

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const lang = {
        adminNotesOptional: 'Admin notes (optional):',
        rejectionReasonRequired: 'Rejection reason (required):',
        transactionIdOptional: 'Transaction ID (optional):',
        completionNotesOptional: 'Completion notes (optional):',
        errorOccurred: 'An error occurred',
        networkError: 'Network error occurred'
    };

    window.approvePayout = function(payoutId) {
        const notes = prompt(lang.adminNotesOptional);
        if (notes !== null) {
            performPayoutAction(payoutId, 'approve', { admin_notes: notes });
        }
    };

    window.rejectPayout = function(payoutId) {
        const reason = prompt(lang.rejectionReasonRequired);
        if (reason && reason.trim()) {
            const notes = prompt(lang.adminNotesOptional);
            performPayoutAction(payoutId, 'reject', {
                rejection_reason: reason.trim(),
                admin_notes: notes || ''
            });
        } else if (reason !== null) {
            alert(lang.rejectionReasonRequired);
        }
    };

    window.completePayout = function(payoutId) {
        const transactionId = prompt(lang.transactionIdOptional);
        if (transactionId !== null) {
            const notes = prompt(lang.completionNotesOptional);
            performPayoutAction(payoutId, 'complete', {
                transaction_id: transactionId || '',
                completion_notes: notes || ''
            });
        }
    };

    function performPayoutAction(payoutId, action, data) {
        if (!csrfToken) {
            alert('CSRF token not found. Please refresh the page.');
            return;
        }

        fetch(`/admin/payouts/${payoutId}/${action}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                alert(result.message);
                location.reload();
            } else {
                alert(result.message || lang.errorOccurred);
            }
        })
        .catch(error => {
            console.error('Payout action error:', error);
            alert(lang.networkError + ': ' + error.message);
        });
    }
});