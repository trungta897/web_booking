<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Conversation with') }} {{ $user->name }}
            </h2>
            <a href="{{ route('messages.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to All Messages') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div id="messages-container" class="space-y-6 max-h-96 overflow-y-auto mb-6">
                        @forelse($messages as $message)
                            <div class="flex items-start space-x-4 {{ $message->sender_id === auth()->id() ? 'justify-end' : '' }}">
                                <div class="flex-shrink-0 {{ $message->sender_id === auth()->id() ? 'order-last ml-4' : 'mr-4' }}">
                                    <img class="h-10 w-10 rounded-full" src="{{ $message->sender->avatar ? asset('storage/' . $message->sender->avatar) : asset('images/default-avatar.png') }}" alt="{{ $message->sender->name }}">
                                </div>
                                <div class="flex-1 min-w-0 max-w-md">
                                    <div class="{{ $message->sender_id === auth()->id() ? 'bg-blue-100 rounded-tl-lg rounded-br-lg rounded-bl-lg' : 'bg-gray-100 rounded-tr-lg rounded-br-lg rounded-bl-lg' }} p-3">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $message->sender->name }}
                                            <span class="ml-2 text-xs font-normal text-gray-500">{{ $message->created_at->format('M d, Y H:i') }}</span>
                                        </p>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ $message->message }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500">No messages yet. Start the conversation!</p>
                        @endforelse
                    </div>

                    <form action="{{ route('messages.store', $user) }}" method="POST" class="mt-6">
                        @csrf
                        <div class="flex items-start space-x-4">
                            <div class="flex-1 min-w-0">
                                <label for="message" class="sr-only">Message</label>
                                <textarea
                                    id="message"
                                    name="message"
                                    rows="3"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Type your message here..."
                                    required
                                ></textarea>
                                <input type="hidden" name="receiver_id" value="{{ $user->id }}">
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                                Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">About {{ $user->name }}</h3>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <img class="h-16 w-16 rounded-full" src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $user->name }}">
                        </div>
                        <div>
                            <h4 class="text-md font-medium text-gray-900">{{ $user->name }}</h4>
                            <p class="text-sm text-gray-600">{{ $user->email }}</p>

                            @if($user->role === 'tutor' && $user->tutor)
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Subjects:</span>
                                        {{ $user->tutor->subjects->pluck('name')->join(', ') }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Hourly Rate:</span>
                                        ${{ $user->tutor->hourly_rate }}/hour
                                    </p>
                                    <div class="mt-2">
                                        <a href="{{ route('tutors.show', $user->tutor) }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                                            View Full Profile
                                        </a>
                                        @if(auth()->user()->role === 'student')
                                            <a href="{{ route('bookings.create', $user->tutor) }}" class="ml-4 text-sm text-indigo-600 hover:text-indigo-500">
                                                Book a Session
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

    @push('scripts')
        <script src="{{ asset('js/pages/messages-show.js') }}"></script>
    @endpush
</x-app-layout>
