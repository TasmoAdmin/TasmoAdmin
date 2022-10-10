<?php

namespace TasmoAdmin\Helper;

use JsonException;

class JsonLanguageHelper
{
    private string $language;

    private string $languageFile;

    private string $fallbackLanguage;

    private string $fallbackLanguageFile;

    private string $cacheDir;

    public function __construct(
        string $language,
        string $languageFile,
        string $fallbackLanguage,
        string $fallbackLanguageFile,
        string $cacheDir
    ) {
        $this->language = $language;
        $this->languageFile = $languageFile;
        $this->fallbackLanguage = $fallbackLanguage;
        $this->fallbackLanguageFile = $fallbackLanguageFile;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @throws JsonException
     */
    public function dumpJson(): void
    {
        $cacheFile = $this->cacheFile();

        if ($this->hasExistingFile($cacheFile) && $this->isCacheModifiedNewerThenLanguageFile($cacheFile)) {
            return;
        }

        $this->writeFile($cacheFile);
    }

    /**
     * @param string $cacheFile
     * @return void
     * @throws JsonException
     */
    private function writeFile(string $cacheFile): void
    {
        $jsonCompiled = json_encode([
            $this->language => $this->loadLanguage($this->languageFile),
            $this->fallbackLanguage => $this->loadLanguage($this->fallbackLanguageFile),
        ], JSON_THROW_ON_ERROR);
        file_put_contents($cacheFile, $jsonCompiled);
    }

    private function loadLanguage(string $languageFile): array
    {
        $config = parse_ini_file($languageFile);
        return $this->removeBlocks($config);
    }

    private function removeBlocks($config): array
    {
        $jsConfig = [];
        foreach ($config as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $blockKey => $blockValue) {
                    $jsConfig[$blockKey] = $blockValue;
                }
            } else {
                $jsConfig[$key] = $value;
            }
        }

        return $jsConfig;
    }

    private function hasExistingFile(string $cacheFile): bool
    {
        return file_exists($cacheFile);
    }

    private function cacheFile(): string
    {
        return sprintf('%s/json_i18n_%s.cache.json', $this->cacheDir, $this->language);
    }

    private function isCacheModifiedNewerThenLanguageFile(string $cacheFile): bool
    {
        return filemtime($cacheFile) > filemtime($this->languageFile);
    }
}
