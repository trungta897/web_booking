/**
 * Extracted from: components\tutor-calendar.blade.php
 * Generated on: 2025-07-15 03:51:34
 */

document.addEventListener('DOMContentLoaded', function() {
function tutorCalendar(calendarData) {
    return {
        calendarData: calendarData,
        currentView: 'current',
        showBookingModal: false,
        selectedDate: '',
        selectedBookings: [],

        showCurrentMonth() {
            this.currentView = 'current';
        },

        showNextMonth() {
            this.currentView = 'next';
        },

        async openBookingModal(date) {
            this.selectedDate = this.formatDate(date);
            this.selectedBookings = this.calendarData.bookings_by_date[date] || [];
            this.showBookingModal = true;

            // Optionally fetch fresh data via AJAX for more details
            try {
                const response = await fetch(`/calendar/bookings/${date}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        this.selectedBookings = data.bookings;
                    }
                }
            } catch (error) {
                console.warn('Could not fetch fresh booking data:', error);
                // Continue with cached data from calendarData
            }
        },

        closeBookingModal() {
            this.showBookingModal = false;
            this.selectedDate = '';
            this.selectedBookings = [];
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('vi-VN', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        hasBookings(date) {
            return this.calendarData.days_with_bookings && this.calendarData.days_with_bookings.includes(date);
        },

        getBookingCount(date) {
            return this.calendarData.bookings_by_date && this.calendarData.bookings_by_date[date] ? this.calendarData.bookings_by_date[date].length : 0;
        },

        getBookingStatus(date) {
            if (!this.calendarData.bookings_by_date || !this.calendarData.bookings_by_date[date]) {
                return null;
            }

            const bookings = this.calendarData.bookings_by_date[date];
            if (bookings.length === 0) return null;

            const hasPending = bookings.some(booking => booking.status === 'pending');
            const hasAccepted = bookings.some(booking => booking.status === 'accepted');
            const hasCompleted = bookings.some(booking => booking.status === 'completed');

            if (hasAccepted && hasPending) return 'mixed';
            if (hasCompleted) return 'completed';
            if (hasAccepted) return 'accepted';
            if (hasPending) return 'pending';
            return null;
        }
    }
}
});