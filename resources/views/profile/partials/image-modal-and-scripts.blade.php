<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeImageModal()"></div>

        <!-- Modal panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                 <div class="w-full">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            {{ __('profile.certificate_details') }}
                        </h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600" onclick="closeImageModal()">
                             <span class="sr-only">Close</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Content -->
                    <div class="text-center">
                        <img id="modalImage" src="" alt="Certificate" class="max-w-full max-h-[80vh] mx-auto rounded-lg shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openImageModal(imageSrc, caption) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('modal-title').textContent = caption;
        document.getElementById('imageModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === "Escape" && !document.getElementById('imageModal').classList.contains('hidden')) {
            closeImageModal();
        }
    });
</script>
@endpush
