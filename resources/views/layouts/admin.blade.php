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
                            <form action="{{ route('admin.dashboard') /* Placeholder for admin.search */ }}" method="GET"> {{-- Search Form --}}
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                                    <i class="fas fa-search search-icon"></i>
                                </span>
                                <input type="search" name="q" placeholder="Search..." class="search-input">
                            </form>
                        </div>
                    </div>
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <button id="theme-toggle-button" class="top-bar-icon-btn">
                            <i id="theme-toggle-dark-icon" class="fas fa-moon hidden"></i>
                            <i id="theme-toggle-light-icon" class="fas fa-sun"></i>
                        </button>

                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="top-bar-icon-btn">
                                <i class="fas fa-bell"></i>
                                {{-- Add a badge for unread notifications later --}}
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-700 rounded-md shadow-lg overflow-hidden z-20" style="display: none;">
                                <div class="py-2 px-4 text-sm font-medium text-gray-700 dark:text-gray-200 border-b dark:border-gray-600">Notifications</div>
                                <div class="divide-y divide-gray-100 dark:divide-gray-600">
                                    {{-- Placeholder Notifications --}}
                                    <a href="#" class="block px-4 py-3 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <p class="font-medium">New user registered</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">2 hours ago</p>
                                    </a>
                                    <a href="#" class="block px-4 py-3 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">
                                        <p class="font-medium">Booking confirmed</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Yesterday</p>
                                    </a>
                                    <a href="#" class="block px-4 py-2 text-center text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">View all notifications</a>
                                </div>
                            </div>
                        </div>

                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="top-bar-icon-btn">
                                <i class="fas fa-th"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg overflow-hidden z-20" style="display: none;">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">Settings</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">User Profile</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600">Help Center</a>
                            </div>
                        </div>

                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                <span class="sr-only">Open user menu</span>
                                <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name ?? 'A').'&color=7F9CF5&background=EBF4FF' }}" alt="{{ Auth::user()->name ?? 'Admin' }}">
                            </button>
                            <div x-show="open" @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 origin-top-right rounded-md bg-white dark:bg-gray-700 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-20"
                                 role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1"
                                 style="display: none;">
                                <div class="px-4 py-3">
                                    <p class="text-sm text-gray-900 dark:text-white">Signed in as</p>
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ Auth::user()->name ?? 'Admin User' }}</p>
                                    <p class="truncate text-xs font-medium text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
                                </div>
                                <div class="py-1" role="none">
                                    <a href="{{ route('admin.profile.show') }}" class="text-gray-700 dark:text-gray-200 block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-600" role="menuitem" tabindex="-1" id="user-menu-item-0">View Profile</a>
                                    <a href="#" class="text-gray-700 dark:text-gray-200 block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-600" role="menuitem" tabindex="-1" id="user-menu-item-1">Account Settings</a>

                                    <form method="POST" action="{{ route('admin.profile.suspend') }}" onsubmit="return confirm('Are you sure you want to suspend your account? This action will log you out.');" role="none">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-red-600 dark:text-red-400 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-600" role="menuitem" tabindex="-1" id="user-menu-item-2">
                                            Suspend Account
                                        </button>
                                    </form>
                                </div>
                                <div class="py-1" role="none">
                                    <form method="POST" action="{{ route('logout') }}" role="none">
                                        @csrf
                                        <button type="submit" class="text-gray-700 dark:text-gray-200 block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600" role="menuitem" tabindex="-1" id="user-menu-item-3">
                                            Sign out
                                        </button>
                                    </form>
                                </div>
                            </div>
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
