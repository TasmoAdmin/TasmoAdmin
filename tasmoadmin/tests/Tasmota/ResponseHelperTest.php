<?php

namespace Tests\TasmoAdmin\Tasmota;

use PHPUnit\Framework\TestCase;
use TasmoAdmin\Tasmota\ResponseHelper;

class ResponseHelperTest extends TestCase
{
    public function testGetFriendlyNameNonArray(): void
    {
        $data = new \stdClass();
        $data->Status = new \stdClass();
        $data->Status->FriendlyName = 'Name1';

        $responseHelper = new ResponseHelper($data);

        self::assertEquals('Name1', $responseHelper->getFriendlyName());
    }

    public function testGetFriendlyNameArraySingle(): void
    {
        $data = new \stdClass();
        $data->Status = new \stdClass();
        $data->Status->FriendlyName = [
            'Name1',
        ];

        $responseHelper = new ResponseHelper($data);

        self::assertEquals('Name1', $responseHelper->getFriendlyName(0));
    }

    public function testGetFriendlyNameArrayMultiple(): void
    {
        $data = new \stdClass();
        $data->Status = new \stdClass();
        $data->Status->FriendlyName = [
            'Name1',
            'Name2',
        ];

        $responseHelper = new ResponseHelper($data);

        self::assertEquals('Name1', $responseHelper->getFriendlyName(0));
        self::assertEquals('Name2', $responseHelper->getFriendlyName(1));
    }

    public function testGetFriendlyNameArrayMultipleSecondEmpty(): void
    {
        $data = new \stdClass();
        $data->Status = new \stdClass();
        $data->Status->FriendlyName = [
            'Name1',
            '',
        ];

        $responseHelper = new ResponseHelper($data);

        self::assertEquals('Name1', $responseHelper->getFriendlyName(0));
        self::assertEquals('Name1 2', $responseHelper->getFriendlyName(1));
    }

    public function testGetFriendlyNameArrayFirstEmpty(): void
    {
        $data = new \stdClass();
        $data->Status = new \stdClass();
        $data->Status->FriendlyName = [
            '',
            'Name2',
        ];

        $responseHelper = new ResponseHelper($data);

        self::assertEquals('DEVICES_AUTOSCAN_DEVICE:  1', $responseHelper->getFriendlyName(0));
        self::assertEquals('Name2', $responseHelper->getFriendlyName(1));
    }

    public function testGetFriendlyNameArrayMultipleEmpty(): void
    {
        $data = new \stdClass();
        $data->Status = new \stdClass();
        $data->Status->FriendlyName = [
            '',
            '',
        ];

        $responseHelper = new ResponseHelper($data);

        self::assertEquals('DEVICES_AUTOSCAN_DEVICE:  1', $responseHelper->getFriendlyName(0));
        self::assertEquals('DEVICES_AUTOSCAN_DEVICE:  2', $responseHelper->getFriendlyName(1));
    }
}
