/**
 * Extracted from: vnpay-result.blade.php
 * Generated on: 2025-07-15 03:51:34
 */

// Auto redirect after successful payment (optional)
        @if($result['success'])
            // Uncomment if you want auto redirect after 10 seconds
            // setTimeout(() => {
            //     window.location.href = '{{ route("bookings.index") }}';
            // }, 10000);
        @endif

        // Add confetti effect for success
        @if($result['success'])
            document.addEventListener('DOMContentLoaded', function() {
                // Simple confetti effect using CSS animation
                const confettiContainer = document.createElement('div');
                confettiContainer.style.position = 'fixed';
                confettiContainer.style.top = '0';
                confettiContainer.style.left = '0';
                confettiContainer.style.width = '100%';
                confettiContainer.style.height = '100%';
                confettiContainer.style.pointerEvents = 'none';
                confettiContainer.style.zIndex = '9999';

                for (let i = 0; i < 30; i++) {
                    const confetti = document.createElement('div');
                    confetti.style.position = 'absolute';
                    confetti.style.width = '10px';
                    confetti.style.height = '10px';
                    confetti.style.background = `hsl(${Math.random() * 360}, 100%, 50%)`;
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.animation = `fall ${Math.random() * 3 + 2}s linear infinite`;
                    confetti.style.animationDelay = Math.random() * 2 + 's';
                    confettiContainer.appendChild(confetti);
                }

                document.body.appendChild(confettiContainer);

                // Remove confetti after 5 seconds
                setTimeout(() => {
                    document.body.removeChild(confettiContainer);
                }, 5000);
            });
        @endif