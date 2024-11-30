<?php

namespace App\Helpers;

class Terbilang
{
    private static $number = [
        '',
        'satu',
        'dua',
        'tiga',
        'empat',
        'lima',
        'enam',
        'tujuh',
        'delapan',
        'sembilan',
        'sepuluh',
        'sebelas'
    ];

    public static function make($number)
    {
        if ($number < 12) {
            return static::$number[$number];
        } elseif ($number < 20) {
            return static::$number[$number - 10] . ' belas';
        } elseif ($number < 100) {
            return static::$number[floor($number / 10)] . ' puluh ' . static::$number[$number % 10];
        } elseif ($number < 200) {
            return 'seratus ' . static::make($number - 100);
        } elseif ($number < 1000) {
            return static::$number[floor($number / 100)] . ' ratus ' . static::make($number % 100);
        } elseif ($number < 2000) {
            return 'seribu ' . static::make($number - 1000);
        } elseif ($number < 1000000) {
            return static::make(floor($number / 1000)) . ' ribu ' . static::make($number % 1000);
        } elseif ($number < 1000000000) {
            return static::make(floor($number / 1000000)) . ' juta ' . static::make($number % 1000000);
        } elseif ($number < 1000000000000) {
            return static::make(floor($number / 1000000000)) . ' milyar ' . static::make($number % 1000000000);
        }

        return static::make(floor($number / 1000000000000)) . ' trilyun ' . static::make($number % 1000000000000);
    }
}
