$ErrorActionPreference = 'Stop'
$workspace = Split-Path -Parent $PSScriptRoot
$target = Join-Path $PSScriptRoot 'app'
if (Test-Path -LiteralPath (Join-Path $target 'index.php')) { throw 'Local app already exists. Preparation would overwrite local fixes; preserve the app before rebuilding it.' }
New-Item -ItemType Directory -Path $target -Force | Out-Null
# Use a separate working copy. Never mount the live-code workspace as the web root.
Get-ChildItem -LiteralPath $workspace -File | Where-Object {
    $_.Extension -in @('.php','.js','.css','.html','.htm','.png','.gif','.jpg','.jpeg','.ico','.svg','.txt') -and
    $_.Name -notmatch '^(phpinfo|info|backup|test)\.|error_log|activity\.log'
} | Copy-Item -Destination $target
foreach ($directory in @('db','scripts','images','css','fonts','includes','class','custom','datafiles','PHPMailer')) {
    $source = Join-Path $workspace $directory
    if (Test-Path -LiteralPath $source) { Copy-Item -LiteralPath $source -Destination $target -Recurse -Force }
}
$encoding = New-Object System.Text.UTF8Encoding($false)
Get-ChildItem -LiteralPath $target -Recurse -File | Where-Object { $_.Extension -in @('.php','.js','.html','.htm','.css') } | ForEach-Object {
    $content = [IO.File]::ReadAllText($_.FullName)
    # Route application links and requests to local endpoints, including subdomains.
    $content = $content -replace '(?i)(?:https?:)?//(?:[a-z0-9-]+\.)*(?:multiplottr\.com|multiplotter\.com|onlinecustommaps\.com)', 'http://localhost:8080'
    if ($_.DirectoryName -eq (Join-Path $target 'db')) {
        $content = [regex]::Replace($content, '(?s)\$dbconfig\s*=\s*array\s*\(.*?\);', '$dbconfig = array(); // REDACTED: configure privately')
    }
    [IO.File]::WriteAllText($_.FullName, $content, $encoding)
}
Write-Output 'Prepared separate local application copy.'
