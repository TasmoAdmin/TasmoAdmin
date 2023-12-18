<?php

namespace TasmoAdmin\Helper;

class SupportedLanguageHelper
{
    public static function getSupportedLanguages(): array
    {
        return [
            "cz" => "Čeština",
            "de" => "Deutsch",
            "en" => "English",
            "es" => "Español",
            "fr" => "Français",
            "hu" => "Magyar",
            "it" => "Italiano",
            "nl" => "Nederlands",
            "pl" => "Polski",
            "ru" => "Русский",
            "zh_TW" => "繁體中文",
        ];
    }
}
