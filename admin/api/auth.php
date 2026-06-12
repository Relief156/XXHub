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

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? 'login';
$username = trim($input['username'] ?? '');
$password = $input['password'] ?? '';

if (empty($username) || empty($password)) {
    jsonResponse(['code' => 400, 'msg' => '请输入用户名和密码']);
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if (!$admin || !verifyPassword($password, $admin['password_hash'])) {
    jsonResponse(['code' => 401, 'msg' => '用户名或密码错误']);
}

$token = jwtEncode([
    'admin_id' => $admin['id'],
    'username' => $admin['username'],
], 86400);

jsonResponse([
    'code' => 0,
    'msg' => '登录成功',
    'data' => [
        'token' => $token,
        'username' => $admin['username'],
    ]
]);
