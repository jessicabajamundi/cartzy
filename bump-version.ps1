# Bump patch version and build number in pubspec.yaml and app_update_service.dart
$pubspecPath = Join-Path $PSScriptRoot "pubspec.yaml"
$updateServicePath = Join-Path $PSScriptRoot "lib\services\app_update_service.dart"

$content = Get-Content $pubspecPath -Raw
if ($content -match 'version:\s*(\d+)\.(\d+)\.(\d+)\+(\d+)') {
    $major = [int]$matches[1]
    $minor = [int]$matches[2]
    $patch = [int]$matches[3] + 1
    $build = [int]$matches[4] + 1
    $newVersion = "version: $major.$minor.$patch+$build"
    $newContent = $content -replace 'version:\s*\d+\.\d+\.\d+\+\d+', $newVersion
    Set-Content -Path $pubspecPath -Value $newContent -NoNewline

    # Also synchronize fallback version in AppUpdateService
    if (Test-Path $updateServicePath) {
        $serviceContent = Get-Content $updateServicePath -Raw
        $serviceContent = $serviceContent -replace "currentAppVersion = '[^']+';", "currentAppVersion = '$major.$minor.$patch';"
        $serviceContent = $serviceContent -replace "defaultReleasePage = '[^']+';", "defaultReleasePage = 'https://github.com/jessicabajamundi/cartzy/releases/tag/v$major.$minor.$patch';"
        Set-Content -Path $updateServicePath -Value $serviceContent -NoNewline
    }

    Write-Host "Successfully bumped mobile version to v$major.$minor.$patch+$build" -ForegroundColor Green
} else {
    Write-Error "Could not parse version in pubspec.yaml"
}

