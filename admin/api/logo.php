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
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

$admin = requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['logo'])) {
    jsonResponse(['code' => 400, 'msg' => '请选择图片文件']);
}

$file = $_FILES['logo'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(['code' => 400, 'msg' => '文件上传失败']);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'])) {
    jsonResponse(['code' => 400, 'msg' => '仅支持 PNG/JPG/GIF/WEBP/SVG 格式']);
}

if ($file['size'] > 2 * 1024 * 1024) {
    jsonResponse(['code' => 400, 'msg' => 'Logo 大小不能超过 2MB']);
}

if (!is_dir(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0775, true);
}

$existing = glob(UPLOADS_DIR . '/site_logo.*');
foreach ($existing as $old) {
    @unlink($old);
}

$logoPath = UPLOADS_DIR . '/site_logo.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $logoPath)) {
    jsonResponse(['code' => 500, 'msg' => 'Logo 保存失败']);
}

saveConfig('site_logo_ext', $ext);

jsonResponse(['code' => 0, 'msg' => 'Logo 上传成功']);
