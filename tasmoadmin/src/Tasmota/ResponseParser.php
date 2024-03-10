<?php

namespace TasmoAdmin\Tasmota;

class ResponseParser
{
    public function processResult(string $result): \stdClass
    {
        $data = json_decode($result);
        if (JSON_ERROR_CTRL_CHAR === json_last_error()) {  // https://github.com/TasmoAdmin/TasmoAdmin/issues/78
            $result = preg_replace('/[[:cntrl:]]/', '', $result);
            $data = json_decode($result);
        }

        if (JSON_ERROR_NONE !== json_last_error()) {
            $data = json_decode($this->fixJsonFormatv5100($result));
        }
        if (JSON_ERROR_NONE !== json_last_error()) {
            $data = json_decode($this->fixJsonFormatv8500($result));
        }

        if (JSON_ERROR_NONE !== json_last_error()) {
            $data = new \stdClass();
            $data->ERROR = __('JSON_ERROR', 'API')
                .' => '
                .json_last_error()
                .': '
                .json_last_error_msg();
            $data->ERROR .= '<br/><strong>'
                .__('JSON_ERROR_CONTACT_DEV', 'API', [$result])
                .'</strong>';
            $data->ERROR .= '<br/>'.__('JSON_ANSWER', 'API').' => '.print_r($result, true);
        }

        if (isset($data) && empty($data->ERROR)) {
            $data = $this->compatibility($data);
        }

        return $this->stateTextsDetection($data);
    }

    private function compatibility(\stdClass $status): \stdClass
    {
        /**
         * < 5.12.0
         * $status->StatusNET->IP
         * >= 5.12.0
         * $status->StatusNET->IPAddress
         * https://github.com/TasmoAdmin/TasmoAdmin/issues/107.
         */
        if (!empty($status->StatusNET->IP)) {
            $status->StatusNET->IPAddress = $status->StatusNET->IP; // @phpstan-ignore-line
        }

        return $status;
    }

    private function stateTextsDetection(\stdClass $status): \stdClass
    {
        /**
         * v6.2.0.2 2018-09-04
         *  MQTT Changed Statetext is sent as JSON, this is fail cuz it can be translated and not detected by other software.
         *
         * This function tries to detect the state by hardcoded keywords.
         */
        $offArray = explode(
            ', ',
            strtolower(
                ''

                /*
                 * EN
                 */.'off, down, offline, out, '

                /*
                 * DE
                 */.'aus, unten, runter, schließen, schliessen, zu, '

                /*
                 * PL
                 */.'z, poniżej, ponizej, blisko, do, zamknięte, zamkniete'
            )
        );
        $onArray = explode(
            ', ',
            strtolower(
                ''

                /*
                 * EN
                 */.'on, up, online, in, '

                /*
                 * DE
                 */.'an, oben, hoch, öffnen, oeffnen, offen, '

                /*
                 * PL
                 */.'do, powyżej, powyzej, wysoki, otwarte'
            )
        );

        $state = null;

        // status 0 request for 1 relais
        if (isset($status->StatusSTS->POWER)) {
            $state = $status->StatusSTS->POWER;
            if (isset($status->StatusSTS->POWER->STATE)) {
                $state = $status->StatusSTS->POWER->STATE;
            }
            // try to detect OFF
            if (in_array(strtolower($state), $offArray)) {
                $state = 'OFF';
            } elseif (in_array(strtolower($state), $onArray)) {
                $state = 'ON';
            }

            if (!empty($state)) {
                if (isset($status->StatusSTS->POWER->STATE)) {
                    $status->StatusSTS->POWER->STATE = $state;
                } else {
                    $status->StatusSTS->POWER = $state;
                }
            }
        }

        // toggle request for 1 relais
        if (isset($status->POWER)) {
            $state = $status->POWER;
            // try to detect OFF
            if (in_array(strtolower($state), $offArray)) {
                $state = 'OFF';
            } elseif (in_array(strtolower($state), $onArray)) {
                $state = 'ON';
            }

            if (!empty($state)) {
                $status->POWER = $state;
            }
        }

        $i = 1;
        $power = 'POWER'.$i;

        // status 0 request for multi relais
        while (isset($status->StatusSTS->{$power})) {
            $state = $status->StatusSTS->{$power};
            if (isset($status->StatusSTS->{$power}->STATE)) {
                $state = $status->StatusSTS->{$power}->STATE;
            }
            // try to detect OFF
            if (in_array(strtolower($state), $offArray)) {
                $state = 'OFF';
            } elseif (in_array(strtolower($state), $onArray)) {
                $state = 'ON';
            }

            if (!empty($state)) {
                if (isset($status->StatusSTS->{$power}->STATE)) {
                    $status->StatusSTS->{$power}->STATE = $state;
                } else {
                    $status->StatusSTS->{$power} = $state;
                }
            }

            ++$i;
            $power = 'POWER'.$i;
        }

        $i = 1;
        $power = 'POWER'.$i;

        // toggle request for multi relais
        while (isset($status->{$power})) {
            $state = $status->{$power};
            if (isset($status->{$power}->STATE)) {
                $state = $status->{$power}->STATE;
            }

            // try to detect OFF
            if (in_array(strtolower($state), $offArray)) {
                $state = 'OFF';
            } elseif (in_array(strtolower($state), $onArray)) {
                $state = 'ON';
            }

            if (!empty($state)) {
                if (isset($status->{$power}->STATE)) {
                    $status->{$power}->STATE = $state;
                } else {
                    $status->{$power} = $state;
                }
                $status->{$power} = $state;
            }

            ++$i;
            $power = 'POWER'.$i;
        }

        return $status;
    }

