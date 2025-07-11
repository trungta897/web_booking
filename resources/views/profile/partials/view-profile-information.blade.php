<div class="max-w-4xl">
    <div class="flex justify-between items-start mb-6">
        <!-- Titles and Edit Button -->
        <div>
            <h3 class="text-lg font-medium text-gray-900">Profile Information</h3>
            <p class="mt-1 text-sm text-gray-600">
                View or edit your profile details
            </p>
        </div>
        <button @click="showEditForm = !showEditForm" class="ml-4 flex-shrink-0 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            <span x-text="showEditForm ? 'Cancel' : 'Edit Profile'"></span>
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
                    <li class="p-4 bg-gray-50 rounded-lg flex items-start gap-4">
                        @if($education->image && file_exists(public_path('uploads/education/' . $education->image)))
                             <a href="{{ asset('uploads/education/' . $education->image) }}" target="_blank" class="flex-shrink-0">
                                <img src="{{ asset('uploads/education/' . $education->image) }}" alt="Certificate" class="h-16 w-16 object-cover rounded-md border hover:opacity-80 transition-opacity">
                            </a>
                        @else
                             <div class="flex-shrink-0 h-16 w-16 bg-gray-200 rounded-md flex items-center justify-center">
                                <svg class="h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                        @endif
                        <div class="flex-grow">
                            <p class="font-semibold text-gray-800">{{ $education->degree }}</p>
                            <p class="text-sm text-gray-600">{{ $education->institution }}</p>
                            <p class="text-sm text-gray-500">{{ $education->year }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
           <p class="text-sm text-gray-500">{{ __('tutors.no_education_records_yet') }}</p>
        @endif
    </div>
    @endif
</div>
