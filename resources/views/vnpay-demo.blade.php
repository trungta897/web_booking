<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('VNPay Payment Demo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">{{ __('Trải nghiệm thanh toán VNPay') }}</h3>
                        <p class="text-gray-600">{{ __('Thử nghiệm tính năng thanh toán VNPay với số tiền tùy chọn') }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Demo Form -->
                        <div>
                            <h4 class="text-md font-semibold mb-4">{{ __('Demo VNPay Payment') }}</h4>

                            @if(config('services.vnpay.tmn_code') && config('services.vnpay.hash_secret'))
                                <form id="vnpay-demo-form" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Số tiền (VND)') }}</label>
                                        <select name="amount" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="50000">50,000 VND</option>
                                            <option value="100000" selected>100,000 VND</option>
                                            <option value="200000">200,000 VND</option>
                                            <option value="500000">500,000 VND</option>
                                            <option value="1000000">1,000,000 VND</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Thông tin đơn hàng') }}</label>
                                        <input type="text" name="order_info" value="Demo VNPay Payment" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>

                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors disabled:opacity-50" id="demo-btn">
                                        <i class="fas fa-credit-card mr-2"></i>
                                        {{ __('Thử nghiệm thanh toán VNPay') }}
                                    </button>
                                </form>

                                <div id="result" class="mt-6 hidden">
                                    <h5 class="font-semibold text-green-600">{{ __('Đã tạo URL thanh toán:') }}</h5>
                                    <div class="mt-2 p-3 bg-gray-100 rounded">
                                        <a id="payment-link" href="#" target="_blank" class="text-blue-600 hover:text-blue-800 break-all text-sm"></a>
                                    </div>
                                    <button id="test-payment" class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                        {{ __('Mở trang thanh toán VNPay') }}
                                    </button>
                                </div>

                                <div id="error" class="mt-4 hidden">
                                    <h5 class="font-semibold text-red-600">{{ __('Lỗi:') }}</h5>
                                    <div class="mt-2 p-3 bg-red-100 rounded">
                                        <p id="error-message" class="text-red-800"></p>
                                    </div>
                                </div>
                            @else
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                                    <strong>{{ __('Lỗi cấu hình!') }}</strong>
                                    <p>{{ __('VNPay chưa được cấu hình đúng. Vui lòng liên hệ quản trị viên.') }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Information -->
                        <div>
                            <h4 class="text-md font-semibold mb-4">{{ __('Thông tin VNPay') }}</h4>

                            <div class="space-y-4">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <h5 class="font-semibold text-blue-900 mb-2">{{ __('Các phương thức thanh toán hỗ trợ:') }}</h5>
                                    <ul class="text-sm text-blue-700 space-y-1">
                                        <li>• {{ __('Internet Banking của các ngân hàng Việt Nam') }}</li>
                                        <li>• {{ __('Thẻ ATM nội địa') }}</li>
                                        <li>• {{ __('Ví điện tử VNPay') }}</li>
                                        <li>• {{ __('QR Code thanh toán') }}</li>
                                    </ul>
                                </div>

                                <div class="bg-yellow-50 p-4 rounded-lg">
                                    <h5 class="font-semibold text-yellow-900 mb-2">{{ __('Lưu ý:') }}</h5>
                                    <ul class="text-sm text-yellow-700 space-y-1">
                                        <li>• {{ __('Đây là môi trường demo/sandbox') }}</li>
                                        <li>• {{ __('Không có tiền thật được trừ') }}</li>
                                        <li>• {{ __('Sử dụng thông tin thẻ test của VNPay') }}</li>
                                    </ul>
                                </div>

                                <div class="bg-green-50 p-4 rounded-lg">
                                    <h5 class="font-semibold text-green-900 mb-2">{{ __('Bảo mật:') }}</h5>
                                    <ul class="text-sm text-green-700 space-y-1">
                                        <li>• {{ __('Mã hóa SSL 256-bit') }}</li>
                                        <li>• {{ __('Tuân thủ chuẩn bảo mật PCI DSS') }}</li>
                                        <li>• {{ __('Xác thực OTP 2 lớp') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('vnpay-demo-form');
            const demoBtn = document.getElementById('demo-btn');
            const resultDiv = document.getElementById('result');
            const errorDiv = document.getElementById('error');
            const paymentLink = document.getElementById('payment-link');
            const testPaymentBtn = document.getElementById('test-payment');
            const errorMessage = document.getElementById('error-message');

            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    // Reset UI
                    demoBtn.disabled = true;
                    demoBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>{{ __("Đang tạo...") }}';
                    resultDiv.classList.add('hidden');
                    errorDiv.classList.add('hidden');

                    try {
                        const formData = new FormData(form);

                        const response = await fetch('{{ route('vnpay.demo.create') }}', {
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
                            errorMessage.textContent = data.error || '{{ __("Có lỗi xảy ra") }}';
                            errorDiv.classList.remove('hidden');
                        }
                    } catch (error) {
                        errorMessage.textContent = '{{ __("Lỗi kết nối:") }} ' + error.message;
                        errorDiv.classList.remove('hidden');
                    } finally {
                        demoBtn.disabled = false;
                        demoBtn.innerHTML = '<i class="fas fa-credit-card mr-2"></i>{{ __("Thử nghiệm thanh toán VNPay") }}';
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
