/**
 * Language Switcher Component JavaScript
 * Enhanced version with better error handling and debugging
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Language switcher initialized');
    
    // Initialize Alpine.js if not already loaded
    if (typeof Alpine === 'undefined') {
        console.warn('Alpine.js not found, falling back to vanilla JavaScript');
        initVanillaLanguageSwitcher();
    }
    
    // Add click handlers for language links with loading state
    const languageLinks = document.querySelectorAll('a[href*="/language/"]');
    languageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Add loading state
            const originalText = this.textContent;
            this.innerHTML = '<span class="inline-block animate-spin mr-2">⟳</span>' + originalText.trim();
            this.style.pointerEvents = 'none';
            
            // Reset after 3 seconds if something goes wrong
            setTimeout(() => {
                this.innerHTML = originalText;
                this.style.pointerEvents = 'auto';
            }, 3000);
        });
    });
});

function initVanillaLanguageSwitcher() {
    const button = document.getElementById('language-menu-button');
    const menu = document.getElementById('language-menu');
    
    if (!button || !menu) {
        console.warn('Language switcher elements not found');
        return;
    }
    
    // Toggle menu function
    function toggleLanguageMenu() {
        const isHidden = menu.classList.contains('hidden');
        
        if (isHidden) {
            menu.classList.remove('hidden');
            button.setAttribute('aria-expanded', 'true');
        } else {
            menu.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');
        }
    }
    
    // Add click handler to button
    button.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleLanguageMenu();
    });
    
    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!button.contains(event.target) && !menu.contains(event.target)) {
            menu.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');
        }
    });
    
    // Close menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            menu.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');
        }
    });
}

// Show success/error messages
function showLanguageMessage(message, type = 'success') {
    const existingMessage = document.querySelector('.language-message');
    if (existingMessage) {
        existingMessage.remove();
    }
    
    const messageEl = document.createElement('div');
    messageEl.className = `language-message fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
        type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'
    }`;
    messageEl.textContent = message;
    
    document.body.appendChild(messageEl);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        messageEl.remove();
    }, 3000);
}

// Check for flash messages and display them
document.addEventListener('DOMContentLoaded', function() {
    // This would typically be handled by the backend, but we can check for URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const type = urlParams.get('type');
    
    if (message) {
        showLanguageMessage(decodeURIComponent(message), type || 'success');
    }
});