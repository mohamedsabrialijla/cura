$ErrorActionPreference = "Stop"
$ff = "C:\Users\Mohamed\AppData\Local\Microsoft\WinGet\Packages\Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe\ffmpeg-9.0-full_build\bin\ffmpeg.exe"
$edit = "f:\cura\public\site\assets\video\_edit"
$src = "f:\cura\public\site\assets\video\misbar-training.orig.mp4"
$out = "f:\cura\public\site\assets\video\cura-training.mp4"

$INTRO = 3.70
$OUTRO = 89.15
$DUR = 93.908333
$OUTRO_LEN = [math]::Round($DUR - $OUTRO, 3)

Write-Host "=== 1/4 intro ==="
& $ff -y -loop 1 -i "$edit\cura-intro.png" -i $src -filter_complex `
  "[0:v]scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2,format=yuv420p,fps=30,fade=t=in:st=0:d=0.35,fade=t=out:st=$($INTRO-0.45):d=0.4[v];[1:a]atrim=0:${INTRO},asetpts=PTS-STARTPTS,afade=t=in:st=0:d=0.2,afade=t=out:st=$($INTRO-0.35):d=0.3[a]" `
  -map "[v]" -map "[a]" -t $INTRO -c:v libx264 -preset fast -crf 20 -pix_fmt yuv420p -c:a aac -b:a 128k `
  "$edit\part-intro.mp4"
if ($LASTEXITCODE -ne 0) { throw "intro failed" }

Write-Host "=== 2/4 body (delogo Misbar + Cura watermark) ==="
# delogo removes pixels in watermark zone; then place transparent Cura
# fps=30 keeps quality for web and is far faster than source 120fps
Push-Location $edit
try {
  & $ff -y -ss $INTRO -to $OUTRO -i $src -i "cura-wm.png" -filter_complex `
    "[0:v]fps=30,delogo=x=12:y=10:w=340:h=300:show=0[clean];[1:v]format=rgba,colorchannelmixer=aa=0.96[wm];[clean][wm]overlay=20:16:format=auto[v1];[v1]delogo=x=18:y=1480:w=640:h=390:show=0:enable='lt(t\,2.1)'[v]" `
    -map "[v]" -map 0:a -c:v libx264 -preset fast -crf 20 -pix_fmt yuv420p -c:a aac -b:a 128k `
    "part-body.mp4"
  if ($LASTEXITCODE -ne 0) { throw "body failed" }
} finally {
  Pop-Location
}

Write-Host "=== 3/4 outro ==="
& $ff -y -loop 1 -i "$edit\cura-outro.png" -i $src -filter_complex `
  "[0:v]scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2,format=yuv420p,fps=30,fade=t=in:st=0:d=0.4,fade=t=out:st=$($OUTRO_LEN-0.55):d=0.5[v];[1:a]atrim=${OUTRO}:${DUR},asetpts=PTS-STARTPTS,afade=t=in:st=0:d=0.25,afade=t=out:st=$($OUTRO_LEN-0.4):d=0.35[a]" `
  -map "[v]" -map "[a]" -t $OUTRO_LEN -c:v libx264 -preset fast -crf 20 -pix_fmt yuv420p -c:a aac -b:a 128k `
  "$edit\part-outro.mp4"
if ($LASTEXITCODE -ne 0) { throw "outro failed" }

Write-Host "=== 4/4 concat ==="
@"
file 'part-intro.mp4'
file 'part-body.mp4'
file 'part-outro.mp4'
"@ | Set-Content -Encoding ASCII "$edit\concat.txt"

& $ff -y -f concat -safe 0 -i "$edit\concat.txt" -c:v libx264 -preset medium -crf 21 -pix_fmt yuv420p -c:a aac -b:a 128k -movflags +faststart $out
if ($LASTEXITCODE -ne 0) { throw "concat failed" }

Copy-Item $out "f:\cura\public\site\assets\video\misbar-training.mp4" -Force
& $ff -y -ss 12 -i $out -update 1 -frames:v 1 "f:\cura\public\site\assets\video\misbar-training-poster.jpg" 2>$null

# verify frames
foreach ($t in @(1, 5, 15, 45, 91)) {
  & $ff -y -ss $t -i $out -update 1 -frames:v 1 "$edit\v$t.jpg" 2>$null
}
Write-Host "DONE"
Get-Item $out | Format-Table Name, Length
