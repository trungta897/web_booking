/**
 * Extracted from: components\language-switcher.blade.php
 * Generated on: 2025-07-15 03:51:33
 */

document.addEventListener('DOMContentLoaded', function() {
function toggleLanguageMenu() {
    const menu = document.getElementById('language-menu');
    menu.classList.toggle('hidden');
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const button = document.getElementById('language-menu-button');
    const menu = document.getElementById('language-menu');

    if (!button.contains(event.target) && !menu.contains(event.target)) {
        menu.classList.add('hidden');
    }
});
});