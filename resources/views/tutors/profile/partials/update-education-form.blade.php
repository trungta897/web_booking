<h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('tutors.education_and_certificates') }}</h3>
<div id="education-entries">
    @php
        // Get education records directly from database to avoid null relationship issues
        $educationRecords = $tutor ? \App\Models\Education::where('tutor_id', $tutor->id)->get() : collect();
    @endphp

    @if($educationRecords->isNotEmpty())
        @foreach($educationRecords as $education)
            @php $educationId = $education->id; @endphp
            <div class="education-entry mb-4 p-4 border rounded-lg bg-gray-50">
                <input type="hidden" name="education[{{ $educationId }}][id]" value="{{ $education->id }}">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <x-input-label for="degree_{{ $educationId }}" :value="__('common.degree')" />
                        @php
                            $degreeValue = old('education.'.$educationId.'.degree', $education->degree);
                            if (is_array($degreeValue)) { $degreeValue = $education->degree ?? ''; }
                        @endphp
                        <x-text-input :id="'degree_'.$educationId" name="education[{{ $educationId }}][degree]" type="text" class="mt-1 block w-full" :value="$degreeValue" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('education.'.$educationId.'.degree')" />
                    </div>
                    <div>
                        <x-input-label for="institution_{{ $educationId }}" :value="__('common.institution')" />
                        @php
                            $institutionValue = old('education.'.$educationId.'.institution', $education->institution);
                            if (is_array($institutionValue)) { $institutionValue = $education->institution ?? ''; }
                        @endphp
                        <x-text-input :id="'institution_'.$educationId" name="education[{{ $educationId }}][institution]" type="text" class="mt-1 block w-full" :value="$institutionValue" required/>
                        <x-input-error class="mt-2" :messages="$errors->get('education.'.$educationId.'.institution')" />
                    </div>
                    <div>
                        <x-input-label for="year_{{ $educationId }}" :value="__('common.year')" />
                        @php
                            $yearValue = old('education.'.$educationId.'.year', $education->year);
                            if (is_array($yearValue)) { $yearValue = $education->year ?? ''; }
                        @endphp
                        <x-text-input :id="'year_'.$educationId" name="education[{{ $educationId }}][year]" type="text" class="mt-1 block w-full" :value="$yearValue" />
                        <x-input-error class="mt-2" :messages="$errors->get('education.'.$educationId.'.year')" />
                    </div>
                </div>
                <div class="mt-4">
                    <x-input-label for="education_images_{{ $educationId }}" :value="__('tutors.certificate_images')" />

                    <!-- Display existing images -->
                    @if ($education->hasImages())
                        <div class="mt-2 mb-3">
                            <p class="text-xs text-gray-500 mb-2">{{ __('tutors.current_images') }}:</p>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($education->getAllImages() as $index => $imageName)
                                    <div class="relative group">
                                        <img src="{{ asset('uploads/education/' . $imageName) }}"
                                             class="h-20 w-20 object-cover rounded border cursor-pointer hover:opacity-80 transition-opacity"
                                             onclick="openImageModal('{{ asset('uploads/education/' . $imageName) }}', '{{ $education->degree }} - Image {{ $index + 1 }}')" />
                                        <button type="button"
                                                class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 text-xs opacity-0 group-hover:opacity-100 transition-opacity remove-image-btn"
                                                data-education-id="{{ $educationId }}"
                                                data-image-name="{{ $imageName }}"
                                                title="Remove image">×</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Upload new images -->
                    <div class="mt-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('tutors.add_more_images') }}</label>
                        <input type="file"
                               name="education[{{ $educationId }}][new_images][]"
                               id="education_images_{{ $educationId }}"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 multiple-image-input"
                               data-preview="preview_{{ $educationId }}"
                               accept="image/*"
                               multiple>
                        <p class="mt-1 text-xs text-gray-500">{{ __('tutors.select_multiple_images') }} ({{ __('profile.max_size') }}: 5MB {{ __('common.each') }})</p>
                    </div>

                    <!-- Preview new images -->
                    <div id="preview_{{ $educationId }}" class="mt-3" style="display: none;">
                        <p class="text-sm font-medium text-gray-700 mb-2">{{ __('tutors.new_images_preview') }}:</p>
                        <div class="grid grid-cols-3 gap-2" id="preview_grid_{{ $educationId }}">
                            <!-- Preview images will be inserted here -->
                        </div>
                    </div>

                    <x-input-error class="mt-2" :messages="$errors->get('education.'.$educationId.'.new_images')" />
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

        const handleMultipleImagePreview = (input) => {
            const previewId = input.dataset.preview;
            const previewDiv = document.getElementById(previewId);
            const previewGrid = document.getElementById('preview_grid_' + previewId.split('_')[1]);

            if (!previewDiv || !previewGrid) return;

            const files = Array.from(input.files);

            if (files.length > 0) {
                previewGrid.innerHTML = ''; // Clear previous previews

                files.forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            const imgContainer = document.createElement('div');
                            imgContainer.className = 'relative';
                            imgContainer.innerHTML = `
                                <img src="${e.target.result}"
                                     alt="Preview ${index + 1}"
                                     class="h-20 w-20 object-cover rounded border-2 border-green-300">
                                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 rounded-b">
                                    New ${index + 1}
                                </div>
                            `;
                            previewGrid.appendChild(imgContainer);
                        };
                        reader.readAsDataURL(file);
                    }
                });

                previewDiv.style.display = 'block';
            } else {
                previewDiv.style.display = 'none';
            }
        };

        const bindImagePreview = (container) => {
            container.querySelectorAll('.multiple-image-input').forEach(input => {
                input.removeEventListener('change', () => handleMultipleImagePreview(input)); // Avoid double binding
                input.addEventListener('change', () => handleMultipleImagePreview(input));
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
                    <label for="education_images_${newIndex}" class="block text-sm font-medium text-gray-700">{{ __("tutors.certificate_images") }} ({{ __('common.optional') }})</label>
                    <input id="education_images_${newIndex}" name="education[${newIndex}][new_images][]" type="file" class="multiple-image-input mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*" data-preview="preview_${newIndex}" multiple>
                    <p class="mt-1 text-xs text-gray-500">{{ __('tutors.select_multiple_images') }} ({{ __('profile.max_size') }}: 5MB {{ __('common.each') }})</p>
                    <div id="preview_${newIndex}" class="mt-3" style="display: none;">
                        <p class="text-sm font-medium text-gray-700 mb-2">{{ __('tutors.new_images_preview') }}:</p>
                        <div class="grid grid-cols-3 gap-2" id="preview_grid_${newIndex}">
                            <!-- Preview images will be inserted here -->
                        </div>
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
