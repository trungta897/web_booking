/**
 * Extracted from: admin\tutors\show.blade.php
 * Generated on: 2025-07-15 03:51:33
 */

document.addEventListener('DOMContentLoaded', function() {
function openImageModal(imageSrc, degree, institutionYear) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            const modalDegree = document.getElementById('modalDegree');
            const modalInstitutionYear = document.getElementById('modalInstitutionYear');

            modalImage.src = imageSrc;
            modalDegree.innerText = degree;
            modalInstitutionYear.innerText = institutionYear;

            modal.classList.remove('hidden');
        }

        document.getElementById('closeModal').onclick = function() {
            document.getElementById('imageModal').classList.add('hidden');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('imageModal');
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        }
});