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

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

$admin = requireAdmin();
$input = json_decode(file_get_contents('php://input'), true);

$allowedKeys = ['site_title', 'site_alias', 'site_description', 'site_url', 'site_icon', 'upload_max_size', 'allowed_extensions', 'aliyun_ak_id', 'aliyun_ak_secret'];

$updates = [];
foreach ($input as $key => $value) {
    if (in_array($key, $allowedKeys)) {
        $updates[$key] = $value;
    }
}
if (!empty($updates)) {
    saveConfig($updates);
}

jsonResponse(['code' => 0, 'msg' => '设置保存成功']);
