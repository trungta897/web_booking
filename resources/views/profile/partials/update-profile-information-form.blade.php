<h3 class="text-lg font-medium text-gray-900 mb-6">{{ __('profile.profile_information') }}</h3>

@if (session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

<!-- Display submission errors -->
@if ($errors->any())
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">{{ __('profile.there_were_errors_with_your_submission') }}: {{ count($errors->all()) }}</h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif


<div>
    <x-input-label for="name" :value="__('common.name')" />
    @php
        $nameValue = old('name', $user->name);
        if (is_array($nameValue)) { $nameValue = $user->name ?? ''; }
    @endphp
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="$nameValue" required autofocus autocomplete="name" />
    <x-input-error class="mt-2" :messages="$errors->get('name')" />
</div>

<div class="mt-4">
    <x-input-label for="email" :value="__('common.email')" />
    @php
        $emailValue = old('email', $user->email);
        if (is_array($emailValue)) { $emailValue = $user->email ?? ''; }
    @endphp
    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="$emailValue" required autocomplete="username" />
    <x-input-error class="mt-2" :messages="$errors->get('email')" />
</div>

<div class="mt-4">
    <x-input-label for="avatar" :value="__('common.avatar')" />
    @if ($user->avatar)
        <div class="mt-2">
            <p class="text-sm font-medium text-gray-700 mb-2">{{ __('profile.current_avatar') }}:</p>
            <img src="{{ asset('uploads/avatars/' . $user->avatar) }}" alt="Current Avatar" class="h-20 w-20 object-cover rounded-full border-2 border-gray-300">
        </div>
    @endif
    <input id="avatar" name="avatar" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" accept="image/*" />
    <p class="mt-1 text-sm text-gray-500">{{ __('profile.max_size') }}: 5MB</p>
    <x-input-error class="mt-2" :messages="$errors->get('avatar')" />

    <!-- Avatar file info display -->
    <div id="avatar-info" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-sm" style="display: none;">
        <div class="text-blue-800">
            <strong>{{ __('profile.selected_file') }}:</strong> <span id="avatar-filename"></span><br>
            <strong>{{ __('profile.file_size') }}:</strong> <span id="avatar-size"></span><br>
            <strong>{{ __('profile.file_type') }}:</strong> <span id="avatar-type"></span>
        </div>
    </div>

    <!-- Avatar preview -->
    <div id="avatar-preview" class="mt-2" style="display: none;">
        <p class="text-sm font-medium text-gray-700 mb-2">{{ __('profile.preview') }}:</p>
        <img src="" alt="Avatar Preview" class="h-20 w-20 object-cover rounded-full border-2 border-indigo-300">
    </div>
</div>

{{-- Removed save button - will be handled by main form --}}
