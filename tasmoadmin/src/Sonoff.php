<?php

namespace TasmoAdmin;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use TasmoAdmin\Tasmota\ResponseParser;

class Sonoff
{
    public const COMMAND_INFO_STATUS_ALL = 'status 0';

    private DeviceRepository $deviceRepository;

    private ResponseParser $responseParser;

    private Client $client;

    private Config $config;

    public function __construct(DeviceRepository $deviceRepository, Client $client, Config $config)
    {
        $this->deviceRepository = $deviceRepository;
        $this->responseParser = new ResponseParser();
        $this->client = $client;
        $this->config = $config;
    }

    public function getAllStatus(Device $device): \stdClass
    {
        return $this->doRequest($device, self::COMMAND_INFO_STATUS_ALL);
    }

    public function buildCmndUrl(Device $device, string $cmnd): string
    {
        return $this->buildUrl($device, 'cm', ['cmnd' => $cmnd]);
    }

    public function backup(Device $device, string $downloadPath): string
    {
        $url = $this->buildBasicAuthUrl($device, 'dl');
        $downloadFilePath = $downloadPath.$device->getBackupName();
        if (file_exists($downloadFilePath)) {
            unlink($downloadFilePath);
        }

        $this->client->get($url, ['sink' => $downloadFilePath]);

        return $downloadFilePath;
    }

    public function getNTPStatus(Device $device)
    {
        $cmnd = 'NtpServer1';

        $status = $this->doRequest($device, $cmnd);
        if (!empty($status->Command) && 'Unknown' === $status->Command) {
            return '';
        }

        return $status;
    }

    public function getFullTopic(Device $device): string
    {
        $cmnd = 'FullTopic';

        $status = $this->doRequest($device, $cmnd);
        if (!empty($status->Command) && 'Unknown' === $status->Command) {
            return '';
        }

        if (!empty($status->ERROR)) {
            return '';
        }

        return $status->FullTopic;
    }

    public function getSwitchTopic(Device $device): string
    {
        $cmnd = 'SwitchTopic';

        $status = $this->doRequest($device, $cmnd);

        if (!empty($status->Command) && 'Unknown' === $status->Command) {
            return '';
        }

        if (!empty($status->ERROR)) {
            return '';
        }

        return $status->SwitchTopic;
    }

    public function getMqttRetry(Device $device): string
    {
        $cmnd = 'MqttRetry';

        $status = $this->doRequest($device, $cmnd);
        if (!empty($status->Command) && 'Unknown' === $status->Command) {
            return '';
        }

        if (!empty($status->ERROR)) {
            return '';
        }

        return $status->MqttRetry;
    }

    public function getTelePeriod(Device $device): string
    {
        $cmnd = 'TelePeriod';

        $status = $this->doRequest($device, $cmnd);
        if (!empty($status->Command) && 'Unknown' === $status->Command) {
            return '';
        }

        if (!empty($status->ERROR)) {
            return '';
        }

        return $status->TelePeriod;
    }

    public function getSensorRetain(Device $device): string
    {
        $cmnd = 'SensorRetain';

        $status = $this->doRequest($device, $cmnd);
        if (!empty($status->Command) && 'Unknown' === $status->Command) {
            return '';
        }

        if (!empty($status->ERROR)) {
            return '';
        }

        return $status->SensorRetain;
    }

    public function getMqttFingerprint(Device $device): string
    {
        $cmnd = 'MqttFingerprint';

        $status = $this->doRequest($device, $cmnd);
        if (!empty($status->Command) && 'Unknown' === $status->Command) {
            return '';
        }
        if (!empty($status->ERROR)) {
            return '';
        }

        if (empty($status->MqttFingerprint)) {
            return '';
        }

        return $status->MqttFingerprint;
    }

