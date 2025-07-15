/**
 * Extracted from: bookings\refund-confirm.blade.php
 * Generated on: 2025-07-15 03:51:33
 */

document.addEventListener('DOMContentLoaded', function() {
function toggleRefundAmount() {
            const partialRefundSection = document.getElementById('partialRefundSection');
            const refundAmountInput = document.getElementById('refund_amount');
            const partialRadio = document.querySelector('input[name="refund_type"][value="partial"]');

            if (partialRadio.checked) {
                partialRefundSection.classList.remove('hidden');
                refundAmountInput.required = true;
            } else {
                partialRefundSection.classList.add('hidden');
                refundAmountInput.required = false;
                refundAmountInput.value = '';
            }
        }

        // Format number input with thousand separators
        document.getElementById('refund_amount').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^\d]/g, '');
            if (value) {
                // Add thousand separators for display (optional)
                // e.target.value = parseInt(value).toLocaleString('vi-VN');
            }
        });
});