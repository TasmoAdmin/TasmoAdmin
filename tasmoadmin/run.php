<?php


pcntl_async_signals(true);

require_once __DIR__ . '/vendor/autoload.php';


$clientId = 'devuser';
$server = '192.168.1.100';
$port = 1883;


$mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
pcntl_signal(SIGINT, function (int $signal, $info) use ($mqtt) {
    $mqtt->interrupt();
});

$connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
    ->setUsername('devuser')
    ->setPassword('devpass');

$mqtt->connect($connectionSettings, true);
$mqtt->subscribe('tasmota', function ($topic, $message, $retained, $matchedWildcards) {
    echo sprintf("Received message on topic [%s]: %s\n", $topic, $message);
}, 0);
$mqtt->loop(true);
$mqtt->disconnect();
