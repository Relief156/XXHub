<?php
function jsonResponse($data, $code = 200)
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getClientIP()
{
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function sanitizeInput($str)
{
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function generateThumbnail($sourcePath, $destPath, $maxWidth = 400, $maxHeight = 400)
{
    if (!function_exists('imagecreatetruecolor')) {
        return copy($sourcePath, $destPath);
    }

    $info = @getimagesize($sourcePath);
    if (!$info) {
        return copy($sourcePath, $destPath);
    }

    $mime = $info['mime'];
    $srcW = $info[0];
    $srcH = $info[1];

    $ratio = min($maxWidth / $srcW, $maxHeight / $srcH);
    if ($ratio >= 1) {
        return copy($sourcePath, $destPath);
    }

    $newW = (int)($srcW * $ratio);
    $newH = (int)($srcH * $ratio);

    $srcImg = null;
    switch ($mime) {
        case 'image/jpeg':
            $srcImg = @imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $srcImg = @imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $srcImg = @imagecreatefromgif($sourcePath);
            break;
        case 'image/webp':
            if (function_exists('imagecreatefromwebp')) {
                $srcImg = @imagecreatefromwebp($sourcePath);
            }
            break;
    }

    if (!$srcImg) {
        return copy($sourcePath, $destPath);
    }

    $dstImg = imagecreatetruecolor($newW, $newH);
    if (!$dstImg) {
        imagedestroy($srcImg);
        return copy($sourcePath, $destPath);
    }

    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($dstImg, false);
        imagesavealpha($dstImg, true);
    }

    imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);

    $result = false;
    switch ($mime) {
        case 'image/jpeg':
            $result = @imagejpeg($dstImg, $destPath, 85);
            break;
        case 'image/png':
            $result = @imagepng($dstImg, $destPath, 8);
            break;
        case 'image/gif':
            $result = @imagegif($dstImg, $destPath);
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) {
                $result = @imagewebp($dstImg, $destPath, 85);
            }
            break;
    }

    imagedestroy($srcImg);
    imagedestroy($dstImg);

    if (!$result) {
        return copy($sourcePath, $destPath);
    }
    return true;
}

function getStickerImageUrl($sticker)
{
    $base = '/assets/uploads/';
    $thumb = $sticker['thumbnail'] ?? '';
    $filename = $sticker['filename'] ?? '';

    if ($thumb && file_exists(UPLOADS_DIR . '/thumbs/' . $thumb)) {
        return $base . 'thumbs/' . $thumb;
    }
    return $base . $filename;
}

function getStickerOriginalUrl($sticker)
{
    return '/assets/uploads/' . ($sticker['filename'] ?? '');
}

function formatFileSize($bytes)
{
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 1) . ' MB';
    }
    if ($bytes >= 1024) {
        return round($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
}

function formatNumber($num)
{
    if ($num >= 10000) {
        return round($num / 10000, 1) . '万';
    }
    if ($num >= 1000) {
        return round($num / 1000, 1) . 'k';
    }
    return (string)$num;
}

function getFileTypeLabel($type)
{
    return $type === 'gif' ? 'GIF动图' : '静态图片';
}

function slugify($text)
{
    $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
    $text = preg_replace('/\s+/', '-', trim($text));
    return strtolower($text);
}

function allowedExtension($ext)
{
    $allowed = getConfig('allowed_extensions') ?: env('ALLOWED_EXTENSIONS', 'png,gif,jpg,jpeg,webp');
    return in_array(strtolower($ext), explode(',', $allowed));
}

function maxUploadSize()
{
    return (int)(getConfig('upload_max_size') ?: env('UPLOAD_MAX_SIZE', 10485760));
}

function isCurrentPage($page)
{
    $current = basename($_SERVER['SCRIPT_NAME']);
    return $current === $page;
}

function renderLogo()
{
    $title = siteTitle();
    $ext = getConfig('site_logo_ext');
    $logoFile = $ext ? (UPLOADS_DIR . '/site_logo.' . $ext) : null;
    $iconHtml = '';

    if ($logoFile && file_exists($logoFile)) {
        $iconHtml = '<img class="logo-img" src="/assets/uploads/site_logo.' . $ext . '" alt="logo">';
    } else {
        $icon = getConfig('site_icon') ?: '🐖';
        $iconHtml = '<span class="logo-icon">' . htmlspecialchars($icon) . '</span>';
    }

    if (preg_match('/^(.*?)(hub)$/i', $title, $m)) {
        return '<a href="/" class="logo-text"><span class="logo-prefix">' . htmlspecialchars($m[1]) . '</span><span class="logo-suffix">' . htmlspecialchars($m[2]) . '</span>' . $iconHtml . '</a>';
    }
    return '<a href="/" class="logo-text">' . htmlspecialchars($title) . $iconHtml . '</a>';
}
