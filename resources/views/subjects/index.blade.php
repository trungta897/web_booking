<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('subjects.title') }}</h1>
                <p class="mt-4 text-lg text-gray-600">{{ __('subjects.subtitle') }}</p>
            </div>

            <!-- Subjects Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($subjects as $subject)
                    <a href="{{ route('subjects.tutors', $subject) }}" class="block card-hover p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                        <div class="flex justify-between items-start mb-3">
                            <h3 class="text-xl font-semibold text-gray-900">{{ translateSubjectName($subject->name) }}</h3>
                            <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">
                                {{ $subject->tutors_count }} {{ $subject->tutors_count == 1 ? __('subjects.tutor') : __('subjects.tutors') }}
                            </span>
                        </div>
                        <p class="text-gray-600 mb-4 min-h-[60px]">
                            {{ translateSubjectDescription($subject->name) }}
                        </p>
                        <div class="mt-auto pt-4 border-t border-gray-100">
                             <div class="flex items-center text-sm text-gray-500">
                                <svg class="w-4 h-4 mr-1.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v11.494m0 0A7.027 7.027 0 0112 17.747a7.027 7.027 0 010-11.494M4 19.5V7.5a3 3 0 013-3h10a3 3 0 013 3v12a3 3 0 01-3 3H7a3 3 0 01-3-3z"></path></svg>
                                {{ $subject->courses_count ?? 0 }} {{ ($subject->courses_count ?? 0) == 1 ? __('subjects.course') : __('subjects.courses') }}
                            </div>
                        </div>
                    </a>
                @empty
                    <p class="text-gray-600 col-span-full text-center">{{ __('subjects.no_subjects') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