    public function getPrefixe(Device $device): \stdClass
    {
        $cmnds = ['Prefix1', 'Prefix2', 'Prefix3'];

        $status = new \stdClass();
        foreach ($cmnds as $cmnd) {
            $tmp = $this->doRequest($device, $cmnd);

            if (!empty($tmp->Command) && 'Unknown' === $tmp->Command) {
                $status->{$cmnd} = '';
            } else {
                if (!empty($status->ERROR)) {
                    $status->{$cmnd} = '';
                } else {
                    $status->{$cmnd} = $tmp->{$cmnd};
                }
            }
        }

        unset($tmp);

        return $status;
    }

    public function getStateTexts(Device $device): \stdClass
    {
        $cmnds = ['StateText1', 'StateText2', 'StateText3', 'StateText4'];

        $status = new \stdClass();
        foreach ($cmnds as $cmnd) {
            $tmp = $this->doRequest($device, $cmnd);
            if (!empty($tmp->Command) && 'Unknown' === $tmp->Command) {
                $status->{$cmnd} = '';
            } else {
                if (!empty($status->ERROR)) {
                    $status->{$cmnd} = '';
                } else {
                    $status->{$cmnd} = $tmp->{$cmnd};
                }
            }
        }

        unset($tmp);

        return $status;
    }

    public function saveConfig(Device $device, string $backlog): \stdClass
    {
        return $this->doRequest($device, $backlog);
    }

    public function doAjax($deviceId, string $cmnd)
    {
        $device = $this->getDeviceById($deviceId);

        if (null === $device) {
            $response = new \stdClass();
            $response->ERROR = sprintf('No devices found with ID: %d', $deviceId);

            return $response;
        }

        $url = $this->buildCmndUrl($device, $cmnd);

        try {
            $response = $this->client->request('GET', $url);

            return $this->responseParser->processResult($response->getBody()->getContents());
        } catch (GuzzleException $exception) {
            $result = new \stdClass();
            $result->ERROR = $exception->getMessage();

            return $result;
        }
    }

    public function getDeviceById($id = null): ?Device
    {
        return $this->deviceRepository->getDeviceById($id);
    }

