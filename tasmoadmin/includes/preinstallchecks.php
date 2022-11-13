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

        if( !function_exists( "curl_init" ) ) {
            $result->addError("ERROR: PHP Curl is missing.");
        }

        if( !class_exists( "ZipArchive" ) ) {
            $result->addError("ERROR: PHP Zip is missing.");
        }

        if( !class_exists( "DOMElement" ) ) {
            $result->addError("ERROR: PHP XML is missing.");
        }

        return $result;
    }
}


return new PreInstallChecks();


