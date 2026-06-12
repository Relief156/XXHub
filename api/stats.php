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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['code' => 405, 'msg' => 'Method Not Allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$id = (int)($input['id'] ?? 0);
$action = $input['action'] ?? 'view';

if ($id <= 0) {
    jsonResponse(['code' => 400, 'msg' => '缺少ID参数']);
}

$db = getDB();

if ($action === 'download') {
    $db->prepare("UPDATE stickers SET downloads = downloads + 1 WHERE id = ?")->execute([$id]);
} else {
    $db->prepare("UPDATE stickers SET views = views + 1 WHERE id = ?")->execute([$id]);
}

jsonResponse(['code' => 0, 'msg' => 'success']);
