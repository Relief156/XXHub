<?php
define('ROOT_DIR', __DIR__ . '/..');
define('ASSETS_DIR', ROOT_DIR . '/assets');
define('UPLOADS_DIR', ROOT_DIR . '/assets/uploads');
define('THUMBS_DIR', ROOT_DIR . '/assets/uploads/thumbs');
define('CONFIG_FILE', ROOT_DIR . '/config.json');
define('ENV_FILE', ROOT_DIR . '/.env');

function env($key, $default = '')
{
    static $env = null;
    if ($env === null) {
        $env = [];
        $envFile = ENV_FILE;
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') {
                    continue;
                }
                $pos = strpos($line, '=');
                if ($pos !== false) {
                    $k = trim(substr($line, 0, $pos));
                    $v = trim(substr($line, $pos + 1));
                    $v = trim($v, '"\'');
                    $env[$k] = $v;
                }
            }
        }
    }
    return $env[$key] ?? $default;
}

function getConfig($key = null, $default = null)
{
    if (!isset($GLOBALS['_xxhub_config'])) {
        if (file_exists(CONFIG_FILE)) {
            $GLOBALS['_xxhub_config'] = json_decode(file_get_contents(CONFIG_FILE), true) ?: [];
        } else {
            $GLOBALS['_xxhub_config'] = [];
        }
    }
    $config = $GLOBALS['_xxhub_config'];
    if ($key === null) {
        return $config;
    }
    return $config[$key] ?? $default;
}

function saveConfig($key, $value = null)
{
    if (is_array($key)) {
        $updates = $key;
    } else {
        $updates = [$key => $value];
    }

    $config = getConfig();
    foreach ($updates as $k => $v) {
        $config[$k] = $v;
    }

    if (!is_writable(CONFIG_FILE) && file_exists(CONFIG_FILE)) {
        throw new RuntimeException('config.json 无写入权限，请在服务器上执行: chmod 666 config.json');
    }

    $result = @file_put_contents(CONFIG_FILE, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    if ($result === false) {
        throw new RuntimeException('无法写入 config.json，请检查文件权限: chmod 666 config.json');
    }

    $GLOBALS['_xxhub_config'] = $config;
}

function isInstalled()
{
    return file_exists(CONFIG_FILE) && getConfig('installed') === true;
}

function siteTitle()
{
    return getConfig('site_title') ?: env('SITE_TITLE', 'xxHub');
}

function siteAlias()
{
    return getConfig('site_alias') ?: env('SITE_ALIAS', 'xx');
}

function siteDescription()
{
    return getConfig('site_description') ?: env('SITE_DESCRIPTION', '表情包收集网站');
}

function siteUrl()
{
    return getConfig('site_url') ?: (($_SERVER['HTTPS'] ?? '') === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

if (!isInstalled() && basename($_SERVER['SCRIPT_NAME']) !== 'install.php') {
    header('Location: /install');
    exit;
}
