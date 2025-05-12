<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Messages') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="space-y-6">
                        @forelse($messages as $message)
                            <div class="flex items-start space-x-4 {{ $message->sender_id === auth()->id() ? 'justify-end' : '' }}">
                                <div class="flex-shrink-0">
                                    <img class="h-10 w-10 rounded-full" src="{{ $message->sender->profile_photo_url }}" alt="{{ $message->sender->name }}">
                                </div>
                                <div class="flex-1 min-w-0 {{ $message->sender_id === auth()->id() ? 'text-right' : '' }}">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $message->sender->name }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $message->created_at->diffForHumans() }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-900">
                                        {{ $message->content }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500">No messages yet.</p>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $messages->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
