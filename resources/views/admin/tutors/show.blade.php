@extends('layouts.admin')

@section('content')
    <!-- Header -->
    <div class="admin-page-header bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 px-6 py-4">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('admin.tutor_details') }}: {{ $user->name }}
        </h2>
    </div>

    <!-- Main Content -->
    <div class="px-6 py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Tutor Information Card -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('admin.tutor_information') }}</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-8">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.name') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.email') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.account_status') }}</dt>
                        <dd class="mt-1 text-sm">
                            <span @class([
                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                'bg-green-100 text-green-800' => $user->account_status === 'active',
                                'bg-yellow-100 text-yellow-800' => $user->account_status === 'suspended',
                                'bg-red-100 text-red-800' => $user->account_status === 'banned',
                                'dark:bg-green-700 dark:text-green-100' => $user->account_status === 'active',
                                'dark:bg-yellow-700 dark:text-yellow-100' => $user->account_status === 'suspended',
                                'dark:bg-red-700 dark:text-red-100' => $user->account_status === 'banned',
                            ])>
                                {{ __('admin.' . $user->account_status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.joined_date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.phone_number') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->phone_number ?? __('admin.na') }}</dd>
                    </div>
                     <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.address') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->address ?? __('admin.na') }}</dd>
                    </div>
                    @if($user->tutor)
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.hourly_rate') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ formatCurrency($user->tutor->hourly_rate) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.average_rating') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $averageRating ? number_format($averageRating, 1) . ' / 5' : __('admin.na') }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.bio') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->tutor->bio ?? __('admin.na') }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('admin.subjects') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                @if($user->tutor->subjects->count() > 0)
                                    @foreach($user->tutor->subjects as $subject)
                                        {{ translateSubjectName($subject->name) }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @else
                                    {{ __('admin.no_subjects_specified') }}
                                @endif
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Tutor Bookings Card -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('admin.bookings_as_tutor') }} ({{ $user->tutorBookings->count() }})</h3>
                @if($user->tutorBookings->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('admin.student') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('admin.subject') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('admin.date_time') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('admin.status') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ __('admin.price') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($user->tutorBookings as $booking)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $booking->student->name ?? __('admin.na') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ translateSubjectName($booking->subject->name) ?? __('admin.na') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            {{ Carbon\Carbon::parse($booking->start_time)->format('d/m/Y H:i') }} - {{ Carbon\Carbon::parse($booking->end_time)->format('H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                             <span @class([
                                                'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                'bg-green-100 text-green-800' => $booking->status === 'completed' || $booking->status === 'accepted',
                                                'bg-yellow-100 text-yellow-800' => $booking->status === 'pending',
                                                'bg-red-100 text-red-800' => $booking->status === 'cancelled' || $booking->status === 'rejected',
                                                'bg-gray-100 text-gray-800' => !in_array($booking->status, ['completed', 'accepted', 'pending', 'cancelled', 'rejected']),
                                                'dark:bg-green-700 dark:text-green-100' => $booking->status === 'completed' || $booking->status === 'accepted',
                                                'dark:bg-yellow-700 dark:text-yellow-100' => $booking->status === 'pending',
                                                'dark:bg-red-700 dark:text-red-100' => $booking->status === 'cancelled' || $booking->status === 'rejected',
                                                'dark:bg-gray-600 dark:text-gray-200' => !in_array($booking->status, ['completed', 'accepted', 'pending', 'cancelled', 'rejected']),
                                            ])>
                                                {{ __('admin.' . $booking->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ formatCurrency($booking->price) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400">{{ __('admin.no_bookings_found_for_tutor') }}</p>
                @endif
            </div>

            <!-- Tutor Reviews Card -->
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('admin.reviews_received') }} ({{ $user->reviewsReceived->count() }})</h3>
                @if($user->reviewsReceived->count() > 0)
                    <div class="space-y-4">
                        @foreach($user->reviewsReceived as $review)
                            <div class="p-4 border rounded-md dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $review->reviewer->name ?? __('admin.anonymous') }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $review->created_at->format('d/m/Y') }}</p>
                                </div>
                                <div class="flex items-center mt-1">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="h-5 w-5 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.97a1 1 0 00.95.69h4.174c.969 0 1.371 1.24.588 1.81l-3.378 2.452a1 1 0 00-.364 1.118l1.287 3.971c.3.921-.755 1.688-1.54 1.118l-3.378-2.452a1 1 0 00-1.175 0l-3.378 2.452c-.784.57-1.838-.197-1.539-1.118l1.286-3.971a1 1 0 00-.364-1.118L2.04 9.398c-.783-.57-.38-1.81.588-1.81h4.174a1 1 0 00.95-.69l1.286-3.97z" />
                                        </svg>
                                    @endfor
                                </div>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $review->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400">{{ __('admin.no_reviews_found_for_tutor') }}</p>
                @endif
            </div>

            <!-- Education and Certificates Card -->
            @if($user->tutor && $user->tutor->educations && $user->tutor->educations->count() > 0)
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('tutors.education_and_certificates') }}</h3>
                <div class="space-y-6">
                    @foreach($user->tutor->educations as $edu)
                        <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                            <div class="flex items-start gap-4">
                                <div class="flex-grow">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200">{{ $edu->degree }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $edu->institution }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-500">{{ $edu->year }}</p>
                                </div>
                            </div>

                            <!-- Certificate Images -->
                            @if($edu->hasImages())
                                <div class="mt-4">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('tutors.certificate_images') }}:</p>
                                    <div class="grid grid-cols-4 gap-2">
                                        @foreach($edu->getAllImages() as $index => $image)
                                            @if(file_exists(public_path('uploads/education/' . $image)))
                                                <div class="relative group">
                                                    <img src="{{ asset('uploads/education/' . $image) }}"
                                                         alt="Certificate {{ $index + 1 }}"
                                                         class="certificate-image h-20 w-20 object-cover rounded-md border cursor-pointer hover:opacity-80 transition-all duration-300"
                                                         onclick="openImageModal('{{ asset('uploads/education/' . $image) }}', '{{ $edu->degree }}', '{{ $edu->institution }} - {{ $edu->year }}')" />
                                                    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all rounded-md pointer-events-none">
                                                        <svg class="h-8 w-8 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z M15 15l-2-2m0 0l-2-2m2 2l2-2m-2 2l-2 2"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="absolute bottom-1 right-1 bg-black bg-opacity-60 text-white text-xs px-1 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                                                        {{ $index + 1 }}/{{ count($edu->getAllImages()) }}
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="mt-4 flex items-center justify-center h-20 bg-gray-100 dark:bg-gray-600 rounded border-2 border-dashed border-gray-300 dark:border-gray-500">
                                    <div class="text-center">
                                        <svg class="mx-auto h-8 w-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('tutors.no_images_uploaded') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('tutors.education_and_certificates') }}</h3>
                <div class="text-center py-8 px-4 border-2 border-dashed rounded-lg border-gray-300 dark:border-gray-600">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('tutors.no_education_records_yet') }}</p>
                </div>
            </div>
            @endif

            <div class="mt-6">
                <a href="{{ route('admin.tutors') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('admin.back_to_tutors_list') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-75">
        <div class="relative max-w-2xl w-full mx-auto rounded-lg overflow-hidden">
            <span class="absolute top-2 right-2">
                <button id="closeModal" class="text-white text-3xl">&times;</button>
            </span>
            <img id="modalImage" src="" alt="Certificate Image" class="w-full h-auto">
            <div class="p-4">
                <h4 id="modalDegree" class="text-lg font-semibold text-white"></h4>
                <p id="modalInstitutionYear" class="text-sm text-gray-300"></p>
            </div>
        </div>
    </div>

    

    @push('scripts')
        <script src="{{ asset('js/pages/admin-tutors-show.js') }}"></script>
    @endpush
@endsection
