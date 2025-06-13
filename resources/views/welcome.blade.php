<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Online Tutoring Platform</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
            @vite(['resources/css/app.css', 'resources/css/custom.css', 'resources/js/app.js'])

            <style>
            :root {
                --primary: #4f46e5;
                --primary-dark: #4338ca;
                --secondary: #8b5cf6;
                --accent: #f97316;
                --dark: #1e293b;
                --light: #f8fafc;
                --success: #10b981;
                --warning: #f59e0b;
                --danger: #ef4444;
                --text-primary: #111827;  /* Darker than default */
                --text-secondary: #1f2937; /* Darker than default */
                --text-light: #ffffff;     /* Pure white for contrast */
            }

            /* Modern Gradients */
            .gradient-primary {
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            }
            .gradient-dark {
                background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            }
            .gradient-light {
                background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            }
            .gradient-accent {
                background: linear-gradient(135deg, var(--accent) 0%, #f97316 100%);
            }

            /* Smooth Transitions */
            .smooth-transition {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .hover-scale {
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .hover-scale:hover {
                transform: scale(1.05);
            }

            /* Modern Cards */
            .card-hover {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                background: white;
                border-radius: 1rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }
            .card-hover:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            /* Modern Buttons */
            .btn-primary {
                background: var(--primary);
                color: var(--text-light);
                padding: 0.75rem 1.5rem;
                border-radius: 0.75rem;
                font-weight: 600;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
            }
            .btn-primary:hover {
                background: var(--primary-dark);
                transform: translateY(-2px);
                box-shadow: 0 6px 8px -1px rgba(79, 70, 229, 0.3);
            }

            /* Modern Inputs */
            .modern-input {
                border: 2px solid #e2e8f0;
                border-radius: 0.75rem;
                padding: 0.75rem 1rem;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .modern-input:focus {
                border-color: var(--primary);
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            }

            /* Section Styles */
            .section {
                padding: 5rem 0;
                position: relative;
            }
            .section::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 1px;
                background: linear-gradient(90deg, transparent, rgba(0,0,0,0.1), transparent);
            }

            /* Container */
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 1.5rem;
            }

            /* Stats Card */
            .stats-card {
                background: white;
                border-radius: 1rem;
                padding: 1.5rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .stats-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            }

            /* Search Box */
            .search-box {
                background: white;
                border-radius: 1rem;
                padding: 1.5rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                margin-top: -3rem;
                position: relative;
                z-index: 10;
            }

            /* Subject Tags */
            .subject-tag {
                display: inline-block;
                padding: 0.5rem 1rem;
                background: var(--light);
                color: var(--text-primary);
                border-radius: 2rem;
                margin: 0.25rem;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                font-weight: 500;
            }
            .subject-tag:hover {
                background: var(--primary);
                color: var(--text-light);
                transform: translateY(-2px);
            }

            /* Text Gradients */
            .text-gradient {
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            /* Floating Elements */
            .floating {
                animation: float 6s ease-in-out infinite;
            }
            @keyframes float {
                0% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
                100% { transform: translateY(0px); }
            }

            /* Modern Footer */
            .footer-link {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .footer-link:hover {
                color: white;
                transform: translateX(5px);
            }

            /* Social Icons */
            .social-icon {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .social-icon:hover {
                transform: translateY(-3px);
                color: white;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .section {
                    padding: 3rem 0;
                }
                .search-box {
                    margin-top: -2rem;
                }
            }
            </style>
    </head>
    <body class="antialiased bg-gradient-light">
        <div class="min-h-screen">
            <!-- Navigation -->
            @include('layouts.navigation')

            <!-- Hero Section -->
            <div id="hero-section" class="relative overflow-hidden bg-white">
                <!-- Decorative background elements -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute -top-24 -right-24 w-96 h-96 bg-primary/5 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-secondary/5 rounded-full blur-3xl"></div>
                </div>

                <div class="container relative">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center py-20">
                        <div class="hero-content space-y-8">
                            <div class="inline-flex items-center px-4 py-2 rounded-full bg-primary/10 text-primary text-sm font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Start Learning Today
                            </div>
                            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                                Find Your Perfect <span class="text-gradient">Tutor</span>
                            </h1>
                            <p class="text-lg leading-8 text-gray-600 max-w-2xl">
                                Get help with your studies from qualified tutors. Simple, easy, and effective learning experience tailored to your needs.
                            </p>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 sm:gap-6">
                    @auth
                                    <a href="@if(Auth::user()->role === 'admin') {{ route('admin.dashboard') }} @elseif(Auth::user()->role === 'tutor') {{ route('tutor.dashboard') }} @else {{ route('profile.edit') }} @endif" class="btn-primary w-full sm:w-auto text-center">
                                        Go to Dashboard
                                    </a>
                    @else
                                    <a href="{{ route('register') }}" class="btn-primary w-full sm:w-auto text-center">
                                        Get Started
                                    </a>
                                    <a href="{{ route('login') }}" class="text-lg font-semibold text-primary hover:text-primary-dark smooth-transition flex items-center">
                                        Log in <span aria-hidden="true" class="ml-2">→</span>
                                    </a>
                    @endauth
                            </div>
                            <div class="flex items-center gap-8 pt-4">
                                <div class="flex items-center">
                                    <div class="flex -space-x-2">
                                        <img class="w-8 h-8 rounded-full border-2 border-white" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="User">
                                        <img class="w-8 h-8 rounded-full border-2 border-white" src="https://images.unsplash.com/photo-1519244703995-f4e0f30006d5?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="User">
                                        <img class="w-8 h-8 rounded-full border-2 border-white" src="https://images.unsplash.com/photo-1517841905240-472988babdf9?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="User">
                                    </div>
                                    <span class="ml-3 text-sm text-gray-600">Join 1000+ students</span>
                                </div>
                                <div class="flex items-center">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>
                                    <span class="ml-2 text-sm text-gray-600">4.9/5 Rating</span>
                                </div>
                            </div>
                        </div>
                        <div class="hero-image relative lg:block">
                            <div class="relative">
                                <div class="absolute -inset-4 bg-gradient-to-r from-primary/20 to-secondary/20 rounded-2xl blur-2xl"></div>
                                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1471&q=80"
                                     alt="Students learning"
                                     class="relative rounded-2xl shadow-2xl hover-scale">
                            </div>
                            <!-- Floating elements -->
                            <div class="absolute -bottom-6 -left-6 bg-white p-4 rounded-xl shadow-lg floating">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Expert Tutors</p>
                                        <p class="text-xs text-gray-500">1000+ Available</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Box -->
            <div class="container">
                <div class="search-box">
                    <form action="{{ route('tutors.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                                <select name="subject" class="modern-input w-full">
                                    <option value="">All Subjects</option>
                                    @foreach($popularSubjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Experience</label>
                                <select name="experience" class="modern-input w-full">
                                    <option value="">Any Experience</option>
                                    <option value="1">1+ Years</option>
                                    <option value="3">3+ Years</option>
                                    <option value="5">5+ Years</option>
                                    <option value="10">10+ Years</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                                <select name="price_range" class="modern-input w-full">
                                    <option value="">Any Price</option>
                                    <option value="0-25">$0 - $25/hr</option>
                                    <option value="26-50">$26 - $50/hr</option>
                                    <option value="51-100">$51 - $100/hr</option>
                                    <option value="101+">$101+/hr</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="btn-primary w-full">
                                    Search Tutors
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="section bg-white">
                <div class="container">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div class="stats-card text-center">
                            <div class="text-3xl font-bold text-primary mb-2">1000+</div>
                            <div class="text-gray-600">Active Tutors</div>
                        </div>
                        <div class="stats-card text-center">
                            <div class="text-3xl font-bold text-secondary mb-2">5000+</div>
                            <div class="text-gray-600">Happy Students</div>
                        </div>
                        <div class="stats-card text-center">
                            <div class="text-3xl font-bold text-success mb-2">50+</div>
                            <div class="text-gray-600">Subjects</div>
                        </div>
                        <div class="stats-card text-center">
                            <div class="text-3xl font-bold text-warning mb-2">4.9/5</div>
                            <div class="text-gray-600">Average Rating</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How It Works Section -->
            <div class="section bg-gradient-light">
                <div class="container">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                            How It <span class="text-gradient">Works</span>
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="card-hover p-8">
                            <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full gradient-primary">
                                <span class="text-4xl font-bold text-white">1</span>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">
                                Find a Tutor
                            </h3>
                            <p class="text-gray-600">
                                Browse through our list of qualified tutors and find the perfect match for your needs.
                            </p>
                        </div>
                        <div class="card-hover p-8">
                            <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full gradient-primary">
                                <span class="text-4xl font-bold text-white">2</span>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">
                                Book a Session
                            </h3>
                            <p class="text-gray-600">
                                Choose a time that works for you and book your tutoring session.
                            </p>
                        </div>
                        <div class="card-hover p-8">
                            <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full gradient-primary">
                                <span class="text-4xl font-bold text-white">3</span>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">
                                Start Learning
                            </h3>
                            <p class="text-gray-600">
                                Meet with your tutor online and begin your learning journey.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Popular Subjects / Browse Subjects Section -->
            <div class="section bg-white">
                <div class="container">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                            Browse Subjects
                        </h2>
                        <p class="mt-4 text-lg text-gray-600">Find the perfect tutor for your subject</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @forelse($popularSubjects as $subject)
                            <a href="{{ route('tutors.index', ['subject' => $subject->id]) }}" class="block card-hover p-6 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $subject->name }}</h3>
                                    <span class="text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $subject->tutors_count }} {{ Str::plural('Tutor', $subject->tutors_count) }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-4 min-h-[40px]">
                                    {{-- Ensure $subject->description is available or provide a good fallback --}}
                                    {{ Str::limit($subject->description ?? 'Explore ' . $subject->name . ' courses and find expert tutors.', 100) }}
                                </p>
                                <div class="flex items-center text-sm text-indigo-600 hover:text-indigo-800">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v11.494m0 0A7.027 7.027 0 0112 17.747a7.027 7.027 0 010-11.494M4 19.5V7.5a3 3 0 013-3h10a3 3 0 013 3v12a3 3 0 01-3 3H7a3 3 0 01-3-3z"></path></svg>
                                    View Tutors for {{ $subject->name }}
                                </div>
                            </a>
                        @empty
                            <p class="text-gray-600 col-span-full text-center">No subjects available at the moment.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Featured Tutors -->
            <div class="section bg-gradient-light">
                <div class="container">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                            Featured <span class="text-gradient">Tutors</span>
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($featuredTutors as $tutor)
                            <div class="card-hover p-6">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <img class="h-16 w-16 rounded-full" src="{{ $tutor->user->avatar ? asset('storage/' . $tutor->user->avatar) : asset('images/default-avatar.png') }}" alt="{{ $tutor->user->name }}">
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $tutor->user->name }}</h3>
                                        <div class="flex items-center">
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="h-4 w-4 {{ $i <= $tutor->reviews_avg_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                            </div>
                                            <span class="ml-2 text-sm text-gray-600">({{ $tutor->reviews_count }} reviews)</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <div class="flex flex-wrap mt-2">
                                        @foreach($tutor->subjects->take(3) as $subject)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 mr-2 mb-2">
                                                {{ $subject->name }}
                                </span>
                                        @endforeach
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 mb-4">{{ Str::limit($tutor->bio, 100) }}</p>
                                <div class="flex items-center justify-between mt-auto">
                                    <div class="text-lg font-medium text-gray-900">${{ number_format($tutor->hourly_rate, 2) }}/hr</div>
                                    <a href="{{ route('tutors.show', $tutor) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-10">
                        <a href="{{ route('tutors.index') }}" class="btn-primary inline-block">
                            Browse All Tutors
                        </a>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="section bg-gradient-light">
                <div class="container">
                    <div class="text-center mb-16">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                            Why Choose <span class="text-gradient">Us</span>
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="card-hover p-8">
                            <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full gradient-primary">
                                <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">
                                Easy Booking
                            </h3>
                            <p class="text-gray-600">
                                Book sessions in just a few clicks. No complicated process.
                            </p>
                        </div>
                        <div class="card-hover p-8">
                            <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full gradient-primary">
                                <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">
                                Safe & Secure
                            </h3>
                            <p class="text-gray-600">
                                Your safety is our priority. All tutors are verified.
                            </p>
                        </div>
                        <div class="card-hover p-8">
                            <div class="mb-6 flex h-20 w-20 items-center justify-center rounded-full gradient-primary">
                                <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">
                                Expert Tutors
                            </h3>
                            <p class="text-gray-600">
                                Learn from qualified tutors who are experts in their fields.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="gradient-dark text-blue-600">
                <div class="container py-12">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div>
                            <h3 class="text-lg font-semibold text-white mb-4">About Us</h3>
                            <p class="text-gray-300">
                                We connect students with qualified tutors for personalized learning experiences.
                            </p>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white mb-4">Quick Links</h3>
                            <ul class="space-y-2">
                                <li><a href="{{ route('home') }}" class="footer-link text-gray-300">Home</a></li>
                                <li><a href="{{ route('tutors.index') }}" class="footer-link text-gray-300">Find a Tutor</a></li>
                                <li><a href="{{ route('register') }}?role=tutor" class="footer-link text-gray-300">Become a Tutor</a></li>
                                <li><a href="#contact-us" class="footer-link text-gray-300">Contact Us</a></li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white mb-4">Subjects</h3>
                            <ul class="space-y-2">
                                <li><a href="{{ route('tutors.index', ['subject_name' => 'Mathematics']) }}" class="footer-link text-gray-300">Mathematics</a></li>
                                <li><a href="{{ route('tutors.index', ['subject_name' => 'Science']) }}" class="footer-link text-gray-300">Science</a></li>
                                <li><a href="{{ route('tutors.index', ['subject_name' => 'Languages']) }}" class="footer-link text-gray-300">Languages</a></li>
                                <li><a href="{{ route('tutors.index') }}" class="footer-link text-gray-300">More Subjects</a></li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white mb-4">Connect With Us</h3>
                            <div class="flex space-x-4">
                                <a href="#" class="social-icon text-gray-300">
                                    <span class="sr-only">Facebook</span>
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                <a href="#" class="social-icon text-gray-300">
                                    <span class="sr-only">Instagram</span>
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                <a href="#" class="social-icon text-gray-300">
                                    <span class="sr-only">Twitter</span>
                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 pt-8 border-t border-gray-700 text-center">
                        <p class="text-gray-300">
                            &copy; {{ date('Y') }} TutorHub. All rights reserved.
                        </p>
                </div>
                </div>
            </footer>
        </div>
    </body>
</html>
