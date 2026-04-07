<?php
$ch = curl_init('https://3dhub.geosabah.my/3dmodel/KK_OSPREY/tileset.json');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
$result = curl_exec($ch);
echo $result;
