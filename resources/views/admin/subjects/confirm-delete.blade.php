<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Confirm Delete Subject: ') }} {{ $subject->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 text-center">
                    <svg class="mx-auto mb-4 text-gray-400 dark:text-gray-500 w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete the subject "{{ $subject->name }}"?</h3>
                    <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">This will also remove this subject from all tutor profiles and may affect existing bookings. This action cannot be undone.</p>

                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <x-danger-button type="submit">
                            {{ __('Yes, delete it') }}
                        </x-danger-button>
                    </form>

                    <a href="{{ route('admin.subjects') }}" class="ml-2 inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                        {{ __('No, cancel') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
