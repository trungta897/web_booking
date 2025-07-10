<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch application language.
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchLanguage(Request $request, $locale)
    {
        // Available languages
        $availableLocales = ['en', 'vi'];

        // Validate locale
        if (!in_array($locale, $availableLocales)) {
            return redirect()->back()->with('error', 'Invalid language selected.');
        }

        try {
            // Start the session if it's not already started
            if (!$request->session()->isStarted()) {
                $request->session()->start();
            }

            // Store in session
            $request->session()->put('locale', $locale);
            $request->session()->save();

            // Set application locale for immediate effect
            App::setLocale($locale);

            // Set success message based on the new locale
            $message = $locale === 'vi' ? 'Đã chuyển đổi ngôn ngữ thành công sang Tiếng Việt' : 'Language successfully changed to English';

            // Redirect back to the previous page with success message
            return redirect()->back()->with('language_success', $message);
        } catch (\Exception $e) {
            Log::error('Language Switch Error', [
                'error' => $e->getMessage(),
                'locale' => $locale,
            ]);

            return redirect()->back()->with('error', 'Failed to change language. Please try again.');
        }
    }

    /**
     * Get current language.
     *
     * @return string
     */
    public function getCurrentLanguage()
    {
        return App::getLocale();
    }

    /**
     * Get available languages.
     *
     * @return array
     */
    public function getAvailableLanguages()
    {
        return [
            'en' => __('common.english'),
            'vi' => __('common.vietnamese'),
        ];
    }
}
