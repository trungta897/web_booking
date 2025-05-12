<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')

                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="phone_number" :value="__('Phone Number')" />
                            <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full" :value="old('phone_number', $user->phone_number)" autocomplete="tel" />
                            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="address" :value="__('Address')" />
                            <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address', $user->address)" autocomplete="street-address" />
                            <x-input-error class="mt-2" :messages="$errors->get('address')" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="avatar" :value="__('Avatar')" />
                            <input id="avatar" name="avatar" type="file" class="mt-1 block w-full" accept="image/*" />
                            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
                        </div>

                        <div class="flex items-center gap-4 mt-4">
                            <x-primary-button>{{ __('Save') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <form method="post" action="{{ route('profile.destroy') }}">
                        @csrf
                        @method('delete')
                        <x-danger-button>{{ __('Delete Account') }}</x-danger-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
