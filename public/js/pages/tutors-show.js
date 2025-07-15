/**
 * Extracted from: tutors\show.blade.php
 * Generated on: 2025-07-15 03:51:34
 */

document.addEventListener('DOMContentLoaded', function() {
// Favorite functionality
        function toggleFavorite(tutorId) {
            fetch(`/tutors/${tutorId}/favorite`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data) => {
                const btn = document.getElementById('favoriteBtn');
                const text = document.getElementById('favoriteText');
                if (data.is_favorite) {
                    btn.classList.add('bg-red-50', 'border-red-300', 'text-red-700');
                    text.textContent = '{{ __('common.remove_from_favorites') }}';
                } else {
                    btn.classList.remove('bg-red-50', 'border-red-300', 'text-red-700');
                    text.textContent = '{{ __('common.add_to_favorites') }}';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Real-time availability checking
        function checkAvailability() {
            const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            days.forEach(day => {
                fetch(`/tutors/{{ $tutor->id }}/availability/${day}`)
                    .then(response => response.json())
                    .then(data) => {
                        const element = document.getElementById(`availability-${day}`);
                        if (data.available) {
                            element.textContent = data.slots.join(', ');
                            element.classList.add('text-green-600');
                        } else {
                            element.textContent = '{{ __('common.unavailable') }}';
                            element.classList.add('text-red-600');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading availability:', error);
                        const element = document.getElementById(`availability-${day}`);
                        element.textContent = '{{ __('common.error') }}';
                        element.classList.add('text-red-600');
                    });
            });
        }

        // Star rating functionality
        function setRating(rating) {
            document.getElementById('rating').value = rating;

            // Update star colors
            for (let i = 1; i <= 5; i++) {
                const star = document.getElementById(`star-${i}`);
                if (i <= rating) {
                    star.classList.remove('text-gray-300');
                    star.classList.add('text-yellow-400');
                } else {
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-300');
                }
            }
        }

        // Check availability on page load
        checkAvailability();
        // Refresh availability every 5 minutes
        setInterval(checkAvailability, 300000);

        // Certificate modal functionality
        function showCertificateModal(imageUrl, degree, institution) {
            const modal = document.getElementById('certificateModal');
            const modalImage = document.getElementById('modalCertificateImage');
            const modalTitle = document.getElementById('modalCertificateTitle');
            const modalInstitution = document.getElementById('modalCertificateInstitution');

            modalImage.src = imageUrl;
            modalTitle.textContent = degree;
            modalInstitution.textContent = institution;
            modal.classList.remove('hidden');
        }

        function closeCertificateModal() {
            document.getElementById('certificateModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('certificateModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCertificateModal();
            }
        });
});