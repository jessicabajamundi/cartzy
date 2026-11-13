# Bump patch version and build number in pubspec.yaml
$pubspecPath = Join-Path $PSScriptRoot "pubspec.yaml"
$content = Get-Content $pubspecPath -Raw
if ($content -match 'version:\s*(\d+)\.(\d+)\.(\d+)\+(\d+)') {
    $major = [int]$matches[1]
    $minor = [int]$matches[2]
    $patch = [int]$matches[3] + 1
    $build = [int]$matches[4] + 1
    $newVersion = "version: $major.$minor.$patch+$build"
    $newContent = $content -replace 'version:\s*\d+\.\d+\.\d+\+\d+', $newVersion
    Set-Content -Path $pubspecPath -Value $newContent -NoNewline
    Write-Host "Successfully bumped mobile version to v$major.$minor.$patch+$build" -ForegroundColor Green
} else {
    Write-Error "Could not parse version in pubspec.yaml"
}

