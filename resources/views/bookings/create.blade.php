<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('booking.book_session_with') }} {{ $tutor->user?->name ?? 'Tutor' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('bookings.store', $tutor) }}">
                        @csrf

                        @if($errors->any())
                            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                <ul class="list-disc pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-4">
                            <label for="subject_id" class="block text-sm font-medium text-gray-700">{{ __('booking.subject') }}</label>
                            <select name="subject_id" id="subject_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">{{ __('booking.select_subject') }}</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ translateSubjectName($subject->name) }}</option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="start_time" class="block text-sm font-medium text-gray-700">
                                {{ __('booking.start_time') }}
                            </label>
                            <div class="flex space-x-2">
                                <div class="flex-1">
                                    <input type="text" name="start_date_display" id="start_date_display"
                                        value="{{ $oldStartDateDisplay }}"
                                        placeholder="dd-mm-yyyy"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                    <input type="hidden" name="start_date" id="start_date" value="{{ $oldStartDate }}">
                                    <span class="text-xs text-gray-400">{{ __('booking.date_input_label') }}</span>
                                </div>
                                <div class="w-32">
                                    <input type="time" name="start_time_only" id="start_time_only"
                                        value="{{ $oldStartTime }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required step="60">
                                    <span class="text-xs text-gray-400">{{ __('booking.time_input_label') }}</span>
                                </div>
                            </div>
                            <input type="hidden" name="start_time" id="start_time_hidden" value="{{ $oldStartDateTime }}">
                            @error('start_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="end_time" class="block text-sm font-medium text-gray-700">
                                {{ __('booking.end_time') }}
                            </label>
                            <div class="flex space-x-2">
                                <div class="flex-1">
                                    <input type="text" name="end_date_display" id="end_date_display"
                                        value="{{ $oldEndDateDisplay }}"
                                        placeholder="dd-mm-yyyy"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required>
                                    <input type="hidden" name="end_date" id="end_date" value="{{ $oldEndDate }}">
                                    <span class="text-xs text-gray-400">{{ __('booking.date_input_label') }}</span>
                                </div>
                                <div class="w-32">
                                    <input type="time" name="end_time_only" id="end_time_only"
                                        value="{{ $oldEndTime }}"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        required step="60">
                                    <span class="text-xs text-gray-400">{{ __('booking.time_input_label') }}</span>
                                </div>
                            </div>
                            <input type="hidden" name="end_time" id="end_time_hidden" value="{{ $oldEndDateTime }}">
                            @error('end_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700">{{ __('booking.notes_for_tutor') }}</label>
                            <textarea name="notes" id="notes" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="{{ __('booking.notes_placeholder') }}"></textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition">
                                {{ __('booking.request_booking') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

        @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const startDateDisplay = document.getElementById('start_date_display');
            const startDateHidden = document.getElementById('start_date');
            const startTimeInput = document.getElementById('start_time_only');
            const startTimeHidden = document.getElementById('start_time_hidden');

            const endDateDisplay = document.getElementById('end_date_display');
            const endDateHidden = document.getElementById('end_date');
            const endTimeInput = document.getElementById('end_time_only');
            const endTimeHidden = document.getElementById('end_time_hidden');

            // Format date as dd-mm-yyyy for display
            function formatDateForDisplay(date) {
                const day = date.getDate().toString().padStart(2, '0');
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                const year = date.getFullYear();
                return `${day}-${month}-${year}`;
            }

            // Format date as YYYY-MM-DD for backend
            function formatDateForBackend(date) {
                const year = date.getFullYear();
                const month = (date.getMonth() + 1).toString().padStart(2, '0');
                const day = date.getDate().toString().padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            // Format time as HH:MM for input[type="time"] (24h format)
            function formatTimeForInput(date) {
                const hours = date.getHours().toString().padStart(2, '0');
                const minutes = date.getMinutes().toString().padStart(2, '0');
                return `${hours}:${minutes}`;
            }

            // Parse dd-mm-yyyy format to Date object
            function parseDateFromDisplay(dateString) {
                const parts = dateString.split('-');
                if (parts.length === 3) {
                    const day = parseInt(parts[0]);
                    const month = parseInt(parts[1]) - 1; // Month is 0-indexed
                    const year = parseInt(parts[2]);
                    return new Date(year, month, day);
                }
                return null;
            }

            // Validate date format dd-mm-yyyy
            function isValidDateFormat(dateString) {
                const regex = /^(\d{2})-(\d{2})-(\d{4})$/;
                const match = dateString.match(regex);
                if (!match) return false;

                const day = parseInt(match[1]);
                const month = parseInt(match[2]);
                const year = parseInt(match[3]);

                if (month < 1 || month > 12) return false;
                if (day < 1 || day > 31) return false;
                if (year < 2000 || year > 2100) return false;

                // Check if date is valid
                const date = new Date(year, month - 1, day);
                return date.getFullYear() === year &&
                       date.getMonth() === (month - 1) &&
                       date.getDate() === day;
            }

            // Combine date and time inputs to create datetime string for backend
            function updateHiddenDateTime(dateDisplay, dateHidden, timeInput, hiddenInput) {
                if (dateHidden.value && timeInput.value) {
                    const datetime = `${dateHidden.value}T${timeInput.value}`;
                    hiddenInput.value = datetime;
                }
            }

            // Set default values
            const now = new Date();
            const defaultStart = new Date(now.getTime() + (60 * 60 * 1000)); // 1 hour from now
            const defaultEnd = new Date(now.getTime() + (3 * 60 * 60 * 1000)); // 3 hours from now

            // Set initial values if not provided by server
            if (!startDateDisplay.value) {
                startDateDisplay.value = formatDateForDisplay(defaultStart);
                startDateHidden.value = formatDateForBackend(defaultStart);
            }
            if (!startTimeInput.value) {
                startTimeInput.value = formatTimeForInput(defaultStart);
            }
            if (!endDateDisplay.value) {
                endDateDisplay.value = formatDateForDisplay(defaultEnd);
                endDateHidden.value = formatDateForBackend(defaultEnd);
            }
            if (!endTimeInput.value) {
                endTimeInput.value = formatTimeForInput(defaultEnd);
            }

            // Update hidden fields with initial values
            updateHiddenDateTime(startDateDisplay, startDateHidden, startTimeInput, startTimeHidden);
            updateHiddenDateTime(endDateDisplay, endDateHidden, endTimeInput, endTimeHidden);

            // Function to automatically set end time based on start time
            function setEndTimeBasedOnStartTime() {
                if (startDateHidden.value && startTimeInput.value) {
                    const startDateTime = new Date(`${startDateHidden.value}T${startTimeInput.value}`);
                    const endDateTime = new Date(startDateTime.getTime() + (2 * 60 * 60 * 1000)); // Add 2 hours

                    endDateDisplay.value = formatDateForDisplay(endDateTime);
                    endDateHidden.value = formatDateForBackend(endDateTime);
                    endTimeInput.value = formatTimeForInput(endDateTime);

                    updateHiddenDateTime(endDateDisplay, endDateHidden, endTimeInput, endTimeHidden);
                }
            }

            // Event listeners for start date changes
            startDateDisplay.addEventListener('blur', function() {
                if (this.value && isValidDateFormat(this.value)) {
                    const date = parseDateFromDisplay(this.value);
                    if (date) {
                        startDateHidden.value = formatDateForBackend(date);
                        updateHiddenDateTime(startDateDisplay, startDateHidden, startTimeInput, startTimeHidden);
                        setEndTimeBasedOnStartTime();
                        this.classList.remove('border-red-500');
                    } else {
                        this.classList.add('border-red-500');
                    }
                } else if (this.value) {
                    this.classList.add('border-red-500');
                } else {
                    this.classList.remove('border-red-500');
                }
            });

            startDateDisplay.addEventListener('input', function() {
                // Remove error styling while typing
                this.classList.remove('border-red-500');
            });

            startTimeInput.addEventListener('change', function() {
                updateHiddenDateTime(startDateDisplay, startDateHidden, startTimeInput, startTimeHidden);
                setEndTimeBasedOnStartTime();
            });

            // Event listeners for end date changes
            endDateDisplay.addEventListener('blur', function() {
                if (this.value && isValidDateFormat(this.value)) {
                    const date = parseDateFromDisplay(this.value);
                    if (date) {
                        endDateHidden.value = formatDateForBackend(date);
                        updateHiddenDateTime(endDateDisplay, endDateHidden, endTimeInput, endTimeHidden);
                        this.classList.remove('border-red-500');
                    } else {
                        this.classList.add('border-red-500');
                    }
                } else if (this.value) {
                    this.classList.add('border-red-500');
                } else {
                    this.classList.remove('border-red-500');
                }
            });

            endDateDisplay.addEventListener('input', function() {
                // Remove error styling while typing
                this.classList.remove('border-red-500');
            });

            endTimeInput.addEventListener('change', function() {
                updateHiddenDateTime(endDateDisplay, endDateHidden, endTimeInput, endTimeHidden);
            });

            // Form submission validation
            document.querySelector('form').addEventListener('submit', function(e) {
                let isValid = true;

                // Validate start date
                if (!startDateDisplay.value || !isValidDateFormat(startDateDisplay.value)) {
                    startDateDisplay.classList.add('border-red-500');
                    isValid = false;
                }

                // Validate end date
                if (!endDateDisplay.value || !isValidDateFormat(endDateDisplay.value)) {
                    endDateDisplay.classList.add('border-red-500');
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                    alert('{{ __('booking.validation.date_format_error') }}');
                    return false;
                }

                // Update hidden fields
                updateHiddenDateTime(startDateDisplay, startDateHidden, startTimeInput, startTimeHidden);
                updateHiddenDateTime(endDateDisplay, endDateHidden, endTimeInput, endTimeHidden);

                // Validate that end time is after start time
                if (startTimeHidden.value && endTimeHidden.value) {
                    const startDateTime = new Date(startTimeHidden.value);
                    const endDateTime = new Date(endTimeHidden.value);

                    if (endDateTime <= startDateTime) {
                        e.preventDefault();
                        alert('{{ __('booking.validation.end_time_after_start') }}');
                        return false;
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>

