<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$alias = siteAlias();
$title = siteTitle();
$url = siteUrl();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API 接口文档 - <?php echo $title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .api-guide {
            max-width: 900px;
            margin: 0 auto;
        }
        .api-guide h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            color: var(--primary);
        }
        .api-guide .subtitle {
            color: var(--text-secondary);
            margin-bottom: 30px;
            font-size: 0.95rem;
        }
        .api-section {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 20px;
        }
        .api-section h2 {
            font-size: 1.15rem;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .api-section h2 .method {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 700;
            color: #fff;
            background: #4caf50;
        }
        .api-section p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 10px;
            line-height: 1.7;
        }
        .api-section .url {
            display: inline-block;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 16px;
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 0.9rem;
            color: var(--primary);
            margin: 8px 0;
            word-break: break-all;
        }
        .api-section table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 0.88rem;
        }
        .api-section th, .api-section td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #222;
        }
        .api-section th {
            color: var(--text-muted);
            font-weight: 500;
        }
        .api-section td {
            color: var(--text);
        }
        .api-section .code-block {
            background: #0a0a0a;
            border: 1px solid #222;
            border-radius: 8px;
            padding: 14px 18px;
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 0.85rem;
            color: #4caf50;
            overflow-x: auto;
            margin: 10px 0;
            white-space: pre-wrap;
        }
        .try-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 20px;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            margin-top: 12px;
        }
        .try-btn:hover { background: var(--primary-hover); }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <?php echo renderLogo(); ?>
            <nav class="nav-links">
                <a href="/" class="nav-link">全部<?php echo $alias; ?></a>
                <a href="/upload" class="nav-link nav-btn-primary">添加<?php echo $alias; ?></a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="api-guide">
            <h1>📡 API 接口文档</h1>
            <p class="subtitle"><?php echo $title; ?> 提供公开的随机图片 API，可用作博客头像、QQ机器人、网站装饰等场景</p>

            <div class="api-section">
                <h2><span class="method">GET</span> 随机图片</h2>
                <p>每次请求随机返回一张已审核通过的<?php echo $alias; ?>图片，直接输出图片二进制数据</p>
                <div class="url">/api/random</div>
                <p style="margin-top:10px;">直接在浏览器打开即可看到随机图片，也可嵌入网页：</p>
                <div class="code-block">&lt;img src="<?php echo $url; ?>/api/random" alt="<?php echo $alias; ?>"&gt;</div>
                <a href="/api/random" target="_blank" class="try-btn">🔗 试试看</a>

                <table style="margin-top:18px;">
                    <tr><th>参数</th><th>说明</th><th>示例</th></tr>
                    <tr><td><code>tag</code></td><td>按标签筛选</td><td><code>?tag=搞笑</code></td></tr>
                    <tr><td><code>type</code></td><td>图片类型：<code>static</code> / <code>gif</code></td><td><code>?type=gif</code></td></tr>
                    <tr><td><code>json</code></td><td>返回 JSON 格式（含标题、ID）</td><td><code>?json</code></td></tr>
                </table>
                <p style="margin-top:10px;font-size:0.82rem;">参数可自由组合，如 <code>/api/random?tag=可爱&type=gif</code></p>
            </div>

            <div class="api-section">
                <h2><span class="method">GET</span> JSON 格式</h2>
                <p>加上 <code>?json</code> 参数即可返回结构化数据</p>
                <div class="url">/api/random?json</div>
                <div class="code-block">{
  "code": 0,
  "msg": "success",
  "data": {
    "id": 1,
    "title": "猪籽军舰",
    "image_url": "/assets/uploads/thumbs/thumb_xxx.jpeg",
    "original_url": "/assets/uploads/xxx.jpeg",
    "type_label": "静态图片"
  }
}</div>
                <a href="/api/random?json" target="_blank" class="try-btn">🔗 试试看</a>
            </div>

            <div class="api-section">
                <h2>💡 使用场景</h2>
                <p><strong>网页嵌入：</strong></p>
                <div class="code-block">&lt;img src="/api/random"&gt;</div>
                <p style="margin-top:10px;"><strong>Markdown：</strong></p>
                <div class="code-block">![随机<?php echo $alias; ?>](<?php echo $url; ?>/api/random)</div>
                <p style="margin-top:10px;"><strong>QQ机器人 / 微信机器人：</strong></p>
                <div class="code-block">const img = await fetch('<?php echo $url; ?>/api/random')
// 直接拿到图片，发给用户即可</div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p class="copyright" style="border:none;padding:0;margin:0;">&copy; 2077 <?php echo $title; ?>. 并不保留所有权利。</p>
    </footer>
</body>
</html>
