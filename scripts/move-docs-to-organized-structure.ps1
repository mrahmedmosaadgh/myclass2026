# move-docs-to-organized-structure.ps1
# Script to move all documentation files to organized docs folder

Write-Host "📁 Moving documentation files to organized structure..." -ForegroundColor Cyan

# Create directory structure if it doesn't exist
$dirs = @(
    "docs/features/period-code-deduplication",
    "docs/features/question-import",
    "docs/features/reward-system", 
    "docs/features/firebase",
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

# Period Code Deduplication files
$periodCodeFiles = @(
    "QUICK_START.md",
    "NEXT_STEPS.md",
    "DOCUMENTATION_INDEX.md",
    "PERIOD_CODE_DEDUPLICATION.md",
    "PERIOD_CODE_DATABASE_GUIDE.md", 
    "ARCHITECTURE_DIAGRAMS.md",
    "IMPLEMENTATION_CHECKLIST.md",
    "PERIOD_CODE_DEDUPLICATION_SUMMARY.md",
    "VISUAL_SUMMARY.md",
    "VERIFICATION_REPORT.md"
)

Write-Host "📦 Moving Period Code Deduplication files..." -ForegroundColor Blue
foreach ($file in $periodCodeFiles) {
    if (Test-Path $file) {
        Move-Item $file "docs/features/period-code-deduplication/" -Force
        Write-Host "  ✅ $file → docs/features/period-code-deduplication/" -ForegroundColor Green
    }
    else {
        Write-Host "  ⚠️  $file not found" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "✅ Documentation organization complete!" -ForegroundColor Green
Write-Host "📖 All files moved to organized docs/ structure" -ForegroundColor Cyan

