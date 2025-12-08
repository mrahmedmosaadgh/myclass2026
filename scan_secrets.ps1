# scan_secrets.ps1
# Scans the project for common secret patterns

$patterns = @(
    "ghp_[a-zA-Z0-9]{36}",          # GitHub Personal Access Token
    "github_pat_[a-zA-Z0-9_]{82}",  # GitHub Fine-grained PAT
    "hf_[a-zA-Z0-9]{34}",           # Hugging Face Token
    "sk-[a-zA-Z0-9]{32,}",          # OpenAI/DeepSeek Key
    "AIza[0-9A-Za-z-_]{35}",        # Google API Key
    "eyJ[a-zA-Z0-9_-]{10,}",        # JWT / Firebase Credentials (partial check)
    "AKIA[0-9A-Z]{16}",             # AWS Access Key
    "-----BEGIN PRIVATE KEY-----"   # Private Keys
)

$excludes = @("node_modules", "vendor", ".git", "public/build", "storage")

Write-Host "🔍 Scanning for secrets..." -ForegroundColor Cyan

$files = Get-ChildItem -Recurse -File | Where-Object {
    $path = $_.FullName
    $shouldSkip = $false
    foreach ($exclude in $excludes) {
        if ($path -like "*\$exclude\*") {
            $shouldSkip = $true
            break
        }
    }
    -not $shouldSkip
}

$found = $false

foreach ($file in $files) {
    try {
        $content = Get-Content $file.FullName -ErrorAction SilentlyContinue
        $lineNum = 0
        foreach ($line in $content) {
            $lineNum++
            foreach ($pattern in $patterns) {
                if ($line -match $pattern) {
                    Write-Host "⚠️  SECRET FOUND in $($file.Name):$lineNum" -ForegroundColor Red
                    Write-Host "   Match: $($Matches[0])" -ForegroundColor Yellow
                    $found = $true
                }
            }
        }
    } catch {
        # Ignore read errors (binary files etc)
    }
}

if (-not $found) {
    Write-Host "✅ No common secrets found!" -ForegroundColor Green
} else {
    Write-Host "❌ Secrets found! Please remove them before committing." -ForegroundColor Red
}
