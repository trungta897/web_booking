<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tutor Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Welcome, {{ Auth::user()->name }}!</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-medium text-blue-700">Upcoming Sessions</h4>
                            <p class="text-2xl font-bold text-blue-600">0</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="font-medium text-green-700">Total Students</h4>
                            <p class="text-2xl font-bold text-green-600">0</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h4 class="font-medium text-purple-700">Earnings</h4>
                            <p class="text-2xl font-bold text-purple-600">$0</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
