<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Booking Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Session Information</h3>
                                <p class="mt-1 text-sm text-gray-600">Booking #{{ $booking->id }}</p>
                            </div>
                            <span class="px-3 py-1 text-sm font-semibold rounded-full
                                @if($booking->status === 'accepted') bg-green-100 text-green-800
                                @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($booking->status === 'rejected') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Tutor</h4>
                            <div class="mt-2 flex items-center">
                                <img class="h-10 w-10 rounded-full" src="{{ $booking->tutor->user->profile_photo_url }}" alt="{{ $booking->tutor->user->name }}">
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $booking->tutor->user->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $booking->subject->name }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Student</h4>
                            <div class="mt-2 flex items-center">
                                <img class="h-10 w-10 rounded-full" src="{{ $booking->student->profile_photo_url }}" alt="{{ $booking->student->name }}">
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $booking->student->name }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Date & Time</h4>
                            <p class="mt-2 text-sm text-gray-900">
                                {{ $booking->start_time->format('F d, Y') }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $booking->start_time->format('h:i A') }} - {{ $booking->end_time->format('h:i A') }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Price</h4>
                            <p class="mt-2 text-sm text-gray-900">${{ number_format($booking->price, 2) }}</p>
                        </div>

                        @if($booking->notes)
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-500">Notes</h4>
                                <p class="mt-2 text-sm text-gray-900">{{ $booking->notes }}</p>
                            </div>
                        @endif

                        @if($booking->meeting_link)
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-500">Meeting Link</h4>
                                <a href="{{ $booking->meeting_link }}" target="_blank" class="mt-2 text-sm text-indigo-600 hover:text-indigo-900">
                                    {{ $booking->meeting_link }}
                                </a>
                            </div>
                        @endif

                        @if($booking->status === 'accepted' && $booking->payment_status !== 'paid' && auth()->user()->id === $booking->student_id)
                            <div class="md:col-span-2">
                                <h4 class="text-sm font-medium text-gray-500">Payment</h4>
                                <div class="mt-2">
                                    <form id="payment-form" class="max-w-md">
                                        <div id="payment-element" class="mb-4"></div>
                                        <button id="submit-payment" class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition">
                                            Pay ${{ number_format($booking->price, 2) }}
                                        </button>
                                        <div id="payment-message" class="hidden mt-2 text-sm text-red-600"></div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($booking->status === 'pending' && auth()->user()->id === $booking->tutor->user_id)
                        <div class="mt-6 flex justify-end space-x-3">
                            <form action="{{ route('bookings.update', $booking) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition">
                                    Reject
                                </button>
                            </form>
                            <form action="{{ route('bookings.update', $booking) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="accepted">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-blue-600 uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition">
                                    Accept
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($booking->status === 'pending' && auth()->user()->id === $booking->student_id)
                        <div class="mt-6 flex justify-end">
                            <form action="{{ route('bookings.destroy', $booking) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest shadow-sm hover:text-red-500 focus:outline-none focus:border-red-300 focus:ring focus:ring-red-200 active:text-red-800 active:bg-gray-50 disabled:opacity-25 transition" onclick="return confirm('Are you sure you want to cancel this booking?')">
                                    Cancel Booking
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($booking->status === 'accepted' && $booking->payment_status !== 'paid' && auth()->user()->id === $booking->student_id)
        @push('scripts')
        <script src="https://js.stripe.com/v3/"></script>
        <script>
            const stripe = Stripe('{{ config('services.stripe.key') }}');
            const elements = stripe.elements();
            const paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');

            const form = document.getElementById('payment-form');
            const submitButton = document.getElementById('submit-payment');
            const messageDiv = document.getElementById('payment-message');

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                submitButton.disabled = true;

                try {
                    const response = await fetch('{{ route('payments.create-intent', $booking) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.error) {
                        throw new Error(data.error);
                    }

                    const { error } = await stripe.confirmPayment({
                        elements,
                        clientSecret: data.clientSecret,
                        confirmParams: {
                            return_url: '{{ route('bookings.show', $booking) }}',
                        }
                    });

                    if (error) {
                        throw error;
                    }
                } catch (error) {
                    messageDiv.textContent = error.message;
                    messageDiv.classList.remove('hidden');
                    submitButton.disabled = false;
                }
            });
        </script>
        @endpush
    @endif
</x-app-layout>
