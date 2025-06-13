<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Tutors') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tutor List</h3>

                    <!-- Search and Filters (Optional - can be added later) -->
                    {{--
                    <div class="mb-6">
                        <form method="GET" action="{{ route('admin.tutors') }}">
                            <div class="flex space-x-4">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tutors..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                    Search
                                </button>
                            </div>
                        </form>
                    </div>
                    --}}

                    @if($tutors->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subjects</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($tutors as $tutorUser)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $tutorUser->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-500">{{ $tutorUser->email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if($tutorUser->tutor && $tutorUser->tutor->subjects->count() > 0)
                                                    {{ $tutorUser->tutor->subjects->pluck('name')->implode(', ') }}
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span @class([
                                                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                    'bg-green-100 text-green-800' => $tutorUser->account_status === 'active',
                                                    'bg-yellow-100 text-yellow-800' => $tutorUser->account_status === 'suspended',
                                                    'bg-red-100 text-red-800' => $tutorUser->account_status === 'banned',
                                                    'dark:bg-green-700 dark:text-green-100' => $tutorUser->account_status === 'active',
                                                    'dark:bg-yellow-700 dark:text-yellow-100' => $tutorUser->account_status === 'suspended',
                                                    'dark:bg-red-700 dark:text-red-100' => $tutorUser->account_status === 'banned',
                                                ])>
                                                    {{ ucfirst($tutorUser->account_status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $tutorUser->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.tutors.show', $tutorUser) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                                                <form method="POST" action="{{ route('admin.tutors.suspend', $tutorUser) }}" class="inline-block" onsubmit="return confirm('Are you sure you want to {{ $tutorUser->account_status === 'suspended' ? 'reinstate' : 'suspend' }} this tutor?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="{{ $tutorUser->account_status === 'suspended' ? 'text-green-600 hover:text-green-900' : 'text-red-600 hover:text-red-900' }}">
                                                        {{ $tutorUser->account_status === 'suspended' ? 'Reinstate' : 'Suspend' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $tutors->links() }}
                        </div>
                    @else
                        <p class="text-gray-500">No tutors found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
