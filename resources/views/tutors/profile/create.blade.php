<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Tutor Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <form method="post" action="{{ route('tutor.profile.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="bio" :value="__('Bio')" />
                            <textarea id="bio" name="bio" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="experience" :value="__('Experience')" />
                            <textarea id="experience" name="experience" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('experience')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="qualifications" :value="__('Qualifications')" />
                            <textarea id="qualifications" name="qualifications" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required></textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('qualifications')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="hourly_rate" :value="__('Hourly Rate')" />
                            <x-text-input id="hourly_rate" name="hourly_rate" type="number" step="0.01" class="mt-1 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('hourly_rate')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="availability" :value="__('Availability')" />
                            <select id="availability" name="availability[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" multiple required>
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                                <option value="saturday">Saturday</option>
                                <option value="sunday">Sunday</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('availability')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="subjects_taught" :value="__('Subjects Taught')" />
                            <select id="subjects_taught" name="subjects_taught[]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" multiple required>
                                <option value="math">Math</option>
                                <option value="science">Science</option>
                                <option value="english">English</option>
                                <option value="history">History</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('subjects_taught')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="location_preference" :value="__('Location Preference')" />
                            <x-text-input id="location_preference" name="location_preference" type="text" class="mt-1 block w-full" required />
                            <x-input-error class="mt-2" :messages="$errors->get('location_preference')" />
                        </div>

                        <div class="flex items-center gap-4 mt-4">
                            <x-primary-button>{{ __('Create Profile') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
