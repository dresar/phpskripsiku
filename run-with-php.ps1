# Jalankan perintah PHP/Composer dengan PATH yang benar (XAMPP)
# Usage: .\run-with-php.ps1 composer install
#        .\run-with-php.ps1 php -v
#        .\run-with-php.ps1 php -S localhost:8000 router.php

$phpPath = "C:\xampp\php"
$composerPath = "C:\ProgramData\ComposerSetup\bin"

if (-not (Test-Path "$phpPath\php.exe")) {
    Write-Host "PHP tidak ditemukan di $phpPath. Sesuaikan path di skrip ini." -ForegroundColor Red
    exit 1
}

$env:Path = "$phpPath;$composerPath;" + $env:Path

if ($args.Count -eq 0) {
    Write-Host "Contoh: .\run-with-php.ps1 composer install" -ForegroundColor Yellow
    Write-Host "        .\run-with-php.ps1 php -S localhost:8000 router.php" -ForegroundColor Yellow
    & "$phpPath\php.exe" -v
    exit 0
}

$cmd = $args[0]
$cmdArgs = $args[1..($args.Count - 1)]
if ($cmd -eq "php") {
    & "$phpPath\php.exe" $cmdArgs
} else {
    & $cmd $cmdArgs
}
