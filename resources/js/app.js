import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse'

Alpine.plugin(collapse)

window.Alpine = Alpine;

Alpine.start();

// Image Zoom Modal functionality - Fix gray area issue
document.addEventListener('DOMContentLoaded', () => {
    // Create modal HTML structure
    const createImageModal = () => {
        const modal = document.createElement('div');
        modal.className = 'image-modal';
        modal.innerHTML = `
            <div class="image-modal-content">
                <button class="image-modal-close" aria-label="Close">&times;</button>
                <img class="image-modal-img" src="" alt="Zoomed image">
            </div>
        `;
        document.body.appendChild(modal);
        return modal;
    };

    // Initialize image zoom functionality
    const initImageZoom = () => {
        let modal = document.querySelector('.image-modal');
        if (!modal) {
            modal = createImageModal();
        }

        const modalImg = modal.querySelector('.image-modal-img');
        const closeBtn = modal.querySelector('.image-modal-close');

        // Add click handlers to all zoomable images
        const zoomableImages = document.querySelectorAll('.zoomable-image, .profile-image, img[data-zoomable="true"]');

        zoomableImages.forEach(img => {
            // Ensure proper styling
            if (!img.classList.contains('zoomable-image')) {
                img.classList.add('zoomable-image');
            }

            img.addEventListener('click', (e) => {
                e.preventDefault();
                modalImg.src = img.src;
                modalImg.alt = img.alt || 'Zoomed image';
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });

        // Close modal handlers
        const closeModal = () => {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        };

        closeBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Keyboard close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });
    };

    // Initialize on page load
    initImageZoom();

    // Re-initialize when new content is loaded (for dynamic content)
    const observer = new MutationObserver(() => {
        initImageZoom();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Theme toggle script
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
