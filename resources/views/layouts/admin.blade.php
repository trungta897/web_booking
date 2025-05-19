<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Admin Dashboard - {{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <!-- Font Awesome for Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased admin-body">
        <div x-data="{ sidebarOpen: true }" class="flex h-screen">
            <!-- Sidebar -->
            <aside
                class="admin-sidebar w-64 py-4 px-2 space-y-4 fixed inset-y-0 left-0 transform transition-transform duration-300 ease-in-out z-30 overflow-y-auto"
                :class="{'translate-x-0': sidebarOpen, '-translate-x-full md:translate-x-0': !sidebarOpen}"
                aria-label="Sidebar">

                <div class="admin-sidebar-header flex items-center justify-between">
                    <a href="{{ route('admin.dashboard') }}" class="app-name-admin flex items-center px-2">
                        <!-- Laravel Logo -->
                        <svg class="w-7 h-7 mr-2" viewBox="0 0 50 52" xmlns="http://www.w3.org/2000/svg">
                            <path d="M25.36 0L0 14.64v21.72L25.36 51V36.36L10.72 27V18.28L25.36 9.32V0zm0 27v12.92L40 32.48v-9.04L25.36 14.4V27zM50 14.64L25.36 0v9.32L40 18.28V27L25.36 36.36V51L50 36.36V14.64z" fill="#FF2D20"/>
                        </svg>
                        <span class="font-bold text-xl">Admin Dashboard</span>
                    </a>
                    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-500 hover:text-gray-700 focus:outline-none mr-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <nav class="space-y-1 flex-1">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.tutors') }}" class="nav-link {{ request()->routeIs('admin.tutors') ? 'active' : '' }}">
                        <i class="fas fa-chalkboard-teacher"></i> Tutors
                    </a>
                    <a href="{{ route('admin.students') }}" class="nav-link {{ request()->routeIs('admin.students') ? 'active' : '' }}">
                        <i class="fas fa-user-graduate"></i> Students
                    </a>
                    <a href="{{ route('admin.bookings') }}" class="nav-link {{ request()->routeIs('admin.bookings') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check"></i> Bookings
                    </a>
                    <a href="{{ route('admin.subjects') }}" class="nav-link {{ request()->routeIs('admin.subjects*') ? 'active' : '' }}">
                        <i class="fas fa-book"></i> Subjects
                    </a>
                    <a href="{{ route('admin.reports') }}" class="nav-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </nav>

                <div class="sidebar-section-separator">
                    <a href="{{ route('home') }}" target="_blank" class="nav-link">
                        <i class="fas fa-external-link-alt"></i> View Site
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                        @csrf
                        <button type="submit" class="nav-link w-full text-left">
                            <i class="fas fa-sign-out-alt"></i> Log Out
                        </button>
                    </form>
                </div>
            </aside>

            <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300 ease-in-out" :class="{'md:ml-64': sidebarOpen}">
                <header class="admin-top-bar">
                    <div class="flex items-center">
                        <button @click="sidebarOpen = !sidebarOpen" class="top-bar-icon-btn md:hidden mr-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <div class="relative hidden md:block top-bar-search-wrapper">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                <i class="fas fa-search search-icon"></i>
                            </span>
                            <input type="text" placeholder="Search..." class="search-input">
                        </div>
                    </div>
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <button class="top-bar-icon-btn">
                            <i class="fas fa-sun"></i>
                        </button>
                        <button class="top-bar-icon-btn">
                            <i class="fas fa-bell"></i>
                        </button>
                        <button class="top-bar-icon-btn">
                            <i class="fas fa-th"></i>
                        </button>
                        <div class="ml-2 relative">
                             <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name ?? 'A').'&color=7F9CF5&background=EBF4FF' }}" alt="{{ Auth::user()->name ?? 'Admin' }}">
                            </button>
                        </div>
                    </div>
                </header>

                <div class="flex-1 overflow-y-auto admin-main-content">
                    @if (isset($header))
                        <div class="admin-page-header bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700 px-6 py-4">
                            {{ $header }}
                        </div>
                    @endif

                    <div class="px-6 py-4 space-y-4">
                        @if(session('success'))
                            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                                <p class="font-bold">Success</p>
                                <p>{{ session('success') }}</p>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                                <p class="font-bold">Error</p>
                                <p>{{ session('error') }}</p>
                            </div>
                        @endif
                    </div>

                    <main class="px-6 pb-6">
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js"></script>
    </body>
</html>
