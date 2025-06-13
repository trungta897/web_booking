<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Reports</h3>

                    {{-- Placeholder for report generation form or display area --}}
                    <p class="text-gray-500">Report generation tools will be available here.</p>

                    {{-- Example: A simple list or a form to select report type --}}
                    {{--
                    <div class="mt-6">
                        <h4 class="text-md font-semibold text-gray-700 mb-2">Available Reports:</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li>User Activity Report</li>
                            <li>Booking Statistics</li>
                            <li>Revenue Report</li>
                            <li>Tutor Performance</li>
                        </ul>
                    </div>

                    <div class="mt-8">
                        <form method="POST" action="#"> {{-- Replace # with actual report generation route --}}
                            @csrf
                            <label for="report_type" class="block text-sm font-medium text-gray-700">Select Report Type</label>
                            <select id="report_type" name="report_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option>User Activity</option>
                                <option>Booking Statistics</option>
                                <option>Revenue</option>
                            </select>

                            <div class="mt-4">
                                <x-primary-button>
                                    {{ __('Generate Report') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
