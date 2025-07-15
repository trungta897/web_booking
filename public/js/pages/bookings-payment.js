/**
 * Extracted from: bookings\payment.blade.php
 * Generated on: 2025-07-15 03:51:33
 */

let selectedPaymentMethod = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Elements
            const paymentMethodCards = document.querySelectorAll('.payment-method-card');
            const paymentButton = document.getElementById('payment-button');
            const cancelButton = document.getElementById('cancel-payment-button');
            const buttonText = document.getElementById('button-text');
            const vnpayForm = document.getElementById('vnpay-form');
            const stripeForm = document.getElementById('stripe-form');
            const paymentError = document.getElementById('payment-error');

            // Payment method selection
            paymentMethodCards.forEach(card => {
                card.addEventListener('click', function() {
                    const method = this.dataset.method;

                    // Check VNPay minimum amount restriction
                    @php
                        $bookingAmount = (float) $booking->price;
                        $vnpayMinimum = 5000;
                        $isVnpayBlocked = $bookingAmount < $vnpayMinimum;
                    @endphp

                    if (method === 'vnpay' && {{ $isVnpayBlocked ? 'true' : 'false' }}) {
                        showError('Số tiền booking quá nhỏ để thanh toán qua VNPay. Vui lòng chọn Stripe.');
                        return;
                    }

                    // Remove previous selections
                    paymentMethodCards.forEach(c => {
                        c.classList.remove('border-blue-500', 'border-purple-500', 'bg-blue-50', 'bg-purple-50');
                        c.classList.add('border-gray-200');
                        const radio = c.querySelector('input[type="radio"]');
                        if (radio) radio.checked = false;
                    });

                    // Hide all forms
                    document.querySelectorAll('.payment-form').forEach(form => {
                        form.classList.add('hidden');
                    });

                    // Select current method
                    selectedPaymentMethod = method;

                    const radio = this.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;

                    if (method === 'vnpay') {
                        this.classList.remove('border-gray-200');
                        this.classList.add('border-blue-500', 'bg-blue-50');
                        vnpayForm.classList.remove('hidden');
                        buttonText.textContent = 'Thanh toán với VNPay';
                    } else if (method === 'stripe') {
                        this.classList.remove('border-gray-200');
                        this.classList.add('border-purple-500', 'bg-purple-50');
                        stripeForm.classList.remove('hidden');
                        buttonText.textContent = 'Thanh toán với Stripe';
                    }

                    // Enable payment button
                    paymentButton.disabled = false;
                    paymentButton.classList.remove('opacity-50');
                });
            });

            // Payment button click
            paymentButton.addEventListener('click', function(e) {
                e.preventDefault();

                if (!selectedPaymentMethod) {
                    showError('Vui lòng chọn phương thức thanh toán');
                    return;
                }

                // Show loading
                paymentButton.disabled = true;
                buttonText.textContent = 'Đang xử lý...';
                document.getElementById('spinner').classList.remove('hidden');

                if (selectedPaymentMethod === 'vnpay') {
                    processVNPayPayment();
                } else if (selectedPaymentMethod === 'stripe') {
                    processStripePayment();
                }
            });

            // Cancel payment button click
            cancelButton.addEventListener('click', function(e) {
                e.preventDefault();

                // Show confirmation dialog
                if (confirm('Bạn có chắc chắn muốn hủy thanh toán? Bạn có thể quay lại thanh toán sau.')) {

                    // Redirect to student dashboard
                    @auth
                        @if(auth()->user()->role === 'student')
                            window.location.href = '{{ route("student.dashboard") }}';
                        @else
                            window.location.href = '{{ route("bookings.show", $booking) }}';
                        @endif
                    @else
                        window.location.href = '{{ route("bookings.index") }}';
                    @endauth
                }
            });

                                                                                                function processVNPayPayment() {

                fetch(`/web_booking/public/bookings/{{ $booking->id }}/payment/process`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        payment_method: 'vnpay'
                    })
                })
                .then(response => {

                    if (!response.ok) {
                                                // Handle different error status codes
                        if (response.status === 403) {
                            throw new Error('Bạn không có quyền thanh toán cho booking này.');
                        } else if (response.status === 422) {
                            throw new Error('Booking này không thể thanh toán (đã thanh toán hoặc chưa được chấp nhận).');
                        } else if (response.status === 404) {
                            throw new Error('Booking không tồn tại hoặc đã bị xóa. Vui lòng kiểm tra lại.');
                        }

                        // If response is not ok, try to get error message
                        return response.json().then(data => {
                            throw new Error(data.error || `Lỗi server: ${response.status}`);
                        }).catch(() => {
                            if (response.status === 500) {
                                throw new Error('Lỗi server nội bộ. Vui lòng thử lại sau.');
                            } else {
                                throw new Error(`Lỗi kết nối server: ${response.status}`);
                            }
                        });
                    }

                    return response.json();
                })
                                .then(data => {
                    if (data.payment_url) {
                        window.location.href = data.payment_url;
                    } else {
                        throw new Error(data.error || 'Có lỗi xảy ra khi tạo link thanh toán VNPay');
                    }
                })
                .catch(error => {
                    console.error('VNPay payment error:', error);

                    // Handle different error types
                    let errorMessage = 'Có lỗi xảy ra khi kết nối đến VNPay';

                    if (error.name === 'TypeError' && error.message.includes('fetch')) {
                        errorMessage = 'Không thể kết nối đến server. Vui lòng thử lại.';
                    } else if (error.message) {
                        errorMessage = error.message;
                    }

                    showError(errorMessage);
                    resetPaymentButton();
                });
            }

            function processStripePayment() {
                // TODO: Implement Stripe payment processing
                showError('Stripe payment chưa được triển khai');
                resetPaymentButton();
            }

            function showError(message) {
                paymentError.textContent = message;
                paymentError.classList.remove('hidden');

                // Hide error after 5 seconds
                setTimeout(() => {
                    paymentError.classList.add('hidden');
                }, 5000);
            }

            function resetPaymentButton() {
                paymentButton.disabled = selectedPaymentMethod ? false : true;
                paymentButton.classList.toggle('opacity-50', !selectedPaymentMethod);

                if (selectedPaymentMethod === 'vnpay') {
                    buttonText.textContent = 'Thanh toán với VNPay';
                } else if (selectedPaymentMethod === 'stripe') {
                    buttonText.textContent = 'Thanh toán với Stripe';
                } else {
                    buttonText.textContent = 'Chọn phương thức thanh toán';
                }
                document.getElementById('spinner').classList.add('hidden');
            }

            // Auto-select appropriate payment method
            @php
                $bookingAmount = (float) $booking->price;
                $vnpayMinimum = 5000;
                $isVnpayBlocked = $bookingAmount < $vnpayMinimum;
            @endphp

            @if($isVnpayBlocked)
                // VNPay is blocked, disable it and auto-select Stripe
                const vnpayCard = document.querySelector('[data-method="vnpay"]');
                if (vnpayCard) {
                    vnpayCard.classList.add('opacity-50', 'cursor-not-allowed');
                    vnpayCard.classList.remove('cursor-pointer', 'hover:border-blue-500');
                }

                const stripeCard = document.querySelector('[data-method="stripe"]');
                if (stripeCard) {
                    setTimeout(() => stripeCard.click(), 100);
                }
            @else
                // Auto-select VNPay for Vietnamese users when amount is sufficient
                const vnpayCard = document.querySelector('[data-method="vnpay"]');
                if (vnpayCard) {
                    setTimeout(() => vnpayCard.click(), 100);
                }
            @endif
        });