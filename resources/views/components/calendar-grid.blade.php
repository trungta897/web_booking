@props(['weeks', 'monthType'])

<div class="calendar-grid">
    <!-- Calendar Header -->
    <div class="grid grid-cols-7 gap-0 mb-2">
        <div class="p-2 text-center text-xs font-medium text-gray-500 uppercase">{{ __('common.sunday') }}</div>
        <div class="p-2 text-center text-xs font-medium text-gray-500 uppercase">{{ __('common.monday') }}</div>
        <div class="p-2 text-center text-xs font-medium text-gray-500 uppercase">{{ __('common.tuesday') }}</div>
        <div class="p-2 text-center text-xs font-medium text-gray-500 uppercase">{{ __('common.wednesday') }}</div>
        <div class="p-2 text-center text-xs font-medium text-gray-500 uppercase">{{ __('common.thursday') }}</div>
        <div class="p-2 text-center text-xs font-medium text-gray-500 uppercase">{{ __('common.friday') }}</div>
        <div class="p-2 text-center text-xs font-medium text-gray-500 uppercase">{{ __('common.saturday') }}</div>
    </div>

    <!-- Calendar Body -->
    <div class="border border-gray-200 rounded-lg overflow-hidden">
        @foreach($weeks as $weekIndex => $week)
            <div class="grid grid-cols-7 gap-0">
                @foreach($week as $dayIndex => $day)
                    <div class="border-r border-b border-gray-100 last:border-r-0 {{ $weekIndex === count($weeks) - 1 ? 'border-b-0' : '' }}"
                         :class="{
                             'bg-blue-50': hasBookings('{{ $day['date'] }}'),
                             'bg-green-50': getBookingStatus('{{ $day['date'] }}') === 'confirmed',
                             'bg-yellow-50': getBookingStatus('{{ $day['date'] }}') === 'pending',
                             'bg-gradient-to-br from-green-50 to-yellow-50': getBookingStatus('{{ $day['date'] }}') === 'mixed'
                         }">
                        <button @click="openBookingModal('{{ $day['date'] }}')"
                                class="w-full h-16 p-1 hover:bg-gray-50 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset"
                                :class="{
                                    'cursor-pointer': hasBookings('{{ $day['date'] }}'),
                                    'cursor-default': !hasBookings('{{ $day['date'] }}')
                                }">
                            <div class="flex flex-col items-center justify-center h-full">
                                <!-- Day Number -->
                                <span class="text-sm font-medium {{ !$day['is_current_month'] ? 'text-gray-400' : ($day['is_today'] ? 'text-blue-600 font-bold' : ($day['is_past'] ? 'text-gray-500' : 'text-gray-900')) }}">
                                    {{ $day['day'] }}
                                </span>

                                <!-- Booking Indicator -->
                                <div x-show="hasBookings('{{ $day['date'] }}')" class="mt-1">
                                    <div class="flex items-center justify-center">
                                        <span class="inline-flex items-center justify-center w-5 h-4 text-xs font-medium rounded-full"
                                              :class="{
                                                  'bg-green-100 text-green-700': getBookingStatus('{{ $day['date'] }}') === 'confirmed',
                                                  'bg-yellow-100 text-yellow-700': getBookingStatus('{{ $day['date'] }}') === 'pending',
                                                  'bg-blue-100 text-blue-700': getBookingStatus('{{ $day['date'] }}') === 'mixed'
                                              }"
                                              x-text="getBookingCount('{{ $day['date'] }}')">
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>

<style>
.calendar-grid {
    font-family: inherit;
}

.calendar-grid button:hover .booking-indicator {
    transform: scale(1.1);
    transition: transform 0.1s ease-in-out;
}

/* Today highlight */
.calendar-grid .today {
    position: relative;
}

.calendar-grid .today::after {
    content: '';
    position: absolute;
    bottom: 2px;
    left: 50%;
    transform: translateX(-50%);
    width: 6px;
    height: 6px;
    background-color: #3B82F6;
    border-radius: 50%;
}

/* Past day styling */
.calendar-grid .past-day {
    opacity: 0.6;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .calendar-grid button {
        height: 3rem;
        padding: 0.25rem;
    }

    .calendar-grid .text-sm {
        font-size: 0.75rem;
    }
}
</style>
