/**
 * Extracted from: tutors\earnings\create-payout.blade.php
 * Generated on: 2025-07-15 03:51:34
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('payoutForm');
    const submitBtn = document.getElementById('submitBtn');
    const amountInput = document.getElementById('amount');
    const availableAmount = {{ $earnings['available_earnings'] }};
    const minimumAmount = {{ $minimumPayout }};

    // Format account number input
    document.getElementById('account_number').addEventListener('input', function(e) {
        // Remove non-digits
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
    });

    // Validate amount on input
    amountInput.addEventListener('input', function(e) {
        const amount = parseFloat(e.target.value) || 0;

        if (amount > 0) {
            if (amount < minimumAmount) {
                e.target.setCustomValidity('{{ __("common.minimum_amount_required") }}: ' + minimumAmount.toLocaleString() + ' VND');
            } else if (amount > availableAmount) {
                e.target.setCustomValidity('{{ __("common.exceeds_available_amount") }}: ' + availableAmount.toLocaleString() + ' VND');
            } else {
                e.target.setCustomValidity('');
            }
        } else {
            e.target.setCustomValidity('');
        }
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const amount = formData.get('amount') || availableAmount;

        // Show confirmation
        const confirmation = confirm(
            '{{ __("common.confirm_payout_request") }}\n\n' +
            '{{ __("common.amount") }}: ' + parseFloat(amount).toLocaleString() + ' VND\n' +
            '{{ __("common.bank") }}: ' + formData.get('bank_name') + '\n' +
            '{{ __("common.account") }}: ' + formData.get('account_number') + '\n\n' +
            '{{ __("common.continue_with_request") }}'
        );

        if (confirmation) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("common.processing") }}...';

            // Submit form via AJAX for better UX
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.href = '{{ route("tutors.earnings.index") }}';
                } else {
                    alert(data.message || '{{ __("common.error_occurred") }}');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> {{ __("common.submit_payout_request") }}';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{ __("common.network_error") }}');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> {{ __("common.submit_payout_request") }}';
            });
        }
    });
});