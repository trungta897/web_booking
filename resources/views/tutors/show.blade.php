<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $tutor->user->name }}'s Profile
            </h2>
            @auth
                @if(auth()->user()->role === 'student')
                    <button
                        onclick="toggleFavorite({{ $tutor->id }})"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition"
                        id="favoriteBtn"
                    >
                        <svg class="h-5 w-5 mr-2" :class="{'text-red-500': isFavorite}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                        </svg>
                        <span id="favoriteText">Add to Favorites</span>
                    </button>
                @endif
            @endauth
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Profile Header -->
                    <div class="flex items-start space-x-6 mb-8">
                        <div class="flex-shrink-0">
                            <img class="h-32 w-32 rounded-full" src="{{ $tutor->user->avatar ? asset('storage/' . $tutor->user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $tutor->user->name }}">
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h1 class="text-2xl font-bold text-gray-900">{{ $tutor->user->name }}</h1>
                                    <div class="flex items-center mt-2">
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="h-5 w-5 {{ $i <= $tutor->reviews_avg_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="ml-2 text-sm text-gray-600">({{ $tutor->reviews_count }} reviews)</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-900">${{ number_format($tutor->hourly_rate, 2) }}/hr</div>
                                    @auth
                                        @if(auth()->user()->role === 'student')
                                            <a href="{{ route('bookings.create', ['tutor' => $tutor->id]) }}" class="mt-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-600 bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Book Now
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </div>
                            <p class="mt-4 text-gray-600">{{ $tutor->bio }}</p>
                        </div>
                    </div>

                    <!-- Subjects -->
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Subjects</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($tutor->subjects as $subject)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                    {{ $subject->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Education -->
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Education</h2>
                        <div class="space-y-4">
                            @if($tutor->education && count($tutor->education) > 0)
                                @foreach($tutor->education as $education)
                                    <div class="border-l-4 border-indigo-500 pl-4">
                                        <h3 class="font-medium text-gray-900">{{ $education->degree }} in {{ $education->field_of_study }}</h3>
                                        <p class="text-gray-600">{{ $education->institution }}</p>
                                        <p class="text-sm text-gray-500">{{ $education->start_year }} - {{ $education->end_year ?? 'Present' }}</p>
                                    </div>
                                @endforeach
                            @else
                                <p class="text-gray-500">No education information available</p>
                            @endif
                        </div>
                    </div>

                    <!-- Availability -->
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Availability</h2>
                        <div class="grid grid-cols-7 gap-2">
                            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                                <div class="text-center">
                                    <div class="font-medium text-gray-900">{{ $day }}</div>
                                    <div class="text-sm text-gray-600" id="availability-{{ strtolower($day) }}">
                                        Loading...
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Reviews</h2>
                        <div class="space-y-6">
                            @forelse($tutor->reviews as $review)
                                <div class="border-b border-gray-200 pb-6 last:border-0 last:pb-0">
                                    <div class="flex items-center mb-2">
                                        @if($review->student)
                                            <img class="h-10 w-10 rounded-full" src="{{ $review->student->avatar ? asset('storage/' . $review->student->avatar) : asset('images/default-avatar.png') }}" alt="{{ $review->student->name }}">
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-gray-900">{{ $review->student->name }}</h3>
                                            @else
                                                <img class="h-10 w-10 rounded-full" src="{{ asset('images/default-avatar.png') }}" alt="Unknown User">
                                                <div class="ml-3">
                                                    <h3 class="text-sm font-medium text-gray-900">Unknown User</h3>
                                            @endif
                                                <div class="flex items-center">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    @endfor
                                                </div>
                                            </div>
                                            <div class="ml-auto text-sm text-gray-500">
                                                {{ $review->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                        <p class="text-gray-600">{{ $review->comment }}</p>
                                    </div>
                                @empty
                                    <p class="text-gray-600">No reviews yet.</p>
                                @endforelse
                        </div>

                        @auth
                            @if(auth()->user()->role === 'student')
                                <div class="mt-8 border-t border-gray-200 pt-8">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Leave a Review</h3>

                                    @if(session('error'))
                                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    <!-- Check if user has any completed bookings with this tutor -->
                                    @php
                                        $completedBookings = \App\Models\Booking::where('student_id', auth()->id())
                                            ->where('tutor_id', $tutor->id)
                                            ->where('status', 'completed')
                                            ->whereDoesntHave('review')
                                            ->get();
                                    @endphp

                                    @if($completedBookings->count() > 0)
                                        <form action="{{ route('tutors.reviews.store', $tutor) }}" method="POST" class="space-y-4">
                                            @csrf

                                            <div>
                                                <label for="booking_id" class="block text-sm font-medium text-gray-700">Select Session</label>
                                                <select name="booking_id" id="booking_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    @foreach($completedBookings as $booking)
                                                        <option value="{{ $booking->id }}">
                                                            {{ $booking->subject->name }} - {{ $booking->start_time->format('M d, Y g:i A') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('booking_id')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
                                                <div class="mt-1 flex items-center">
                                                    <div class="flex space-x-1">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <button type="button" onclick="setRating({{ $i }})" class="star-rating-btn">
                                                                <svg id="star-{{ $i }}" class="h-6 w-6 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                </svg>
                                                            </button>
                                                        @endfor
                                                    </div>
                                                    <input type="hidden" name="rating" id="rating" value="0">
                                                </div>
                                                @error('rating')
                                                    <p class="mt-1 text-sm text-red-600">{{ $errors->first('rating') }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="comment" class="block text-sm font-medium text-gray-700">Comment</label>
                                                <textarea id="comment" name="comment" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Share your experience with this tutor">{{ old('comment') }}</textarea>
                                                @error('comment')
                                                    <p class="mt-1 text-sm text-red-600">{{ $errors->first('comment') }}</p>
                                                @enderror
                                            </div>

                                            <div class="text-right">
                                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    Submit Review
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm text-yellow-700">
                                                        You can only leave a review after completing a session with this tutor.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Favorite functionality
        function toggleFavorite(tutorId) {
            fetch(`/tutors/${tutorId}/favorite`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                const btn = document.getElementById('favoriteBtn');
                const text = document.getElementById('favoriteText');
                if (data.is_favorite) {
                    btn.classList.add('bg-red-50', 'border-red-300', 'text-red-700');
                    text.textContent = 'Remove from Favorites';
                } else {
                    btn.classList.remove('bg-red-50', 'border-red-300', 'text-red-700');
                    text.textContent = 'Add to Favorites';
                }
            });
        }

        // Real-time availability checking
        function checkAvailability() {
            const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            days.forEach(day => {
                fetch(`/tutors/{{ $tutor->id }}/availability/${day}`)
                    .then(response => response.json())
                    .then(data => {
                        const element = document.getElementById(`availability-${day}`);
                        if (data.available) {
                            element.textContent = data.slots.join(', ');
                            element.classList.add('text-green-600');
                        } else {
                            element.textContent = 'Unavailable';
                            element.classList.add('text-red-600');
                        }
                    });
            });
        }

        // Star rating functionality
        function setRating(rating) {
            document.getElementById('rating').value = rating;

            // Update star colors
            for (let i = 1; i <= 5; i++) {
                const star = document.getElementById(`star-${i}`);
                if (i <= rating) {
                    star.classList.remove('text-gray-300');
                    star.classList.add('text-yellow-400');
                } else {
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-300');
                }
            }
        }

        // Check availability on page load
        checkAvailability();
        // Refresh availability every 5 minutes
        setInterval(checkAvailability, 300000);
    </script>
    @endpush
</x-app-layout>
