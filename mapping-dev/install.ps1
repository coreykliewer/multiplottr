$ErrorActionPreference='Stop'
$workspaceRoot=Split-Path -Parent $PSScriptRoot
$runtimeTarget=Join-Path $workspaceRoot 'local-test/app/mapping-dev'
New-Item -ItemType Directory -Path $runtimeTarget -Force | Out-Null
foreach($name in @('document.js','fixtures.js','document-service.php','index.html','workspace.css','workspace.js','sidebar.js','description-editor.js','plot-queue.js')){Copy-Item -LiteralPath (Join-Path $PSScriptRoot $name) -Destination (Join-Path $runtimeTarget $name) -Force}
New-Item -ItemType Directory -Path (Join-Path $runtimeTarget 'assets') -Force | Out-Null
Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'assets/star.svg') -Destination (Join-Path $runtimeTarget 'assets/star.svg') -Force
Write-Output 'Installed only the separate local mapping workspace assets. Existing application snapshots unchanged.'
