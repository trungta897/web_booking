<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    // Cache TTL constants (in seconds)
    public const TTL_SHORT = 300;     // 5 minutes
    public const TTL_MEDIUM = 1800;   // 30 minutes
    public const TTL_LONG = 3600;     // 1 hour
    public const TTL_VERY_LONG = 86400; // 24 hours

    // Cache key prefixes
    private const PREFIX_TUTOR = 'tutor';
    private const PREFIX_SUBJECT = 'subject';
    private const PREFIX_ADMIN = 'admin';
    private const PREFIX_USER = 'user';
    private const PREFIX_LANDING = 'landing';

    /**
     * Get cache with consistent key formatting.
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Forget cache key.
     */
    public static function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Forget multiple cache keys.
     */
    public static function forgetMany(array $keys): void
    {
        foreach ($keys as $key) {
            self::forget($key);
        }
    }

    // Tutor Cache Keys
    public static function tutorDetailsKey(int $tutorId): string
    {
        return self::PREFIX_TUTOR . '.details.' . $tutorId;
    }

    public static function tutorStatsKey(int $tutorId): string
    {
        return self::PREFIX_TUTOR . '.stats.' . $tutorId;
    }

    public static function tutorsWithFiltersKey(array $filters): string
    {
        $filtersForCache = $filters;
        unset($filtersForCache['page'], $filtersForCache['per_page']);

        return self::PREFIX_TUTOR . '.filtered.' . md5(serialize($filtersForCache));
    }

    public static function featuredTutorsKey(int $limit = 6): string
    {
        return self::PREFIX_TUTOR . '.featured.' . $limit;
    }

    public static function topRatedTutorsKey(int $limit = 10): string
    {
        return self::PREFIX_TUTOR . '.top_rated.' . $limit;
    }

    // Subject Cache Keys
    public static function subjectsActiveKey(): string
    {
        return self::PREFIX_SUBJECT . '.active';
    }

    public static function subjectsWithTutorCountKey(): string
    {
        return self::PREFIX_SUBJECT . '.with_tutor_count';
    }

    public static function popularSubjectsKey(int $limit = 8): string
    {
        return self::PREFIX_SUBJECT . '.popular.' . $limit;
    }

    public static function subjectAnalyticsKey(): string
    {
        return self::PREFIX_SUBJECT . '.analytics';
    }

    // Admin Cache Keys
    public static function adminDashboardStatsKey(): string
    {
        return self::PREFIX_ADMIN . '.dashboard.stats';
    }

    // Landing Page Cache Keys
    public static function landingPageDataKey(): string
    {
        return self::PREFIX_LANDING . '.page_data';
    }

    // Cache clearing methods
    public static function clearTutorCaches(?int $tutorId = null): void
    {
        $keys = [
            self::featuredTutorsKey(6),
            self::featuredTutorsKey(10),
            self::featuredTutorsKey(12),
            self::topRatedTutorsKey(6),
            self::topRatedTutorsKey(10),
            self::topRatedTutorsKey(12),
            self::landingPageDataKey(),
        ];

        if ($tutorId) {
            $keys[] = self::tutorDetailsKey($tutorId);
            $keys[] = self::tutorStatsKey($tutorId);
        }

        self::forgetMany($keys);
    }

    public static function clearSubjectCaches(): void
    {
        $keys = [
            self::subjectsActiveKey(),
            self::subjectsWithTutorCountKey(),
            self::popularSubjectsKey(5),
            self::popularSubjectsKey(8),
            self::popularSubjectsKey(10),
            self::subjectAnalyticsKey(),
            self::landingPageDataKey(),
        ];

        self::forgetMany($keys);
    }

    public static function clearAdminCaches(): void
    {
        $keys = [
            self::adminDashboardStatsKey(),
        ];

        self::forgetMany($keys);
    }

    public static function clearAllCaches(): void
    {
        self::clearTutorCaches();
        self::clearSubjectCaches();
        self::clearAdminCaches();
    }
}
