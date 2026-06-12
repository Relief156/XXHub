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
$action = $input['action'] ?? 'list';

$db = getDB();

if ($action === 'list') {
    $status = $input['status'] ?? 'pending';
    $page = max(1, (int)($input['page'] ?? 1));
    $perPage = 20;

    $where = "WHERE s.status = ?";
    $params = [$status];

    if ($status === 'all') {
        $where = "WHERE 1=1";
        $params = [];
    }

    $countStmt = $db->prepare("SELECT COUNT(*) FROM stickers s {$where}");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("SELECT s.* FROM stickers s {$where} ORDER BY s.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute(array_merge($params, [$perPage, $offset]));
    $stickers = $stmt->fetchAll();

    foreach ($stickers as &$sticker) {
        $sticker['image_url'] = getStickerImageUrl($sticker);
        $sticker['original_url'] = getStickerOriginalUrl($sticker);
        $sticker['size_formatted'] = formatFileSize($sticker['file_size']);
        $sticker['type_label'] = getFileTypeLabel($sticker['file_type']);
    }

    jsonResponse([
        'code' => 0,
        'msg' => 'success',
        'data' => [
            'list' => $stickers,
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $perPage)
        ]
    ]);
}

if ($action === 'approve' || $action === 'reject') {
    $id = (int)($input['id'] ?? 0);
    $reason = trim($input['reason'] ?? '');

    if ($id <= 0) {
        jsonResponse(['code' => 400, 'msg' => '缺少ID参数']);
    }

    $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("UPDATE stickers SET status = ?, reject_reason = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$newStatus, $reason, $id]);

        $logStmt = $db->prepare("INSERT INTO audit_logs (sticker_id, admin_id, action, reason) VALUES (?, ?, ?, ?)");
        $logStmt->execute([$id, $admin['admin_id'], $newStatus, $reason]);

        $db->commit();

        $actionLabel = ($action === 'approve') ? '已通过' : '已拒绝';
        jsonResponse(['code' => 0, 'msg' => "操作成功，{$actionLabel}"]);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['code' => 500, 'msg' => '操作失败: ' . $e->getMessage()]);
    }
}

if ($action === 'delete') {
    $id = (int)($input['id'] ?? 0);

    if ($id <= 0) {
        jsonResponse(['code' => 400, 'msg' => '缺少ID参数']);
    }

    $db->beginTransaction();
    try {
        $stmt = $db->prepare("SELECT filename, thumbnail FROM stickers WHERE id = ?");
        $stmt->execute([$id]);
        $sticker = $stmt->fetch();

        if (!$sticker) {
            jsonResponse(['code' => 404, 'msg' => '表情包不存在']);
        }

        $filename = $sticker['filename'];
        $thumbnail = $sticker['thumbnail'];

        $stmt = $db->prepare("DELETE FROM stickers WHERE id = ?");
        $stmt->execute([$id]);

        $logStmt = $db->prepare("INSERT INTO audit_logs (sticker_id, admin_id, action, reason) VALUES (?, ?, 'deleted', '')");
        $logStmt->execute([$id, $admin['admin_id']]);

        $db->commit();

        @unlink(UPLOADS_DIR . '/' . $filename);
        if ($thumbnail) {
            @unlink(THUMBS_DIR . '/' . $thumbnail);
        }

        jsonResponse(['code' => 0, 'msg' => '删除成功']);
    } catch (Exception $e) {
        $db->rollBack();
        jsonResponse(['code' => 500, 'msg' => '操作失败: ' . $e->getMessage()]);
    }
}

if ($action === 'updateTitle') {
    $id = (int)($input['id'] ?? 0);
    $title = trim($input['title'] ?? '');

    if ($id <= 0) {
        jsonResponse(['code' => 400, 'msg' => '缺少ID参数']);
    }
    if (empty($title)) {
        jsonResponse(['code' => 400, 'msg' => '标题不能为空']);
    }

    try {
        $stmt = $db->prepare("UPDATE stickers SET title = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$title, $id]);

        jsonResponse(['code' => 0, 'msg' => '修改成功']);
    } catch (Exception $e) {
        jsonResponse(['code' => 500, 'msg' => '操作失败: ' . $e->getMessage()]);
    }
}

if ($action === 'logs') {
    $page = max(1, (int)($input['page'] ?? 1));
    $perPage = 20;

    $countStmt = $db->prepare("SELECT COUNT(*) FROM audit_logs");
    $countStmt->execute();
    $total = (int)$countStmt->fetchColumn();

    $offset = ($page - 1) * $perPage;
    $stmt = $db->prepare("
        SELECT al.*, s.title as sticker_title, a.username as admin_username
        FROM audit_logs al
        LEFT JOIN stickers s ON al.sticker_id = s.id
        LEFT JOIN admins a ON al.admin_id = a.id
        ORDER BY al.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$perPage, $offset]);
    $logs = $stmt->fetchAll();

    jsonResponse([
        'code' => 0,
        'msg' => 'success',
        'data' => [
            'list' => $logs,
            'total' => $total,
            'page' => $page
        ]
    ]);
}

jsonResponse(['code' => 400, 'msg' => '未知操作']);
