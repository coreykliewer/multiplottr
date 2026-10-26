<?php
require '/config/maps-config.php';
$dir = '/tmp/maps-config-probe-' . bin2hex(random_bytes(6));
mkdir($dir);
$checks = 0;
function check_maps($value, $message) {
    global $checks;
    if (!$value) throw new RuntimeException($message);
    $checks++;
}
function sample_maps($config, $host = 'localhost:8080') {
    global $dir;
    file_put_contents($dir . '/maps.local.json', is_string($config) ? $config : json_encode($config));
    return local_maps_configuration($dir, $host);
}
try {
    check_maps(!local_maps_configuration('/config/local', 'localhost:8080')['enabled'], 'Offline tests require the active local mode to remain disabled');
    $fake = 'AIza' . str_repeat('A', 35); // Synthetic format probe; never sent to Google.
    file_put_contents($dir . '/blocked-key-fingerprints.json', json_encode([hash('sha256', 'blocked') ]));
    check_maps(!local_maps_configuration($dir, 'localhost:8080')['enabled'], 'Missing file must disable Maps');
    check_maps(!sample_maps('{')['enabled'], 'Malformed JSON must disable Maps');
    check_maps(!sample_maps(['enabled' => false, 'allowBrowserNetwork' => true, 'apiKey' => $fake])['enabled'], 'Mode gate');
    $result=sample_maps(['enabled' => true, 'allowBrowserNetwork' => false, 'apiKey' => $fake]);
    check_maps(!$result['enabled'] && $result['apiKey'] === '', 'Network gate and key redaction');
    check_maps(!sample_maps(['enabled' => 'true', 'allowBrowserNetwork' => true, 'apiKey' => $fake])['enabled'], 'Strict Boolean gate');
    check_maps(!sample_maps(['enabled' => true, 'allowBrowserNetwork' => true, 'apiKey' => "[REDACTED_CONFIG_VALUE]"])['enabled'], 'Missing key');
    $on=['enabled' => true, 'allowBrowserNetwork' => true, 'apiKey' => $fake];
    check_maps(sample_maps($on)['enabled'], 'Valid synthetic configuration should prepare Maps policy');
    check_maps(sample_maps($on, '127.0.0.1:8080')['enabled'], 'Second local host');
    check_maps(!sample_maps($on, 'example.invalid:8080')['enabled'], 'Host boundary');
    file_put_contents($dir . '/blocked-key-fingerprints.json', json_encode([hash('sha256', $fake)]));
    check_maps(!sample_maps($on)['enabled'], 'Known embedded keys must be rejected');
    unlink($dir . '/blocked-key-fingerprints.json');
    check_maps(!sample_maps($on)['enabled'], 'Missing fingerprint list must fail closed');
    $off=local_maps_csp(false);$maps=local_maps_csp(true);
    check_maps(strpos($off, 'https://') === false, 'Offline CSP has no external source');
    check_maps(strpos($maps, '*') === false && strpos($maps, 'form-action \'self\'') !== false && strpos($maps, 'frame-src \'self\'') !== false, 'Maps CSP remains exact-host only');
    check_maps(strpos($maps,'https://mapsresources-pa.googleapis.com')!==false && strpos($maps,'https://maps.gstatic.com')!==false, 'Maps assets in scoped policy');
    file_put_contents('/tmp/maps-policy-test.json', json_encode(['csp' => $maps]));
    echo $checks . " configuration/CSP checks passed. No active configuration was changed.\n";
} finally {
    foreach (['maps.local.json', 'blocked-key-fingerprints.json'] as $file) if (is_file($dir . '/' . $file)) unlink($dir . '/' . $file);
    rmdir($dir);
}
