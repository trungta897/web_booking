@extends('layouts.admin')

@section('content')
    <!-- Header -->
    <div class="admin-page-header bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 px-6 py-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('admin.generate_reports') }}
        </h2>
    </div>

    <!-- Main Content -->
    <div class="px-6 py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.reports') }}</h3>

                    {{-- Placeholder for report generation form or display area --}}
                    <p class="text-gray-500">{{ __('admin.report_generation_tools') }}</p>

                    {{-- Example: A simple list or a form to select report type --}}
                    <div class="mt-6">
                        <h4 class="text-md font-semibold text-gray-700 mb-2">{{ __('admin.available_reports') }}</h4>
                        <ul class="list-disc list-inside space-y-1">
                            <li>{{ __('admin.user_activity_report') }}</li>
                            <li>{{ __('admin.booking_statistics') }}</li>
                            <li>{{ __('admin.revenue_report') }}</li>
                            <li>{{ __('admin.tutor_performance') }}</li>
                        </ul>
                    </div>

                    <div class="mt-8">
                        <form method="POST" action="#"> {{-- Replace # with actual report generation route --}}
                            @csrf
                            <label for="report_type" class="block text-sm font-medium text-gray-700">{{ __('admin.select_report_type') }}</label>
                            <select id="report_type" name="report_type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option>{{ __('admin.user_activity') }}</option>
                                <option>{{ __('admin.booking_statistics') }}</option>
                                <option>{{ __('admin.revenue_report') }}</option>
                            </select>

                            <div class="mt-4">
                                <x-primary-button>
                                    {{ __('admin.generate_report') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
