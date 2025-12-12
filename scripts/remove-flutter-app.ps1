# remove-flutter-app.ps1
# Script to remove flutter_app folder and all Flutter-related files

Write-Host "🗑️ Removing Flutter app folder and related files..." -ForegroundColor Red

# Remove flutter_app folder completely
if (Test-Path "flutter_app") {
    Write-Host "📁 Removing flutter_app folder..." -ForegroundColor Yellow
    Remove-Item -Path "flutter_app" -Recurse -Force
    Write-Host "✅ flutter_app folder deleted" -ForegroundColor Green
}
else {
    Write-Host "⚠️ flutter_app folder not found" -ForegroundColor Yellow
}

# Remove any Flutter-related files in root directory
$flutterFiles = Get-ChildItem -Path "." -Filter "*flutter*" -File
if ($flutterFiles.Count -gt 0) {
    Write-Host "📄 Found $($flutterFiles.Count) Flutter-related files in root:" -ForegroundColor Yellow
    foreach ($file in $flutterFiles) {
        Write-Host "  - $($file.Name)" -ForegroundColor Gray
        Remove-Item $file.FullName -Force
        Write-Host "  ✅ Deleted $($file.Name)" -ForegroundColor Green
    }
}
else {
    Write-Host "✅ No Flutter-related files found in root directory" -ForegroundColor Green
}

# Remove any Flutter-related documentation from docs folder
$flutterDocsFiles = Get-ChildItem -Path "docs" -Filter "*flutter*" -File -Recurse -ErrorAction SilentlyContinue
if ($flutterDocsFiles.Count -gt 0) {
    Write-Host "📚 Found $($flutterDocsFiles.Count) Flutter documentation files:" -ForegroundColor Yellow
    foreach ($file in $flutterDocsFiles) {
        $relativePath = $file.FullName.Replace((Get-Location).Path + "\", "")
        Write-Host "  - $relativePath" -ForegroundColor Gray
        Remove-Item $file.FullName -Force
        Write-Host "  ✅ Deleted $relativePath" -ForegroundColor Green
    }
}
else {
    Write-Host "✅ No Flutter documentation files found" -ForegroundColor Green
}

# Check for any remaining Flutter references
Write-Host "`n🔍 Checking for any remaining Flutter references..." -ForegroundColor Cyan

$remainingFlutterFiles = Get-ChildItem -Path "." -Recurse -File | Where-Object { 
    $_.Name -like "*flutter*" -or 
    $_.Name -like "*Flutter*" -or
    $_.Name -like "*FLUTTER*"
} -ErrorAction SilentlyContinue

if ($remainingFlutterFiles.Count -gt 0) {
    Write-Host "⚠️ Found $($remainingFlutterFiles.Count) remaining Flutter-related files:" -ForegroundColor Yellow
    foreach ($file in $remainingFlutterFiles) {
        $relativePath = $file.FullName.Replace((Get-Location).Path + "\", "")
        Write-Host "  - $relativePath" -ForegroundColor Gray
    }
    Write-Host "❓ Do you want to delete these as well? (Review them first)" -ForegroundColor Yellow
}
else {
    Write-Host "✅ No remaining Flutter references found" -ForegroundColor Green
}

Write-Host "`n🎉 Flutter app cleanup complete!" -ForegroundColor Green
Write-Host "📋 Summary:" -ForegroundColor Cyan
Write-Host "  - flutter_app/ folder removed" -ForegroundColor White
Write-Host "  - Flutter-related files in root removed" -ForegroundColor White
Write-Host "  - Flutter documentation files removed" -ForegroundColor White