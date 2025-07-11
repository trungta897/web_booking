<div class="max-w-4xl">
    <div class="flex justify-between items-start mb-6">
        <!-- Titles and Edit Button -->
        <div>
            <h3 class="text-lg font-medium text-gray-900">{{ __('profile.profile_information') }}</h3>
            <p class="mt-1 text-sm text-gray-600">
                {{ __('profile.view_or_edit_your_profile_details') }}
            </p>
        </div>
        <button @click="showEditForm = !showEditForm" class="ml-4 flex-shrink-0 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <span x-text="showEditForm ? '{{ __('common.cancel') }}' : '{{ __('profile.edit_profile') }}'"></span>
        </button>
    </div>

    <!-- Avatar and Main Info -->
    <div class="flex flex-col sm:flex-row gap-6 items-start">
        <!-- Textual Info -->
        <div class="flex-grow">
             <dl class="divide-y divide-gray-200">
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">{{ __('common.name') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->name }}</dd>
                </div>
                <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                    <dt class="text-sm font-medium text-gray-500">{{ __('common.email') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $user->email }}</dd>
                </div>
                 @if ($tutor)
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('tutors.hourly_rate') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ number_format($tutor->hourly_rate ?? 0, 0, ',', '.') }} VND</dd>
                    </div>
                    <div class="py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('tutors.bio') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            @php
                                $bioText = $tutor->bio ?? '';
                                if (is_array($bioText)) { $bioText = ''; }
                            @endphp
                            <p class="whitespace-pre-wrap">{{ $bioText }}</p>
                        </dd>
                    </div>
                @endif
             </dl>
        </div>
        <!-- Avatar -->
        <div class="flex-shrink-0">
            @if($user->avatar && file_exists(public_path('uploads/avatars/' . $user->avatar)))
                <img src="{{ asset('uploads/avatars/' . $user->avatar) }}" alt="Profile Picture" class="h-24 w-24 object-cover rounded-full border-4 border-white shadow-lg">
            @else
                <div class="h-24 w-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-3xl font-bold border-4 border-white shadow-lg">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
        </div>
    </div>

        <!-- Education Section -->
    @if ($tutor)
    <div class="mt-6 pt-6 border-t border-gray-200">
        <h4 class="text-md font-medium text-gray-900 mb-4">{{ __('tutors.education_and_certificates') }}</h4>
        @php
            // Get education records directly from database to avoid null relationship issues
            $educationRecords = \App\Models\Education::where('tutor_id', $tutor->id)->get();
        @endphp

        @if ($educationRecords->isNotEmpty())
            <ul class="space-y-4">
                @foreach ($educationRecords as $education)
                    <li class="p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-start gap-4 mb-3">
                            <div class="flex-grow">
                                <p class="font-semibold text-gray-800">{{ $education->degree }}</p>
                                <p class="text-sm text-gray-600">{{ $education->institution }}</p>
                                <p class="text-sm text-gray-500">{{ $education->year }}</p>
                            </div>
                        </div>

                        <!-- Images Gallery -->
                        @if($education->hasImages())
                            <div class="mt-3">
                                <p class="text-xs text-gray-500 mb-2">{{ __('tutors.certificate_images') }}:</p>
                                <div class="grid grid-cols-4 gap-2">
                                    @foreach($education->getAllImages() as $index => $imageName)
                                        @if(file_exists(public_path('uploads/education/' . $imageName)))
                                            <div class="relative group">
                                                <img src="{{ asset('uploads/education/' . $imageName) }}"
                                                     alt="Certificate {{ $index + 1 }}"
                                                     class="h-20 w-20 object-cover rounded border cursor-pointer hover:opacity-80 transition-opacity"
                                                     onclick="openImageModal('{{ asset('uploads/education/' . $imageName) }}', '{{ $education->degree }} - {{ __('common.image') }} {{ $index + 1 }}')" />
                                                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all rounded pointer-events-none">
                                                    <svg class="h-6 w-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="mt-3 flex items-center justify-center h-20 bg-gray-100 rounded border-2 border-dashed border-gray-300">
                                <div class="text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-xs text-gray-500 mt-1">{{ __('tutors.no_images_uploaded') }}</p>
                                </div>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
           <p class="text-sm text-gray-500">{{ __('tutors.no_education_records_yet') }}</p>
        @endif
    </div>
    @endif
</div>
