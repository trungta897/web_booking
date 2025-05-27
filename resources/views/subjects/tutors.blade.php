<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-semibold mb-6">Tutors for {{ $subject->name }}</h1>

                    @if($tutors->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($tutors as $tutor)
                                <div class="card-hover p-6 rounded-lg shadow-lg">
                                    <div class="flex items-center mb-4">
                                        <div class="flex-shrink-0">
                                            <img class="h-16 w-16 rounded-full" src="{{ $tutor->user->avatar ? asset('storage/' . $tutor->user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $tutor->user->name }}">
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-lg font-medium text-gray-900">{{ $tutor->user->name }}</h3>
                                            @if($tutor->reviews_count > 0)
                                            <div class="flex items-center mt-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="h-4 w-4 {{ $i <= $tutor->reviews_avg_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                                <span class="ml-2 text-sm text-gray-600">({{ $tutor->reviews_count }} reviews)</span>
                                            </div>
                                            @else
                                                <span class="text-sm text-gray-500 mt-1">No reviews yet</span>
                                            @endif
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2"><strong>Teaches:</strong> {{ $tutor->subjects->pluck('name')->implode(', ') }}</p>
                                    <p class="text-sm text-gray-600 mb-4 min-h-[60px]">{{ Str::limit($tutor->bio, 120) }}</p>
                                    <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                                        <div class="text-lg font-medium text-gray-900">${{ number_format($tutor->hourly_rate, 2) }}/hr</div>
                                        <a href="{{ route('tutors.show', $tutor) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            View Profile
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8">
                            {{ $tutors->links() }}
                        </div>
                    @else
                        <p class="text-gray-600">No tutors found for {{ $subject->name }}.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
