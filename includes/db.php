<?php
function getDB()
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dbType = getConfig('db_type') ?: env('DB_TYPE', 'sqlite');

    try {
        if ($dbType === 'sqlite') {
            $dbPath = ROOT_DIR . '/data/xxhub.db';
            $dbDir = dirname($dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->exec('PRAGMA journal_mode=WAL');
            $pdo->exec('PRAGMA foreign_keys=ON');
        } else {
            $host = getConfig('db_host') ?: env('DB_HOST', 'localhost');
            $port = getConfig('db_port') ?: env('DB_PORT', '3306');
            $name = getConfig('db_name') ?: env('DB_NAME', 'xxhub');
            $user = getConfig('db_user') ?: env('DB_USER', 'root');
            $pass = getConfig('db_pass') ?: env('DB_PASS', '');
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass);
        }

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        die('数据库连接失败: ' . $e->getMessage());
    }
}

function testDBConnection($type, $host, $port, $name, $user, $pass)
{
    try {
        if ($type === 'sqlite') {
            $dbPath = ROOT_DIR . '/data/xxhub.db';
            $dbDir = dirname($dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->exec('PRAGMA journal_mode=WAL');
        } else {
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass);
        }
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return [true, null];
    } catch (PDOException $e) {
        return [false, $e->getMessage()];
    }
}

function initTables()
{
    $db = getDB();
    $dbType = getConfig('db_type') ?: env('DB_TYPE', 'sqlite');
    $isSQLite = ($dbType === 'sqlite');

    if ($isSQLite) {
        $db->exec("CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            key_name TEXT NOT NULL UNIQUE,
            key_value TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS admins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS stickers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL DEFAULT '',
            filename TEXT NOT NULL,
            thumbnail TEXT DEFAULT '',
            file_type TEXT DEFAULT 'static',
            file_size INTEGER DEFAULT 0,
            tags TEXT DEFAULT '',
            views INTEGER DEFAULT 0,
            downloads INTEGER DEFAULT 0,
            status TEXT DEFAULT 'pending',
            reject_reason TEXT DEFAULT '',
            ip_address TEXT DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE IF NOT EXISTS audit_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sticker_id INTEGER,
            admin_id INTEGER,
            action TEXT NOT NULL,
            reason TEXT DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    } else {
        $db->exec("CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            key_name VARCHAR(100) NOT NULL UNIQUE,
            key_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->exec("CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->exec("CREATE TABLE IF NOT EXISTS stickers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL DEFAULT '',
            filename VARCHAR(255) NOT NULL,
            thumbnail VARCHAR(255) DEFAULT '',
            file_type VARCHAR(20) DEFAULT 'static',
            file_size INT DEFAULT 0,
            tags VARCHAR(500) DEFAULT '',
            views INT DEFAULT 0,
            downloads INT DEFAULT 0,
            status VARCHAR(20) DEFAULT 'pending',
            reject_reason TEXT,
            ip_address VARCHAR(45) DEFAULT '',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_created (created_at),
            INDEX idx_views (views)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->exec("CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sticker_id INT,
            admin_id INT,
            action VARCHAR(20) NOT NULL,
            reason TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_sticker (sticker_id),
            INDEX idx_admin (admin_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
