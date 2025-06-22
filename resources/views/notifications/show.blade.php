@php
    // Ensure notification data is properly decoded as array
    $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true) ?? [];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('common.notification_details') }}
            </h2>
            <a href="{{ route('notifications.index') }}" class="btn-secondary text-sm">
                ← {{ __('common.back_to_notifications') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Notification Header -->
                    <div class="flex items-start justify-between mb-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    {{ $data['message'] ?? __('common.notification') }}
                                </h3>
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ $notification->created_at->format('d-m-Y H:i') }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        @if($notification->read_at)
                                            {{ __('common.read_at') }}: {{ $notification->read_at->format('d-m-Y H:i') }}
                                        @else
                                            {{ __('common.unread') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            @if(!$notification->read_at)
                                <form method="POST" action="{{ route('notifications.mark-as-read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="btn-secondary text-sm">
                                        {{ __('common.mark_as_read') }}
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-danger text-sm" onclick="return confirm('{{ __('common.are_you_sure') }}')">
                                    {{ __('common.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Notification Content -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h4 class="font-medium text-gray-900 mb-3">{{ __('common.notification_content') }}</h4>

                        @if(isset($data['message']))
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.message') }}</label>
                                <p class="text-gray-900 bg-white p-3 rounded border">{{ $data['message'] }}</p>
                            </div>
                        @endif

                        @if(isset($data['type']))
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.type') }}</label>
                                <span class="badge-primary">{{ ucfirst(str_replace('_', ' ', $data['type'])) }}</span>
                            </div>
                        @endif

                        @if(isset($data['details']))
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.details') }}</label>
                                <p class="text-gray-900 bg-white p-3 rounded border">{{ $data['details'] }}</p>
                            </div>
                        @endif

                        <!-- Specific notification type fields -->
                        @if(isset($data['type']) && $data['type'] === 'booking_confirmation')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                @if(isset($data['booking_id']))
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.booking_id') }}</label>
                                        <p class="text-gray-900 bg-white p-2 rounded border text-sm">{{ $data['booking_id'] }}</p>
                                    </div>
                                @endif

                                @if(isset($data['tutor_name']))
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.tutor') }}</label>
                                        <p class="text-gray-900 bg-white p-2 rounded border text-sm">{{ $data['tutor_name'] }}</p>
                                    </div>
                                @endif

                                @if(isset($data['subject']))
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.subject') }}</label>
                                        <p class="text-gray-900 bg-white p-2 rounded border text-sm">{{ $data['subject'] }}</p>
                                    </div>
                                @endif

                                @if(isset($data['date']) && isset($data['time']))
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.schedule') }}</label>
                                        <p class="text-gray-900 bg-white p-2 rounded border text-sm">{{ $data['date'] }} {{ $data['time'] }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(isset($data['type']) && $data['type'] === 'payment_confirmation')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                @if(isset($data['amount']) && isset($data['currency']))
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.amount') }}</label>
                                        <p class="text-gray-900 bg-white p-2 rounded border text-sm font-semibold">${{ $data['amount'] }} {{ $data['currency'] }}</p>
                                    </div>
                                @endif

                                @if(isset($data['payment_method']))
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.payment_method') }}</label>
                                        <p class="text-gray-900 bg-white p-2 rounded border text-sm">{{ $data['payment_method'] }}</p>
                                    </div>
                                @endif

                                @if(isset($data['transaction_id']))
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.transaction_id') }}</label>
                                        <p class="text-gray-900 bg-white p-2 rounded border text-sm font-mono">{{ $data['transaction_id'] }}</p>
                                    </div>
                                @endif

                                @if(isset($data['booking_id']))
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.booking_id') }}</label>
                                        <p class="text-gray-900 bg-white p-2 rounded border text-sm">{{ $data['booking_id'] }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(isset($data['type']) && $data['type'] === 'user_registration')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                @if(isset($data['user_name']))
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.user_name') }}</label>
                                        <p class="text-gray-900 bg-white p-2 rounded border text-sm">{{ $data['user_name'] }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if(isset($data['link']) && $data['link'])
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('common.related_link') }}</label>
                                @php
                                    // Convert common paths to route names for display
                                    $displayLink = $data['link'];
                                    $actualLink = $data['link'];
                                    if ($actualLink === '/bookings' || $actualLink === 'bookings') {
                                        $actualLink = route('bookings.index');
                                        $displayLink = 'Bookings Page';
                                    } elseif (str_starts_with($actualLink, '/bookings/') && is_numeric(basename($actualLink))) {
                                        $bookingId = basename($actualLink);
                                        $actualLink = route('bookings.show', $bookingId);
                                        $displayLink = 'Booking Details #' . $bookingId;
                                    } elseif ($actualLink === '/messages' || $actualLink === 'messages') {
                                        $actualLink = route('messages.index');
                                        $displayLink = 'Messages Page';
                                    } elseif ($actualLink === '/notifications' || $actualLink === 'notifications') {
                                        $actualLink = route('notifications.index');
                                        $displayLink = 'Notifications Page';
                                    } elseif (!str_starts_with($actualLink, 'http')) {
                                        $actualLink = url($actualLink);
                                    }
                                @endphp
                                <a href="{{ $actualLink }}" class="text-blue-600 hover:text-blue-800 underline bg-white p-2 rounded border block">
                                    {{ $displayLink }}
                                </a>
                            </div>
                        @endif

                        <!-- Additional Data Display for debugging (only if there are extra fields) -->
                        @php
                            $knownFields = ['message', 'type', 'details', 'link', 'booking_id', 'tutor_name', 'student_name', 'subject', 'date', 'time', 'amount', 'currency', 'payment_method', 'transaction_id', 'user_name', 'action_required'];
                            $extraData = array_diff_key($data, array_flip($knownFields));
                        @endphp
                        @if(count($extraData) > 0)
                            <div class="mt-6">
                                <h5 class="font-medium text-gray-700 mb-3">{{ __('common.additional_details') }}</h5>
                                <div class="bg-white rounded border p-3">
                                    <pre class="text-xs text-gray-600 whitespace-pre-wrap">{{ json_encode($extraData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    @if(isset($data['link']) && $data['link'])
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="font-medium text-gray-900 mb-3">{{ __('common.quick_actions') }}</h4>
                            <div class="flex space-x-3">
                                @php
                                    // Convert common paths to route names
                                    $link = $data['link'];
                                    if ($link === '/bookings' || $link === 'bookings') {
                                        $link = route('bookings.index');
                                    } elseif (str_starts_with($link, '/bookings/') && is_numeric(basename($link))) {
                                        $bookingId = basename($link);
                                        $link = route('bookings.show', $bookingId);
                                    } elseif ($link === '/messages' || $link === 'messages') {
                                        $link = route('messages.index');
                                    } elseif ($link === '/notifications' || $link === 'notifications') {
                                        $link = route('notifications.index');
                                    } elseif (!str_starts_with($link, 'http')) {
                                        // For relative URLs, use url() helper
                                        $link = url($link);
                                    }
                                @endphp
                                <a href="{{ $link }}" class="btn-primary">
                                    {{ __('common.view_related_content') }}
                                </a>
                                <a href="{{ route('notifications.index') }}" class="btn-secondary">
                                    {{ __('common.back_to_notifications') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
