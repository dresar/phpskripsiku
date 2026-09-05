<?php
/**
 * MQTT Subscriber - Subscribe topic latex/monitoring, decode JSON, insert ke SQLite
 * Jalankan: php mqtt/subscriber.php (di background atau terminal terpisah)
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

use App\Database;
use App\ReadingRepository;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

$config = require dirname(__DIR__) . '/config/mqtt.php';
$logFile = dirname(__DIR__) . '/logs/mqtt_subscriber.log';

function logMessage(string $message, string $level = 'INFO'): void
{
    global $logFile;
    $dir = dirname($logFile);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $line = date('Y-m-d H:i:s') . " [{$level}] {$message}" . PHP_EOL;
    @file_put_contents($logFile, $line, FILE_APPEND);
    echo $line;
}

function validatePayload(array $data): bool
{
    return isset(
        $data['ph'],
        $data['tds'],
        $data['suhu'],
        $data['status']
    ) && is_numeric($data['ph'])
      && is_numeric($data['tds'])
      && is_numeric($data['suhu'])
      && is_string($data['status']);
}

function sanitizePayload(array $data): array
{
    return [
        'ph'    => (float) $data['ph'],
        'tds'   => (float) $data['tds'],
        'suhu'  => (float) $data['suhu'],
        'status'=> trim(preg_replace('/[^\p{L}\p{N}\s\-]/u', '', (string) $data['status'])) ?: 'Unknown',
    ];
}

function updateMqttHeartbeat(): void
{
    $file = dirname(__DIR__) . '/database/mqtt_last_seen.txt';
    @file_put_contents($file, (string) time());
}

try {
    $pdo = Database::get();
    $repo = new ReadingRepository($pdo);

    $connectionSettings = (new ConnectionSettings())
        ->setKeepAliveInterval(60)
        ->setConnectTimeout(10);

    if (!empty($config['username'])) {
        $connectionSettings->setUsername($config['username'])->setPassword($config['password'] ?? '');
    }

    $mqtt = new MqttClient(
        $config['host'],
        (int) $config['port'],
        $config['client_id']
    );

    $mqtt->connect($connectionSettings);
    logMessage('Connected to MQTT broker: ' . $config['host'] . ':' . $config['port']);

    $mqtt->subscribe($config['topic'], function (string $topic, string $message) use ($repo) {
        $payload = @json_decode($message, true);
        if (!is_array($payload)) {
            logMessage('Invalid JSON: ' . substr($message, 0, 200), 'WARN');
            return;
        }
        if (!validatePayload($payload)) {
            logMessage('Invalid payload structure: ' . json_encode($payload), 'WARN');
            return;
        }
        $sanitized = sanitizePayload($payload);
        try {
            $repo->insert(
                $sanitized['ph'],
                $sanitized['tds'],
                $sanitized['suhu'],
                $sanitized['status']
            );
            updateMqttHeartbeat();
            logMessage('Inserted: ph=' . $sanitized['ph'] . ' tds=' . $sanitized['tds'] . ' suhu=' . $sanitized['suhu'] . ' status=' . $sanitized['status']);
        } catch (Throwable $e) {
            logMessage('Insert error: ' . $e->getMessage(), 'ERROR');
        }
    }, 0);

    logMessage('Subscribed to topic: ' . $config['topic']);
    $mqtt->loop(true);
} catch (Throwable $e) {
    logMessage('Fatal: ' . $e->getMessage(), 'ERROR');
    exit(1);
}
