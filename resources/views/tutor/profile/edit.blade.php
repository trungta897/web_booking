<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Tutor Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('tutor.profile.update') }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="hourly_rate" :value="__('Hourly Rate ($)')" />
                            <x-text-input id="hourly_rate" name="hourly_rate" type="number" step="0.01" class="mt-1 block w-full" :value="old('hourly_rate', $tutor ? $tutor->hourly_rate : '')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('hourly_rate')" />
                        </div>

                        <div>
                            <x-input-label for="experience_years" :value="__('Years of Experience')" />
                            <x-text-input id="experience_years" name="experience_years" type="number" class="mt-1 block w-full" :value="old('experience_years', $tutor ? $tutor->experience_years : '')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('experience_years')" />
                        </div>

                        <div>
                            <x-input-label for="bio" :value="__('Bio')" />
                            <textarea id="bio" name="bio" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" rows="4" required>{{ old('bio', $tutor ? $tutor->bio : '') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
                        </div>

                        <div>
                            <x-input-label for="subjects" :value="__('Subjects')" />
                            <select id="subjects" name="subjects[]" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" multiple required>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ ($tutor && $tutor->subjects && $tutor->subjects->pluck('id')->contains($subject->id)) ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('subjects')" />
                        </div>

                        <div class="education-section">
                            <h3 class="text-lg font-medium mb-4">Education</h3>
                            <div id="education-entries">
                                @if($tutor && $tutor->education)
                                    @foreach($tutor->education as $index => $education)
                                        <div class="education-entry mb-4 p-4 border rounded-lg">
                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                                <div>
                                                    <x-input-label for="degree_{{ $index }}" :value="__('Degree')" />
                                                    <x-text-input id="degree_{{ $index }}" name="education[{{ $index }}][degree]" type="text" class="mt-1 block w-full" :value="old('education.'.$index.'.degree', $education->degree)" required />
                                                </div>
                                                <div>
                                                    <x-input-label for="institution_{{ $index }}" :value="__('Institution')" />
                                                    <x-text-input id="institution_{{ $index }}" name="education[{{ $index }}][institution]" type="text" class="mt-1 block w-full" :value="old('education.'.$index.'.institution', $education->institution)" required />
                                                </div>
                                                <div>
                                                    <x-input-label for="year_{{ $index }}" :value="__('Year')" />
                                                    <x-text-input id="year_{{ $index }}" name="education[{{ $index }}][year]" type="text" class="mt-1 block w-full" :value="old('education.'.$index.'.year', $education->year)" required />
                                                </div>
                                            </div>
                                            <button type="button" class="mt-2 text-red-600 hover:text-red-800 remove-education">Remove</button>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <button type="button" id="add-education" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-blue-600 dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Add Education
                            </button>
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                            <a href="{{ route('tutor.profile.show') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-blue-600 dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const educationSection = document.getElementById('education-entries');
            const addEducationBtn = document.getElementById('add-education');
            let educationCount = {{ $tutor && $tutor->education ? count($tutor->education) : 0 }};

            addEducationBtn.addEventListener('click', function() {
                const template = `
                    <div class="education-entry mb-4 p-4 border rounded-lg">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <x-input-label for="degree_${educationCount}" :value="__('Degree')" />
                                <x-text-input id="degree_${educationCount}" name="education[${educationCount}][degree]" type="text" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="institution_${educationCount}" :value="__('Institution')" />
                                <x-text-input id="institution_${educationCount}" name="education[${educationCount}][institution]" type="text" class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="year_${educationCount}" :value="__('Year')" />
                                <x-text-input id="year_${educationCount}" name="education[${educationCount}][year]" type="text" class="mt-1 block w-full" required />
                            </div>
                        </div>
                        <button type="button" class="mt-2 text-red-600 hover:text-red-800 remove-education">Remove</button>
                    </div>
                `;
                educationSection.insertAdjacentHTML('beforeend', template);
                educationCount++;
            });

            educationSection.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-education')) {
                    e.target.closest('.education-entry').remove();
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
