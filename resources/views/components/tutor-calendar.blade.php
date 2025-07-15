@props(['calendarData'])

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="tutorCalendar(@js($calendarData))">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h4 class="text-lg font-medium text-gray-900">{{ __('common.teaching_schedule') }}</h4>
            <div class="flex space-x-2">
                <button @click="showCurrentMonth" :class="{'bg-blue-500 text-white': currentView === 'current', 'bg-gray-100 text-gray-700': currentView !== 'current'}" class="px-3 py-1 rounded-md text-sm font-medium transition-colors">
                    {{ __('common.current_month') }}
                </button>
                <button @click="showNextMonth" :class="{'bg-blue-500 text-white': currentView === 'next', 'bg-gray-100 text-gray-700': currentView !== 'next'}" class="px-3 py-1 rounded-md text-sm font-medium transition-colors">
                    {{ __('common.next_month') }}
                </button>
            </div>
        </div>

        <!-- Current Month Calendar -->
        <div x-show="currentView === 'current'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <h5 class="text-md font-medium text-gray-800 mb-4" x-text="calendarData.current_month_name"></h5>
            @include('components.calendar-grid', ['weeks' => $calendarData['calendar_weeks'], 'monthType' => 'current'])
        </div>

        <!-- Next Month Calendar -->
        <div x-show="currentView === 'next'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
            <h5 class="text-md font-medium text-gray-800 mb-4" x-text="calendarData.next_month_name"></h5>
            @include('components.calendar-grid', ['weeks' => $calendarData['next_calendar_weeks'], 'monthType' => 'next'])
        </div>

        <!-- Booking Details Modal -->
        <div x-show="showBookingModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeBookingModal"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900" x-text="`{{ __('common.schedule_for') }} ${selectedDate}`"></h3>
                            <button @click="closeBookingModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div x-show="selectedBookings.length > 0">
                            <div class="space-y-3">
                                <template x-for="booking in selectedBookings" :key="booking.id">
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900" x-text="booking.subject_name"></h4>
                                                <p class="text-sm text-gray-600">{{ __('common.student') }}: <span x-text="booking.student_name"></span></p>
                                                <p class="text-sm text-gray-500">
                                                    <span x-text="booking.start_time"></span> - <span x-text="booking.end_time"></span>
                                                </p>
                                                <p class="text-sm font-medium text-gray-700" x-text="booking.price"></p>
                                            </div>
                                            <div class="flex flex-col items-end space-y-2">
                                                <span :class="{
                                                    'bg-yellow-50': booking.status === 'pending',
                                                    'bg-green-50': booking.status === 'accepted',
                                                    'bg-blue-50': booking.status === 'completed',
                                                    'bg-gray-100 text-gray-800': booking.status === 'cancelled'
                                                }" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" x-text="booking.status"></span>
                                                <a :href="`/bookings/${booking.id}`" class="text-sm text-indigo-600 hover:text-indigo-900">
                                                    {{ __('common.view_details') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div x-show="selectedBookings.length === 0" class="text-gray-500 text-center py-4">
                            {{ __('common.no_bookings_on_this_date') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-6 flex items-center justify-center space-x-6 text-sm">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-blue-100 border border-blue-200 rounded mr-2"></div>
                <span class="text-gray-600">{{ __('common.has_classes') }}</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-100 border border-green-200 rounded mr-2"></div>
                <span class="text-gray-600">{{ __('common.accepted') }}</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-yellow-100 border border-yellow-200 rounded mr-2"></div>
                <span class="text-gray-600">{{ __('common.pending') }}</span>
            </div>
        </div>
    </div>
</div>



    @push('scripts')
        <script src="{{ asset('js/pages/components-tutor-calendar.js') }}"></script>
    @endpush