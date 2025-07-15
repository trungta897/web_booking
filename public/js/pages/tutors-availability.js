/**
 * Extracted from: tutors\availability.blade.php
 * Generated on: 2025-07-15 03:51:34
 */

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