<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Availability') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('tutor.availability.update') }}">
                        @csrf
                        <div class="mb-6">
                            <div class="flex items-center mb-2">
                                <label for="is_available" class="block text-sm font-medium text-gray-700 mr-4">Overall Availability</label>
                                <div class="relative inline-block w-10 mr-2 align-middle select-none">
                                    <input type="checkbox" name="is_available" id="is_available" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                                        {{ $tutor->is_available ? 'checked' : '' }}>
                                    <label for="is_available" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                                <span class="text-sm text-gray-700">{{ $tutor->is_available ? 'Available for bookings' : 'Not available' }}</span>
                            </div>
                            <p class="text-sm text-gray-500">Toggle your overall availability. If turned off, students won't be able to book sessions with you.</p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Weekly Schedule</h3>
                            <p class="text-sm text-gray-500 mb-4">Set your recurring weekly availability for each day. Students will only be able to book sessions during these time slots.</p>

                            <div class="space-y-4">
                                @foreach([0, 1, 2, 3, 4, 5, 6] as $day)
                                    @php
                                        $dayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$day];
                                        $availability = $availabilities->where('day_of_week', $day)->first();
                                    @endphp
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="text-md font-medium text-gray-900">{{ $dayName }}</h4>
                                            <div class="relative inline-block w-10 mr-2 align-middle select-none">
                                                <input type="checkbox" name="days[{{ $day }}][is_available]" id="day_{{ $day }}" class="day-toggle toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                                                    {{ $availability && $availability->is_available ? 'checked' : '' }} data-day="{{ $day }}">
                                                <label for="day_{{ $day }}" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                            </div>
                                        </div>
                                        <div id="time_slots_{{ $day }}" class="time-slots grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 {{ (!$availability || !$availability->is_available) ? 'hidden' : '' }}">
                                            <div>
                                                <label for="start_time_{{ $day }}" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                                <input type="time" name="days[{{ $day }}][start_time]" id="start_time_{{ $day }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    value="{{ $availability ? $availability->start_time : '09:00:00' }}">
                                            </div>
                                            <div>
                                                <label for="end_time_{{ $day }}" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                                <input type="time" name="days[{{ $day }}][end_time]" id="end_time_{{ $day }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                    value="{{ $availability ? $availability->end_time : '17:00:00' }}">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                Save Availability
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .toggle-checkbox:checked {
            right: 0;
            border-color: #68D391;
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: #68D391;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dayToggles = document.querySelectorAll('.day-toggle');

            dayToggles.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const day = this.dataset.day;
                    const timeSlots = document.getElementById(`time_slots_${day}`);

                    if (this.checked) {
                        timeSlots.classList.remove('hidden');
                    } else {
                        timeSlots.classList.add('hidden');
                    }
                });
            });
        });
    </script>
</x-app-layout>
