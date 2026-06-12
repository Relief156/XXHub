<?php
error_reporting(0);
set_exception_handler(function ($e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['code' => 500, 'msg' => '服务器错误: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
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

$title = trim($_POST['title'] ?? '');
$tags = trim($_POST['tags'] ?? '');

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => '文件大小超过服务器限制',
        UPLOAD_ERR_FORM_SIZE => '文件大小超过表单限制',
        UPLOAD_ERR_PARTIAL => '文件上传不完整',
        UPLOAD_ERR_NO_FILE => '没有选择文件',
    ];
    $code = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
    $msg = $errors[$code] ?? "上传错误 (错误码: {$code})";
    jsonResponse(['code' => 400, 'msg' => $msg]);
}

$file = $_FILES['image'];
$maxSize = maxUploadSize();
if ($file['size'] > $maxSize) {
    jsonResponse(['code' => 400, 'msg' => '文件大小不能超过 ' . formatFileSize($maxSize)]);
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!allowedExtension($ext)) {
    jsonResponse(['code' => 400, 'msg' => '不支持的文件类型: .' . $ext]);
}

$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (class_exists('finfo')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
} elseif (function_exists('mime_content_type')) {
    $mime = mime_content_type($file['tmp_name']);
} else {
    $extToMime = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png', 'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];
    $mime = $extToMime[$ext] ?? 'application/octet-stream';
}

if (!in_array($mime, $allowedMimes)) {
    jsonResponse(['code' => 400, 'msg' => '文件类型不合法，仅支持图片文件']);
}

$imgInfo = @getimagesize($file['tmp_name']);
if (!$imgInfo) {
    jsonResponse(['code' => 400, 'msg' => '无法识别图片文件']);
}

if (!is_dir(UPLOADS_DIR)) {
    if (!@mkdir(UPLOADS_DIR, 0775, true)) {
        jsonResponse(['code' => 500, 'msg' => '无法创建上传目录，请在服务器上执行: mkdir -p assets/uploads && chmod 775 assets/uploads']);
    }
}
if (!is_writable(UPLOADS_DIR)) {
    jsonResponse(['code' => 500, 'msg' => '上传目录无写入权限，请在服务器上执行: chmod 775 assets/uploads']);
}
if (!is_dir(THUMBS_DIR)) {
    if (!@mkdir(THUMBS_DIR, 0775, true)) {
        jsonResponse(['code' => 500, 'msg' => '无法创建缩略图目录: assets/uploads/thumbs/']);
    }
}
if (!is_writable(THUMBS_DIR)) {
    jsonResponse(['code' => 500, 'msg' => '缩略图目录无写入权限，请在服务器上执行: chmod 775 assets/uploads/thumbs/']);
}

$uniqueName = bin2hex(random_bytes(16)) . '.' . $ext;
$destPath = UPLOADS_DIR . '/' . $uniqueName;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    jsonResponse(['code' => 500, 'msg' => '文件保存失败']);
}

$thumbName = 'thumb_' . $uniqueName;
$thumbPath = THUMBS_DIR . '/' . $thumbName;
try {
    generateThumbnail($destPath, $thumbPath, 400, 400);
} catch (Exception $e) {
    $thumbName = '';
    copy($destPath, $thumbPath);
}

$fileType = ($ext === 'gif') ? 'gif' : 'static';

$db = getDB();
$stmt = $db->prepare("INSERT INTO stickers (title, filename, thumbnail, file_type, file_size, tags, status, ip_address) VALUES (?, ?, ?, ?, ?, ?, 'approved', ?)");
$stmt->execute([$title ?: pathinfo($file['name'], PATHINFO_FILENAME), $uniqueName, $thumbName, $fileType, $file['size'], $tags, getClientIP()]);
$stickerId = (int)$db->lastInsertId();

jsonResponse([
    'code' => 0,
    'msg' => '上传成功 ✓',
    'data' => [
        'id' => $stickerId,
        'title' => $title,
        'filename' => $uniqueName,
        'status' => 'approved',
    ]
]);
