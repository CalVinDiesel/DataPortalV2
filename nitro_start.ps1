# Nitro-Cluster Ghost Automation
Write-Host "🚀 Launching Nitro-Cluster Server Farm (Ports 9001-9004)..." -ForegroundColor Cyan

$Ports = 9001..9004
foreach ($Port in $Ports) {
    if (Get-NetTCPConnection -LocalPort $Port -ErrorAction SilentlyContinue) {
        Write-Host "⚠️ Port $Port is already busy. Skipping." -ForegroundColor Yellow
    }
    else {
        Start-Process php -ArgumentList "-S 127.0.0.1:$Port -t public" -WindowStyle Minimized
        Write-Host "✅ Ghost Worker started on Port $Port" -ForegroundColor Green
    }
}

Write-Host "✨ Nitro-Cluster is ACTIVE and HIDDEN in the background." -ForegroundColor Green
Write-Host "To stop them later, run: Get-Process php | Stop-Process" -ForegroundColor Gray
