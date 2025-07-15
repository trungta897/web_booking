/**
 * Tutors Show Page JavaScript
 * Handles favorite functionality, availability checking, reviews, and certificate modal
 */

document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    
    // Favorite functionality
    window.toggleFavorite = function(tutorId) {
        if (!csrfToken) {
            alert('CSRF token not found. Please refresh the page.');
            return;
        }

        fetch(`/tutors/${tutorId}/favorite`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const btn = document.getElementById('favoriteBtn');
            const text = document.getElementById('favoriteText');
            if (data.is_favorite) {
                btn.classList.add('bg-red-50', 'border-red-300', 'text-red-700');
                text.textContent = 'Remove from Favorites';
            } else {
                btn.classList.remove('bg-red-50', 'border-red-300', 'text-red-700');
                text.textContent = 'Add to Favorites';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating favorites.');
        });
    };

    // Real-time availability checking
    function checkAvailability() {
        const tutorId = document.querySelector('[data-tutor-id]')?.dataset.tutorId;
        if (!tutorId) return;

        const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        days.forEach(day => {
            fetch(`/tutors/${tutorId}/availability/${day}`)
                .then(response => response.json())
                .then(data => {
                    const element = document.getElementById(`availability-${day}`);
                    if (element) {
                        if (data.available) {
                            element.textContent = data.slots.join(', ');
                            element.className = 'text-green-600';
                        } else {
                            element.textContent = 'Unavailable';
                            element.className = 'text-red-600';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading availability:', error);
                    const element = document.getElementById(`availability-${day}`);
                    if (element) {
                        element.textContent = 'Error';
                        element.className = 'text-red-600';
                    }
                });
        });
    }

    // Star rating functionality
    window.setRating = function(rating) {
        const ratingInput = document.getElementById('rating');
        if (ratingInput) {
            ratingInput.value = rating;
        }

        // Update star colors
        for (let i = 1; i <= 5; i++) {
            const star = document.getElementById(`star-${i}`);
            if (star) {
                if (i <= rating) {
                    star.classList.remove('text-gray-300');
                    star.classList.add('text-yellow-400');
                } else {
                    star.classList.remove('text-yellow-400');
                    star.classList.add('text-gray-300');
                }
            }
        }
    };

    // Certificate modal functionality
    window.showCertificateModal = function(imageUrl, degree, institution) {
        const modal = document.getElementById('certificateModal');
        const modalImage = document.getElementById('modalCertificateImage');
        const modalTitle = document.getElementById('modalCertificateTitle');
        const modalInstitution = document.getElementById('modalCertificateInstitution');

        if (modal && modalImage && modalTitle && modalInstitution) {
            modalImage.src = imageUrl;
            modalTitle.textContent = degree;
            modalInstitution.textContent = institution;
            modal.classList.remove('hidden');
        }
    };

    window.closeCertificateModal = function() {
        const modal = document.getElementById('certificateModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    };

    // Close modal when clicking outside
    const certificateModal = document.getElementById('certificateModal');
    if (certificateModal) {
        certificateModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeCertificateModal();
            }
        });
    }

    // Check availability on page load
    checkAvailability();
    
    // Refresh availability every 5 minutes
    setInterval(checkAvailability, 300000);
});