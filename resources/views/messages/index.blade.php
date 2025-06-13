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
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Your Conversations</h3>

                    <div class="space-y-4">
                        @forelse($userConversations as $conversation)
                            <div class="flex items-center p-4 hover:bg-gray-50 rounded-lg transition-colors {{ $conversation['unread_count'] > 0 ? 'bg-indigo-50' : '' }}">
                                <div class="flex-shrink-0 mr-4">
                                    <img class="h-12 w-12 rounded-full" src="{{ $conversation['user']->avatar ? asset('storage/' . $conversation['user']->avatar) : asset('images/default-avatar.png') }}" alt="{{ $conversation['user']->name }}">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ $conversation['user']->name }}
                                            @if($conversation['user']->role === 'tutor')
                                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Tutor
                                                </span>
                                            @elseif($conversation['user']->role === 'student')
                                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Student
                                                </span>
                                            @elseif($conversation['user']->role === 'admin')
                                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                                    Admin
                                                </span>
                                            @endif
                                            @if($conversation['unread_count'] > 0)
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    {{ $conversation['unread_count'] }} new
                                                </span>
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($conversation['last_message']->created_at)->diffForHumans() }}
                                        </p>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600 truncate">
                                        @if($conversation['last_message']->sender_id === auth()->id())
                                            <span class="text-gray-400">You: </span>
                                        @endif
                                        {{ $conversation['last_message']->message }}
                                    </p>
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('messages.show', $conversation['user']) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        View
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No messages</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    You haven't started any conversations yet.
                                </p>
                                <div class="mt-6">
                                    <a href="{{ route('tutors.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Find Tutors
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
