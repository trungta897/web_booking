<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Book a Session with {{ $tutor->user?->name ?? 'Tutor' }}
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
                            <label for="subject_id" class="block text-sm font-medium text-gray-700">Subject</label>
                            <select name="subject_id" id="subject_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="">Select a subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                            @error('subject_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input type="datetime-local" name="start_time" id="start_time"
                                value="{{ old('start_time', $defaultStartTime ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required min="{{ $defaultStartTime ?? now()->format('Y-m-d\TH:i') }}">
                            @error('start_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                            <input type="datetime-local" name="end_time" id="end_time"
                                value="{{ old('end_time', $defaultEndTime ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required min="{{ $defaultStartTime ?? now()->format('Y-m-d\TH:i') }}">
                            @error('end_time')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes for Tutor</label>
                            <textarea name="notes" id="notes" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                placeholder="Any specific topics or areas you'd like to focus on?"></textarea>
                            @error('notes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition">
                                Request Booking
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
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');

            function setEndTimeBasedOnStartTime(startTimeValue) {
                if (startTimeValue) {
                    endTimeInput.min = startTimeValue;

                    const startTime = new Date(startTimeValue);
                    if (isNaN(startTime.getTime())) {
                        console.error("Error parsing start time for end time calculation:", startTimeValue);
                        endTimeInput.value = '';
                        return;
                    }

                    const endTime = new Date(startTime.getTime() + (2 * 60 * 60 * 1000));

                    const year = endTime.getFullYear();
                    const month = (endTime.getMonth() + 1).toString().padStart(2, '0');
                    const day = endTime.getDate().toString().padStart(2, '0');
                    const hours = endTime.getHours().toString().padStart(2, '0');
                    const minutes = endTime.getMinutes().toString().padStart(2, '0');

                    endTimeInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
                } else {
                    endTimeInput.value = '';
                    if (document.getElementById('start_time').min) {
                         endTimeInput.min = document.getElementById('start_time').min;
                    }
                }
            }

            // Event listener for when the start time is changed by the user or programmatically
            startTimeInput.addEventListener('change', function() {
                console.log("Start time changed to:", this.value);
                setEndTimeBasedOnStartTime(this.value);
            });

            // Initial setup on page load
            setTimeout(function() {
                console.log("Initial startTimeInput.value on load:", startTimeInput.value);
                if (startTimeInput.value) {
                    // Trigger the change event to run the full logic including setting endTime
                    startTimeInput.dispatchEvent(new Event('change'));
                } else if (startTimeInput.min) {
                    // If no initial value, but min is set, ensure endTime.min respects it.
                    endTimeInput.min = startTimeInput.min;
                    console.log("Initial start_time has no value, endTime.min set to:", endTimeInput.min);
                }
            }, 100); // Increased delay slightly to 100ms
        });
    </script>
    @endpush
</x-app-layout>

