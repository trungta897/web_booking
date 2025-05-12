<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Favorite Tutors') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($favorites->isEmpty())
                        <div class="text-center py-8">
                            <p class="text-gray-500">You haven't added any tutors to your favorites yet.</p>
                            <a href="{{ route('tutors.index') }}" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest hover:bg-blue-700">
                                Find Tutors
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($favorites as $tutor)
                                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $tutor->user->name }}
                                            </h3>
                                            <form action="{{ route('tutors.favorite', $tutor) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-red-500 hover:text-red-700">
                                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                        <div class="mb-4">
                                            <div class="flex items-center mb-2">
                                                <div class="flex items-center">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="w-5 h-5 {{ $i <= $tutor->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    @endfor
                                                </div>
                                                <span class="ml-2 text-sm text-gray-600">
                                                    {{ number_format($tutor->average_rating, 1) }} ({{ $tutor->reviews_count }} reviews)
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <h4 class="text-sm font-medium text-gray-900 mb-2">Subjects</h4>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($tutor->subjects as $subject)
                                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                                        {{ $subject->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="flex justify-between items-center">
                                            <span class="text-lg font-semibold text-gray-900">
                                                ${{ number_format($tutor->hourly_rate, 2) }}/hr
                                            </span>
                                            <a href="{{ route('tutors.show', $tutor) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest hover:bg-blue-700">
                                                View Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $favorites->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
