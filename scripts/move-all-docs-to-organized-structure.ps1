# move-all-docs-to-organized-structure.ps1
# Script to find and move ALL documentation files to organized docs folder

Write-Host "🔍 Scanning for documentation files in project root..." -ForegroundColor Cyan

# Create directory structure
$dirs = @(
    "docs/features/period-code-deduplication",
    "docs/features/question-import", 
    "docs/features/reward-system",
    "docs/features/firebase",
    "docs/features/documentation-portal",
    "docs/project",
    "docs/development", 
    "docs/user-guides",
    "docs/archive"
)

foreach ($dir in $dirs) {
    if (!(Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force
        Write-Host "✅ Created: $dir" -ForegroundColor Green
    }
}

# Find ALL .md files in root directory
$rootMdFiles = Get-ChildItem -Path "." -Filter "*.md" -File | Where-Object { $_.Directory.Name -eq (Get-Location).Path.Split('\')[-1] }

Write-Host "📄 Found $($rootMdFiles.Count) markdown files in root directory:" -ForegroundColor Yellow
foreach ($file in $rootMdFiles) {
    Write-Host "  - $($file.Name)" -ForegroundColor Gray
}

# Categorize and move files
Write-Host "`n📦 Categorizing and moving files..." -ForegroundColor Blue

# Firebase files
$firebaseFiles = $rootMdFiles | Where-Object { $_.Name -like "*FIREBASE*" -or $_.Name -like "*firebase*" }
foreach ($file in $firebaseFiles) {
    Move-Item $file.FullName "docs/features/firebase/" -Force
    Write-Host "🔥 $($file.Name) → docs/features/firebase/" -ForegroundColor Green
}

# Period Code Deduplication files
$periodCodeFiles = $rootMdFiles | Where-Object { 
    $_.Name -like "*PERIOD*" -or 
    $_.Name -like "*QUICK_START*" -or 
    $_.Name -like "*NEXT_STEPS*" -or
    $_.Name -like "*DOCUMENTATION_INDEX*" -or
    $_.Name -like "*ARCHITECTURE*" -or
    $_.Name -like "*IMPLEMENTATION*" -or
    $_.Name -like "*VERIFICATION*" -or
    $_.Name -like "*VISUAL_SUMMARY*"
}
foreach ($file in $periodCodeFiles) {
    if (Test-Path $file.FullName) {
        # Check if file still exists (not already moved)
        Move-Item $file.FullName "docs/features/period-code-deduplication/" -Force
        Write-Host "⚙️ $($file.Name) → docs/features/period-code-deduplication/" -ForegroundColor Green
    }
}

# Reward System files
$rewardFiles = $rootMdFiles | Where-Object { $_.Name -like "*REWARD*" -or $_.Name -like "*reward*" }
foreach ($file in $rewardFiles) {
    if (Test-Path $file.FullName) {
        Move-Item $file.FullName "docs/features/reward-system/" -Force
        Write-Host "🏆 $($file.Name) → docs/features/reward-system/" -ForegroundColor Green
    }
}

# Question Import files
$questionFiles = $rootMdFiles | Where-Object { $_.Name -like "*QUESTION*" -or $_.Name -like "*IMPORT*" }
foreach ($file in $questionFiles) {
    if (Test-Path $file.FullName) {
        Move-Item $file.FullName "docs/features/question-import/" -Force
        Write-Host "📝 $($file.Name) → docs/features/question-import/" -ForegroundColor Green
    }
}

# Documentation Portal files
$docPortalFiles = $rootMdFiles | Where-Object { $_.Name -like "*DOCUMENTATION*" -and $_.Name -like "*PORTAL*" }
foreach ($file in $docPortalFiles) {
    if (Test-Path $file.FullName) {
        Move-Item $file.FullName "docs/features/documentation-portal/" -Force
        Write-Host "📚 $($file.Name) → docs/features/documentation-portal/" -ForegroundColor Green
    }
}

# Any remaining .md files go to project folder
$remainingFiles = Get-ChildItem -Path "." -Filter "*.md" -File | Where-Object { $_.Directory.Name -eq (Get-Location).Path.Split('\')[-1] }
foreach ($file in $remainingFiles) {
    Move-Item $file.FullName "docs/project/" -Force
    Write-Host "📋 $($file.Name) → docs/project/" -ForegroundColor Green
}

Write-Host "`n✅ All documentation files moved to organized structure!" -ForegroundColor Green
Write-Host "📖 Check docs/ folder for organized documentation" -ForegroundColor Cyan

# Show final structure
Write-Host "`n📁 Final docs structure:" -ForegroundColor Yellow
Get-ChildItem -Path "docs" -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Replace((Get-Location).Path + "\", "")
    Write-Host "  $relativePath" -ForegroundColor Gray
}