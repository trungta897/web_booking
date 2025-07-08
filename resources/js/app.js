import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Theme toggle script
document.addEventListener('DOMContentLoaded', () => {
    const themeToggleButton = document.getElementById('theme-toggle-button');
    const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
    const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

    // Function to apply the current theme
    const applyTheme = (isDark) => {
        if (isDark) {
            document.documentElement.classList.add('dark');
            // Dark mode: show moon (dark icon), hide sun (light icon)
            if (themeToggleDarkIcon) themeToggleDarkIcon.classList.remove('hidden');
            if (themeToggleLightIcon) themeToggleLightIcon.classList.add('hidden');
            localStorage.setItem('color-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            // Light mode: hide moon (dark icon), show sun (light icon)
            if (themeToggleDarkIcon) themeToggleDarkIcon.classList.add('hidden');
            if (themeToggleLightIcon) themeToggleLightIcon.classList.remove('hidden');
            localStorage.setItem('color-theme', 'light');
        }
    };

    // Check initial theme preference (support both old and new keys for compatibility)
    const savedTheme = localStorage.getItem('color-theme') || localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    // Debug logging (uncomment for troubleshooting)
    // console.log('Theme Toggle Debug:', {
    //     savedTheme,
    //     prefersDark,
    //     hasButton: !!themeToggleButton,
    //     hasDarkIcon: !!themeToggleDarkIcon,
    //     hasLightIcon: !!themeToggleLightIcon
    // });

    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        applyTheme(true);
    } else {
        applyTheme(false); // Default to light if no preference or saved light
    }

    // Add event listener for the toggle button
    if (themeToggleButton) {
        themeToggleButton.addEventListener('click', (e) => {
            e.preventDefault();
            const isDarkMode = document.documentElement.classList.contains('dark');
            // console.log('Theme toggle clicked, current dark mode:', isDarkMode);
            applyTheme(!isDarkMode);
        });

        // Add visual feedback
        themeToggleButton.style.cursor = 'pointer';
        themeToggleButton.setAttribute('title', 'Toggle theme');
    } else {
        console.warn('Theme toggle button not found!');
    }
});
