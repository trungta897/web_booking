/**
 * Tutors Earnings Create Payout JavaScript
 * Handles payout request form and validation
 */

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Payout form validation
    const payoutForm = document.getElementById('payoutForm');
    if (payoutForm) {
        payoutForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const amount = parseFloat(document.getElementById('amount')?.value || 0);
            const bankAccount = document.getElementById('bank_account')?.value?.trim();
            const bankName = document.getElementById('bank_name')?.value?.trim();

            // Validation
            if (amount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            if (!bankAccount || !bankName) {
                alert('Please fill in all bank details');
                return;
            }

            // Submit form
            this.submit();
        });
    }

    // Amount formatting
    const amountInput = document.getElementById('amount');
    if (amountInput) {
        amountInput.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value > 0) {
                const formatted = new Intl.NumberFormat('vi-VN').format(value);
                const preview = document.getElementById('amountPreview');
                if (preview) {
                    preview.textContent = formatted + ' VND';
                }
            }
        });
    }
});
