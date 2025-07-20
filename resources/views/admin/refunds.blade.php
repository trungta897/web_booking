@extends('layouts.admin')

@section('title', __('admin.refunds_management'))

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Enhanced Header with Actions -->
        <div class="mb-8">
            <div class="md:flex md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                <i class="fas fa-undo-alt text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h1 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:text-3xl">
                                {{ __('admin.refunds_management') }}
                            </h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ __('admin.refunds_subtitle') }}
                            </p>
                        </div>
                    </div>
                </div>
                                <div class="mt-4 flex md:mt-0 md:ml-4">
                    <button type="button"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                            onclick="refreshData()">
                        <i class="fas fa-sync-alt mr-2"></i>
                        {{ __('admin.refresh') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Pending Refunds -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600 dark:text-yellow-400"></i>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                {{ __('admin.pending_refunds') }}
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $stats['pending'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Processing Refunds -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cog text-blue-600 dark:text-blue-400"></i>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                {{ __('admin.processing_refunds') }}
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $stats['processing'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Refunds -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check text-green-600 dark:text-green-400"></i>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                {{ __('admin.completed_refunds') }}
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $stats['completed'] }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Amount -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-purple-600 dark:text-purple-400"></i>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                {{ __('admin.total_refund_amount') }}
                            </p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format(abs($stats['total_amount_month']), 0, ',', '.') }}₫
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Metrics Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Processing Time Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-stopwatch mr-2 text-blue-600 dark:text-blue-400"></i>
                        {{ __('admin.avg_processing_time') }}
                    </h3>
                </div>
                <div class="p-6 text-center">
                    @if($stats['avg_processing_time'])
                        <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                            {{ $stats['avg_processing_time'] }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.hours') }}</div>
                    @else
                        <div class="text-gray-500 dark:text-gray-400">{{ __('admin.no_data_available') }}</div>
                    @endif
                </div>
            </div>

            <!-- Payment Method Breakdown -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-credit-card mr-2 text-green-600 dark:text-green-400"></i>
                        {{ __('admin.by_payment_method') }}
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($stats['by_payment_method'] as $method)
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ ucfirst($method->payment_method) }}
                            </span>
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $method->count }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ number_format(abs($method->total_amount), 0, ',', '.') }}₫
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 dark:text-gray-400">{{ __('admin.no_data_available') }}</div>
                    @endforelse
                </div>
            </div>

            <!-- Refund Type Breakdown -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                        <i class="fas fa-chart-pie mr-2 text-purple-600 dark:text-purple-400"></i>
                        {{ __('admin.by_refund_type') }}
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    @forelse($stats['by_type'] as $type)
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                @if($type->type === 'refund')
                                    {{ __('admin.full_refund') }}
                                @else
                                    {{ __('admin.partial_refund') }}
                                @endif
                            </span>
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $type->count }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ number_format(abs($type->total_amount), 0, ',', '.') }}₫
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-gray-500 dark:text-gray-400">{{ __('admin.no_data_available') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Trends Chart -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 mb-8">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-chart-line mr-2 text-indigo-600 dark:text-indigo-400"></i>
                    {{ __('admin.refund_trends') }} ({{ __('admin.monthly_view') }})
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('admin.refund_trends_description') }}
                </p>
            </div>
            <div class="p-6">
                <div style="position: relative; height: 400px;">
                    <canvas id="refundTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Refund Reasons -->
        @if(count($stats['top_reasons']) > 0)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 mb-8">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-list-alt mr-2 text-orange-600 dark:text-orange-400"></i>
                    {{ __('admin.top_refund_reasons') }}
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($stats['top_reasons'] as $reasonText => $count)
                        <div class="flex justify-between items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $reasonText }}</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Filters Section -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700 mb-8">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                    <i class="fas fa-filter mr-2 text-gray-600 dark:text-gray-400"></i>
                    {{ __('admin.filters') }}
                </h3>
            </div>
            <div class="p-6">
                <form method="GET" action="{{ route('admin.refunds') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('admin.status_filter') }}
                        </label>
                        <select name="status" id="status"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">{{ __('admin.all_statuses') }}</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                                {{ __('admin.status_pending') }}
                            </option>
                            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>
                                {{ __('admin.status_processing') }}
                            </option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>
                                {{ __('admin.status_completed') }}
                            </option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>
                                {{ __('admin.status_failed') }}
                            </option>
                        </select>
                    </div>

                    <!-- Payment Method Filter -->
                    <div>
                        <label for="method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('admin.payment_method_filter') }}
                        </label>
                        <select name="method" id="method"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option value="">{{ __('admin.all_methods') }}</option>
                            <option value="vnpay" {{ request('method') === 'vnpay' ? 'selected' : '' }}>
                                {{ __('admin.vnpay') }}
                            </option>
                            <option value="stripe" {{ request('method') === 'stripe' ? 'selected' : '' }}>
                                {{ __('admin.stripe') }}
                            </option>
                        </select>
                    </div>

                    <!-- From Date -->
                    <div>
                        <label for="from_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('admin.from_date') }}
                        </label>
                        <input type="text" name="from_date" id="from_date" value="{{ request('from_date') }}"
                               placeholder="dd/mm/yy"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <!-- To Date -->
                    <div>
                        <label for="to_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('admin.to_date') }}
                        </label>
                        <input type="text" name="to_date" id="to_date" value="{{ request('to_date') }}"
                               placeholder="dd/mm/yy"
                               class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-end space-x-2">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-filter mr-2"></i>
                            {{ __('admin.filter_button') }}
                        </button>
                        <a href="{{ route('admin.refunds') }}"
                           class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i>
                            {{ __('admin.clear_filters') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Refund Requests List -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('admin.refund_requests_list') }}
                    </h3>
                    <div class="flex space-x-2">
                        <button type="button"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                                onclick="exportData()">
                            <i class="fas fa-download mr-1"></i>
                            {{ __('admin.export') }}
                        </button>
                        <button type="button"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                                onclick="printData()">
                            <i class="fas fa-print mr-1"></i>
                            {{ __('admin.print') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                @if(count($refunds) > 0)
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('admin.booking_id_column') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('admin.student_column') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('admin.amount_column') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('admin.reason_column') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('admin.status_column') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('admin.request_date_column') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('admin.actions_column') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($refunds as $refund)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        #{{ $refund->booking->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $refund->booking->student->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        <span class="font-semibold">{{ number_format(abs((float) $refund->amount), 0, ',', '.') }}₫</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white max-w-xs truncate">
                                        @php
                                            $metadata = is_array($refund->metadata) ? $refund->metadata : [];
                                            $reason = $metadata['reason'] ?? $metadata['refund_reason'] ?? 'N/A';
                                        @endphp
                                        {{ $reason }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClasses = [
                                                'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                                                'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                                                'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                                                'failed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                                            ];
                                        @endphp
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClasses[$refund->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300' }}">
                                            {{ __('admin.status_' . $refund->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $refund->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.bookings.show', $refund->booking) }}"
                                           class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200">
                                            {{ __('admin.view_details_action') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    @if($refunds->hasPages())
                        <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                            {{ $refunds->appends(request()->query())->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                            <i class="fas fa-inbox text-gray-400 dark:text-gray-500 text-3xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            {{ __('admin.no_refunds_found') }}
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            {{ __('admin.no_refunds_message') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


    @push('scripts')
        <script src="{{ asset('js/pages/admin-refunds.js') }}"></script>
    @endpush
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@endpush
