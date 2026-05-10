<?php
$html = file_get_contents("https://1drv.ms/u/c/d5f5f7b5bb7ddcd6/IQCa0LZS6AdNRJpn2BmMa-zCAfs2cecr11_s2HxN7yTsDVg?e=YMxhUr");
file_put_contents('storage/logs/onedrive_html.txt', $html);
echo "Length: " . strlen($html);
