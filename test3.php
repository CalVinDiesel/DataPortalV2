<?php
$html = file_get_contents('storage/logs/onedrive_html.txt');
if (preg_match('/g_listData\s*=\s*(\{.*?\});/s', $html, $m)) {
    $data = json_decode($m[1], true);
    print_r(array_keys($data));
} else {
    echo "g_listData not found\n";
    // Search for other JSON blobs
    if (preg_match('/window\.__NEXT_DATA__\s*=\s*(\{.*?\});/s', $html, $m)) {
        echo "NEXT_DATA found\n";
    }
}
