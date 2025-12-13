<?php

if (! function_exists('secondsToHi')) {
    function secondsToHi(?int $seconds): string
    {
        if (empty($seconds)) {
            return '';
        }

        $hours   = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('%d:%02d', $hours, $minutes);
    }
}
