param()
$ErrorActionPreference='Stop'
$dockerExe='C:\Program Files\Docker\Docker\resources\bin\docker.exe'
$nodeExe=Join-Path $PSScriptRoot 'browser-tools\node_modules\node\bin\node.exe'
$composeFile=Join-Path $PSScriptRoot 'compose.yaml'
Push-Location (Split-Path -Parent $PSScriptRoot)
try {
 Get-Content (Join-Path $PSScriptRoot 'maps-config-tests.php') -Raw | & $dockerExe compose -f $composeFile exec -T web php
 if($LASTEXITCODE -ne 0){throw 'Configuration/CSP checks failed'}
 $policy=& $dockerExe compose -f $composeFile exec -T web cat /tmp/maps-policy-test.json
 if($LASTEXITCODE -ne 0){throw 'Test CSP unavailable'}
 [IO.File]::WriteAllText((Join-Path $PSScriptRoot 'maps-policy-test.json'),$policy)
 foreach($script in @('vendor-tests.cjs','maps-contract-tests.cjs','browser-tests.cjs')){
  & $nodeExe (Join-Path $PSScriptRoot $script)
  if($LASTEXITCODE -ne 0){throw ('Offline check failed: '+$script)}
 }
 powershell.exe -NoProfile -ExecutionPolicy Bypass -File (Join-Path $PSScriptRoot 'Test-Membership.ps1') -Offline
 if($LASTEXITCODE -ne 0){throw 'Offline membership checks failed'}
} finally { Pop-Location }
