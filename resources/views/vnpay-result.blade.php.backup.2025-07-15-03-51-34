<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kết quả thanh toán VNPay') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">


                    <!-- Result Status -->
                    <div class="text-center mb-8">
                        @if($result['success'])
                            <div class="mb-6">
                                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto animate-bounce">
                                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-3xl font-bold text-green-600 mb-3">🎉 {{ __('Thanh toán thành công!') }}</h3>
                            <p class="text-lg text-gray-600 mb-2">{{ __('Giao dịch thành công') }}</p>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                                <p class="text-green-800 font-medium">
                                    ✅ {{ __('common.payment_processed_successfully') }}
                                </p>
                                <p class="text-green-700 text-sm mt-1">
                                    {{ __('common.email_confirmation_sent') }}
                                </p>
                            </div>
                        @elseif(isset($params['vnp_ResponseCode']) && $params['vnp_ResponseCode'] === '24')
                            <div class="mb-6">
                                <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto">
                                    <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-3xl font-bold text-yellow-600 mb-3">⚠️ {{ __('Thanh toán đã bị hủy') }}</h3>
                            <p class="text-lg text-gray-600 mb-2">{{ __('Bạn đã hủy thanh toán. Bạn có thể thử lại sau.') }}</p>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-4">
                                <p class="text-yellow-800 font-medium">
                                    ℹ️ {{ __('common.transaction_not_completed') }}
                                </p>
                                <p class="text-yellow-700 text-sm mt-1">
                                    {{ __('common.can_retry_payment') }}
                                </p>
                            </div>
                        @else
                            <div class="mb-6">
                                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto">
                                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>
                            <h3 class="text-3xl font-bold text-red-600 mb-3">❌ {{ __('Thanh toán thất bại!') }}</h3>
                            <p class="text-lg text-gray-600 mb-2">{{ $result['message'] }}</p>
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mt-4">
                                <p class="text-red-800 font-medium">
                                    🚫 {{ __('common.transaction_failed') }}
                                </p>
                                <p class="text-red-700 text-sm mt-1">
                                    {{ __('common.check_info_and_retry') }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Transaction Details -->
                    @if(isset($params['vnp_TxnRef']))
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 mb-6 border border-blue-100">
                        <h4 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                            <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ __('Chi tiết giao dịch') }}
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if(isset($params['vnp_TxnRef']))
                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                <span class="text-sm text-gray-500 font-medium">{{ __('Mã tham chiếu:') }}</span>
                                <p class="font-bold text-gray-900 text-lg">{{ $params['vnp_TxnRef'] }}</p>
                            </div>
                            @endif

                            @if(isset($params['vnp_Amount']))
                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                <span class="text-sm text-gray-500 font-medium">{{ __('Số tiền:') }}</span>
                                <p class="font-bold text-green-600 text-xl">{{ number_format($params['vnp_Amount'] / 100, 0, ',', '.') }} VND</p>
                            </div>
                            @endif

                            @if(isset($params['vnp_OrderInfo']))
                            <div class="bg-white rounded-lg p-4 shadow-sm md:col-span-2">
                                <span class="text-sm text-gray-500 font-medium">{{ __('Thông tin đơn hàng:') }}</span>
                                <p class="font-medium text-gray-900">{{ $params['vnp_OrderInfo'] }}</p>
                            </div>
                            @endif

                            @if(isset($params['vnp_TransactionNo']))
                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                <span class="text-sm text-gray-500 font-medium">{{ __('Mã giao dịch VNPay:') }}</span>
                                <p class="font-bold text-blue-600">{{ $params['vnp_TransactionNo'] }}</p>
                            </div>
                            @endif

                            @if(isset($params['vnp_ResponseCode']))
                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                <span class="text-sm text-gray-500 font-medium">{{ __('Mã phản hồi:') }}</span>
                                <p class="font-bold {{ $params['vnp_ResponseCode'] === '00' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $params['vnp_ResponseCode'] }}
                                    @if($params['vnp_ResponseCode'] === '00')
                                        <span class="text-sm text-green-500">(Thành công)</span>
                                    @endif
                                </p>
                            </div>
                            @endif

                            @if(isset($params['vnp_PayDate']))
                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                <span class="text-sm text-gray-500 font-medium">{{ __('Thời gian thanh toán:') }}</span>
                                <p class="font-medium text-gray-900">
                                    {{ \Carbon\Carbon::createFromFormat('YmdHis', $params['vnp_PayDate'])->format('d/m/Y H:i:s') }}
                                </p>
                            </div>
                            @endif

                            @if(isset($params['vnp_BankCode']))
                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                <span class="text-sm text-gray-500 font-medium">{{ __('Ngân hàng:') }}</span>
                                <p class="font-medium text-gray-900">{{ $params['vnp_BankCode'] }}</p>
                            </div>
                            @endif

                            @if(isset($params['vnp_CardType']))
                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                <span class="text-sm text-gray-500 font-medium">{{ __('Loại thẻ:') }}</span>
                                <p class="font-medium text-gray-900">{{ $params['vnp_CardType'] }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            @if(isset($params['vnp_ResponseCode']) && $params['vnp_ResponseCode'] === '24')
                                <!-- User cancelled - show dashboard and retry options -->
                                @auth
                                    @if(auth()->user()->role === 'student')
                                        <a href="{{ route('student.dashboard') }}" class="inline-flex justify-center items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:from-blue-700 hover:to-blue-800 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                            </svg>
                                            {{ __('Về Dashboard') }}
                                        </a>
                                    @endif
                                @endauth
                                <a href="{{ route('bookings.index') }}" class="inline-flex justify-center items-center px-8 py-4 bg-gradient-to-r from-green-600 to-green-700 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:from-green-700 hover:to-green-800 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    {{ __('Thử thanh toán lại') }}
                                </a>
                            @else
                                <a href="{{ route('bookings.index') }}" class="inline-flex justify-center items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:from-blue-700 hover:to-blue-800 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 6v6m-4-6h8m-8 0a2 2 0 00-2 2v6a2 2 0 002 2h8a2 2 0 002-2v-6a2 2 0 00-2-2"></path>
                                    </svg>
                                    {{ __('Xem đặt lịch') }}
                                </a>
                            @endif

                        @auth
                            @if(auth()->user()->role === 'student')
                                <a href="{{ route('student.dashboard') }}" class="inline-flex justify-center items-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-indigo-700 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:from-indigo-700 hover:to-indigo-800 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ __('Dashboard của tôi') }}
                                </a>
                            @endif
                        @endauth

                        <a href="{{ route('home') }}" class="inline-flex justify-center items-center px-8 py-4 bg-gradient-to-r from-gray-600 to-gray-700 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:from-gray-700 hover:to-gray-800 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            {{ __('Về trang chủ') }}
                        </a>
                    </div>

                    <!-- Success Tips -->
                    @if($result['success'])
                    <div class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">{{ __('common.helpful_information') }}</h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>{{ __('common.email_confirmation_coming') }}</li>
                                        <li>{{ __('common.schedule_updated') }}</li>
                                        <li>{{ __('common.contact_tutor_via_message') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Auto redirect after successful payment (optional)
        @if($result['success'])
            // Uncomment if you want auto redirect after 10 seconds
            // setTimeout(() => {
            //     window.location.href = '{{ route("bookings.index") }}';
            // }, 10000);
        @endif

        // Add confetti effect for success
        @if($result['success'])
            document.addEventListener('DOMContentLoaded', function() {
                // Simple confetti effect using CSS animation
                const confettiContainer = document.createElement('div');
                confettiContainer.style.position = 'fixed';
                confettiContainer.style.top = '0';
                confettiContainer.style.left = '0';
                confettiContainer.style.width = '100%';
                confettiContainer.style.height = '100%';
                confettiContainer.style.pointerEvents = 'none';
                confettiContainer.style.zIndex = '9999';

                for (let i = 0; i < 30; i++) {
                    const confetti = document.createElement('div');
                    confetti.style.position = 'absolute';
                    confetti.style.width = '10px';
                    confetti.style.height = '10px';
                    confetti.style.background = `hsl(${Math.random() * 360}, 100%, 50%)`;
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.animation = `fall ${Math.random() * 3 + 2}s linear infinite`;
                    confetti.style.animationDelay = Math.random() * 2 + 's';
                    confettiContainer.appendChild(confetti);
                }

                document.body.appendChild(confettiContainer);

                // Remove confetti after 5 seconds
                setTimeout(() => {
                    document.body.removeChild(confettiContainer);
                }, 5000);
            });
        @endif
    </script>

    <style>
        @keyframes fall {
            0% { transform: translateY(-100vh) rotate(0deg); }
            100% { transform: translateY(100vh) rotate(360deg); }
        }
    </style>
    @endpush
</x-app-layout>