    /**
     * This fixes wrong formatted json answer form Tasmota Version 5.10.0
     * Example wrong format: dev/json_error_5100.json.
     */
    private function fixJsonFormatV5100(string $string): string
    {
        $string = substr($string, strpos($string, 'STATUS = '));
        if (false !== strpos($string, 'POWER = ')) {
            $string = substr($string, strpos($string, '{'));
            $string = substr($string, 0, strrpos($string, '}') + 1);
        }
        if (false !== strpos($string, 'ERGEBNIS = ')) {
            $string = substr($string, strpos($string, '{'));
            $string = substr($string, 0, strrpos($string, '}') + 1);
        }
        if (false !== strpos($string, 'RESULT = ')) {
            $string = substr($string, strpos($string, '{'));
            $string = substr($string, 0, strrpos($string, '}') + 1);
        }

        $remove = [
            PHP_EOL,
            "\n",
            'STATUS = ',
            '}STATUS1 = {',
            '}STATUS2 = {',
            '}STATUS3 = {',
            '}STATUS4 = {',
            '}STATUS5 = {',
            '}STATUS6 = {',
            '}STATUS7 = {',
            '}in = {',
            '}STATUS8 = {',
            '}STATUS9 = {',
            '}STATUS10 = {',
            '}STATUS11 = {',
            'STATUS2 = ',
            ':nan,',
            ':nan}',
        ];
        $replace = [
            '',
            '',
            '',
            ',',
            ',',
            ',',
            ',',
            ',',
            ',',
            ',',
            ',',
            ',',
            ',',
            ',',
            ',',
            '',
            ':"NaN",',
            ':"NaN"}',
        ];

        $string = str_replace($remove, $replace, $string);

        // remove everything before the first {
        return strstr($string, '{');
    }

    /**
     * This fixes wrong formatted json answer form Tasmota Version 8.5.0.x
     * Example wrong format: dev/json_error_8500.json.
     *
     * Shutters missed a } at the end
     * https://github.com/TasmoAdmin/TasmoAdmin/issues/398
     */
    private function fixJsonFormatv8500(string $string): string
    {
        $string .= '}';

        return $string;
    }
}
