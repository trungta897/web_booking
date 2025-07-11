<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('common.profile') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ showEditForm: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Flash success message - chỉ hiển thị một lần -->
            @if (session('success'))
                <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                    {{ session('success') }}
                </div>
                @php session()->forget('success'); @endphp
            @endif

            <!-- Combined Profile and Education Read-Only View -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('profile.partials.view-profile-information', ['user' => $user, 'tutor' => $tutor])
            </div>

            <!-- Edit Profile Forms Container -->
            <div x-show="showEditForm" x-collapse>
                <div class="space-y-6">
                    <!-- Form 1: Update Profile -->
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('patch')
                            @include('profile.partials.update-profile-information-form')
                             @if ($tutor)
                                @include('tutors.profile.partials.update-tutor-profile-form', ['subjects' => $subjects, 'tutor' => $tutor])
                             @endif
                            <div class="flex items-center gap-4 mt-6">
                                <x-primary-button>{{ __('profile.save_changes') }}</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <!-- Form 2: Update Education -->
                    @if ($tutor)
                        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                             <form method="post" action="{{ route('profile.update-education') }}" enctype="multipart/form-data">
                                @csrf
                                @include('tutors.profile.partials.update-education-form', ['tutor' => $tutor])
                                <div class="flex items-center gap-4 mt-6">
                                    <x-primary-button>{{ __('tutors.save_education') }}</x-primary-button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <!-- Form 3: Update Password -->
                     <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        @include('profile.partials.update-password-form')
                    </div>

                    <!-- Form 4: Delete Account -->
                    <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Image Modal and scripts -->
    @include('profile.partials.image-modal-and-scripts')
</x-app-layout>
