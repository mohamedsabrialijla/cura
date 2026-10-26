<?php

$src = __DIR__ . '/../public/site/assets/img/misbar/logo.jpeg';
$dest = __DIR__ . '/../public/site/assets/img/misbar/logo.png';

$img = imagecreatefromjpeg($src);
$w = imagesx($img);
$h = imagesy($img);

$out = imagecreatetruecolor($w, $h);
imagealphablending($out, false);
imagesavealpha($out, true);

$transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
imagefill($out, 0, 0, $transparent);

for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgb = imagecolorat($img, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $sat = $max - $min;
        $lum = (0.299 * $r) + (0.587 * $g) + (0.114 * $b);

        $isBackground = $lum > 205 && $sat < 55;
        $isPaleDecor = $lum > 175 && $sat < 30 && $g >= $r && $g >= $b;

        if ($isBackground || $isPaleDecor) {
            continue;
        }

        $alpha = 0;
        if ($lum > 185 && $sat < 45) {
            $alpha = (int) min(127, (($lum - 185) / 35) * 127);
        }

        $col = imagecolorallocatealpha($out, $r, $g, $b, $alpha);
        imagesetpixel($out, $x, $y, $col);
    }
}

imagepng($out, $dest);
copy($dest, __DIR__ . '/../public/site/assets/img/favicon.png');
echo "Saved {$w}x{$h}\n";
