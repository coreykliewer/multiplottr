<?php
// This router is used only by the isolated local copy.
require_once '/config/maps-config.php';
$localMaps = local_maps_configuration('/config/local', $_SERVER['HTTP_HOST'] ?? '');
header('Content-Security-Policy: ' . local_maps_csp($localMaps['enabled']));
header('Cache-Control: no-store');
header('Permissions-Policy: geolocation=(), payment=()');
header('X-Robots-Tag: noindex, nofollow');
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($path === '/__local_maps_config') {
    header('Content-Type: application/json');
    echo json_encode($localMaps, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    return;
}
if ($path === '/__local_health') {
    header('Content-Type: application/json');
    $db = new mysqli('db', 'local_test', 'local-only-test', '[REDACTED_ACCOUNT]');
    $result = $db->query('SHOW TABLES');
    echo json_encode(['environment' => 'local', 'php' => PHP_VERSION, 'tables' => $result->num_rows]);
    return;
}
// Quarantine legacy auth and persistence entrypoints. Only the local service owns membership/maps.
require_once '/config/local-membership.php';
if (strpos($path, '/__local_membership/') === 0) {
    if (!in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost:8080','127.0.0.1:8080'], true)) LocalMembership::respond(403,['error'=>'Local host required']);
    (new LocalMembership())->dispatch($path);
}
if (strpos($path, '/datafiles/') === 0) LocalMembership::respond(403,['error'=>'Legacy data files are disabled locally']);
if ($path === '/' || $path === '/index.php') {
    foreach (array_keys($_GET) as $parameter) if (!in_array($parameter,['map_id','new'],true)) LocalMembership::respond(403,['error'=>'Legacy query data sources are disabled locally']);
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') LocalMembership::respond(403,['error'=>'Legacy writes are disabled; use the local membership service']);
    LocalMembership::session();$membership = new LocalMembership();$memberUser = $membership->user();$memberMap=null;
    if (isset($_GET['map_id'])) {
        $memberMap=$membership->readMap($_GET['map_id'],$memberUser);
        if (!$memberMap) LocalMembership::respond(404,['error'=>'Map not found']);
        unset($_GET['map_id']); // Never query the imported map tables.
    }
    $localMembershipBootstrap=['user'=>$memberUser,'csrf'=>$_SESSION['local_csrf'],'map'=>$memberMap];
    session_write_close();
} elseif (strtolower(pathinfo($path,PATHINFO_EXTENSION)) === 'php') {
    LocalMembership::respond(403,['error'=>'Legacy PHP entrypoint disabled in local testing']);
}
if (preg_match('#(^|/)(\.|db/|Connections/|private-backups/)|\.(sql|gz|log|ini|bak|zip)$#i', $path)) {
    http_response_code(403);
    exit('Unavailable in local testing.');
}
$file = realpath('/app' . $path);
if ($path === '/') $file = '/app/index.php';
if (!$file || strpos($file, '/app/') !== 0 || !is_file($file)) {
    http_response_code(404);
    exit('Local file not found.');
}
if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'php') return false;
chdir(dirname($file));
require $file;
