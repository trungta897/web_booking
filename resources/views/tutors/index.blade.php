<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $pageTitle ?? __('tutors.find_perfect_tutor') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filter Section -->
            <div class="bg-gray-50 p-4 sm:p-6 rounded-lg shadow mb-8">
                <form method="GET" action="{{ route('tutors.index') }}" class="space-y-6">
                    <!-- Search Bar -->
                    <div class="mb-4">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">{{ __('tutors.search_tutors') }}</label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="{{ request('search') }}" 
                                   placeholder="{{ __('tutors.search_placeholder') }}" 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
                        <fieldset class="space-y-2 md:col-span-1">
                            <legend class="text-sm font-medium text-gray-900">{{ __('tutors.core_filters') }}</legend>
                            <div>
                                <label for="subject" class="block text-xs font-medium text-gray-700">{{ __('tutors.subject') }}</label>
                                <select name="subject" id="subject" class="mt-1 block w-full input-field sm:text-sm">
                                    <option value="">{{ __('tutors.all_subjects') }}</option>
                                    @foreach($subjects as $subject_item)
                                        <option value="{{ $subject_item->id }}" {{ request('subject') == $subject_item->id ? 'selected' : '' }}>
                                            {{ translateSubjectName($subject_item->name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="price_range" class="block text-xs font-medium text-gray-700">{{ __('tutors.price_range') }}</label>
                                <select name="price_range" id="price_range" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">{{ __('tutors.any_price') }}</option>
                                    @if(app()->getLocale() === 'vi')
                                        <option value="3-4" {{ request('price_range') == '3-4' ? 'selected' : '' }}>80.000 - 100.000₫/giờ</option>
                                        <option value="4-8" {{ request('price_range') == '4-8' ? 'selected' : '' }}>110.000 - 200.000₫/giờ</option>
                                        <option value="8-12" {{ request('price_range') == '8-12' ? 'selected' : '' }}>210.000 - 300.000₫/giờ</option>
                                    @else
                                        <option value="3-4" {{ request('price_range') == '3-4' ? 'selected' : '' }}>$3 - $4/hr</option>
                                        <option value="4-8" {{ request('price_range') == '4-8' ? 'selected' : '' }}>$4 - $8/hr</option>
                                        <option value="8-12" {{ request('price_range') == '8-12' ? 'selected' : '' }}>$8 - $12/hr</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label for="location" class="block text-xs font-medium text-gray-700">{{ __('tutors.location') }}</label>
                                <input type="text" name="location" id="location" value="{{ request('location') }}" placeholder="{{ __('tutors.location_placeholder') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </fieldset>

                        <fieldset class="space-y-2 md:col-span-1">
                            <legend class="text-sm font-medium text-gray-900">{{ __('tutors.details') }}</legend>
                            <div>
                                <label for="rating" class="block text-xs font-medium text-gray-700">{{ __('tutors.min_rating') }}</label>
                                <select name="rating" id="rating" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">{{ __('tutors.any_rating') }}</option>
                                                <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4+ {{ __('common.stars') }}</option>
            <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3+ {{ __('common.stars') }}</option>
                                                                          <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2+ {{ __('common.stars') }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="experience" class="block text-xs font-medium text-gray-700">{{ __('tutors.min_years') }}</label>
                                <select name="experience" id="experience" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">{{ __('tutors.experience') }}</option>
                                    <option value="1" {{ request('experience') == '1' ? 'selected' : '' }}>1+ Years</option>
                                    <option value="3" {{ request('experience') == '3' ? 'selected' : '' }}>3+ Years</option>
                                    <option value="5" {{ request('experience') == '5' ? 'selected' : '' }}>5+ Years</option>
                                    <option value="10" {{ request('experience') == '10' ? 'selected' : '' }}>10+ Years</option>
                                </select>
                            </div>
                            <div>
                                <label for="day_of_week" class="block text-xs font-medium text-gray-700">{{ __('tutors.availability') }}</label>
                                <select name="day_of_week" id="day_of_week" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">{{ __('tutors.any_day') }}</option>
                                    <option value="monday" {{ request('day_of_week') == 'monday' ? 'selected' : '' }}>{{ __('tutors.monday') }}</option>
                                    <option value="tuesday" {{ request('day_of_week') == 'tuesday' ? 'selected' : '' }}>{{ __('tutors.tuesday') }}</option>
                                    <option value="wednesday" {{ request('day_of_week') == 'wednesday' ? 'selected' : '' }}>{{ __('tutors.wednesday') }}</option>
                                    <option value="thursday" {{ request('day_of_week') == 'thursday' ? 'selected' : '' }}>{{ __('tutors.thursday') }}</option>
                                    <option value="friday" {{ request('day_of_week') == 'friday' ? 'selected' : '' }}>{{ __('tutors.friday') }}</option>
                                    <option value="saturday" {{ request('day_of_week') == 'saturday' ? 'selected' : '' }}>{{ __('tutors.saturday') }}</option>
                                    <option value="sunday" {{ request('day_of_week') == 'sunday' ? 'selected' : '' }}>{{ __('tutors.sunday') }}</option>
                                </select>
                            </div>
                        </fieldset>

                        <div class="md:col-span-1 flex flex-col justify-between">
                            <div>
                                <label for="sort" class="block text-xs font-medium text-gray-700">{{ __('tutors.sort_by') }}</label>
                                <select name="sort" id="sort" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="" {{ request('sort') == '' ? 'selected' : '' }}>{{ __('tutors.most_recent') }}</option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>{{ __('tutors.price_low_high') }}</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>{{ __('tutors.price_high_low') }}</option>
                                    <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>{{ __('tutors.highest_rated') }}</option>
                                    <option value="experience" {{ request('sort') == 'experience' ? 'selected' : '' }}>{{ __('tutors.most_experienced') }}</option>
                                </select>
                            </div>
                            <div class="flex items-end justify-end space-x-3 mt-4">
                                <a href="{{ route('tutors.index') }}" class="btn-secondary text-xs py-2 px-4">
                                    {{ __('tutors.reset') }}
                                </a>
                                <button type="submit" class="btn-primary text-xs py-2 px-4">
                                    {{ __('tutors.apply_filters') }}
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
                                    <img class="h-16 w-16 rounded-full" src="{{ $tutor->user->avatar ? asset('uploads/avatars/' . $tutor->user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $tutor->user->name }}">
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ $tutor->user->name }}</h3>
                                    @if($tutor->reviews_count > 0)
                                        <div class="flex items-center mt-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="h-5 w-5 {{ $i <= $tutor->reviews_avg_rating ? 'star-filled' : 'star-empty' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                            <span class="ml-2 text-sm text-gray-600">({{ $tutor->reviews_count }} {{ $tutor->reviews_count == 1 ? __('tutors.review') : __('tutors.reviews') }})</span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500 mt-1">{{ __('tutors.no_reviews_yet') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('tutors.subjects') }}</h4>
                                <div class="mt-1 flex flex-wrap gap-1.5">
                                    @forelse($tutor->subjects->take(3) as $subject)
                                        <span class="badge-primary">
                                            {{ translateSubjectName($subject->name) }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-500">{{ __('tutors.no_subjects_listed') }}</span>
                                    @endforelse
                                    @if($tutor->subjects->count() > 3)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 font-medium">
                                            +{{ $tutor->subjects->count() - 3 }} {{ __('tutors.more') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 mb-4 flex-grow min-h-[60px]">{{ Str::limit($tutor->bio, 100) }}</p>

                            <div class="mt-auto pt-4 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="text-lg font-bold text-brand-primary">{{ formatCurrency($tutor->hourly_rate) }}<span class="text-sm font-normal text-secondary-500">{{ __('tutors.per_hour') }}</span></div>
                                    <a href="{{ route('tutors.show', $tutor) }}" class="btn-primary text-sm py-2 px-3">
                                        {{ __('tutors.view_profile') }}
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
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('tutors.no_tutors_found') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('tutors.no_tutors_found_desc') }}</p>
                    <div class="mt-6">
                        <a href="{{ route('tutors.index') }}" class="btn-primary text-sm">
                           {{ __('tutors.clear_all_filters') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
