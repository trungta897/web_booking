<nav x-data="{ open: false }" class="bg-gray-900 border-b border-gray-700 dark:bg-gray-900 dark:border-gray-700 sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center">
                        <x-application-logo class="text-white" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')" class="nav-text-white hover:text-white transition-colors duration-300">
                        {{ __('common.home') }}
                    </x-nav-link>

                    <x-nav-link :href="route('tutors.index')" :active="request()->routeIs('tutors.*')" class="nav-text-white hover:text-white transition-colors duration-300">
                        {{ __('common.find_tutors') }}
                    </x-nav-link>

                    <x-nav-link :href="route('subjects.index')" :active="request()->routeIs('subjects.*')" class="nav-text-white hover:text-white transition-colors duration-300">
                        {{ __('common.subjects') }}
                    </x-nav-link>

                    <x-nav-link :href="route('how-it-works')" :active="request()->routeIs('how-it-works')" class="nav-text-white hover:text-white transition-colors duration-300">
                        {{ __('common.how_it_works') }}
                    </x-nav-link>

                    <x-nav-link :href="route('pricing')" :active="request()->routeIs('pricing')" class="nav-text-white hover:text-white transition-colors duration-300">
                        {{ __('common.pricing') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- Language Switcher -->
                <div class="mr-4">
                    <x-language-switcher />
                </div>

                @auth
                    <!-- Notifications -->
                    <div class="relative mr-4">
                        <a href="{{ route('notifications.index') }}" class="p-2 nav-text-white hover:text-white transition-colors duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if(Auth::user()->unreadNotifications && Auth::user()->unreadNotifications->count() > 0)
                            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500"></span>
                            @endif
                        </a>
                    </div>

                    <!-- Messages -->
                    <div class="relative mr-4">
                        <a href="{{ route('messages.index') }}" class="p-2 nav-text-white hover:text-white transition-colors duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            @if(Auth::user()->unreadMessages && Auth::user()->unreadMessages->count() > 0)
                            <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500"></span>
                            @endif
                        </a>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="ml-3 relative">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-gray-800 hover:text-white focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->name }}</div>

                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')" class="flex items-center link-secondary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ __('common.profile') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('favorites.index')" class="flex items-center link-secondary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    {{ __('common.favorites') }}
                                </x-dropdown-link>

                                <x-dropdown-link :href="route('bookings.index')" class="flex items-center link-secondary">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ __('common.bookings') }}
                                </x-dropdown-link>

                                <!-- Authentication -->
                                                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();"
                        class="flex items-center text-error-600 hover:text-error-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        {{ __('common.logout') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                @else
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-white text-white rounded-full hover:bg-gray-800 transition-colors duration-300">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            {{ __('common.login') }}
                        </a>
                        <a href="{{ route('register') }}" class="btn-primary px-6 py-2 rounded-full shadow-sm hover:shadow-md">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            {{ __('common.register') }}
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gray-200 hover:bg-gray-700 focus:outline-none focus:bg-gray-700 focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <!-- Language Switcher for Mobile -->
            <div class="px-4 pb-2">
                <div class="flex space-x-2">
                    <a href="{{ route('language.switch', 'vi') }}"
                       class="flex items-center px-3 py-1 text-sm rounded {{ app()->getLocale() == 'vi' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:text-white' }}">
                        <img src="https://flagcdn.com/w20/vn.png" class="w-4 h-3 mr-2" alt="Vietnamese">
                        Tiếng Việt
                    </a>
                    <a href="{{ route('language.switch', 'en') }}"
                       class="flex items-center px-3 py-1 text-sm rounded {{ app()->getLocale() == 'en' ? 'bg-gray-700 text-white' : 'text-gray-300 hover:text-white' }}">
                        <img src="https://flagcdn.com/w20/us.png" class="w-4 h-3 mr-2" alt="English">
                        English
                    </a>
                </div>
            </div>

            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')" class="nav-text-white hover:text-white hover:bg-gray-700 transition-colors duration-300">
                {{ __('common.home') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('tutors.index')" :active="request()->routeIs('tutors.*')" class="nav-text-white hover:text-white hover:bg-gray-700 transition-colors duration-300">
                {{ __('common.find_tutors') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('subjects.index')" :active="request()->routeIs('subjects.*')" class="nav-text-white hover:text-white hover:bg-gray-700 transition-colors duration-300">
                {{ __('common.subjects') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('how-it-works')" :active="request()->routeIs('how-it-works')" class="nav-text-white hover:text-white hover:bg-gray-700 transition-colors duration-300">
                {{ __('common.how_it_works') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('pricing')" :active="request()->routeIs('pricing')" class="nav-text-white hover:text-white hover:bg-gray-700 transition-colors duration-300">
                {{ __('common.pricing') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        @auth
            <div class="pt-4 pb-1 border-t border-gray-700">
                <div class="px-4">
                    <div class="font-medium text-base text-white">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-300">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')" class="nav-text-white hover:text-white hover:bg-gray-700 transition-colors duration-300">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ __('common.profile') }}
                        </div>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('favorites.index')" class="nav-text-white hover:text-white hover:bg-gray-700 transition-colors duration-300">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                            {{ __('common.favorites') }}
                        </div>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('bookings.index')" class="nav-text-white hover:text-white hover:bg-gray-700 transition-colors duration-300">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ __('common.bookings') }}
                        </div>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('messages.index')" class="nav-text-white hover:text-white hover:bg-gray-700 transition-colors duration-300">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ __('common.messages') }}
                            @if(Auth::user()->unreadMessages && Auth::user()->unreadMessages->count() > 0)
                                <span class="ml-1 inline-block h-2 w-2 rounded-full bg-red-500"></span>
                            @endif
                        </div>
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('notifications.index')" class="nav-text-white hover:text-white hover:bg-gray-700 transition-colors duration-300">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            {{ __('common.notifications') }}
                            @if(Auth::user()->unreadNotifications && Auth::user()->unreadNotifications->count() > 0)
                                <span class="ml-1 inline-block h-2 w-2 rounded-full bg-red-500"></span>
                            @endif
                        </div>
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();"
                                class="nav-text-white hover:text-red-700 hover:bg-red-50 transition-colors duration-300">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                {{ __('common.logout') }}
                            </div>
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>
