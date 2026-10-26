<?php

$h = file_get_contents(__DIR__ . '/../tmp_ks_product.html');
$p = strpos($h, 'related-articles-slider');
if ($p === false) {
    echo "not found\n";
    exit(1);
}
echo substr($h, $p, 20000);
