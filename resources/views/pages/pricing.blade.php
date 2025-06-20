<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900">{{ __('pricing.title') }}</h1>
                <p class="mt-4 text-lg text-gray-600">{{ __('pricing.subtitle') }}</p>
            </div>

            <!-- Pricing Tiers -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                <!-- Basic Plan -->
                <div class="bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="p-8">
                        <div class="flex items-baseline justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900">{{ __('pricing.plans.basic.title') }}</h3>
                        </div>
                        <ul class="space-y-4 mb-8">
                            @foreach(__('pricing.plans.basic.features') as $feature)
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-success-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Standard Plan -->
                <div class="bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300 border-2 border-primary-500">
                    <div class="p-8">
                        <div class="flex items-baseline justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900">{{ __('pricing.plans.standard.title') }}</h3>
                        </div>
                        <ul class="space-y-4 mb-8">
                            @foreach(__('pricing.plans.standard.features') as $feature)
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-success-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="p-8">
                        <div class="flex items-baseline justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900">{{ __('pricing.plans.premium.title') }}</h3>
                        </div>
                        <ul class="space-y-4 mb-8">
                            @foreach(__('pricing.plans.premium.features') as $feature)
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-success-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Buttons Row -->
            <div class="flex flex-col sm:flex-row justify-around items-center gap-4 mb-16 mt-8">
                <a href="{{ route('tutors.index', ['price_range' => '0-25']) }}" class="w-full sm:w-auto btn-primary text-center px-6 py-3 no-underline">
                    {{ __('pricing.plans.basic.title') }}
                </a>
                <a href="{{ route('tutors.index', ['price_range' => '25-40']) }}" class="w-full sm:w-auto btn-primary text-center px-6 py-3 no-underline border-2 border-primary-700 ring-2 ring-offset-2 ring-primary-700">
                    {{ __('pricing.plans.standard.title') }}
                </a>
                <a href="{{ route('tutors.index', ['price_range' => '40-']) }}" class="w-full sm:w-auto btn-primary text-center px-6 py-3 no-underline">
                    {{ __('pricing.plans.premium.title') }}
                </a>
            </div>

            <!-- FAQ Section -->
            <div class="bg-white rounded-lg shadow-sm p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">{{ __('pricing.faq.title') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach(__('pricing.faq.questions') as $faq)
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $faq['question'] }}</h3>
                        <p class="text-gray-600">{{ $faq['answer'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
