@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-red-600 mb-4">500</h1>
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Server Error</h2>
            <p class="text-gray-600 mb-6">Something went wrong on our end. Please try again later.</p>
            <a href="{{ url('/') }}" class="inline-block bg-blue-600 text-blue-600 px-6 py-2 rounded hover:bg-blue-700 transition">
                Return Home
            </a>
        </div>
    </div>
</div>
@endsection
