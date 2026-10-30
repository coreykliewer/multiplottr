$ErrorActionPreference='Stop'
$workspaceRoot=Split-Path -Parent $PSScriptRoot
$pidFile=Join-Path $workspaceRoot 'local-test/sheet-fetch.pid'
if(Test-Path -LiteralPath $pidFile){$helperId=[int][IO.File]::ReadAllText($pidFile);$helper=Get-CimInstance Win32_Process -Filter "ProcessId=$helperId";if($helper -and $helper.CommandLine.Contains((Join-Path $PSScriptRoot 'public-sheet-fetch-server.cjs'))){Stop-Process -Id $helperId;Write-Output 'Public-sheet helper stopped.'}}
