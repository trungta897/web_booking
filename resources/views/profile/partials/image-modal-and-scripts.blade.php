<!-- Image Modal -->
<div id="imageModal" class="hidden fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" onclick="closeImageModal(event)">
    <div class="relative max-w-4xl max-h-full mx-4">
        <!-- Close Button -->
        <button onclick="closeImageModal()" class="absolute top-4 right-4 z-10 bg-black bg-opacity-50 text-white rounded-full p-2 hover:bg-opacity-75 transition-opacity">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Image Container -->
        <div class="bg-white rounded-lg overflow-hidden shadow-2xl max-w-full max-h-full">
            <!-- Image Header -->
            <div class="px-6 py-4 bg-gray-50 border-b">
                <h3 id="modalImageTitle" class="text-lg font-semibold text-gray-900"></h3>
                <p id="modalImageSubtitle" class="text-sm text-gray-600 mt-1"></p>
            </div>

            <!-- Image Content -->
            <div class="p-4 flex items-center justify-center bg-gray-100" style="max-height: 70vh;">
                <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded shadow">
            </div>

            <!-- Image Footer -->
            <div class="px-6 py-3 bg-gray-50 border-t flex justify-end items-center">
                <div class="flex space-x-2">
                    <button onclick="downloadImage()" class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition-colors">
                        {{ __('common.download') }}
                    </button>
                    <button onclick="closeImageModal()" class="px-3 py-1 bg-gray-500 text-white text-sm rounded hover:bg-gray-600 transition-colors">
                        {{ __('common.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
</script>

<style>
/* Modal Animation */
#imageModal {
    animation: fadeIn 0.3s ease-out;
}

#imageModal.hidden {
    animation: fadeOut 0.2s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: scale(1);
    }
    to {
        opacity: 0;
        transform: scale(0.9);
    }
}

/* Hover effects for images */
.certificate-image {
    transition: all 0.3s ease;
}

.certificate-image:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Loading state */
#modalImage {
    transition: opacity 0.3s ease;
}

#modalImage[src=""] {
    opacity: 0.5;
}
</style>
