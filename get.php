<?php

$id = $_GET['q'];
if( $id == 'random' ){



}
header('Content-Type: application/json');

$path = "/home/alban/lab/ripInkle";
$file = $path."/files/$id.json.gz";
$decodedData = gzuncompress(file_get_contents($file));
echo $decodedData;
