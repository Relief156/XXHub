<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (isInstalled()) {
    header('Location: /');
    exit;
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'test_db') {
        $dbType = $_POST['db_type'] ?? 'sqlite';
        $host = $_POST['db_host'] ?? 'localhost';
        $port = $_POST['db_port'] ?? '3306';
        $name = $_POST['db_name'] ?? 'xxhub';
        $user = $_POST['db_user'] ?? '';
        $pass = $_POST['db_pass'] ?? '';

        [$ok, $err] = testDBConnection($dbType, $host, $port, $name, $user, $pass);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $ok, 'message' => $ok ? '连接成功' : $err], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($action === 'install') {
        try {
            $dbType = $_POST['db_type'] ?? 'sqlite';
            $dbHost = $_POST['db_host'] ?? 'localhost';
            $dbPort = $_POST['db_port'] ?? '3306';
            $dbName = $_POST['db_name'] ?? 'xxhub';
            $dbUser = $_POST['db_user'] ?? '';
            $dbPass = $_POST['db_pass'] ?? '';
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password2'] ?? '';
            $siteTitle = $_POST['site_title'] ?? 'xxHub';
            $siteAlias = $_POST['site_alias'] ?? 'xx';

            if (empty($username) || empty($password)) {
                $error = '请填写管理员用户名和密码';
            } elseif ($password !== $password2) {
                $error = '两次输入的密码不一致';
            } elseif (empty($siteAlias)) {
                $error = '请填写网站别名';
            } else {
                saveConfig([
                    'db_type' => $dbType,
                    'db_host' => $dbHost,
                    'db_port' => $dbPort,
                    'db_name' => $dbName,
                    'db_user' => $dbUser,
                    'db_pass' => $dbPass,
                    'site_title' => $siteTitle,
                    'site_alias' => $siteAlias,
                    'site_description' => $_POST['site_description'] ?? '表情包收集网站',
                    'upload_max_size' => (int)($_POST['upload_max_size'] ?? 10485760),
                    'allowed_extensions' => $_POST['allowed_extensions'] ?? 'png,gif,jpg,jpeg,webp',
                    'installed' => true,
                ]);

                initTables();

                $db = getDB();
                $stmt = $db->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
                $stmt->execute([$username, hashPassword($password)]);

                header('Location: /?installed=1');
                exit;
            }
        } catch (Exception $e) {
            $error = '安装失败: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>安装向导 - xxHub</title>
    <style>
        :root {
            --bg: #000;
            --card-bg: #111;
            --primary: #f77f00;
            --primary-hover: #e06b00;
            --text: #f0f0f0;
            --text-secondary: #b0b0b0;
            --border: #f77f00;
            --input-bg: #0a0a0a;
            --success: #4caf50;
            --danger: #f44336;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Microsoft YaHei', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .install-container {
            width: 100%;
            max-width: 560px;
        }
        .install-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .install-header h1 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 8px;
        }
        .install-header p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
        .steps {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-bottom: 30px;
        }
        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-secondary);
        }
        .step.active { color: var(--primary); }
        .step.done { color: var(--success); }
        .step-num {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: 2px solid currentColor;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .step.active .step-num {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .step.done .step-num {
            background: var(--success);
            border-color: var(--success);
            color: #fff;
        }
        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 30px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 0.95rem;
            transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: var(--primary);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .form-row-3 {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 12px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
            width: 100%;
        }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        .btn-outline:hover { background: var(--primary); color: #fff; }
        .btn-sm { padding: 8px 16px; font-size: 0.85rem; }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .alert-error { background: rgba(244,67,54,0.1); border: 1px solid var(--danger); color: var(--danger); }
        .alert-success { background: rgba(76,175,80,0.1); border: 1px solid var(--success); color: var(--success); }
        .alert-info { background: rgba(247,127,0,0.1); border: 1px solid var(--primary); color: var(--primary); }
        .hint {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }
        .section-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }
        .inline-flex {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .flex-end { justify-content: flex-end; }
        .mt-20 { margin-top: 20px; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-header">
            <h1>🚀 xxHub 安装向导</h1>
            <p>首次部署配置引导</p>
        </div>

        <div class="steps">
            <div class="step active">
                <span class="step-num">1</span>
                <span>数据库</span>
            </div>
            <div class="step">
                <span class="step-num">2</span>
                <span>管理员</span>
            </div>
            <div class="step">
                <span class="step-num">3</span>
                <span>网站设置</span>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" id="installForm">
                <input type="hidden" name="action" value="install">

                <div class="section-title">
                    <span id="stepLabel">📦 数据库配置</span>
                </div>

                <div id="step1">
                    <div class="form-group">
                        <label>数据库类型</label>
                        <select name="db_type" id="dbType" onchange="toggleDBFields()">
                            <option value="sqlite">SQLite (推荐，无需额外配置)</option>
                            <option value="mysql">MySQL</option>
                        </select>
                    </div>
                    <div id="mysqlFields" class="hidden">
                        <div class="form-group">
                            <label>数据库主机</label>
                            <input type="text" name="db_host" value="localhost" placeholder="localhost">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>端口</label>
                                <input type="text" name="db_port" value="3306" placeholder="3306">
                            </div>
                            <div class="form-group">
                                <label>数据库名</label>
                                <input type="text" name="db_name" value="xxhub" placeholder="xxhub">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>用户名</label>
                                <input type="text" name="db_user" value="root" placeholder="root">
                            </div>
                            <div class="form-group">
                                <label>密码</label>
                                <input type="password" name="db_pass" placeholder="数据库密码">
                            </div>
                        </div>
                        <button type="button" class="btn btn-outline btn-sm" onclick="testDB()">测试数据库连接</button>
                        <span id="dbTestResult" style="margin-left:10px;font-size:0.85rem;"></span>
                    </div>
                </div>

                <div id="step2">
                    <div class="form-group">
                        <label>管理员用户名</label>
                        <input type="text" name="username" placeholder="admin" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>登录密码</label>
                            <input type="password" name="password" placeholder="至少6位" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label>确认密码</label>
                            <input type="password" name="password2" placeholder="再次输入密码" required minlength="6">
                        </div>
                    </div>
                </div>

                <div id="step3">
                    <div class="form-group">
                        <label>网站标题 (如 PigHub、DoroHub)</label>
                        <input type="text" name="site_title" value="xxHub" placeholder="xxHub" required>
                        <div class="hint">网站的完整标题，如 "PigHub"</div>
                    </div>
                    <div class="form-group">
                        <label>网站别名 (按钮中使用，如"猪猪"、"柴郡")</label>
                        <input type="text" name="site_alias" placeholder="xx" required>
                        <div class="hint">按钮中显示的别名，如"猪猪"会在按钮中显示"精选猪猪"、"全部猪猪"、"添加猪猪"</div>
                    </div>
                    <div class="form-group">
                        <label>网站描述</label>
                        <input type="text" name="site_description" value="表情包收集网站" placeholder="表情包收集网站">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>上传大小限制 (字节)</label>
                            <input type="number" name="upload_max_size" value="10485760">
                            <div class="hint">默认 10MB = 10485760</div>
                        </div>
                        <div class="form-group">
                            <label>允许的文件类型</label>
                            <input type="text" name="allowed_extensions" value="png,gif,jpg,jpeg,webp">
                            <div class="hint">逗号分隔，如 png,gif,jpg</div>
                        </div>
                    </div>
                </div>

                <div class="inline-flex flex-end mt-20">
                    <button type="submit" class="btn btn-primary">⚡ 开始安装</button>
                </div>
            </form>
        </div>

        <p style="text-align:center;margin-top:20px;color:var(--text-secondary);font-size:0.85rem;">
            ⚠️ 安装完成后，请删除 <code>install.php</code> 或限制其访问权限
        </p>
    </div>

    <script>
    function toggleDBFields() {
        var type = document.getElementById('dbType').value;
        var fields = document.getElementById('mysqlFields');
        if (type === 'mysql') {
            fields.classList.remove('hidden');
        } else {
            fields.classList.add('hidden');
        }
    }

    function testDB() {
        var form = document.getElementById('installForm');
        var data = new FormData();
        data.append('action', 'test_db');
        data.append('db_type', form.querySelector('[name="db_type"]').value);
        data.append('db_host', form.querySelector('[name="db_host"]').value);
        data.append('db_port', form.querySelector('[name="db_port"]').value);
        data.append('db_name', form.querySelector('[name="db_name"]').value);
        data.append('db_user', form.querySelector('[name="db_user"]').value);
        data.append('db_pass', form.querySelector('[name="db_pass"]').value);

        var resultEl = document.getElementById('dbTestResult');
        resultEl.textContent = '测试中...';
        resultEl.style.color = '#a0a0a0';

        fetch('/install', { method: 'POST', body: data })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    resultEl.textContent = '✓ ' + res.message;
                    resultEl.style.color = '#4caf50';
                } else {
                    resultEl.textContent = '✗ ' + res.message;
                    resultEl.style.color = '#f44336';
                }
            })
            .catch(function() {
                resultEl.textContent = '✗ 请求失败';
                resultEl.style.color = '#f44336';
            });
    }

    toggleDBFields();
    </script>
</body>
</html>
