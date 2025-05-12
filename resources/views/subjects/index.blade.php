<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold text-gray-900">Browse Subjects</h1>
                <p class="mt-4 text-lg text-gray-600">Find the perfect tutor for your subject</p>
            </div>

            <!-- Subjects Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($subjects as $subject)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-lg transition-shadow duration-300">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $subject->name }}</h3>
                                <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-sm">
                                    {{ $subject->tutors_count }} Tutors
                                </span>
                            </div>
                            <p class="text-gray-600 mb-4">{{ $subject->description }}</p>
                            <div class="flex justify-between items-center">
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    {{ $subject->courses_count }} Courses
                                </div>
                                <a href="{{ route('tutors.index', ['subject' => $subject->id]) }}" class="bg-primary text-blue-600 px-4 py-2 rounded-md hover:bg-primary-dark transition-colors duration-300">
                                    Find Tutors
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
