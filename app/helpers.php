<?php

if (!function_exists('translateSubjectName')) {
    /**
     * Translate subject name based on current locale
     *
     * @param string $subjectName
     * @return string
     */
    function translateSubjectName($subjectName) {
        $translatedName = __('subjects.names.' . $subjectName);

        // If translation not found, return original name
        if ($translatedName === 'subjects.names.' . $subjectName) {
            return $subjectName;
        }

        return $translatedName;
    }
}

if (!function_exists('translateSubjectDescription')) {
    /**
     * Translate subject description based on current locale
     *
     * @param string $subjectName
     * @return string
     */
    function translateSubjectDescription($subjectName) {
        $translatedDescription = __('subjects.descriptions.' . $subjectName);

        // If translation not found, return default fallback
        if ($translatedDescription === 'subjects.descriptions.' . $subjectName) {
            return 'Explore ' . $subjectName . ' courses and find expert tutors.';
        }

        return $translatedDescription;
    }
}
