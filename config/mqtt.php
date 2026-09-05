<?php
/**
 * MQTT Broker Configuration
 */

declare(strict_types=1);

return [
    'host'      => getenv('MQTT_HOST') ?: 'localhost',
    'port'      => (int) (getenv('MQTT_PORT') ?: 1883),
    'client_id' => getenv('MQTT_CLIENT_ID') ?: 'latex-monitoring-subscriber',
    'topic'     => 'latex/monitoring',
    'username'  => getenv('MQTT_USER') ?: null,
    'password'  => getenv('MQTT_PASS') ?: null,
];
