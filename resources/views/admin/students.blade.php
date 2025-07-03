@extends('layouts.admin')

@section('content')
    <!-- Header -->
    <div class="admin-page-header bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 px-6 py-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('admin.manage_students') }}
        </h2>
    </div>

    <!-- Main Content -->
    <div class="px-6 py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('admin.student_list') }}</h3>

                    <!-- Search and Filters (Optional - can be added later) -->
                    {{--
                    <div class="mb-6">
                        <form method="GET" action="{{ route('admin.students') }}">
                            <div class="flex space-x-4">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search students..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Search
                                </button>
                            </div>
                        </form>
                    </div>
                    --}}

                    @if($students->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.name') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.email') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.bookings_count') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.status') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.joined') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('admin.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($students as $studentUser)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $studentUser->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $studentUser->email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $studentUser->studentBookings->count() }} {{-- Assuming studentBookings relationship exists --}}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span @class([
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    'bg-green-100 text-green-800' => $studentUser->account_status === 'active',
                                                    'bg-yellow-100 text-yellow-800' => $studentUser->account_status === 'suspended',
                                                    'bg-red-100 text-red-800' => $studentUser->account_status === 'banned',
                                                    'dark:bg-green-700 dark:text-green-100' => $studentUser->account_status === 'active',
                                                    'dark:bg-yellow-700 dark:text-yellow-100' => $studentUser->account_status === 'suspended',
                                                    'dark:bg-red-700 dark:text-red-100' => $studentUser->account_status === 'banned',
                                                ])>
                                                    {{ __('admin.' . $studentUser->account_status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $studentUser->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.students.show', $studentUser) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">{{ __('admin.view') }}</a>
                                                <form method="POST" action="{{ route('admin.students.suspend', $studentUser) }}" class="inline-block" onsubmit="return confirm('{{ $studentUser->account_status === 'suspended' ? __('admin.confirm_reinstate') : __('admin.confirm_suspend') }}');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="{{ $studentUser->account_status === 'suspended' ? 'text-green-600 hover:text-green-900' : 'text-red-600 hover:text-red-900' }}">
                                                        {{ $studentUser->account_status === 'suspended' ? __('admin.reinstate') : __('admin.suspend') }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $students->links() }}
                        </div>
                    @else
                        <p class="text-gray-500">{{ __('admin.no_students_found') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
