<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900">{{ __('how_it_works.title') }}</h1>
                <p class="mt-4 text-lg text-gray-600">{{ __('how_it_works.subtitle') }}</p>
            </div>

            <!-- Steps -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                @foreach(__('how_it_works.steps') as $index => $step)
                <div class="bg-white p-8 rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <span class="text-2xl font-bold text-primary">{{ $index + 1 }}</span>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 text-center mb-4">{{ $step['title'] }}</h3>
                    <p class="text-gray-600 text-center">{{ $step['description'] }}</p>
                </div>
                @endforeach
            </div>

            <!-- Features -->
            <div class="bg-white rounded-lg shadow-sm p-8 mb-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">{{ __('how_it_works.features.title') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach(__('how_it_works.features.items') as $feature)
                    <div class="text-center">
                        <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mb-4 mx-auto">
                            @if($loop->index == 0)
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            @elseif($loop->index == 1)
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            @elseif($loop->index == 2)
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            @else
                            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                            @endif
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $feature['title'] }}</h3>
                        <p class="text-gray-600">{{ $feature['description'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- CTA Section -->
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('how_it_works.cta.title') }}</h2>
                <p class="text-gray-600 mb-8">{{ __('how_it_works.cta.subtitle') }}</p>
                <div class="flex justify-center space-x-4">
                    <a href="{{ route('tutors.index') }}" class="bg-white text-primary border border-primary px-6 py-3 rounded-md hover:bg-primary/5 transition-colors duration-300">
                        {{ __('how_it_works.cta.find_tutor') }}
                    </a>
                    <a href="{{ route('register') }}" class="bg-white text-primary border border-primary px-6 py-3 rounded-md hover:bg-primary/5 transition-colors duration-300">
                        {{ __('how_it_works.cta.sign_up') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
