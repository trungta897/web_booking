<div class="relative inline-block text-left">
    <div>
        <button type="button"
                class="inline-flex justify-center items-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                id="language-menu-button"
                aria-expanded="true"
                aria-haspopup="true"
                onclick="toggleLanguageMenu()"
                title="{{ __('common.change_language') }}"
                aria-label="{{ __('common.change_language') }}">
            @if(app()->getLocale() == 'vi')
                <img src="https://flagcdn.com/w20/vn.png" class="w-4 h-3 mr-2" alt="Vietnamese">
                Tiếng Việt
            @else
                <img src="https://flagcdn.com/w20/us.png" class="w-4 h-3 mr-2" alt="English">
                English
            @endif
            <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none hidden"
         role="menu"
         aria-orientation="vertical"
         aria-labelledby="language-menu-button"
         tabindex="-1"
         id="language-menu">
        <div class="py-1" role="none">
            <a href="{{ route('language.switch', 'vi') }}"
               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ app()->getLocale() == 'vi' ? 'bg-gray-100 text-gray-900' : '' }}"
               role="menuitem">
                <img src="https://flagcdn.com/w20/vn.png" class="w-4 h-3 mr-3" alt="Vietnamese">
                Tiếng Việt
                @if(app()->getLocale() == 'vi')
                    <svg class="ml-auto h-4 w-4 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </a>
            <a href="{{ route('language.switch', 'en') }}"
               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 {{ app()->getLocale() == 'en' ? 'bg-gray-100 text-gray-900' : '' }}"
               role="menuitem">
                <img src="https://flagcdn.com/w20/us.png" class="w-4 h-3 mr-3" alt="English">
                English
                @if(app()->getLocale() == 'en')
                    <svg class="ml-auto h-4 w-4 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </a>
        </div>
    </div>
</div>



    @push('scripts')
        <script src="{{ asset('js/pages/components-language-switcher.js') }}"></script>
    @endpush