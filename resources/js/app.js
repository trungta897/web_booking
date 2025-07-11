import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse'

Alpine.plugin(collapse)

window.Alpine = Alpine;

Alpine.start();

// Theme toggle script
document.addEventListener('DOMContentLoaded', () => {
    const themeToggleButton = document.getElementById('theme-toggle-button');

    // Only run the theme logic if the toggle button exists on the page
    if (themeToggleButton) {
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        const applyTheme = (isDark) => {
            if (isDark) {
                document.documentElement.classList.add('dark');
                if (themeToggleDarkIcon) themeToggleDarkIcon.classList.remove('hidden');
                if (themeToggleLightIcon) themeToggleLightIcon.classList.add('hidden');
                localStorage.setItem('color-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                if (themeToggleDarkIcon) themeToggleDarkIcon.classList.add('hidden');
                if (themeToggleLightIcon) themeToggleLightIcon.classList.remove('hidden');
                localStorage.setItem('color-theme', 'light');
            }
        };

        const savedTheme = localStorage.getItem('color-theme') || localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            applyTheme(true);
        } else {
            applyTheme(false);
        }

        themeToggleButton.addEventListener('click', (e) => {
            e.preventDefault();
            const isDarkMode = document.documentElement.classList.contains('dark');
            applyTheme(!isDarkMode);
        });

        themeToggleButton.style.cursor = 'pointer';
        themeToggleButton.setAttribute('title', 'Toggle theme');
    }
});
