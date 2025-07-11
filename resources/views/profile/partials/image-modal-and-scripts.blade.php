<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeImageModal()"></div>

        <!-- Modal panel - tối ưu cho ảnh -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
            <div class="bg-white px-2 pt-3 pb-2 sm:p-4">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        {{ __('profile.certificate_details') }}
                    </h3>
                    <button type="button" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100" onclick="closeImageModal()">
                         <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                                <!-- Modal Content - chỉ hiển thị ảnh, không có vùng trống -->
                <div class="flex justify-center items-center relative" style="min-height: 300px;">
                    <!-- Loading indicator -->
                    <div id="modalLoading" class="absolute inset-0 flex justify-center items-center">
                        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
                    </div>

                    <!-- Ảnh chính -->
                    <img id="modalImage"
                         src=""
                         alt="Certificate"
                         class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-lg"
                         style="display: none; background: transparent;">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Đặt script trực tiếp trong head để đảm bảo function có sẵn -->
<script>
                // Định nghĩa functions ngay lập tức
    window.openImageModal = function(imageSrc, caption) {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const modalTitle = document.getElementById('modal-title');
        const modalLoading = document.getElementById('modalLoading');

        if (modal && modalImage && modalTitle && modalLoading) {
            // Reset trạng thái
            modalImage.style.display = 'none';
            modalImage.src = '';
            modalLoading.style.display = 'flex';

            // Cập nhật title
            modalTitle.textContent = caption;

            // Hiển thị modal
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            // Load ảnh mới
            modalImage.onload = function() {
                modalLoading.style.display = 'none';
                modalImage.style.display = 'block';
            };

            modalImage.onerror = function() {
                modalLoading.style.display = 'none';
                modalImage.style.display = 'none';
                modalTitle.textContent = 'Không thể tải ảnh';
            };

            modalImage.src = imageSrc;
        }
    };

        window.closeImageModal = function() {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const modalLoading = document.getElementById('modalLoading');

        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';

            // Reset trạng thái khi đóng modal
            if (modalImage) {
                modalImage.src = '';
                modalImage.style.display = 'none';
            }
            if (modalLoading) {
                modalLoading.style.display = 'none';
            }
        }
    };

            // Xử lý phím Escape để đóng modal
    document.addEventListener('keydown', (e) => {
        if (e.key === "Escape") {
            const modal = document.getElementById('imageModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeImageModal();
            }
        }
    });
</script>
