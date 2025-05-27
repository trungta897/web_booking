<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle ?? 'Find Your Perfect Tutor' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="GET" action="{{ route('tutors.index') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700">Subject</label>
                                <select name="subject" id="subject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ request('subject') == $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="price_range" class="block text-sm font-medium text-gray-700">Price Range</label>
                                <select name="price_range" id="price_range" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Any Price</option>
                                    <option value="0-25" {{ request('price_range') == '0-25' ? 'selected' : '' }}>$0 - $25/hr</option>
                                    <option value="26-50" {{ request('price_range') == '26-50' ? 'selected' : '' }}>$26 - $50/hr</option>
                                    <option value="51-100" {{ request('price_range') == '51-100' ? 'selected' : '' }}>$51 - $100/hr</option>
                                    <option value="101+" {{ request('price_range') == '101+' ? 'selected' : '' }}>$101+/hr</option>
                                </select>
                            </div>

                            <div>
                                <label for="rating" class="block text-sm font-medium text-gray-700">Minimum Rating</label>
                                <select name="rating" id="rating" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Any Rating</option>
                                    <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4+ Stars</option>
                                    <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3+ Stars</option>
                                    <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2+ Stars</option>
                                </select>
                            </div>

                            <div>
                                <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                <input type="text" name="location" id="location" value="{{ request('location') }}" placeholder="City, State, or Zip" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label for="day_of_week" class="block text-sm font-medium text-gray-700">Available On</label>
                                <select name="day_of_week" id="day_of_week" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Any Day</option>
                                    <option value="monday" {{ request('day_of_week') == 'monday' ? 'selected' : '' }}>Monday</option>
                                    <option value="tuesday" {{ request('day_of_week') == 'tuesday' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="wednesday" {{ request('day_of_week') == 'wednesday' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="thursday" {{ request('day_of_week') == 'thursday' ? 'selected' : '' }}>Thursday</option>
                                    <option value="friday" {{ request('day_of_week') == 'friday' ? 'selected' : '' }}>Friday</option>
                                    <option value="saturday" {{ request('day_of_week') == 'saturday' ? 'selected' : '' }}>Saturday</option>
                                    <option value="sunday" {{ request('day_of_week') == 'sunday' ? 'selected' : '' }}>Sunday</option>
                                </select>
                            </div>

                            <div>
                                <label for="experience" class="block text-sm font-medium text-gray-700">Minimum Experience</label>
                                <select name="experience" id="experience" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Any Experience</option>
                                    <option value="1" {{ request('experience') == '1' ? 'selected' : '' }}>1+ Years</option>
                                    <option value="3" {{ request('experience') == '3' ? 'selected' : '' }}>3+ Years</option>
                                    <option value="5" {{ request('experience') == '5' ? 'selected' : '' }}>5+ Years</option>
                                    <option value="10" {{ request('experience') == '10' ? 'selected' : '' }}>10+ Years</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <div>
                                <label for="sort" class="block text-sm font-medium text-gray-700">Sort By</label>
                                <select name="sort" id="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Most Recent</option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                    <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rated</option>
                                    <option value="experience" {{ request('sort') == 'experience' ? 'selected' : '' }}>Most Experienced</option>
                                </select>
                            </div>

                            <div class="flex space-x-4">
                                <a href="{{ route('tutors.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Reset Filters
                                </a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tutors Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($tutors as $tutor)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <img class="h-12 w-12 rounded-full" src="{{ $tutor->user->avatar ? asset('storage/' . $tutor->user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $tutor->user->name }}">
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">{{ $tutor->user->name }}</h3>
                                    <div class="flex items-center">
                                        <div class="flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="h-5 w-5 {{ $i <= $tutor->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="ml-2 text-sm text-gray-600">({{ $tutor->reviews_count }} reviews)</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h4 class="text-sm font-medium text-gray-900">Subjects</h4>
                                <div class="mt-1 flex flex-wrap gap-2">
                                    @foreach($tutor->subjects as $subject)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $subject->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 mb-4">{{ Str::limit($tutor->bio, 100) }}</p>

                            <div class="flex items-center justify-between">
                                <div class="text-lg font-medium text-gray-900">${{ number_format($tutor->hourly_rate, 2) }}/hr</div>
                                <a href="{{ route('tutors.show', $tutor) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-600 bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $tutors->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
