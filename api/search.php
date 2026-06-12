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

$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;

if (empty($q)) {
    jsonResponse(['code' => 400, 'msg' => '请输入搜索关键词']);
}

$db = getDB();

$where = "s.status = 'approved' AND s.title LIKE ?";
$params = ["%{$q}%"];

$countStmt = $db->prepare("SELECT COUNT(*) FROM stickers s WHERE {$where}");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$offset = ($page - 1) * $perPage;
$stmt = $db->prepare("SELECT s.* FROM stickers s WHERE {$where} ORDER BY s.created_at DESC LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$perPage, $offset]));
$stickers = $stmt->fetchAll();

foreach ($stickers as &$sticker) {
    $sticker['image_url'] = getStickerImageUrl($sticker);
    $sticker['original_url'] = getStickerOriginalUrl($sticker);
    $sticker['size_formatted'] = formatFileSize($sticker['file_size']);
    $sticker['type_label'] = getFileTypeLabel($sticker['file_type']);
    $sticker['views_formatted'] = formatNumber($sticker['views']);
    $sticker['downloads_formatted'] = formatNumber($sticker['downloads']);
}

jsonResponse([
    'code' => 0,
    'msg' => 'success',
    'data' => [
        'list' => $stickers,
        'total' => $total,
        'page' => $page,
        'keyword' => $q
    ]
]);
