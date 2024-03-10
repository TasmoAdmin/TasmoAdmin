<?php

class Result
{
    private bool $isValid = true;

    private array $errors = [];

    public function addError(string $error): void
    {
        $this->isValid = false;
        $this->errors[] = $error;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

class PreInstallChecks
{
    public function run(): Result
    {
        $result = new Result();

        if (!function_exists('curl_init')) {
            $result->addError('ERROR: PHP cURL is missing.');
        }

        if (!class_exists('ZipArchive')) {
            $result->addError('ERROR: PHP Zip is missing.');
        }

        if (!class_exists('DOMElement')) {
            $result->addError('ERROR: PHP XML is missing.');
        }

        if (PHP_VERSION_ID < 80100) {
            $result->addError('ERROR: PHP 8.1 or higher is required.');
        }

        return $result;
    }
}

return new PreInstallChecks();
