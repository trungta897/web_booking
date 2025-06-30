<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('common.Transaction History') }} - Booking #{{ $booking->id }}
            </h2>
            <a href="{{ route('bookings.show', $booking) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                {{ __('common.back') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Transaction History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-6">{{ __('common.Transaction History') }}</h3>

                    @if($transactions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('common.transaction_id') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('common.type') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('common.payment_method') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('common.amount') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('common.status') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('common.date') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($transactions as $transaction)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $transaction->transaction_id }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ __('booking.transaction_type.' . $transaction->type) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-sm text-gray-900">{{ $transaction->payment_method_name }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    @php
                                                        $currency = $transaction->currency ?? 'VND';
                                                        $amount = $transaction->amount;
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
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $transaction->status_badge_class }}">
                                                    {{ __('booking.status.' . $transaction->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('common.No transactions yet') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('common.No payment transactions have been made for this booking.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>


















