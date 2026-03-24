$ErrorActionPreference = "Stop"

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptDir
$buildDir = Join-Path $projectRoot "build"
$srcDir = Join-Path $buildDir "swisseph_src"

$arch = $env:PROCESSOR_ARCHITECTURE
if ($arch -eq "AMD64") { $arch = "x64" }

$outDir = Join-Path $projectRoot "libs\\windows-$arch"
$outFile = Join-Path $outDir "swe.dll"

Write-Host "======================================"
Write-Host "Swiss Ephemeris Library Builder (Windows)"
Write-Host "======================================"
Write-Host ""

New-Item -ItemType Directory -Path $buildDir -Force | Out-Null
New-Item -ItemType Directory -Path $outDir -Force | Out-Null
Set-Location $buildDir

$needsClone = $true
if (Test-Path $srcDir) {
    $headCheck = Start-Process -FilePath git -ArgumentList @("-C", $srcDir, "rev-parse", "--verify", "HEAD") -NoNewWindow -Wait -PassThru
    if ($headCheck.ExitCode -eq 0) {
        $needsClone = $false
    } else {
        Write-Host "Existing source checkout is invalid. Recreating..."
        Remove-Item -Recurse -Force $srcDir
    }
}

if ($needsClone) {
    Write-Host "Step 1: Downloading Swiss Ephemeris source..."
    Write-Host "Source: https://github.com/aloistr/swisseph (latest commit)"
    git clone --depth 1 https://github.com/aloistr/swisseph.git swisseph_src
    if (-Not (Test-Path $srcDir)) {
        Write-Error "Failed to clone repository. If you see 'RPC failed' or 'HTTP/2 stream' errors, try: git config --global http.version HTTP/1.1"
        exit 1
    }
} else {
    Write-Host "Step 1: Updating existing source..."
    $shallowLock = Join-Path $srcDir ".git\\shallow.lock"
    if (Test-Path $shallowLock) {
        Remove-Item -Force $shallowLock
    }
    git -C $srcDir fetch --depth 1 origin master
    git -C $srcDir reset --hard FETCH_HEAD
}

Set-Location $srcDir

Write-Host ""
Write-Host "Current Swiss Ephemeris Version Info:"
git log -1 --format="  Commit: %h%n  Date:   %ad%n  Msg:    %s" --date=short
Write-Host ""

$cSrcDir = $srcDir
if (Test-Path (Join-Path $srcDir "src")) { $cSrcDir = Join-Path $srcDir "src" }
elseif (Test-Path (Join-Path $srcDir "src_c")) { $cSrcDir = Join-Path $srcDir "src_c" }

Write-Host "Step 2: Compiling Swiss Ephemeris library..."
Write-Host "Source directory: $cSrcDir"

$gcc = Get-Command gcc -ErrorAction SilentlyContinue
if (-Not $gcc) {
    Write-Error "gcc not found. Install MSYS2/MinGW (mingw-w64) and ensure gcc is on PATH."
    exit 1
}

$cFiles = @("swedate.c","swehouse.c","swejpl.c","swemmoon.c","swemplan.c","sweph.c","swephlib.c","swecl.c","swehel.c")
$oFiles = @()
foreach ($c in $cFiles) {
    $file = Join-Path $cSrcDir $c
    if (Test-Path $file) {
        $oname = [IO.Path]::GetFileNameWithoutExtension($c) + ".o"
        Write-Host "Compiling $c..."
        gcc -c -O3 -o $oname $file
        $oFiles += $oname
    } else {
        Write-Host "Warning: File $file not found, skipping."
    }
}

Write-Host "Creating shared library..."
gcc -shared -o swe.dll $oFiles

Copy-Item -Force "swe.dll" (Join-Path $buildDir "swe.dll")
Copy-Item -Force "swe.dll" $outFile

Write-Host ""
Write-Host "======================================"
Write-Host "Compilation successful!"
Write-Host "======================================"
Write-Host ""
Write-Host "Library created:"
Write-Host "  $buildDir\\swe.dll"
Write-Host "  $outFile"
Write-Host ""
Write-Host "Done!"
