/**
 * Booking Create Page JavaScript
 * Handles date/time input formatting and validation
 */
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
            // Note: Alert messages need to be passed from Blade template via data attributes
            const form = this;
            const dateFormatError = form.dataset.dateFormatError || 'Invalid date format. Please use dd-mm-yyyy.';
            alert(dateFormatError);
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
                const endTimeError = form.dataset.endTimeError || 'End time must be after start time.';
                alert(endTimeError);
                return false;
            }
        }
    });
});
