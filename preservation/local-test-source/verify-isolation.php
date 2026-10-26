<?php
// Inspect isolation locally without requesting any remote site.
$external = @fsockopen('[REDACTED_IP]', 443, $errno, $error, 2);
echo 'External TCP blocked: ' . ($external === false ? 'yes' : 'NO') . PHP_EOL;
if ($external) fclose($external);
echo 'mail() disabled: ' . (!function_exists('mail') ? 'yes' : 'NO') . PHP_EOL;
