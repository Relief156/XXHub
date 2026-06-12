<?php
error_reporting(0);
set_exception_handler(function ($e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['code' => 500, 'msg' => $e->getMessage() ?: '服务器错误'], JSON_UNESCAPED_UNICODE);
    exit;
});
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$tag = $_GET['tag'] ?? '';
$type = $_GET['type'] ?? '';
$json = isset($_GET['json']);

$db = getDB();
$dbType = getConfig('db_type') ?: env('DB_TYPE', 'sqlite');

$where = ["s.status = 'approved'"];
$params = [];

if ($tag) {
    $where[] = "s.tags LIKE ?";
    $params[] = "%{$tag}%";
}

if ($type === 'static') {
    $where[] = "s.file_type = 'static'";
} elseif ($type === 'gif' || $type === 'animated') {
    $where[] = "s.file_type = 'gif'";
}

$whereSQL = implode(' AND ', $where);
$randomFunc = ($dbType === 'sqlite') ? 'RANDOM()' : 'RAND()';

$stmt = $db->prepare("SELECT s.* FROM stickers s WHERE {$whereSQL} ORDER BY {$randomFunc} LIMIT 1");
$stmt->execute($params);
$sticker = $stmt->fetch();

if (!$sticker) {
    if ($json) {
        jsonResponse(['code' => 404, 'msg' => '暂无表情包']);
    }
    http_response_code(404);
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect fill="#111" width="200" height="200"/><text fill="#666" x="30" y="110" font-size="18">暂无图片</text></svg>';
    exit;
}

$imagePath = UPLOADS_DIR . '/' . $sticker['filename'];

if ($json) {
    $sticker['image_url'] = getStickerImageUrl($sticker);
    $sticker['original_url'] = getStickerOriginalUrl($sticker);
    jsonResponse(['code' => 0, 'msg' => 'success', 'data' => $sticker]);
}

if (!file_exists($imagePath)) {
    http_response_code(404);
    header('Content-Type: image/svg+xml');
    echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect fill="#111" width="200" height="200"/><text fill="#666" x="40" y="110" font-size="16">图片已失效</text></svg>';
    exit;
}

$mimeMap = [
    'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
    'png' => 'image/png', 'gif' => 'image/gif',
    'webp' => 'image/webp',
];
$ext = strtolower(pathinfo($sticker['filename'], PATHINFO_EXTENSION));
$mime = $mimeMap[$ext] ?? 'image/jpeg';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($imagePath));
header('Cache-Control: no-cache, must-revalidate');
readfile($imagePath);

