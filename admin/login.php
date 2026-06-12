<?php
require_once __DIR__ . '/../includes/config.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - <?php echo siteTitle(); ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }
        .login-card {
            background: #111;
            border: 1px solid #f77f00;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        .login-card h1 {
            text-align: center;
            color: #f0f0f0;
            margin-bottom: 4px;
            font-size: 1.6rem;
            font-weight: 700;
        }
        .login-card .logo-em { color: #f77f00; }
        .login-card .subtitle {
            text-align: center;
            color: #b0b0c0;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }
        .login-card .form-group {
            margin-bottom: 20px;
        }
        .login-card label {
            display: block;
            margin-bottom: 6px;
            color: #b0b0c0;
            font-size: 0.9rem;
        }
        .login-card input {
            width: 100%;
            padding: 12px 16px;
            background: #0a0a0a;
            border: 1px solid #f77f00;
            border-radius: 10px;
            color: #f0f0f0;
            font-size: 1rem;
            transition: border-color 0.2s;
            outline: none;
        }
        .login-card input:focus { border-color: #f77f00; }
        .login-card .btn {
            width: 100%;
            padding: 12px;
            background: #f77f00;
            color: #fff;
            border: none;
            border-radius: 22px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .login-card .btn:hover { background: #e06b00; }
        .login-card .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .error-msg {
            color: #f44336;
            text-align: center;
            margin-bottom: 16px;
            font-size: 0.9rem;
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h1><span class="logo-em">⚙️</span> <?php echo siteTitle(); ?></h1>
            <p class="subtitle">管理员登录</p>
            <p class="error-msg" id="errorMsg"></p>
            <form id="loginForm">
                <div class="form-group">
                    <label>用户名</label>
                    <input type="text" name="username" placeholder="请输入用户名" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label>密码</label>
                    <input type="password" name="password" placeholder="请输入密码" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn" id="loginBtn">登录</button>
            </form>
        </div>
    </div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('loginBtn');
        var errorEl = document.getElementById('errorMsg');

        btn.disabled = true;
        btn.textContent = '登录中...';
        errorEl.style.display = 'none';

        var data = {
            action: 'login',
            username: this.querySelector('[name="username"]').value,
            password: this.querySelector('[name="password"]').value
        };

        fetch('/admin/api/auth', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(function(r) {
            return r.text().then(function(text) {
                try { return JSON.parse(text); } catch(e) { return { code: 500, msg: '服务器响应异常' }; }
            });
        })
        .then(function(res) {
            if (res.code === 0) {
                localStorage.setItem('admin_token', res.data.token);
                localStorage.setItem('admin_username', res.data.username);
                window.location.href = '/admin/index';
            } else {
                errorEl.textContent = res.msg;
                errorEl.style.display = 'block';
            }
        })
        .catch(function() {
            errorEl.textContent = '网络错误，请稍后重试';
            errorEl.style.display = 'block';
        })
        .finally(function() {
            btn.disabled = false;
            btn.textContent = '登录';
        });
    });
    </script>
</body>
</html>
