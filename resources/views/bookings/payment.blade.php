<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Complete Payment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">
                        Booking Details
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0 mr-4">
                                        <img class="h-12 w-12 rounded-full" src="{{ $booking->tutor->user->avatar ? asset('storage/' . $booking->tutor->user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $booking->tutor->user->name }}">
                                    </div>
                                    <div>
                                        <h3 class="text-md font-medium text-gray-900">{{ $booking->tutor->user->name }}</h3>
                                        <p class="text-sm text-gray-600">{{ $booking->subject->name }}</p>
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Date:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $booking->start_time->format('F j, Y') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Time:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $booking->start_time->format('H:i') }} - {{ $booking->end_time->format('H:i') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Duration:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $booking->duration }} minutes</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-500">Hourly Rate:</span>
                                        <span class="text-sm font-medium text-gray-900">${{ number_format($booking->tutor->hourly_rate, 2) }}/hour</span>
                                    </div>
                                    <div class="flex justify-between pt-3 border-t border-gray-200">
                                        <span class="text-base font-medium text-gray-900">Total:</span>
                                        <span class="text-base font-bold text-gray-900">${{ number_format($booking->price, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-md font-medium text-gray-900 mb-4">Payment Information</h3>

                            <div id="payment-error" class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg hidden"></div>

                            <form id="payment-form" class="space-y-4">
                                <div>
                                    <label for="card-element" class="block text-sm font-medium text-gray-700 mb-2">Credit or debit card</label>
                                    <div id="card-element" class="p-3 border border-gray-300 rounded-md"></div>
                                    <div id="card-errors" class="mt-1 text-sm text-red-600"></div>
                                </div>

                                <button type="submit" id="submit-button" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    <span id="button-text">Pay ${{ number_format($booking->price, 2) }}</span>
                                    <span id="spinner" class="hidden">
                                        <svg class="animate-spin ml-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </form>

                            <p class="mt-4 text-xs text-gray-500">
                                Your payment information is processed securely through Stripe. We do not store your card details.
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
            const stripe = Stripe('{{ config('services.stripe.key') }}');
            const elements = stripe.elements();

            // Create card element
            const card = elements.create('card', {
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

            // Mount the card element
            card.mount('#card-element');

            // Handle validation errors
            card.addEventListener('change', function(event) {
                const displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            // Handle form submission
            const form = document.getElementById('payment-form');
            const submitButton = document.getElementById('submit-button');
            const spinner = document.getElementById('spinner');
            const buttonText = document.getElementById('button-text');
            const paymentError = document.getElementById('payment-error');

            form.addEventListener('submit', async function(event) {
                event.preventDefault();

                // Disable the submit button and show spinner
                submitButton.disabled = true;
                spinner.classList.remove('hidden');
                buttonText.classList.add('opacity-50');

                try {
                    // Create the payment intent
                    const response = await fetch('{{ route('payments.create-intent', $booking) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                    });

                    const data = await response.json();

                    if (data.error) {
                        throw new Error(data.error);
                    }

                    // Confirm card payment
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
                        // Redirect to the booking confirmation page
                        window.location.href = '{{ route('payments.confirm', $booking) }}';
                    }
                } catch (error) {
                    // Show the error to the customer
                    paymentError.textContent = error.message;
                    paymentError.classList.remove('hidden');

                    // Enable the submit button
                    submitButton.disabled = false;
                    spinner.classList.add('hidden');
                    buttonText.classList.remove('opacity-50');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
