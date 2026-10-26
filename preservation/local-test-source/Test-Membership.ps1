param([switch]$Offline)
$ErrorActionPreference='Stop'
$dockerExe='C:\Program Files\Docker\Docker\resources\bin\docker.exe'
$nodeExe=Join-Path $PSScriptRoot 'browser-tools\node_modules\node\bin\node.exe'
$composeFile=Join-Path $PSScriptRoot 'compose.yaml'
$metadata=Join-Path $PSScriptRoot 'membership-fixtures.json'
if(Test-Path -LiteralPath $metadata){throw 'Existing membership fixture metadata requires cleanup before another run.'}
Push-Location (Split-Path -Parent $PSScriptRoot)
try {
    if($Offline){ & $nodeExe (Join-Path $PSScriptRoot 'membership-browser-tests.cjs') --offline }
    else { & $nodeExe (Join-Path $PSScriptRoot 'membership-browser-tests.cjs') }
    if($LASTEXITCODE -ne 0){throw 'Membership checks failed; see the sanitized results file.'}
} finally {
    if(Test-Path -LiteralPath $metadata){
        $encoded=[Convert]::ToBase64String([Text.Encoding]::UTF8.GetBytes([IO.File]::ReadAllText($metadata)))
        Get-Content (Join-Path $PSScriptRoot 'membership-cleanup.php') -Raw | & $dockerExe compose -f $composeFile exec -T -e ('LOCAL_MEMBER_FIXTURES_B64='+$encoded) web php
        if($LASTEXITCODE -ne 0){throw 'Membership fixture cleanup failed; metadata retained.'}
        Remove-Item -LiteralPath $metadata
    }
    Pop-Location
}
