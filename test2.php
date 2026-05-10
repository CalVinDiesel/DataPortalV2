<?php
$html = file_get_contents('storage/logs/onedrive_html.txt');
preg_match_all('/"size":\s*(\d+)/i', $html, $m);
print_r($m[1]);
preg_match_all('/"FileCount":\s*(\d+)/i', $html, $m);
print_r($m[1]);
preg_match_all('/"childCount":\s*(\d+)/i', $html, $m);
print_r($m[1]);
