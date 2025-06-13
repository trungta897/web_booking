<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tutor Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @if($tutorProfile)
                        <h3 class="text-lg font-medium text-gray-900">{{ $tutorProfile->user->name }}</h3>
                        <p class="mt-1 text-sm text-gray-600">{{ $tutorProfile->bio }}</p>
                        <p class="mt-1 text-sm text-gray-600"><strong>Experience:</strong> {{ $tutorProfile->experience }}</p>
                        <p class="mt-1 text-sm text-gray-600"><strong>Qualifications:</strong> {{ $tutorProfile->qualifications }}</p>
                        <p class="mt-1 text-sm text-gray-600"><strong>Hourly Rate:</strong> ${{ $tutorProfile->hourly_rate }}</p>
                        <p class="mt-1 text-sm text-gray-600"><strong>Location Preference:</strong> {{ $tutorProfile->location_preference }}</p>
                        <div class="mt-4">
                            <a href="{{ route('tutor.profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest hover:bg-gray-700">
                                {{ __('Edit Profile') }}
                            </a>
                        </div>
                    @else
                        <p class="text-sm text-gray-600">No tutor profile found.</p>
                        <div class="mt-4">
                            <a href="{{ route('tutor.profile.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest hover:bg-gray-700">
                                {{ __('Create Profile') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
