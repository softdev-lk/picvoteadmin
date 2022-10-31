<?php

require __DIR__.'/vendor/autoload.php';
use Snipe\BanBuilder\CensorWords;
$censor = new CensorWords;
$string = $censor->censorString('boobs');

echo $string;


?>