    public function doAjaxAll(): array
    {
        ini_set('max_execution_time', Constants::EXTENDED_MAX_EXECUTION_TIME);

        $devices = $this->getDevices();

        $urlData = [];
        foreach ($devices as $device) {
            $url = $this->buildCmndUrl($device, self::COMMAND_INFO_STATUS_ALL);
            $urlData[] = [
                'id' => $device->id,
                'url' => $url,
            ];
        }

        $requests = function ($urls) {
            foreach ($urls as $url) {
                yield new Request('GET', $url['url']);
            }
        };

        $results = [];
        $pool = new Pool($this->client, $requests($urlData), [
            'concurrency' => $this->config->getConcurrentRequests(),
            'fulfilled' => function (Response $response, $index) use (&$results, $urlData) {
                $results[$urlData[$index]['id']] = $this->responseParser->processResult($response->getBody()->getContents());
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        ini_set('max_execution_time', Constants::DEFAULT_MAX_EXECUTION_TIME);

        return $results;
    }

    public function setDeviceValue(int $id, string $field, $value = null): ?Device
    {
        return $this->deviceRepository->setDeviceValue($id, $field, $value);
    }

    /**
     * @return Device[]
     */
    public function getDevices(): array
    {
        $repositoryDevices = $this->deviceRepository->getDevices();

        $devices = [];
        $update = false;
        foreach ($repositoryDevices as $device) {
            if (0 === $device->position) {
                $device->position = 1;
                $update = true;
            }
            while (isset($devices[$device->position])) {
                ++$device->position;
            }
            if ($update) {
                $this->deviceRepository->setDeviceValue($device->id, 'position', $device->position);
            }
            $devices[$device->position] = $device;
        }
        ksort($devices);

        return $devices;
    }

    public function search($urls = []): array
    {
        ini_set('max_execution_time', Constants::EXTENDED_MAX_EXECUTION_TIME);

        $requests = function ($urls) {
            foreach ($urls as $url) {
                yield new Request('GET', $url);
            }
        };

        $results = [];
        $pool = new Pool($this->client, $requests($urls), [
            'concurrency' => $this->config->getConcurrentRequests(),
            'fulfilled' => function (Response $response, $index) use (&$results) {
                $results[] = $this->responseParser->processResult($response->getBody()->getContents());
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        ini_set('max_execution_time', Constants::DEFAULT_MAX_EXECUTION_TIME);

        return $results;
    }

    public function decodeOptions($options)
    {
        if (empty($options)) {
            return false;
        }
        $a_setoption = [
            // Tasmota\tools\decode-status.py
            'Save power state and use after restart',
            'Restrict button actions to single, double and hold',
            'Show value units in JSON messages',
            'MQTT enabled',
            'Respond as Command topic instead of RESULT',
            'MQTT retain on Power',
            'MQTT retain on Button',
            'MQTT retain on Switch',
            'Convert temperature to Fahrenheit',
            'MQTT retain on Sensor',
            'MQTT retained LWT to OFFLINE when topic changes',
            'Swap Single and Double press Button',
            'Do not use flash page rotate',
            'Button single press only',
            'Power interlock mode',
            'Do not allow PWM control',
            'Reverse clock',
            'Allow entry of decimal color values',
            'CO2 color to light signal',
            'HASS discovery',
            'Do not control Power with Dimmer',
            'Energy monitoring while powered off',
            'MQTT serial',
            'Rules',
            'Rules once mode',
            'KNX',
            'Use Power device index on single relay devices',
            'KNX enhancement',
            '',
            '',
            '',
            '',
        ];

        if (is_array($options)) {
            $options = $options[0];
        }

        $decodedOptopns = new \stdClass();

        $options = intval($options, 16);
        foreach ($a_setoption as $i => $iValue) {
            $optionV = ($options >> $i) & 1;
            $SetOPtion = 'SetOption'.$i;
            $decodedOptopns->{$SetOPtion} = new \stdClass();
            $decodedOptopns->{$SetOPtion}->desc = $iValue;
            $decodedOptopns->{$SetOPtion}->value = $optionV;
        }

        return $decodedOptopns;
    }

    private function doRequest(Device $device, string $cmnd, int $try = 1): \stdClass
    {
        $url = $this->buildCmndUrl($device, $cmnd);

        try {
            $result = $this->client->get($url)->getBody();
        } catch (GuzzleException $exception) {
            $result = new \stdClass();
            $result->ERROR = __('CURL_ERROR', 'API').' => '.$exception->getMessage();

            return $result;
        }

        $data = $this->responseParser->processResult($result->getContents());

        $skipWarning = false;
        if (str_contains($cmnd, 'Backlog')) {
            $skipWarning = true;
        }

        if (!$skipWarning && !empty($data->WARNING) && 1 === $try) {
            ++$try;
            // set web log level 2 and try again
            $webLog = $this->setWebLog($device, 2, $try);
            if (!isset($webLog->WARNING)) {
                $data = $this->doRequest($device, $cmnd, $try);
            }
        }

        return $data;
    }

    private function buildUrl(Device $device, string $endpoint, array $args = []): string
    {
        $queryParams = [];

        if (!empty($device->password)) {
            $queryParams['user'] = $device->username;
            $queryParams['password'] = $device->password;
        }

        $queryParams += $args;
        $queryString = '?'.http_build_query($queryParams);

        return sprintf('http://%s:%s/%s%s', $device->ip, $device->port, $endpoint, $queryString);
    }

    private function buildBasicAuthUrl(Device $device, string $endpoint): string
    {
        $basicAuth = '';
        if (!empty($device->password)) {
            $basicAuth = rawurlencode($device->username).':'.rawurlencode($device->password).'@';
        }

        return sprintf('http://%s%s/%s', $basicAuth, $device->ip, $endpoint);
    }

    private function setWebLog(Device $device, int $level = 2, int $try = 1): \stdClass
    {
        $cmnd = 'Weblog '.$level;

        return $this->doRequest($device, $cmnd, $try);
    }
}
