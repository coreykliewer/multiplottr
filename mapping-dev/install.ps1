$ErrorActionPreference='Stop'
$workspaceRoot=Split-Path -Parent $PSScriptRoot
$runtimeTarget=Join-Path $workspaceRoot 'local-test/app/mapping-dev'
New-Item -ItemType Directory -Path $runtimeTarget -Force | Out-Null
foreach($name in @('document.js','fixtures.js','document-service.php','index.html','workspace.css','workspace.js','sidebar.js','description-editor.js','plot-queue.js','spreadsheet-input.js','marker-csv.js','public-sheet.js','sheet-controller.js')){Copy-Item -LiteralPath (Join-Path $PSScriptRoot $name) -Destination (Join-Path $runtimeTarget $name) -Force}
New-Item -ItemType Directory -Path (Join-Path $runtimeTarget 'assets') -Force | Out-Null
Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'assets/star.svg') -Destination (Join-Path $runtimeTarget 'assets/star.svg') -Force
Write-Output 'Installed only the separate local mapping workspace assets. Existing application snapshots unchanged.'

& node (Join-Path $PSScriptRoot 'install-sheet-csp.cjs')
if ($LASTEXITCODE -ne 0) { throw 'Public-sheet CSP installation failed' }

& powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'Stop-Sheet-Fetch.ps1')
& powershell -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'Start-Sheet-Fetch.ps1')
if ($LASTEXITCODE -ne 0) { throw 'Public-sheet helper startup failed' }

& node (Join-Path $PSScriptRoot 'install-helper-startup.cjs')
if ($LASTEXITCODE -ne 0) { throw 'Local helper startup integration failed' }
