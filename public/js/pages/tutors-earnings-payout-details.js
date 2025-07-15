/**
 * Tutors Earnings Payout Details JavaScript
 * Handles payout details page functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    // Copy transaction ID to clipboard
    window.copyTransactionId = function(transactionId) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(transactionId).then(() => {
                showToast('Transaction ID copied to clipboard', 'success');
            }).catch(() => {
                fallbackCopyTextToClipboard(transactionId);
            });
        } else {
            fallbackCopyTextToClipboard(transactionId);
        }
    };
    
    // Fallback copy method for older browsers
    function fallbackCopyTextToClipboard(text) {
        const textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            showToast('Transaction ID copied to clipboard', 'success');
        } catch (err) {
            showToast('Failed to copy transaction ID', 'error');
        }
        
        document.body.removeChild(textArea);
    }
    
    // Simple toast notification
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 p-4 rounded-md text-white z-50 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 'bg-blue-500'
        }`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
    
    // Print payout details
    window.printPayoutDetails = function() {
        window.print();
    };
    
    // Refresh payout status
    window.refreshPayoutStatus = function() {
        location.reload();
    };
    
    // Cancel payout functionality
    window.cancelPayout = function(payoutId) {
        if (confirm('Are you sure you want to cancel this payout request?')) {
            fetch(`/tutors/earnings/payout/${payoutId}/cancel`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Payout cancelled successfully');
                    location.reload();
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error occurred');
            });
        }
    };
});