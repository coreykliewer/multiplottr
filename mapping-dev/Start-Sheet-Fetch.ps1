$ErrorActionPreference='Stop'
$workspaceRoot=Split-Path -Parent $PSScriptRoot
try {$health=Invoke-RestMethod 'http://127.0.0.1:8081/health' -TimeoutSec 2;if($health.service -eq 'multiplottr-public-sheet-fetch-v1'){Write-Output 'Public-sheet helper already running.';exit 0}}catch{}
$nodePath=(Get-Command node).Source
$scriptPath=Join-Path $PSScriptRoot 'public-sheet-fetch-server.cjs'
$helper=Start-Process -FilePath $nodePath -ArgumentList ('"'+$scriptPath+'"') -WorkingDirectory $workspaceRoot -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $workspaceRoot 'local-test/sheet-fetch-output.log') -RedirectStandardError (Join-Path $workspaceRoot 'local-test/sheet-fetch-error.log')
[IO.File]::WriteAllText((Join-Path $workspaceRoot 'local-test/sheet-fetch.pid'),[string]$helper.Id)
for($attempt=0;$attempt -lt 20;$attempt++){Start-Sleep -Milliseconds 150;if($helper.HasExited){throw 'Public-sheet helper exited; inspect its error log.'};try{$health=Invoke-RestMethod 'http://127.0.0.1:8081/health' -TimeoutSec 1;if($health.service -eq 'multiplottr-public-sheet-fetch-v1'){Write-Output 'Public-sheet helper started on 127.0.0.1:8081.';exit 0}}catch{}}
throw 'Public-sheet helper failed to start. Check local-test/sheet-fetch-error.log (port 8081 may be occupied).'
