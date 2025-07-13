<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('tutors.tutor_profile', ['name' => $tutor->user->name]) }}
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
                        <span id="favoriteText">{{ __('tutors.Add to Favorites') }}</span>
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
                            <img class="h-32 w-32 rounded-full" src="{{ $tutor->user->avatar ? asset('uploads/avatars/' . $tutor->user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $tutor->user->name }}">
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
                                        <span class="ml-2 text-sm text-gray-600">({{ $tutor->reviews_count }} {{ $tutor->reviews_count == 1 ? __('tutors.review') : __('tutors.reviews') }})</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-900">{{ formatCurrency($tutor->hourly_rate) }}<span class="text-sm font-normal">{{ __('tutors.per_hour') }}</span></div>
                                    @auth
                                        @if(auth()->user()->role === 'student')
                                            <a href="{{ route('bookings.create', ['tutor' => $tutor->id]) }}" class="mt-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                {{ __('tutors.book_now') }}
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
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('tutors.subjects') }}</h2>
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
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('tutors.education') }}</h2>
                        <div class="space-y-4">
                            @if($tutor->education && count($tutor->education) > 0)
                                @foreach($tutor->education as $education)
                                    <div class="border border-gray-200 rounded-lg p-6 bg-gradient-to-r from-blue-50 to-indigo-50">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-start mb-3">
                                                    <!-- Education Icon -->
                                                    <div class="flex-shrink-0 mr-4">
                                                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                                            </svg>
                                                        </div>
                                                    </div>

                                                    <div class="flex-1">
                                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $education->degree }}</h3>
                                                        <p class="text-indigo-600 font-medium text-base mb-1">{{ $education->institution }}</p>
                                                        @if($education->year)
                                                            <div class="flex items-center text-sm text-gray-600">
                                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4"/>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18a1 1 0 001-1V10a1 1 0 00-1-1H3a1 1 0 00-1 1v10a1 1 0 001 1z"/>
                                                                </svg>
                                                                {{ $education->year }}
                                                            </div>
                                                        @endif

                                                        <!-- Experience indicator -->
                                                        <div class="mt-2">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                                </svg>
                                                                {{ __('tutors.verified_education') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Display education images -->
                                            @if($education->hasImages())
                                                <div class="ml-6 flex-shrink-0">
                                                    <p class="text-xs text-gray-500 mb-2 text-center">{{ __('tutors.certificate_images') }}</p>
                                                    <div class="grid grid-cols-2 gap-2 max-w-[120px]">
                                                        @foreach($education->getAllImages() as $index => $imageName)
                                                            @if($index < 4) {{-- Show max 4 images --}}
                                                                <div class="relative group">
                                                                    <img src="{{ asset('uploads/education/' . $imageName) }}"
                                                                         alt="Certificate {{ $index + 1 }} for {{ $education->degree }}"
                                                                         class="h-12 w-12 object-cover rounded-lg border-2 border-white shadow-md cursor-pointer hover:scale-105 transition-transform duration-200"
                                                                         onclick="showCertificateModal('{{ asset('uploads/education/' . $imageName) }}', '{{ $education->degree }}', '{{ $education->institution }}')" />
                                                                    <!-- Hover overlay -->
                                                                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 rounded-lg transition-opacity duration-200 flex items-center justify-center">
                                                                        <svg class="w-4 h-4 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    @if(count($education->getAllImages()) > 4)
                                                        <p class="text-xs text-gray-500 mt-2 text-center font-medium">+{{ count($education->getAllImages()) - 4 }} {{ __('tutors.more_certificates') }}</p>
                                                    @endif
                                                    <p class="text-xs text-indigo-600 mt-1 text-center hover:text-indigo-800 cursor-pointer" onclick="showCertificateModal('{{ asset('uploads/education/' . $education->getAllImages()[0]) }}', '{{ $education->degree }}', '{{ $education->institution }}')">
                                                        {{ __('tutors.click_to_view') }}
                                                    </p>
                                                </div>
                                            @else
                                                <div class="ml-6 flex-shrink-0 w-[120px]">
                                                    <div class="flex flex-col items-center justify-center h-16 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300">
                                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        <p class="text-xs text-gray-400 mt-1">{{ __('tutors.no_certificates') }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('tutors.no_education_info') }}</h3>
                                    <p class="mt-1 text-sm text-gray-500">{{ __('tutors.no_education_provided') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Availability -->
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('tutors.availability') }}</h2>
                        <div class="grid grid-cols-7 gap-2">
                            @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
                                <div class="text-center">
                                    <div class="font-medium text-gray-900">{{ __('tutors.' . strtolower($day)) }}</div>
                                    <div class="text-sm text-gray-600" id="availability-{{ strtolower($day) }}">
                                        {{ __('tutors.loading') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Reviews -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('tutors.reviews') }}</h2>
                        <div class="space-y-6">
                            @forelse($tutor->reviews as $review)
                                <div class="border-b border-gray-200 pb-6 last:border-0 last:pb-0">
                                    <div class="flex items-center mb-2">
                                        @if($review->student)
                                            <img class="h-10 w-10 rounded-full" src="{{ $review->student->avatar ? asset('uploads/avatars/' . $review->student->avatar) : asset('images/default-avatar.png') }}" alt="{{ $review->student->name }}">
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-gray-900">{{ $review->student->name }}</h3>
                                            @else
                                                <div class="h-10 w-10 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                                    ?
                                                </div>
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
                                    <p class="text-gray-600">{{ __('tutors.no_reviews_yet') }}</p>
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
                                                            {{ $booking->subject->name }} - {{ $booking->start_time->format('M d, Y H:i') }}
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
                                                @php
                                                    $commentValue = old('comment', '');
                                                    if (is_array($commentValue)) { $commentValue = ''; }
                                                @endphp
                                                <textarea id="comment" name="comment" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('common.experience_with_tutor') }}">{{ $commentValue }}</textarea>
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
            .then data => {
                const btn = document.getElementById('favoriteBtn');
                const text = document.getElementById('favoriteText');
                if (data.is_favorite) {
                    btn.classList.add('bg-red-50', 'border-red-300', 'text-red-700');
                    text.textContent = '{{ __('common.remove_from_favorites') }}';
                } else {
                    btn.classList.remove('bg-red-50', 'border-red-300', 'text-red-700');
                    text.textContent = '{{ __('common.add_to_favorites') }}';
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
                                                            element.textContent = '{{ __('common.unavailable') }}';
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

        // Certificate modal functionality
        function showCertificateModal(imageUrl, degree, institution) {
            const modal = document.getElementById('certificateModal');
            const modalImage = document.getElementById('modalCertificateImage');
            const modalTitle = document.getElementById('modalCertificateTitle');
            const modalInstitution = document.getElementById('modalCertificateInstitution');

            modalImage.src = imageUrl;
            modalTitle.textContent = degree;
            modalInstitution.textContent = institution;
            modal.classList.remove('hidden');
        }

        function closeCertificateModal() {
            document.getElementById('certificateModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('certificateModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCertificateModal();
            }
        });
    </script>
    @endpush

    <!-- Certificate Modal -->
    <div id="certificateModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex justify-between items-center pb-3">
                    <div>
                        <h3 id="modalCertificateTitle" class="text-lg font-bold text-gray-900"></h3>
                        <p id="modalCertificateInstitution" class="text-sm text-gray-600"></p>
                    </div>
                    <button onclick="closeCertificateModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="text-center">
                    <img id="modalCertificateImage" src="" alt="Certificate" class="max-w-full max-h-96 mx-auto rounded border shadow-lg">
                    <p class="mt-2 text-sm text-gray-500">{{ __('tutors.certificate_diploma_image') }}</p>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end pt-4">
                    <button onclick="closeCertificateModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        {{ __('common.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
