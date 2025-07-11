<h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('tutors.education_and_certificates') }}</h3>
<div id="education-entries">
    @php
        // Get education records directly from database to avoid null relationship issues
        $educationRecords = $tutor ? \App\Models\Education::where('tutor_id', $tutor->id)->get() : collect();
    @endphp

    @if($educationRecords->isNotEmpty())
        @foreach($educationRecords as $index => $education)
            <div class="education-entry mb-4 p-4 border rounded-lg bg-gray-50">
                {{-- <input type="hidden" name="education[{{ $index }}][id]" value="{{ $education->id }}"> --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <x-input-label for="degree_{{ $index }}" :value="__('common.degree')" />
                        @php
                            $degreeValue = old('education.'.$index.'.degree', $education->degree);
                            if (is_array($degreeValue)) { $degreeValue = $education->degree ?? ''; }
                        @endphp
                        <x-text-input :id="'degree_'.$index" name="education[{{ $index }}][degree]" type="text" class="mt-1 block w-full" :value="$degreeValue" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('education.'.$index.'.degree')" />
                    </div>
                    <div>
                        <x-input-label for="institution_{{ $index }}" :value="__('common.institution')" />
                        @php
                            $institutionValue = old('education.'.$index.'.institution', $education->institution);
                            if (is_array($institutionValue)) { $institutionValue = $education->institution ?? ''; }
                        @endphp
                        <x-text-input :id="'institution_'.$index" name="education[{{ $index }}][institution]" type="text" class="mt-1 block w-full" :value="$institutionValue" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('education.'.$index.'.institution')" />
                    </div>
                    <div>
                        <x-input-label for="year_{{ $index }}" :value="__('common.year')" />
                        @php
                            $yearValue = old('education.'.$index.'.year', $education->year);
                            if (is_array($yearValue)) { $yearValue = $education->year ?? ''; }
                        @endphp
                        <x-text-input :id="'year_'.$index" name="education[{{ $index }}][year]" type="text" class="mt-1 block w-full" :value="$yearValue" />
                        <x-input-error class="mt-2" :messages="$errors->get('education.'.$index.'.year')" />
                    </div>
                </div>
                <div class="mt-4">
                    <x-input-label for="education_image_{{ $index }}" :value="__('tutors.certificate_image')" />
                    @if ($education->image)
                        <div class="mt-1">
                            <img src="{{ asset('uploads/education/' . $education->image) }}" class="h-16 w-auto rounded">
                            <p class="text-xs text-gray-500 mt-1">{{ __('tutors.current_image_info') }}</p>
                        </div>
                    @endif
                    <input type="file" name="education[{{ $index }}][image]" id="education_image_{{ $index }}" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 image-input" data-preview="preview_{{ $index }}" accept="image/*">
                    <div id="preview_{{ $index }}" class="mt-2" style="display: none;">
                        <p class="text-sm font-medium text-gray-700 mb-2">{{ __('profile.preview') }}:</p>
                        <img src="" alt="Certificate Preview" class="h-20 w-20 object-cover rounded border-2 border-indigo-300">
                    </div>
                    <x-input-error class="mt-2" :messages="$errors->get('education.'.$index.'.image')" />
                </div>
                <div class="mt-3 flex justify-end">
                    <button type="button" class="text-red-600 hover:text-red-800 text-sm font-medium remove-education">{{ __('common.remove') }}</button>
                </div>
            </div>
        @endforeach
    @endif
</div>
<button type="button" id="add-education" class="mt-4 inline-flex items-center px-4 py-2 bg-gray-200 border rounded-md text-xs uppercase hover:bg-gray-300">{{ __('tutors.add_education') }}</button>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('education-entries')) {
        const educationSection = document.getElementById('education-entries');
        const addEducationBtn = document.getElementById('add-education');
        let educationCount = {{ $educationRecords->count() }};

        const handleImagePreview = (input) => {
            const previewId = input.dataset.preview;
            const previewDiv = document.getElementById(previewId);
            if (!previewDiv) return;

            const previewImg = previewDiv.querySelector('img');
            const file = input.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImg.src = e.target.result;
                    previewDiv.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewDiv.style.display = 'none';
            }
        };

        const bindImagePreview = (container) => {
            container.querySelectorAll('.image-input').forEach(input => {
                input.removeEventListener('change', () => handleImagePreview(input)); // Avoid double binding
                input.addEventListener('change', () => handleImagePreview(input));
            });
        };

        bindImagePreview(educationSection);

        addEducationBtn.addEventListener('click', function() {
            const newIndex = 'new_' + Date.now(); // Unique index for new entries
            const template = document.createElement('div');
            template.className = 'education-entry mb-4 p-4 border rounded-lg bg-gray-50';
            template.innerHTML = `
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label for="degree_${newIndex}" class="block text-sm font-medium text-gray-700">{{ __("common.degree") }}</label>
                        <input id="degree_${newIndex}" name="education[${newIndex}][degree]" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                    </div>
                    <div>
                        <label for="institution_${newIndex}" class="block text-sm font-medium text-gray-700">{{ __("common.institution") }}</label>
                        <input id="institution_${newIndex}" name="education[${newIndex}][institution]" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required />
                    </div>
                    <div>
                        <label for="year_${newIndex}" class="block text-sm font-medium text-gray-700">{{ __("common.year") }}</label>
                        <input id="year_${newIndex}" name="education[${newIndex}][year]" type="text" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" />
                    </div>
                </div>
                <div class="mt-4">
                    <label for="education_image_${newIndex}" class="block text-sm font-medium text-gray-700">{{ __("tutors.certificate_image") }} ({{ __('common.optional') }})</label>
                    <input id="education_image_${newIndex}" name="education[${newIndex}][image]" type="file" class="image-input mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*" data-preview="preview_${newIndex}">
                    <p class="mt-1 text-sm text-gray-500">{{ __('profile.max_size') }}: 5MB</p>
                    <div id="preview_${newIndex}" class="mt-2" style="display: none;">
                        <p class="text-sm font-medium text-gray-700 mb-2">{{ __('profile.preview') }}:</p>
                        <img src="" alt="Certificate Preview" class="h-20 w-20 object-cover rounded border-2 border-indigo-300">
                    </div>
                </div>
                <div class="mt-3 flex justify-end">
                    <button type="button" class="text-red-600 hover:text-red-800 text-sm font-medium remove-education">{{ __('common.remove') }}</button>
                </div>
            `;

            educationSection.appendChild(template);
            bindImagePreview(template); // Bind preview to the new element
        });

        educationSection.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-education');
            if (removeBtn) {
                removeBtn.closest('.education-entry').remove();
            }
        });
    }
});
</script>
@endpush
