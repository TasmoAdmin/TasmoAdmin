<?php

namespace TasmoAdmin\Helper;

class SupportedLanguageHelper
{
    public static function getSupportedLanguages(): array
    {
        return [
            'cs' => 'Čeština',
            'de' => 'Deutsch',
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'he' => 'עִברִית',
            'hu' => 'Magyar',
            'it' => 'Italiano',
            'nl' => 'Nederlands',
            'pl' => 'Polski',
            'pt_BR' => 'Português (Brasil)',
            'ru' => 'Русский',
            'zh_TW' => '繁體中文',
        ];
    }
}
