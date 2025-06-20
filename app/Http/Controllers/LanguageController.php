<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;

class LanguageController extends Controller
{
    /**
     * Switch application language
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

        // Debug logging
        Log::info('Language Switch Request', [
            'requested_locale' => $locale,
            'current_locale_before' => App::getLocale(),
            'session_before' => Session::get('locale'),
        ]);

        // Start session if needed
        if (!Session::isStarted()) {
            Session::start();
        }

        // Set application locale immediately
        App::setLocale($locale);

        // Store in session with explicit save
        Session::put('locale', $locale);
        Session::save(); // Force save session

        // Debug after setting
        Log::info('Language Switch Complete', [
            'current_locale_after' => App::getLocale(),
            'session_after' => Session::get('locale'),
        ]);

        // Redirect back to the previous page with success message
        return redirect()->back()->with('success', __('common.language_changed'));
    }

    /**
     * Get current language
     *
     * @return string
     */
    public function getCurrentLanguage()
    {
        return App::getLocale();
    }

    /**
     * Get available languages
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
