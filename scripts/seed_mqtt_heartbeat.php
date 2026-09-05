<?php
/**
 * Set MQTT heartbeat (agar indicator "Aktif" saat testing tanpa subscriber)
 * Jalankan: php scripts/seed_mqtt_heartbeat.php
 */
$file = dirname(__DIR__) . '/database/mqtt_last_seen.txt';
$dir = dirname($file);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}
file_put_contents($file, (string) time());
echo "MQTT heartbeat updated. Dashboard will show MQTT as active for ~2 minutes.\n";
