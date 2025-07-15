/**
 * Extracted from: messages\show.blade.php
 * Generated on: 2025-07-15 03:51:34
 */

// Scroll to bottom of messages container on page load
        document.addEventListener('DOMContentLoaded', function() {
            const messagesContainer = document.getElementById('messages-container');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        });