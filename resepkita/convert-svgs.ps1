# Convert SVG artboards to PNG
# Requires: ImageMagick or Inkscape installed
# Usage: .\convert-svgs.ps1

$srcDir = "C:\temp\resepkita-design"
$outDir = Join-Path $srcDir "png_output"

# Create output folder
if (!(Test-Path $outDir)) {
    New-Item -Path $outDir -ItemType Directory -Force | Out-Null
    Write-Host "Created output folder: $outDir"
}

# Check for ImageMagick
if (Get-Command magick -ErrorAction SilentlyContinue) {
    Write-Host "Using ImageMagick..."
    Get-ChildItem -Path $srcDir -Filter "*.svg" | ForEach-Object {
        $inPath = $_.FullName
        $outPath = Join-Path $outDir ($_.BaseName + ".png")
        Write-Host "Converting: $($_.Name) -> $($_.BaseName).png"
        magick $inPath -background white -alpha remove -alpha off $outPath
    }
    Write-Host "Done! PNG files in: $outDir"
} 
elseif (Get-Command inkscape -ErrorAction SilentlyContinue) {
    Write-Host "Using Inkscape..."
    Get-ChildItem -Path $srcDir -Filter "*.svg" | ForEach-Object {
        $inPath = $_.FullName
        $outPath = Join-Path $outDir ($_.BaseName + ".png")
        Write-Host "Converting: $($_.Name) -> $($_.BaseName).png"
        inkscape $inPath --export-type=png --export-filename=$outPath
    }
    Write-Host "Done! PNG files in: $outDir"
}
else {
    Write-Host "ERROR: Neither ImageMagick nor Inkscape found."
    Write-Host "Install one of the tools:"
    Write-Host "  - ImageMagick: https://imagemagick.org"
    Write-Host "  - Inkscape: https://inkscape.org"
}