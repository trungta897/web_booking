<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle ?? 'Find Your Perfect Tutor' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filter Section -->
            <div class="bg-gray-50 p-4 sm:p-6 rounded-lg shadow mb-8">
                <form method="GET" action="{{ route('tutors.index') }}" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                        <fieldset class="space-y-2 md:col-span-1">
                            <legend class="text-sm font-medium text-gray-900">Core Filters</legend>
                            <div>
                                <label for="subject" class="block text-xs font-medium text-gray-700">Subject</label>
                                <select name="subject" id="subject" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects as $subject_item)
                                        <option value="{{ $subject_item->id }}" {{ request('subject') == $subject_item->id ? 'selected' : '' }}>
                                            {{ $subject_item->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="price_range" class="block text-xs font-medium text-gray-700">Price Range</label>
                                <select name="price_range" id="price_range" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Any Price</option>
                                    <option value="0-25" {{ request('price_range') == '0-25' ? 'selected' : '' }}>$0 - $25/hr</option>
                                    <option value="26-50" {{ request('price_range') == '26-50' ? 'selected' : '' }}>$26 - $50/hr</option>
                                    <option value="51-100" {{ request('price_range') == '51-100' ? 'selected' : '' }}>$51 - $100/hr</option>
                                    <option value="101+" {{ request('price_range') == '101+' ? 'selected' : '' }}>$101+/hr</option>
                                </select>
                            </div>
                            <div>
                                <label for="location" class="block text-xs font-medium text-gray-700">Location</label>
                                <input type="text" name="location" id="location" value="{{ request('location') }}" placeholder="City, State, or Zip" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </fieldset>

                        <fieldset class="space-y-2 md:col-span-1">
                            <legend class="text-sm font-medium text-gray-900">Details</legend>
                            <div>
                                <label for="rating" class="block text-xs font-medium text-gray-700">Minimum Rating</label>
                                <select name="rating" id="rating" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Any Rating</option>
                                    <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4+ Stars</option>
                                    <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3+ Stars</option>
                                    <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2+ Stars</option>
                                </select>
                            </div>
                            <div>
                                <label for="experience" class="block text-xs font-medium text-gray-700">Minimum Experience</label>
                                <select name="experience" id="experience" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Any Experience</option>
                                    <option value="1" {{ request('experience') == '1' ? 'selected' : '' }}>1+ Years</option>
                                    <option value="3" {{ request('experience') == '3' ? 'selected' : '' }}>3+ Years</option>
                                    <option value="5" {{ request('experience') == '5' ? 'selected' : '' }}>5+ Years</option>
                                    <option value="10" {{ request('experience') == '10' ? 'selected' : '' }}>10+ Years</option>
                                </select>
                            </div>
                            <div>
                                <label for="day_of_week" class="block text-xs font-medium text-gray-700">Available On</label>
                                <select name="day_of_week" id="day_of_week" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
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
                        </fieldset>

                        <div class="md:col-span-1 flex flex-col justify-between">
                            <div>
                                <label for="sort" class="block text-xs font-medium text-gray-700">Sort By</label>
                                <select name="sort" id="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="" {{ request('sort') == '' ? 'selected' : '' }}>Most Recent</option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                                    <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rated</option>
                                    <option value="experience" {{ request('sort') == 'experience' ? 'selected' : '' }}>Most Experienced</option>
                                </select>
                            </div>
                            <div class="flex items-end justify-end space-x-3 mt-4">
                                <a href="{{ route('tutors.index') }}" class="px-4 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Reset
                                </a>
                                <button type="submit" class="btn-primary text-xs py-2 px-4">
                                    Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tutors Grid -->
            @if($tutors->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($tutors as $tutor)
                        <div class="card-hover p-6 rounded-lg shadow-lg flex flex-col">
                            <div class="flex items-center mb-4">
                                <div class="flex-shrink-0">
                                    <img class="h-16 w-16 rounded-full" src="{{ $tutor->user->avatar ? asset('storage/' . $tutor->user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $tutor->user->name }}">
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $tutor->user->name }}</h3>
                                    @if($tutor->reviews_count > 0)
                                        <div class="flex items-center mt-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="h-5 w-5 {{ $i <= $tutor->reviews_avg_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                            <span class="ml-2 text-sm text-gray-600">({{ $tutor->reviews_count }} {{ Str::plural('review', $tutor->reviews_count) }})</span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 mt-1">No reviews yet</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Subjects</h4>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    @forelse($tutor->subjects->take(3) as $subject)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-indigo-100 text-indigo-700 font-medium">
                                            {{ $subject->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-500">No specific subjects listed.</span>
                                    @endforelse
                                    @if($tutor->subjects->count() > 3)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 font-medium">
                                            +{{ $tutor->subjects->count() - 3 }} more
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 mb-4 flex-grow min-h-[60px]">{{ Str::limit($tutor->bio, 100) }}</p>

                            <div class="mt-auto pt-4 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="text-lg font-bold text-primary">${{ number_format($tutor->hourly_rate, 2) }}<span class="text-sm font-normal text-gray-500">/hr</span></div>
                                    <a href="{{ route('tutors.show', $tutor) }}" class="btn-primary text-sm py-2 px-3">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $tutors->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Tutors Found</h3>
                    <p class="mt-1 text-sm text-gray-500">Sorry, no tutors matched your current filters. Try adjusting your criteria.</p>
                    <div class="mt-6">
                        <a href="{{ route('tutors.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                           Clear All Filters
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
