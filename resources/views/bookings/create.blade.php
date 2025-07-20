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
                    <form method="POST" action="{{ route('bookings.store', $tutor) }}"
                          data-date-format-error="{{ __('booking.validation.date_format_error') }}"
                          data-end-time-error="{{ __('booking.validation.end_time_after_start') }}">
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
                                        placeholder="dd/mm/yy"
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
                                        placeholder="dd/mm/yy"
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
        <script src="{{ asset('js/pages/booking-create.js') }}"></script>
    @endpush
</x-app-layout>

