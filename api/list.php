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

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$sort = $_GET['sort'] ?? 'latest';
$tag = $_GET['tag'] ?? '';
$search = $_GET['q'] ?? '';
$status = $_GET['status'] ?? 'approved';

$db = getDB();

$where = ["s.status = ?"];
$params = [$status];

if ($tag) {
    $where[] = "s.tags LIKE ?";
    $params[] = "%{$tag}%";
}

if ($search) {
    $where[] = "s.title LIKE ?";
    $params[] = "%{$search}%";
}

$whereSQL = implode(' AND ', $where);

$sortSQL = 's.created_at DESC';
$dbType = getConfig('db_type') ?: env('DB_TYPE', 'sqlite');
switch ($sort) {
    case 'hottest':
        $sortSQL = 's.views DESC';
        break;
    case 'random':
        $sortSQL = ($dbType === 'sqlite') ? 'RANDOM()' : 'RAND()';
        break;
    case 'downloads':
        $sortSQL = 's.downloads DESC';
        break;
}

$countStmt = $db->prepare("SELECT COUNT(*) FROM stickers s WHERE {$whereSQL}");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$offset = ($page - 1) * $perPage;
$stmt = $db->prepare("SELECT s.* FROM stickers s WHERE {$whereSQL} ORDER BY {$sortSQL} LIMIT ? OFFSET ?");
$stmt->execute(array_merge($params, [$perPage, $offset]));
$stickers = $stmt->fetchAll();

foreach ($stickers as &$sticker) {
    $sticker['image_url'] = getStickerImageUrl($sticker);
    $sticker['original_url'] = getStickerOriginalUrl($sticker);
    $sticker['size_formatted'] = formatFileSize($sticker['file_size']);
    $sticker['type_label'] = getFileTypeLabel($sticker['file_type']);
    $sticker['views_formatted'] = formatNumber($sticker['views']);
    $sticker['downloads_formatted'] = formatNumber($sticker['downloads']);
    $sticker['tags_array'] = $sticker['tags'] ? explode(',', $sticker['tags']) : [];
}

jsonResponse([
    'code' => 0,
    'msg' => 'success',
    'data' => [
        'list' => $stickers,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total / $perPage)
    ]
]);
