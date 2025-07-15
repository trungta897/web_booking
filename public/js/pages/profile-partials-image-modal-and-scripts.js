/**
 * Extracted from: profile\partials\image-modal-and-scripts.blade.php
 * Generated on: 2025-07-15 03:51:34
 */

// Image Modal Functions
function openImageModal(imageSrc, title, subtitle = '') {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('modalImageTitle');
    const modalSubtitle = document.getElementById('modalImageSubtitle');

    if (modal && modalImage && modalTitle) {
        modalImage.src = imageSrc;
        modalImage.alt = title;
        modalTitle.textContent = title;
        modalSubtitle.textContent = subtitle;

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling

        // Add keyboard event listener for ESC key
        document.addEventListener('keydown', handleModalKeydown);
    }
}

function closeImageModal(event = null) {
    // Nếu click vào modal background (không phải content), đóng modal
    if (event && event.target.id !== 'imageModal') return;

    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scrolling

        // Remove keyboard event listener
        document.removeEventListener('keydown', handleModalKeydown);
    }
}

function handleModalKeydown(event) {
    if (event.key === 'Escape') {
        closeImageModal();
    }
}

function downloadImage() {
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('modalImageTitle');

    if (modalImage && modalImage.src) {
        const link = document.createElement('a');
        link.href = modalImage.src;
        link.download = modalTitle.textContent || 'certificate';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Auto-close modal when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeImageModal();
            }
        });
    }

    // Đảm bảo không có modal cũ nào còn sót lại
    document.body.style.overflow = '';
});