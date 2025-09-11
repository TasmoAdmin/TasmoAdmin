<?php

namespace TasmoAdmin\Tasmota;

class ResponseHelper
{
    private object $data;

    public function __construct(object $data)
    {
        $this->data = $data;
    }

    public function getFriendlyName(int $index = 0): string
    {
        if (!is_array($this->data->Status->FriendlyName)) { // array since 5.12.0h
            return $this->data->Status->FriendlyName;
        }

        if (array_key_exists($index, $this->data->Status->FriendlyName)) {
            if (!empty($this->data->Status->FriendlyName[$index])) {
                return $this->data->Status->FriendlyName[$index];
            }

            $name = __('DEVICE', 'DEVICES_AUTOSCAN');
            if (!empty($this->data->Status->FriendlyName[0])) {
                $name = $this->data->Status->FriendlyName[0];
            }

            return sprintf('%s %s', $name, $index + 1);
        }

        return sprintf('%s $s', __('DEVICE', 'DEVICES_AUTOSCAN'), $index + 1);
    }
}
