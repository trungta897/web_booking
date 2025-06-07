<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tutor Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium">Profile Information</h3>
                        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</p>
                                <p class="mt-1">{{ $tutor->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p>
                                <p class="mt-1">{{ $tutor->user->email }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Hourly Rate</p>
                                <p class="mt-1">${{ number_format($tutor->hourly_rate, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Experience (years)</p>
                                <p class="mt-1">{{ $tutor->experience_years }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-medium">Bio</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-300">{{ $tutor->bio }}</p>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-medium">Subjects</h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @if($tutor && $tutor->subjects)
                                @forelse($tutor->subjects->unique('id') as $subject)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                        {{ $subject->name }}
                                    </span>
                                @empty
                                    <p class="text-gray-500 dark:text-gray-400">No subjects listed.</p>
                                @endforelse
                            @else
                                <p class="text-gray-500 dark:text-gray-400">No subjects listed or tutor profile not found.</p>
                            @endif
                        </div>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-medium">Education</h3>
                        <div class="mt-2 space-y-4">
                            @if($tutor && $tutor->education)
                                @forelse($tutor->education as $education)
                                    <div class="border-l-4 border-indigo-500 pl-4">
                                        <p class="font-medium">{{ $education->degree }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $education->institution }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $education->year }}</p>
                                    </div>
                                @empty
                                    <p class="text-gray-500 dark:text-gray-400">No education listed.</p>
                                @endforelse
                            @else
                                <p class="text-gray-500 dark:text-gray-400">No education listed or tutor profile not found.</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('tutor.profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
