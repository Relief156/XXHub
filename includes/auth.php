<?php
function jwtSecret()
{
    $secret = getConfig('jwt_secret');
    if (!$secret) {
        $secret = bin2hex(random_bytes(32));
        saveConfig('jwt_secret', $secret);
    }
    return $secret;
}

function jwtEncode($payload, $expireSeconds = 86400)
{
    $header = ['typ' => 'JWT', 'alg' => 'HS256'];
    $payload['iat'] = time();
    $payload['exp'] = time() + $expireSeconds;

    $segments = [];
    $segments[] = base64urlEncode(json_encode($header));
    $segments[] = base64urlEncode(json_encode($payload));
    $signingInput = implode('.', $segments);
    $signature = hash_hmac('sha256', $signingInput, jwtSecret(), true);
    $segments[] = base64urlEncode($signature);

    return implode('.', $segments);
}

function jwtDecode($token)
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    [$header, $payload, $signature] = $parts;
    $signingInput = $header . '.' . $payload;
    $expectedSig = base64urlEncode(hash_hmac('sha256', $signingInput, jwtSecret(), true));

    if (!hash_equals($expectedSig, $signature)) {
        return null;
    }

    $data = json_decode(base64urlDecode($payload), true);
    if (!$data || !isset($data['exp']) || $data['exp'] < time()) {
        return null;
    }

    return $data;
}

function base64urlEncode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64urlDecode($data)
{
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function getAuthToken()
{
    $headers = [];
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    } else {
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))))] = $value;
            }
        }
    }

    $auth = $headers['Authorization'] ?? '';
    if (preg_match('/Bearer\s+(.+)/i', $auth, $m)) {
        return $m[1];
    }
    return null;
}

function requireAdmin()
{
    $token = getAuthToken();
    if (!$token) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['code' => 401, 'msg' => '未授权访问']);
        exit;
    }

    $payload = jwtDecode($token);
    if (!$payload || !isset($payload['admin_id'])) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['code' => 401, 'msg' => 'Token无效或已过期']);
        exit;
    }

    return $payload;
}

function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}
