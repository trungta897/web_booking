/**
 * Admin Payouts Show Page JavaScript
 * Handles payout actions: approve, reject, complete
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get CSRF token and language strings from meta tags
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const lang = {
        adminNotesOptional: document.querySelector('meta[name="lang-admin-notes-optional"]')?.content || 'Admin notes (optional):',
        rejectionReasonRequired: document.querySelector('meta[name="lang-rejection-reason-required"]')?.content || 'Rejection reason (required):',
        transactionIdOptional: document.querySelector('meta[name="lang-transaction-id-optional"]')?.content || 'Transaction ID (optional):',
        completionNotesOptional: document.querySelector('meta[name="lang-completion-notes-optional"]')?.content || 'Completion notes (optional):',
        errorOccurred: document.querySelector('meta[name="lang-error-occurred"]')?.content || 'An error occurred',
        networkError: document.querySelector('meta[name="lang-network-error"]')?.content || 'Network error occurred'
    };

    // Make functions global for onclick handlers
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

        // Show loading state
        const loadingText = 'Processing...';
        const originalAlert = window.alert;
        window.alert = function(msg) { console.log(loadingText); };

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
            window.alert = originalAlert; // Restore alert

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
            window.alert = originalAlert; // Restore alert
            console.error('Payout action error:', error);
            alert(lang.networkError + ': ' + error.message);
        });
    }
});
