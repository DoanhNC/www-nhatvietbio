<?php

use App\Models\ELanguage;

/**
 * Get translation from database by key
 * Uses session locale to determine current language
 * 
 * @param string $key - Dot notation key (e.g., 'about.title', 'header.quality_title')
 * @param string|null $default - Default value if key not found (defaults to key itself)
 * @return string
 */
function __t(string $key, ?string $default = null): string
{
    static $translations = null;
    static $currentLocale = null;

    // Get current locale from session
    $locale = session('locale', 'vi');

    // Reload translations if locale changed or not loaded
    if ($translations === null || $currentLocale !== $locale) {
        $currentLocale = $locale;
        $translations = [];

        // Try to load from database
        try {
            // Find language by code (strip region suffix for matching)
            // e.g., 'vi' matches 'vi-vn', 'ja' matches 'ja-jp'
            $language = ELanguage::where('is_active', true)
                ->where(function ($query) use ($locale) {
                    $query->where('code', $locale)
                        ->orWhere('code', 'LIKE', $locale . '-%');
                })
                ->first();

            if ($language && is_array($language->translations)) {
                $translations = $language->translations;
            }
        } catch (\Exception $e) {
            // Silently fail - will return default values
            $translations = [];
        }
    }

    // Use data_get for dot notation support
    $value = data_get($translations, $key);

    if ($value !== null && is_string($value)) {
        return $value;
    }

    // Return default or the key itself
    return $default ?? $key;
}

/**
 * Get translation with fallback to Vietnamese file
 * Useful during migration when not all keys are in database
 * 
 * @param string $key
 * @param string|null $default
 * @return string
 */
function __tf(string $key, ?string $default = null): string
{
    $value = __t($key, null);

    // If translation found in database, return it
    if ($value !== $key) {
        return $value;
    }

    // Fallback to Laravel's built-in translation
    $laravelKey = str_replace('.', '/', $key);
    $translated = __($laravelKey);

    // If Laravel translation found, return it
    if ($translated !== $laravelKey) {
        return $translated;
    }

    // Return default or key
    return $default ?? $key;
}
