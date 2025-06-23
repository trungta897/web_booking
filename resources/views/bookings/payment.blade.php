<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Complete Payment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Payment Method Selection -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 mb-6">
                        {{ __('booking.choose_payment_method') }}
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- VNPay Option -->
                        <div class="payment-method-card border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-blue-500 transition-colors" data-method="vnpay">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900">VNPay</h3>
                                        <p class="text-sm text-gray-500">{{ __('booking.vnpay_banking') }}</p>
                                    </div>
                                </div>
                                <input type="radio" name="payment_method" value="vnpay" class="w-4 h-4 text-blue-600">
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">Techcombank</span>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">Vietcombank</span>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">BIDV</span>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">QR Code</span>
                            </div>
                        </div>

                        <!-- Stripe Option -->
                        <div class="payment-method-card border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-purple-500 transition-colors" data-method="stripe">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M2,17H22V7H2V17M20,19H4A2,2 0 0,1 2,17V7A2,2 0 0,1 4,5H20A2,2 0 0,1 22,7V17A2,2 0 0,1 20,19Z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-900">Stripe</h3>
                                        <p class="text-sm text-gray-500">{{ __('booking.stripe_card') }}</p>
                                    </div>
                                </div>
                                <input type="radio" name="payment_method" value="stripe" class="w-4 h-4 text-purple-600">
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">Visa</span>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800">Mastercard</span>
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-indigo-100 text-indigo-800">Amex</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Booking Details & Payment Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Booking Details -->
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4">
                                {{ __('booking.details') }}
                            </h2>

                            <div class="bg-gray-50 p-6 rounded-lg">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0 mr-4">
                                        <img class="h-16 w-16 rounded-full object-cover"
                                             src="{{ $booking->tutor->user->avatar ? asset('storage/' . $booking->tutor->user->avatar) : asset('images/default-avatar.png') }}"
                                             alt="{{ $booking->tutor->user->name }}">
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">{{ $booking->tutor->user->name }}</h3>
                                        <p class="text-sm text-gray-600">{{ $booking->subject->name }}</p>
                                        <div class="flex items-center mt-1">
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="w-4 h-4 {{ $i <= ($booking->tutor->rating ?? 5) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                            </div>
                                            <span class="ml-2 text-sm text-gray-600">({{ $booking->tutor->reviews_count ?? 0 }} {{ __('reviews') }})</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="border-t border-gray-200 pt-4 space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">{{ __('common.date') }}:</span>
                                        <span class="font-medium text-gray-900">{{ $booking->start_time->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">{{ __('common.time') }}:</span>
                                        <span class="font-medium text-gray-900">{{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">{{ __('Duration') }}:</span>
                                        <span class="font-medium text-gray-900">{{ $booking->duration }} {{ __('minutes') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">{{ __('Hourly Rate') }}:</span>
                                        <span class="font-medium text-gray-900">{{ formatCurrency($booking->tutor->hourly_rate) }}/{{ __('hour') }}</span>
                                    </div>
                                    <div class="flex justify-between pt-3 border-t border-gray-200">
                                        <span class="text-base font-medium text-gray-900">{{ __('booking.total_amount') }}:</span>
                                        <span class="text-lg font-bold text-gray-900">{{ $booking->display_amount }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4">
                                {{ __('Payment Information') }}
                            </h2>

                            <!-- Payment Errors -->
                            <div id="payment-error" class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg hidden"></div>

                            <!-- VNPay Form -->
                            <div id="vnpay-form" class="payment-form hidden">
                                <div class="bg-blue-50 p-6 rounded-lg">
                                    <div class="flex items-center mb-4">
                                        <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center mr-3">
                                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-blue-900">VNPay Payment</h3>
                                    </div>
                                    <p class="text-blue-800 mb-4">{{ __('Bạn sẽ được chuyển hướng đến trang thanh toán VNPay để hoàn tất giao dịch.') }}</p>
                                    <ul class="text-sm text-blue-700 space-y-1 mb-4">
                                        <li>• {{ __('Hỗ trợ tất cả ngân hàng tại Việt Nam') }}</li>
                                        <li>• {{ __('Thanh toán qua QR Code') }}</li>
                                        <li>• {{ __('Ví điện tử VNPay') }}</li>
                                        <li>• {{ __('Bảo mật SSL 256-bit') }}</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Stripe Form -->
                            <div id="stripe-form" class="payment-form hidden">
                                <div class="space-y-4">
                                    <div>
                                        <label for="card-element" class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ __('Credit or debit card') }}
                                        </label>
                                        <div id="card-element" class="p-3 border border-gray-300 rounded-md bg-white"></div>
                                        <div id="card-errors" class="mt-1 text-sm text-red-600"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Button -->
                            <button type="button" id="payment-button"
                                    class="w-full mt-6 inline-flex justify-center items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:from-blue-700 hover:to-purple-700 active:from-blue-800 active:to-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-all duration-200 transform hover:scale-105"
                                    disabled>
                                <span id="button-text">{{ __('Select payment method') }}</span>
                                <span id="spinner" class="hidden ml-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>

                            <p class="mt-4 text-xs text-gray-500 text-center">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                {{ __('booking.payment_secure') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedPaymentMethod = null;
            let stripe = null;
            let card = null;

            // Payment method selection
            const paymentMethodCards = document.querySelectorAll('.payment-method-card');
            const paymentForms = document.querySelectorAll('.payment-form');
            const paymentButton = document.getElementById('payment-button');
            const buttonText = document.getElementById('button-text');
            const spinner = document.getElementById('spinner');
            const paymentError = document.getElementById('payment-error');

            // Handle payment method selection
            paymentMethodCards.forEach(card => {
                card.addEventListener('click', function() {
                    const method = this.dataset.method;
                    selectPaymentMethod(method);
                });
            });

            function selectPaymentMethod(method) {
                selectedPaymentMethod = method;

                // Update UI
                paymentMethodCards.forEach(card => {
                    card.classList.remove('border-blue-500', 'border-purple-500', 'bg-blue-50', 'bg-purple-50');
                    card.classList.add('border-gray-200');
                    card.querySelector('input[type="radio"]').checked = false;
                });

                const selectedCard = document.querySelector(`[data-method="${method}"]`);
                selectedCard.classList.remove('border-gray-200');
                selectedCard.classList.add(method === 'vnpay' ? 'border-blue-500' : 'border-purple-500');
                selectedCard.classList.add(method === 'vnpay' ? 'bg-blue-50' : 'bg-purple-50');
                selectedCard.querySelector('input[type="radio"]').checked = true;

                // Show/hide payment forms
                paymentForms.forEach(form => form.classList.add('hidden'));
                document.getElementById(`${method}-form`).classList.remove('hidden');

                // Update button
                paymentButton.disabled = false;
                updateButtonText();

                // Initialize Stripe if needed
                if (method === 'stripe' && !stripe) {
                    initializeStripe();
                }
            }

            function updateButtonText() {
                if (selectedPaymentMethod === 'vnpay') {
                    buttonText.textContent = '{{ __("Thanh toán với VNPay") }} {{ $booking->display_amount }}';
                } else if (selectedPaymentMethod === 'stripe') {
                    buttonText.textContent = '{{ __("Pay with Stripe") }} ${{ number_format($booking->price, 2) }}';
                }
            }

            function initializeStripe() {
                stripe = Stripe('{{ config('services.stripe.key') }}');
                const elements = stripe.elements();

                card = elements.create('card', {
                    hidePostalCode: true,
                    style: {
                        base: {
                            fontSize: '16px',
                            color: '#1f2937',
                            '::placeholder': {
                                color: '#6b7280',
                            },
                        },
                        invalid: {
                            color: '#ef4444',
                            iconColor: '#ef4444',
                        },
                    },
                });

                card.mount('#card-element');

                card.addEventListener('change', function(event) {
                    const displayError = document.getElementById('card-errors');
                    if (event.error) {
                        displayError.textContent = event.error.message;
                    } else {
                        displayError.textContent = '';
                    }
                });
            }

            // Handle payment button click
            paymentButton.addEventListener('click', async function() {
                if (!selectedPaymentMethod) return;

                setLoading(true);
                hideError();

                try {
                    if (selectedPaymentMethod === 'vnpay') {
                        await processVnpayPayment();
                    } else if (selectedPaymentMethod === 'stripe') {
                        await processStripePayment();
                    }
                } catch (error) {
                    showError(error.message);
                    setLoading(false);
                }
            });

            async function processVnpayPayment() {
                const response = await fetch('{{ route('payments.process', $booking) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        payment_method: 'vnpay'
                    })
                });

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                // Redirect to VNPay
                window.location.href = data.redirect_url;
            }

            async function processStripePayment() {
                const response = await fetch('{{ route('payments.process', $booking) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        payment_method: 'stripe'
                    })
                });

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                const { error, paymentIntent } = await stripe.confirmCardPayment(data.clientSecret, {
                    payment_method: {
                        card: card,
                        billing_details: {
                            name: '{{ auth()->user()->name }}',
                            email: '{{ auth()->user()->email }}',
                        },
                    },
                });

                if (error) {
                    throw new Error(error.message);
                } else if (paymentIntent.status === 'succeeded') {
                    window.location.href = '{{ route('payments.confirm', $booking) }}';
                }
            }

            function setLoading(loading) {
                paymentButton.disabled = loading;
                if (loading) {
                    spinner.classList.remove('hidden');
                    buttonText.textContent = '{{ __("booking.processing_payment") }}';
                } else {
                    spinner.classList.add('hidden');
                    updateButtonText();
                }
            }

            function showError(message) {
                paymentError.textContent = message;
                paymentError.classList.remove('hidden');
                paymentError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function hideError() {
                paymentError.classList.add('hidden');
            }
        });
    </script>
    @endpush
</x-app-layout>
