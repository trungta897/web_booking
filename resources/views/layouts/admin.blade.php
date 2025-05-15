<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} Admin</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <!-- Admin Navigation -->
            <nav class="bg-gray-800">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <a href="{{ route('admin.dashboard') }}" class="text-white font-bold text-xl">Admin Panel</a>
                            </div>
                            <div class="hidden md:block">
                                <div class="ml-10 flex items-baseline space-x-4">
                                    <a href="{{ route('admin.dashboard') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-gray-900' : 'hover:bg-gray-700' }}">Dashboard</a>
                                    <a href="{{ route('admin.tutors') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.tutors') ? 'bg-gray-900' : 'hover:bg-gray-700' }}">Tutors</a>
                                    <a href="{{ route('admin.students') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.students') ? 'bg-gray-900' : 'hover:bg-gray-700' }}">Students</a>
                                    <a href="{{ route('admin.bookings') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.bookings') ? 'bg-gray-900' : 'hover:bg-gray-700' }}">Bookings</a>
                                    <a href="{{ route('admin.subjects') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.subjects') ? 'bg-gray-900' : 'hover:bg-gray-700' }}">Subjects</a>
                                    <a href="{{ route('admin.reports') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.reports') ? 'bg-gray-900' : 'hover:bg-gray-700' }}">Reports</a>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="ml-4 flex items-center md:ml-6">
                                <!-- Profile dropdown -->
                                <div class="ml-3 relative">
                                    <div>
                                        <a href="{{ route('home') }}" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                                            View Site
                                        </a>
                                        <form method="POST" action="{{ route('logout') }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">
                                                Log Out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div class="md:hidden">
                    <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                        <a href="{{ route('admin.dashboard') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-gray-900' : 'hover:bg-gray-700' }}">Dashboard</a>
                        <a href="{{ route('admin.tutors') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.tutors') ? 'bg-gray-900' : 'hover:bg-gray-700' }}">Tutors</a>
                        <a href="{{ route('admin.students') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.students') ? 'bg-gray-900' : 'hover:bg-gray-700' }}">Students</a>
                        <a href="{{ route('admin.bookings') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.bookings') ? 'bg-gray-900' : 'hover:bg-gray-700' }}">Bookings</a>
                        <a href="{{ route('admin.subjects') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.subjects') ? 'bg-gray-900' : 'hover:bg-gray-700' }}">Subjects</a>
                        <a href="{{ route('admin.reports') }}" class="text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('admin.reports') ? 'bg-gray-900' : 'hover:bg-gray-700' }}">Reports</a>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 p-4 mb-4 mx-auto max-w-7xl mt-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 p-4 mb-4 mx-auto max-w-7xl mt-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                {{ session('error') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <!-- Admin Footer -->
            <footer class="bg-gray-800 text-white">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <p>© {{ date('Y') }} {{ config('app.name') }} Admin Panel. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
