<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900">Simple, Transparent Pricing</h1>
                <p class="mt-4 text-lg text-gray-600">Choose the plan that works best for you</p>
            </div>

            <!-- Pricing Tiers -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                <!-- Basic Plan -->
                <div class="bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="p-8">
                        <div class="flex items-baseline justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Basic: Up to $25/hr</h3>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                One-on-one tutoring
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Basic subject coverage
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Email support
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Standard Plan -->
                <div class="bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300 border-2 border-primary">
                    <div class="p-8">

                        <div class="flex items-baseline justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Standard: $25-$40/hr</h3>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                One-on-one tutoring
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                All subjects covered
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                24/7 support
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Progress tracking
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="bg-white rounded-lg shadow-sm hover:shadow-lg transition-shadow duration-300">
                    <div class="p-8">
                        <div class="flex items-baseline justify-between mb-4">
                            <h3 class="text-xl font-bold text-gray-900">Premium: $40+/hr</h3>
                        </div>
                        <ul class="space-y-4 mb-8">
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                One-on-one tutoring
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                All subjects covered
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                24/7 priority support
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Advanced progress tracking
                            </li>
                            <li class="flex items-center">
                                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Custom learning materials
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Buttons Row -->
            <div class="flex flex-col sm:flex-row justify-around items-center gap-4 mb-16 mt-8">
                <a href="{{ route('tutors.index', ['price_range' => '0-25']) }}" class="w-full sm:w-auto inline-block bg-blue-500 text-white text-center px-6 py-3 rounded-md hover:bg-blue-600 transition-colors duration-300 no-underline">
                    Basic: Up to $25/hr
                </a>
                <a href="{{ route('tutors.index', ['price_range' => '25-40']) }}" class="w-full sm:w-auto inline-block bg-blue-500 text-white text-center px-6 py-3 rounded-md hover:bg-blue-600 transition-colors duration-300 no-underline border-2 border-blue-700 ring-2 ring-offset-2 ring-blue-700">
                    Standard: $25-$40/hr
                </a>
                <a href="{{ route('tutors.index', ['price_range' => '40-']) }}" class="w-full sm:w-auto inline-block bg-blue-500 text-white text-center px-6 py-3 rounded-md hover:bg-blue-600 transition-colors duration-300 no-underline">
                    Premium: $40+/hr
                </a>
            </div>

            <!-- FAQ Section -->
            <div class="bg-white rounded-lg shadow-sm p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Frequently Asked Questions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">How do I choose the right plan?</h3>
                        <p class="text-gray-600">Consider your learning goals and budget. Basic is great for occasional help, Standard for regular tutoring, and Premium for intensive learning.</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Can I change plans later?</h3>
                        <p class="text-gray-600">Yes, you can upgrade or downgrade your plan at any time. Changes will be reflected in your next billing cycle.</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">What payment methods do you accept?</h3>
                        <p class="text-gray-600">We accept all major credit cards, PayPal, and bank transfers. All payments are secure and encrypted.</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Is there a minimum commitment?</h3>
                        <p class="text-gray-600">No, there's no minimum commitment. You can book sessions as needed and cancel anytime.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
