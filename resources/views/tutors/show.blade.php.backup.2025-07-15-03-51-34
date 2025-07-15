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
                                    {{ translateSubjectName($subject->name) }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Education -->
                    <div class="mb-8">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('tutors.education') }}</h2>
                        <div class="space-y-4">
                            @php
                                // Get education records directly from database to avoid null relationship issues
                                $educations = \App\Models\Education::where('tutor_id', $tutor->id)->get();
                            @endphp

                            @if($educations->isNotEmpty())
                                @foreach($educations as $edu)
                                    <div class="mb-4 p-4 border rounded-lg bg-gray-50">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-bold text-lg text-gray-800">{{ $edu->degree }}</h4>
                                                <p class="text-md text-gray-600">{{ $edu->institution }}</p>
                                            </div>
                                            <span class="text-sm font-medium text-gray-500 bg-gray-200 px-2 py-1 rounded">{{ $edu->year }}</span>
                                        </div>

                                        @if(!empty($edu->images) && is_array($edu->images))
                                            <div class="mt-3">
                                                <p class="text-sm font-semibold text-gray-700 mb-2">Chứng chỉ:</p>
                                                <div class="certificate-grid">
                                                    @foreach($edu->images as $index => $image)
                                                        <div class="certificate-image-container">
                                                            <img src="{{ asset('uploads/education/' . $image) }}"
                                                                 alt="Chứng chỉ {{ $index + 1 }}"
                                                                 class="certificate-image cursor-pointer hover:opacity-80 transition-all duration-300"
                                                                 onclick="openImageModal('{{ asset('uploads/education/' . $image) }}', '{{ $edu->degree }}', '{{ $edu->institution }} - {{ $edu->year }}')" />
                                                            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all rounded-md pointer-events-none">
                                                                <svg class="h-8 w-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z M15 15l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2"></path>
                                                                </svg>
                                                            </div>
                                                            <div class="absolute bottom-1 right-1 bg-black bg-opacity-60 text-white text-xs px-1 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                                                {{ $index + 1 }}/{{ count($edu->images) }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-8 px-4 border-2 border-dashed rounded-lg">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('tutors.no_education_provided') }}</h3>
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
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('tutors.leave_review') }}</h3>

                                    @if(session('error'))
                                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    <!-- Check if user has any completed bookings with this tutor -->
                                    @php
                                        $completedBookingsWithoutReview = $tutor->bookings()
                                            ->where('student_id', auth()->id())
                                            ->where('is_completed', true)
                                            ->whereDoesntHave('review')
                                            ->with(['subject'])
                                            ->orderBy('end_time', 'desc')
                                            ->get();
                                    @endphp

                                    @if($completedBookingsWithoutReview->count() > 0)
                                        <form action="{{ route('tutors.reviews.store', $tutor) }}" method="POST" class="space-y-4">
                                            @csrf

                                            <div>
                                                <label for="booking_id" class="block text-sm font-medium text-gray-700">{{ __('tutors.select_session') }}</label>
                                                <select name="booking_id" id="booking_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                    @foreach($completedBookingsWithoutReview as $booking)
                                                        <option value="{{ $booking->id }}">
                                                            {{ translateSubjectName($booking->subject->name) }} - {{ $booking->start_time->format('M d, Y H:i') }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('booking_id')
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <div>
                                                <label for="rating" class="block text-sm font-medium text-gray-700">{{ __('tutors.rating') }}</label>
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
                                                <label for="comment" class="block text-sm font-medium text-gray-700">{{ __('tutors.comment') }}</label>
                                                @php
                                                    $commentValue = old('comment', '');
                                                    if (is_array($commentValue)) { $commentValue = ''; }
                                                @endphp
                                                <textarea id="comment" name="comment" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('tutors.experience_with_tutor') }}">{{ $commentValue }}</textarea>
                                                @error('comment')
                                                    <p class="mt-1 text-sm text-red-600">{{ $errors->first('comment') }}</p>
                                                @enderror
                                            </div>

                                            <div class="text-right">
                                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                    {{ __('tutors.submit_review') }}
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
                                                        {{ __('tutors.review_after_session') }}
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
            .then(data) => {
                const btn = document.getElementById('favoriteBtn');
                const text = document.getElementById('favoriteText');
                if (data.is_favorite) {
                    btn.classList.add('bg-red-50', 'border-red-300', 'text-red-700');
                    text.textContent = '{{ __('common.remove_from_favorites') }}';
                } else {
                    btn.classList.remove('bg-red-50', 'border-red-300', 'text-red-700');
                    text.textContent = '{{ __('common.add_to_favorites') }}';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Real-time availability checking
        function checkAvailability() {
            const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            days.forEach(day => {
                fetch(`/tutors/{{ $tutor->id }}/availability/${day}`)
                    .then(response => response.json())
                    .then(data) => {
                        const element = document.getElementById(`availability-${day}`);
                        if (data.available) {
                            element.textContent = data.slots.join(', ');
                            element.classList.add('text-green-600');
                        } else {
                            element.textContent = '{{ __('common.unavailable') }}';
                            element.classList.add('text-red-600');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading availability:', error);
                        const element = document.getElementById(`availability-${day}`);
                        element.textContent = '{{ __('common.error') }}';
                        element.classList.add('text-red-600');
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

    <!-- Include the modern image modal component -->
    @include('profile.partials.image-modal-and-scripts')
</x-app-layout>
