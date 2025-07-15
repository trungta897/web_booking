/**
 * Extracted from: components\language-notification.blade.php
 * Generated on: 2025-07-15 03:51:33
 */

document.addEventListener('DOMContentLoaded', function() {
    const notification = document.getElementById('language-notification');
    if (notification) {
        // Show notification
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);

        // Auto hide after 4 seconds
        setTimeout(() => {
            closeLanguageNotification();
        }, 4000);
    }
});

function closeLanguageNotification() {
    const notification = document.getElementById('language-notification');
    if (notification) {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}