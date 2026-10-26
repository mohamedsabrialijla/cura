<?php

$h = file_get_contents(__DIR__ . '/../tmp_ks_product.html');
$needle = 'Recommended products:';
$p = strpos($h, $needle);
if ($p === false) {
    echo "not found\n";
    exit(1);
}
$start = max(0, $p - 500);
echo substr($h, $start, 25000);
