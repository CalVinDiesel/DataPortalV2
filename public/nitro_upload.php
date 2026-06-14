<?php
/**
 * 🚀 ATOMIC NITRO BYPASS (v25)
 * ──────────────────────────────────────────────────────────────────────────
 * This script provides a zero-overhead landing zone for high-speed uploads.
 * It bypasses the 1-second Laravel boot time to achieve Gigabit speeds.
 * ──────────────────────────────────────────────────────────────────────────
 */

// 🚀 NITRO-CLUSTER CORS BRIDGE (v31)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN, X-Requested-With");

// Handle Pre-flight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Safety: Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('POST Required');
}

// 1. Setup Environment (Zero-Laravel)
set_time_limit(0);
ignore_user_abort(true);
ini_set('memory_limit', '512M'); // 🏎️ Extra headroom for high-speed buffers

function nitroLog($msg) {
    // Silenced for speed (v120)
    // $logFile = __DIR__ . '/../storage/logs/nitro_workers.log';
    // $timestamp = date('Y-m-d H:i:s');
    // file_put_contents($logFile, "[$timestamp] $msg\n", FILE_APPEND);
}

// Parse .env manually for raw speed (fallback if not in environment)
$env = file_exists(__DIR__ . '/../.env') ? parse_ini_file(__DIR__ . '/../.env') : [];
$root = getenv('NITRO_STORAGE_ROOT') ?: ($_ENV['NITRO_STORAGE_ROOT'] ?? ($env['NITRO_STORAGE_ROOT'] ?? 'C:/DataPortal_Nitro_Storage'));

    $projectId  = $_GET['projectID'] ?? 'unknown_project';
    $isFirst    = ($_GET['isFirstChunk'] ?? 'true') !== 'false';
    $slot       = $_GET['slot'] ?? null; 
    $storageDir = rtrim($root, '/') . '/' . $projectId;

// 2. Ensure Storage Directory exists
if (!file_exists($storageDir)) {
    if (!mkdir($storageDir, 0755, true) && !is_dir($storageDir)) {
        header('HTTP/1.1 500 Server Error');
        exit('Storage inaccessible');
    }
}

// 3. Open Input Buffer (8MB blocks)
$input      = fopen('php://input', 'rb');
$bufferSize = 8388608; // 8MB Hyper-Drive Buffer (v120)
stream_set_chunk_size($input, $bufferSize);

try {
    while (!feof($input)) {
        // A. Read Multiplex Header: Path Length
        $headerRaw = readStrict($input, 4);
        if ($headerRaw === false || strlen($headerRaw) < 4) break;
        $headerArr = unpack('Vlen', $headerRaw);
        $pathLen   = $headerArr['len'];

        // B. Read Multiplex Header: Relative Path
        $path = readStrict($input, $pathLen);
        if ($path === false) break;
        
        // C. Read Multiplex Header: File Size (Double)
        $sizeRaw = readStrict($input, 8);
        if ($sizeRaw === false) break;
        $sizeArr = unpack('dsize', $sizeRaw);
        $fileSize = $sizeArr['size'];

        // D. Prepare Destination File
        $localAbsPath = $storageDir . '/' . $path;
        if ($slot !== null && is_numeric($slot)) {
            $localAbsPath .= ".slot{$slot}";
        }
        
        if (!file_exists(dirname($localAbsPath))) {
            mkdir(dirname($localAbsPath), 0755, true);
        }

        // E. Stream Write (Append or Overwrite)
        $mode = $isFirst ? 'wb' : 'ab';
        $output = fopen($localAbsPath, $mode);
        if (!$output) throw new Exception("Write failed: $path");
        stream_set_chunk_size($output, $bufferSize);

        // 🚀 KERNEL STREAMER (v120): High-speed direct buffer copy
        stream_copy_to_stream($input, $output, (int)$fileSize);
        
        fclose($output);
    }
    
    fclose($input);
    $resp = json_encode(['success' => true, 'nitro' => 'active']);
    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($resp));
    echo $resp;
    flush();
    
} catch (Exception $e) {
    header('HTTP/1.1 500 Server Error');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Ensures strict binary read from socket
 */
function readStrict($stream, $len) {
    if ($len <= 0) return "";
    $data = '';
    while (strlen($data) < $len && !feof($stream)) {
        $buffer = fread($stream, $len - strlen($data));
        if ($buffer === false) return false;
        $data .= $buffer;
    }
    return $data;
}
