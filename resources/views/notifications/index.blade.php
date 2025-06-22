<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('common.notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="space-y-4">
                        @forelse($notifications as $notification)
                            <div class="flex items-start space-x-4 {{ $notification->read_at ? 'opacity-75' : '' }} bg-white hover:bg-gray-50 rounded-lg border border-gray-200 p-4 transition-colors duration-200">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('notifications.show', $notification->id) }}" class="block group">
                                        @php
                                            $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true) ?? [];
                                        @endphp
                                        <p class="text-sm font-medium text-gray-900 group-hover:text-blue-600">
                                            {{ $data['message'] ?? 'Notification' }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </p>
                                        @if(isset($data['link']) && $data['link'])
                                            <div class="mt-2">
                                                <span class="text-sm font-medium text-blue-600 group-hover:text-blue-800">
                                                    {{ __('common.click_to_view_details') }} →
                                                </span>
                                            </div>
                                        @endif
                                    </a>
                                </div>
                                <div class="flex-shrink-0 flex space-x-2">
                                    @if(!$notification->read_at)
                                        <form method="POST" action="{{ route('notifications.mark-as-read', $notification->id) }}">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium text-indigo-600 hover:text-indigo-900" title="{{ __('common.mark_as_read') }}" aria-label="{{ __('common.mark_as_read') }}">
                                                {{ __('common.mark_as_read') }}
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')" title="{{ __('common.delete_notification') }}" aria-label="{{ __('common.delete_notification') }}">
                                            {{ __('common.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500">{{ __('common.no_notifications') }}</p>
                        @endforelse
                    </div>

                    @if($notifications->hasPages())
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
