<?php
// Local-only configuration. This file and the key directory are outside the web root.
function local_maps_configuration($directory, $host) {
    $off = ['enabled' => false, 'reason' => 'disabled', 'apiKey' => "[REDACTED_CONFIG_VALUE]"];
    if (!in_array($host, ['localhost:8080', '127.0.0.1:8080'], true)) {
        $off['reason'] = 'local_host_required';
        return $off;
    }
    $file = $directory . '/maps.local.json';
    if (!is_file($file)) return $off;
    $raw = @file_get_contents($file);
    $config = is_string($raw) && strlen($raw) <= 4096 ? json_decode($raw, true) : null;
    if (!is_array($config)) { $off['reason'] = 'invalid_configuration'; return $off; }
    if (($config['enabled'] ?? false) !== true) return $off;
    if (($config['allowBrowserNetwork'] ?? false) !== true) {
        $off['reason'] = 'browser_network_not_authorized';
        return $off;
    }
    $key = $config['apiKey'] ?? '';
    if (!is_string($key) || !preg_match('/^AIza[A-Za-z0-9_-]{35}$/D', $key)) {
        $off['reason'] = 'development_key_required';
        return $off;
    }
    $fingerprints = json_decode(@file_get_contents($directory . '/blocked-key-fingerprints.json') ?: '[]', true);
    // Fail closed if the production-key exclusion list cannot be read.
    if (!is_array($fingerprints) || !$fingerprints || count(array_filter($fingerprints, function ($value) { return is_string($value) && preg_match('/^[a-f0-9]{64}$/D', $value); })) !== count($fingerprints) || in_array(hash('sha256', $key), $fingerprints, true)) {
        $off['reason'] = 'development_key_required';
        return $off;
    }
    return ['enabled' => true, 'reason' => 'development_mode', 'apiKey' => $key];
}
function local_maps_csp($enabled) {
    $base = "default-src 'self' data: blob:; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; connect-src 'self'; form-action 'self'; frame-src 'self'; base-uri 'self'";
    if (!$enabled) return $base;
    // Only the browser receives these exact-host exceptions. No Docker network changes.
    return "default-src 'self' data: blob:; " .
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://maps.googleapis.com https://maps.gstatic.com; " .
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
        "connect-src 'self' https://maps.googleapis.com https://mapsresources-pa.googleapis.com https://maps.gstatic.com; " .
        "img-src 'self' data: blob: https://maps.googleapis.com https://mapsresources-pa.googleapis.com https://maps.gstatic.com; " .
        "font-src 'self' https://fonts.gstatic.com; " .
        "form-action 'self'; frame-src 'self'; base-uri 'self'";
}
