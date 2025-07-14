<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Complete Payment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Check if already paid -->
            @if($booking->is_confirmed || $booking->completedTransactions()->exists())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="text-center py-8">
                            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-green-600 mb-3">{{ __('booking.payment_completed') }}</h3>
                            <p class="text-gray-600 mb-6">{{ __('booking.info.already_paid') }}</p>

                            @php
                                $completedTransaction = $booking->completedTransactions()->first();
                            @endphp

                            @if($completedTransaction)
                                <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                                    <h4 class="font-medium text-gray-900 mb-2">{{ __('booking.payment_details') }}</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">{{ __('booking.payment_method') }}:</span>
                                            <span class="font-medium">{{ $completedTransaction->payment_method_name }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">{{ __('booking.transaction_id') }}:</span>
                                            <span class="font-medium font-mono text-xs">{{ $completedTransaction->transaction_id }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">{{ __('booking.amount') }}:</span>
                                            <span class="font-medium">
                                                @php
                                                    $currency = $completedTransaction->currency ?? 'VND';
                                                    $amount = $completedTransaction->amount;
                                                    $locale = session('locale') ?: app()->getLocale();
                                                    if (!$locale || !in_array($locale, ['en', 'vi'])) {
                                                        $locale = config('app.locale', 'vi');
                                                    }

                                                    // Smart detection: If currency is VND but amount is small (< 1000),
                                                    // it's likely USD amount saved with wrong currency
                                                    if ($currency === 'VND' && $amount < 1000) {
                                                        // This is likely USD amount with wrong currency label
                                                        if ($locale === 'vi') {
                                                            // Vietnamese: Convert USD to VND for display
                                                            $vndAmount = $amount * 25000; // 1 USD = 25,000 VND
                                                            $displayAmount = number_format($vndAmount, 0, ',', '.') . ' ₫';
                                                        } else {
                                                            // English: Display as USD
                                                            $displayAmount = '$' . number_format($amount, 2);
                                                        }
                                                    } elseif ($currency === 'VND') {
                                                        if ($locale === 'vi') {
                                                            // Vietnamese: Display VND as is
                                                            $displayAmount = number_format($amount, 0, ',', '.') . ' ₫';
                                                        } else {
                                                            // English: Convert VND to USD for display
                                                            $usdAmount = $amount / 25000; // 1 USD = 25,000 VND
                                                            $displayAmount = '$' . number_format($usdAmount, 2);
                                                        }
                                                    } else {
                                                        // Currency is USD or other
                                                        if ($locale === 'vi') {
                                                            // Vietnamese: Convert to VND
                                                            $vndAmount = $amount * 25000; // 1 USD = 25,000 VND
                                                            $displayAmount = number_format($vndAmount, 0, ',', '.') . ' ₫';
                                                        } else {
                                                            // English: Display as original currency
                                                            $displayAmount = '$' . number_format($amount, 2);
                                                        }
                                                    }
                                                @endphp
                                                {{ $displayAmount }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">{{ __('booking.paid_at') }}:</span>
                                            <span class="font-medium">{{ $completedTransaction->processed_at ? $completedTransaction->processed_at->format('d/m/Y H:i') : $completedTransaction->created_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="space-y-3">
                                <a href="{{ route('bookings.show', $booking) }}"
                                   class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                    {{ __('common.back_to_booking') }}
                                </a>
                                <a href="{{ route('bookings.transactions', $booking) }}"
                                   class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 ml-3 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    {{ __('booking.view_transaction_history') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($booking->status !== 'accepted')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="text-center py-8">
                            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-yellow-600 mb-3">{{ __('booking.payment_not_ready') }}</h3>
                            <p class="text-gray-600 mb-6">{{ __('booking.errors.booking_not_accepted_payment') }}</p>
                            <a href="{{ route('bookings.show', $booking) }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                {{ __('common.back_to_booking') }}
                            </a>
                        </div>
                    </div>
                </div>
            @else
            <!-- Payment Method Selection -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 mb-6">
                        {{ __('booking.choose_payment_method') }}
                    </h2>

                    <div class="grid grid-cols-1 gap-4 mb-6">
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
                                        @if($booking->tutor->user->avatar && file_exists(public_path('uploads/avatars/' . $booking->tutor->user->avatar)))
                                            <img class="h-20 w-20 rounded-full object-cover border-4 border-white shadow-lg"
                                                 src="{{ asset('uploads/avatars/' . $booking->tutor->user->avatar) }}"
                                                 alt="{{ $booking->tutor->user->name }}">
                                        @else
                                            <div class="h-20 w-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-2xl font-bold border-4 border-white shadow-lg">
                                                {{ strtoupper(substr($booking->tutor->user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                   </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900">{{ $booking->tutor->user->name }}</h3>
                                        <p class="text-sm text-gray-600">{{ translateSubjectName($booking->subject->name) }}</p>
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
                                        <span class="font-medium text-gray-900">{{ formatHourlyRate($booking->tutor->hourly_rate) }}</span>
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

                            <!-- Payment Buttons -->
                            <div class="mt-6 space-y-3">
                                <!-- Payment Button -->
                                <button type="button" id="payment-button"
                                        class="w-full inline-flex justify-center items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:from-blue-700 hover:to-purple-700 active:from-blue-800 active:to-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 transition-all duration-200 transform hover:scale-105"
                                        disabled>
                                    <span id="button-text">{{ __('Select payment method') }}</span>
                                    <span id="spinner" class="hidden ml-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>

                                <!-- Cancel Button -->
                                <button type="button" id="cancel-payment-button"
                                        class="w-full inline-flex justify-center items-center px-6 py-3 bg-gray-500 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ __('booking.cancel_payment') }}
                                </button>
                            </div>

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
    <script>
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
                    const method = this.dataset.method;
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

            // Auto-select VNPay for Vietnamese users
            const vnpayCard = document.querySelector('[data-method="vnpay"]');
            if (vnpayCard) {
                setTimeout(() => vnpayCard.click(), 100); // Small delay to ensure DOM is ready
            }
        });
    </script>
    @endpush
            @endif
        </div>
    </div>
</x-app-layout>
