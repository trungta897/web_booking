<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Test VNPay Payment') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">VNPay Configuration Status</h3>

                    <div class="space-y-4">
                        <div class="flex items-center space-x-2">
                            <span class="w-4 h-4 rounded-full {{ config('services.vnpay.tmn_code') ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            <span>TMN Code: {{ config('services.vnpay.tmn_code') ? 'Configured' : 'Not configured' }}</span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <span class="w-4 h-4 rounded-full {{ config('services.vnpay.hash_secret') ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            <span>Hash Secret: {{ config('services.vnpay.hash_secret') ? 'Configured' : 'Not configured' }}</span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <span class="w-4 h-4 rounded-full {{ config('services.vnpay.url') ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            <span>VNPay URL: {{ config('services.vnpay.url') }}</span>
                        </div>

                        <div class="flex items-center space-x-2">
                            <span class="w-4 h-4 rounded-full {{ config('services.vnpay.return_url') ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            <span>Return URL: {{ config('services.vnpay.return_url') }}</span>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h4 class="text-md font-semibold mb-4">Test VNPay Payment URL Generation</h4>

                        @if(config('services.vnpay.tmn_code') && config('services.vnpay.hash_secret'))
                            <form id="vnpay-test-form" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Amount (VND)</label>
                                    <input type="number" name="amount" value="100000" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Order Info</label>
                                    <input type="text" name="order_info" value="Test payment" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm">
                                </div>

                                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50" id="generate-btn">
                                    Generate VNPay URL
                                </button>
                            </form>

                            <div id="result" class="mt-4 hidden">
                                <h5 class="font-semibold text-green-600">Generated Payment URL:</h5>
                                <div class="mt-2 p-3 bg-gray-100 rounded">
                                    <a id="payment-link" href="#" target="_blank" class="text-blue-600 hover:text-blue-800 break-all"></a>
                                </div>
                                <button id="test-payment" class="mt-2 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Test Payment (Open in New Tab)
                                </button>
                            </div>

                            <div id="error" class="mt-4 hidden">
                                <h5 class="font-semibold text-red-600">Error:</h5>
                                <div class="mt-2 p-3 bg-red-100 rounded">
                                    <p id="error-message" class="text-red-800"></p>
                                </div>
                            </div>
                        @else
                            <p class="text-red-600">VNPay is not properly configured. Please check your .env file.</p>
                        @endif
                    </div>

                    <div class="mt-8">
                        <h4 class="text-md font-semibold mb-4">Available Routes</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Create VNPay Payment: POST /bookings/{booking}/payment/vnpay</li>
                            <li>VNPay Return: GET /payments/vnpay/return</li>
                            <li>VNPay IPN: POST /payments/vnpay/ipn</li>
                        </ul>
                    </div>

                    <div class="mt-8">
                        <h4 class="text-md font-semibold mb-4">Environment Variables</h4>
                        <pre class="bg-gray-100 p-4 rounded text-sm">
VNPAY_TMN_CODE={{ config('services.vnpay.tmn_code') }}
VNPAY_HASH_SECRET={{ config('services.vnpay.hash_secret') ? str_repeat('*', strlen(config('services.vnpay.hash_secret'))) : 'Not set' }}
VNPAY_URL={{ config('services.vnpay.url') }}
VNPAY_RETURN_URL={{ config('services.vnpay.return_url') }}
                        </pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('vnpay-test-form');
            const generateBtn = document.getElementById('generate-btn');
            const resultDiv = document.getElementById('result');
            const errorDiv = document.getElementById('error');
            const paymentLink = document.getElementById('payment-link');
            const testPaymentBtn = document.getElementById('test-payment');
            const errorMessage = document.getElementById('error-message');

            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    // Reset UI
                    generateBtn.disabled = true;
                    generateBtn.textContent = 'Generating...';
                    resultDiv.classList.add('hidden');
                    errorDiv.classList.add('hidden');

                    try {
                        const formData = new FormData(form);

                        const response = await fetch('{{ route('test.vnpay') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            paymentLink.href = data.payment_url;
                            paymentLink.textContent = data.payment_url;
                            resultDiv.classList.remove('hidden');

                            testPaymentBtn.onclick = function() {
                                window.open(data.payment_url, '_blank');
                            };
                        } else {
                            errorMessage.textContent = data.error || 'Unknown error occurred';
                            errorDiv.classList.remove('hidden');
                        }
                    } catch (error) {
                        errorMessage.textContent = 'Network error: ' + error.message;
                        errorDiv.classList.remove('hidden');
                    } finally {
                        generateBtn.disabled = false;
                        generateBtn.textContent = 'Generate VNPay URL';
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
