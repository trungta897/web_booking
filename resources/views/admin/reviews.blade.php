@extends('layouts.admin')

@section('content')
    <!-- Header -->
    <div class="admin-page-header bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 px-6 py-4">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reviews</h2>
    </div>

    <!-- Main Content -->
    <div class="px-6 py-4">
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>All Reviews</h3>
            <div class="flex items-center space-x-4">
                <form action="{{ route('admin.reviews') }}" method="GET" class="flex items-center space-x-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reviews..." class="search-input">
                    <select name="rating" class="admin-form-select" onchange="this.form.submit()">
                        <option value="">All Ratings</option>
                        @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                                {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                            </option>
                        @endfor
                    </select>
                </form>
            </div>
        </div>
        <div class="admin-card-body">
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Tutor</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                            <tr>
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <img class="h-8 w-8 rounded-full" src="{{ $review->student_photo }}" alt="{{ $review->student_name }}">
                                        <span>{{ $review->student_name }}</span>
                                    </div>
                                </td>
                                <td>{{ $review->tutor_name }}</td>
                                <td>
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                </td>
                                <td class="max-w-md">{{ $review->comment }}</td>
                                <td>{{ \Carbon\Carbon::parse($review->created_at)->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-500">No reviews found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $reviews->links() }}
            </div>
        </div>
    </div>
@endsection
