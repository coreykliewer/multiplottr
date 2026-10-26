param([switch]$NativePreview)
$ErrorActionPreference='Stop'
$dockerExe='C:\Program Files\Docker\Docker\resources\bin\docker.exe'
$nodeExe=Join-Path $PSScriptRoot 'browser-tools\node_modules\node\bin\node.exe'
$composeFile=Join-Path $PSScriptRoot 'compose.yaml'
$fixtureFile=Join-Path $PSScriptRoot 'fixture.json'
if(Test-Path -LiteralPath $fixtureFile){throw 'Existing fixture metadata found; resolve it before creating another local test account.'}
$mode=[IO.File]::ReadAllText((Join-Path $PSScriptRoot 'config\maps.local.json')) | ConvertFrom-Json
if($mode.enabled -ne $true -or $mode.allowBrowserNetwork -ne $true){throw 'Live tests require the explicitly enabled development mode.'}
$mode=$null
Push-Location (Split-Path -Parent $PSScriptRoot)
$fixtureUser=$null
try {
    $fixtureJson=Get-Content (Join-Path $PSScriptRoot 'fixture-setup.php') -Raw | & $dockerExe compose -f $composeFile exec -T web php
    if($LASTEXITCODE -ne 0){throw 'Local test account creation failed'}
    $fixtureUser=[int](($fixtureJson | ConvertFrom-Json).userId)
    if($fixtureUser -le 0){throw 'Invalid local test account id'}
    [IO.File]::WriteAllText($fixtureFile,$fixtureJson)
    & $nodeExe (Join-Path $PSScriptRoot 'maps-live-tests.cjs')
    if($LASTEXITCODE -ne 0){throw 'Real Maps tests failed; inspect sanitized maps-live-results.json'}
    if($NativePreview){
        & $nodeExe (Join-Path $PSScriptRoot 'maps-native-print-test.cjs')
        if($LASTEXITCODE -ne 0){throw 'Native preview test failed'}
        Write-Output 'Inspect maps-native-print-preview.png visually. No print job is sent.'
    }
} finally {
    if($fixtureUser){
        Get-Content (Join-Path $PSScriptRoot 'fixture-cleanup.php') -Raw | & $dockerExe compose -f $composeFile exec -T -e ('LOCAL_FIXTURE_USER='+$fixtureUser) web php
        if($LASTEXITCODE -ne 0){Write-Error 'Local test account cleanup failed'}
    }
    if(Test-Path -LiteralPath $fixtureFile){Remove-Item -LiteralPath $fixtureFile}
    Pop-Location
}
