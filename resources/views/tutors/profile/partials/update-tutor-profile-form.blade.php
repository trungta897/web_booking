<h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('tutors.tutor_information') }}</h3>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="hourly_rate" :value="__('pricing.hourly_rate')" />
        @php
            $hourlyRateValue = old('hourly_rate', $tutor->hourly_rate ?? '');
            if (is_array($hourlyRateValue)) { $hourlyRateValue = $tutor->hourly_rate ?? ''; }
        @endphp
        <x-text-input id="hourly_rate" name="hourly_rate" type="number" step="0.01" class="mt-1 block w-full" :value="$hourlyRateValue" />
        <x-input-error class="mt-2" :messages="$errors->get('hourly_rate')" />
    </div>
    <div>
        <x-input-label for="experience_years" :value="__('tutors.experience_years')" />
        @php
            $experienceYearsValue = old('experience_years', $tutor->experience_years ?? '');
            if (is_array($experienceYearsValue)) { $experienceYearsValue = $tutor->experience_years ?? ''; }
        @endphp
        <x-text-input id="experience_years" name="experience_years" type="number" class="mt-1 block w-full" :value="$experienceYearsValue" />
        <x-input-error class="mt-2" :messages="$errors->get('experience_years')" />
    </div>
</div>
<div class="mt-4">
    <x-input-label for="bio" :value="__('tutors.bio')" />
    @php
        $bioValue = old('bio', $tutor->bio ?? '');
        if (is_array($bioValue)) { $bioValue = $tutor->bio ?? ''; }
    @endphp
    <textarea id="bio" name="bio" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" rows="4">{{ $bioValue }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('bio')" />
</div>
<div class="mt-4">
    <x-input-label for="subjects" :value="__('tutors.subjects')" />
    @php
        $selectedSubjects = old('subjects', $tutor ? $tutor->subjects->pluck('id')->toArray() : []);
        if (!is_array($selectedSubjects)) { $selectedSubjects = []; }
    @endphp
    <select id="subjects" name="subjects[]" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" multiple>
        @foreach($subjects as $subject)
            <option value="{{ $subject->id }}" {{ in_array($subject->id, $selectedSubjects) ? 'selected' : '' }}>
                {{ $subject->name }}
            </option>
        @endforeach
    </select>
    <p class="mt-2 text-sm text-gray-500">{{ __('common.Hold Ctrl/Cmd to select multiple subjects') }}</p>
    <x-input-error class="mt-2" :messages="$errors->get('subjects')" />
</div>
