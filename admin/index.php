<?php
require_once __DIR__ . '/../includes/config.php';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理后台 - <?php echo siteTitle(); ?></title>
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
            --warning: #ff9800;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Microsoft YaHei', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
        }
        .sidebar {
            width: 240px;
            background: var(--card-bg);
            border-right: 1px solid var(--border);
            padding: 20px;
            flex-shrink: 0;
        }
        .sidebar .logo {
            font-size: 1.4rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 30px;
        }
        .sidebar .nav-item {
            display: block;
            padding: 10px 14px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .sidebar .nav-item:hover, .sidebar .nav-item.active {
            background: rgba(247, 127, 0, 0.1);
            color: var(--primary);
        }
        .sidebar .nav-item .badge {
            float: right;
            background: var(--primary);
            color: #fff;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.75rem;
        }
        .main {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }
        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .main-header h1 { font-size: 1.5rem; }
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-secondary);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-sm { padding: 5px 12px; font-size: 0.8rem; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-success { background: var(--success); color: #fff; }
        .btn-success:hover { background: #43a047; }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: #e53935; }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-secondary);
        }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
        .tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .tab {
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            color: var(--text-secondary);
            background: var(--card-bg);
            border: 1px solid var(--border);
            transition: all 0.2s;
        }
        .tab.active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
        .tab:hover:not(.active) { border-color: var(--primary); color: var(--primary); }
        .review-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .review-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }
        .review-card .card-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: var(--bg);
        }
        .review-card .card-body {
            padding: 16px;
        }
        .review-card .card-title {
            font-size: 0.95rem;
            margin-bottom: 8px;
        }
        .review-card .card-meta {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 12px;
        }
        .review-card .card-meta span { margin-right: 12px; }
        .review-card .card-actions {
            display: flex;
            gap: 8px;
        }
        .review-card .card-actions .btn { flex: 1; text-align: center; justify-content: center; }
        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 0.75rem;
        }
        .status-pending { background: rgba(255,152,0,0.15); color: var(--warning); }
        .status-approved { background: rgba(76,175,80,0.15); color: var(--success); }
        .status-rejected { background: rgba(244,67,54,0.15); color: var(--danger); }
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.show { display: flex; }
        .modal {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            width: 90%;
            max-width: 400px;
        }
        .modal h3 { margin-bottom: 16px; }
        .modal textarea, .modal input {
            width: 100%;
            padding: 10px;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            font-size: 0.9rem;
        }
        .modal textarea {
            resize: vertical;
            min-height: 80px;
        }
        .modal .modal-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
            justify-content: flex-end;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
        }
        .pagination button {
            padding: 8px 14px;
            border: 1px solid var(--border);
            background: var(--card-bg);
            color: var(--text);
            border-radius: 8px;
            cursor: pointer;
        }
        .pagination button.active { background: var(--primary); border-color: var(--primary); color: #fff; }
        .pagination button:disabled { opacity: 0.4; cursor: not-allowed; }
        .logs-table {
            width: 100%;
            border-collapse: collapse;
        }
        .logs-table th, .logs-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        .logs-table th { color: var(--text-secondary); font-weight: 500; font-size: 0.85rem; }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }
        .empty-state .icon { font-size: 3rem; margin-bottom: 16px; }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: #fff;
            font-size: 0.9rem;
            z-index: 2000;
            animation: slideIn 0.3s ease;
            display: none;
        }
        .toast.success { background: var(--success); }
        .toast.error { background: var(--danger); }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .stats-row {
            display: flex;
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            flex: 1;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
        }
        .stat-card .stat-num {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
        }
        .stat-card .stat-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo">⚙️ <?php echo siteTitle(); ?></div>
        <nav>
            <a class="nav-item active" data-page="review" onclick="switchPage('review')">
                📋 审核管理 <span class="badge" id="pendingBadge">0</span>
            </a>
            <a class="nav-item" data-page="all" onclick="switchPage('all')">📁 全部表情包</a>
            <a class="nav-item" data-page="logs" onclick="switchPage('logs')">📝 审核日志</a>
            <a class="nav-item" data-page="settings" onclick="switchPage('settings')">⚙️ 网站设置</a>
            <a class="nav-item" href="/" target="_blank">🏠 回到首页</a>
        </nav>
    </aside>

    <div class="main">
        <div class="main-header">
            <h1 id="pageTitle">审核管理</h1>
            <div class="user-info">
                <span id="adminName"></span>
                <button class="btn btn-outline btn-sm" onclick="logout()">退出登录</button>
            </div>
        </div>

        <div id="pageReview">
            <h2 class="section-title">📋 待审核表情包</h2>
            <div class="review-grid" id="reviewGrid"></div>
            <div class="empty-state" id="reviewEmpty">
                <div class="icon">🎉</div>
                <p>没有待审核的表情包</p>
            </div>
            <div class="pagination" id="reviewPagination"></div>
        </div>

        <div id="pageAll" style="display:none;">
            <div class="tabs">
                <span class="tab active" data-status="approved" onclick="switchStatus('approved', this)">✅ 已通过</span>
                <span class="tab" data-status="rejected" onclick="switchStatus('rejected', this)">❌ 已拒绝</span>
                <span class="tab" data-status="pending" onclick="switchStatus('pending', this)">⏳ 待审核</span>
            </div>
            <div class="review-grid" id="allGrid"></div>
            <div class="empty-state" id="allEmpty">
                <div class="icon">📭</div>
                <p>暂无数据</p>
            </div>
            <div class="pagination" id="allPagination"></div>
        </div>

        <div id="pageLogs" style="display:none;">
            <h2 class="section-title">📝 审核日志</h2>
            <table class="logs-table">
                <thead>
                    <tr>
                        <th>时间</th>
                        <th>表情包</th>
                        <th>管理员</th>
                        <th>操作</th>
                        <th>原因</th>
                    </tr>
                </thead>
                <tbody id="logsBody"></tbody>
            </table>
            <div class="pagination" id="logsPagination"></div>
        </div>

        <div id="pageSettings" style="display:none;">
            <h2 class="section-title">⚙️ 网站设置</h2>
            <div style="background:var(--card-bg);border:1px solid var(--border);border-radius:12px;padding:24px;max-width:600px;">
                <div style="margin-bottom:16px;">
                    <label style="color:var(--text-secondary);font-size:0.9rem;display:block;margin-bottom:4px;">网站标题</label>
                    <input type="text" id="settingTitle" style="width:100%;padding:10px;background:var(--input-bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:0.95rem;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="color:var(--text-secondary);font-size:0.9rem;display:block;margin-bottom:4px;">网站别名</label>
                    <input type="text" id="settingAlias" style="width:100%;padding:10px;background:var(--input-bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:0.95rem;">
                    <span style="font-size:0.8rem;color:var(--text-secondary);">按钮中显示的别名，如"猪猪"</span>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="color:var(--text-secondary);font-size:0.9rem;display:block;margin-bottom:4px;">网站描述</label>
                    <input type="text" id="settingDesc" style="width:100%;padding:10px;background:var(--input-bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:0.95rem;">
                </div>
                <div style="margin-bottom:16px;">
                    <label style="color:var(--text-secondary);font-size:0.9rem;display:block;margin-bottom:4px;">网站地址</label>
                    <input type="text" id="settingUrl" style="width:100%;padding:10px;background:var(--input-bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:0.95rem;" placeholder="https://你的域名.com">
                    <span style="font-size:0.8rem;color:var(--text-secondary);">用于 API 文档等处生成完整链接，如不填则自动检测</span>
                </div>
                <div style="margin-bottom:16px;">
                    <label style="color:var(--text-secondary);font-size:0.9rem;display:block;margin-bottom:4px;">Logo 图片</label>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <input type="file" id="logoInput" accept=".png,.jpg,.jpeg,.gif,.webp,.svg" style="flex:1;padding:10px;background:var(--input-bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:0.9rem;">
                        <button class="btn btn-primary btn-sm" onclick="uploadLogo()" style="white-space:nowrap;">上传 Logo</button>
                    </div>
                    <div id="logoPreview" style="margin-top:8px;"></div>
                    <span style="font-size:0.8rem;color:var(--text-secondary);">上传图片替换 Logo 旁的图标（建议 80~120px 宽，PNG 透明底最佳）</span>
                </div>
                <button class="btn btn-primary" onclick="saveSettings()">💾 保存设置</button>
                <span id="settingsMsg" style="margin-left:12px;font-size:0.9rem;"></span>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="rejectModal">
        <div class="modal">
            <h3>拒绝原因</h3>
            <textarea id="rejectReason" placeholder="可选填写拒绝原因..."></textarea>
            <div class="modal-actions">
                <button class="btn btn-outline" onclick="closeRejectModal()">取消</button>
                <button class="btn btn-danger" id="confirmRejectBtn" onclick="confirmReject()">确认拒绝</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <h3>确认删除</h3>
            <p>确定要删除这个表情包吗？此操作不可恢复。</p>
            <div class="modal-actions">
                <button class="btn btn-outline" onclick="closeDeleteModal()">取消</button>
                <button class="btn btn-danger" onclick="confirmDelete()">确认删除</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="editTitleModal">
        <div class="modal">
            <h3>修改标题</h3>
            <input type="text" id="editTitleInput" placeholder="请输入新标题">
            <div class="modal-actions">
                <button class="btn btn-outline" onclick="closeEditTitleModal()">取消</button>
                <button class="btn btn-primary" onclick="confirmEditTitle()">确认修改</button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script>
    var token = localStorage.getItem('admin_token');
    if (!token) {
        window.location.href = '/admin/login';
    }

    document.getElementById('adminName').textContent = localStorage.getItem('admin_username') || 'Admin';

    var currentPage = 'review';
    var currentStatus = 'approved';
    var reviewPage = 1, allPage = 1, logsPage = 1;
    var rejectId = null, deleteId = null, editTitleId = null;

    function api(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(data || {})
        }).then(function(r) {
            if (r.status === 401) {
                localStorage.removeItem('admin_token');
                window.location.href = '/admin/login';
                throw new Error('Unauthorized');
            }
            return r.text().then(function(text) {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    return { code: 500, msg: '服务器响应异常 (状态码: ' + r.status + ')' };
                }
            });
        });
    }

    function showToast(msg, type) {
        var t = document.getElementById('toast');
        t.textContent = msg;
        t.className = 'toast ' + (type || 'success');
        t.style.display = 'block';
        setTimeout(function() { t.style.display = 'none'; }, 2000);
    }

    function switchPage(page) {
        currentPage = page;
        document.querySelectorAll('.nav-item').forEach(function(el) { el.classList.remove('active'); });
        document.querySelector('[data-page="' + page + '"]').classList.add('active');

        var titles = { review: '审核管理', all: '全部表情包', logs: '审核日志', settings: '网站设置' };
        document.getElementById('pageTitle').textContent = titles[page] || '';

        document.getElementById('pageReview').style.display = page === 'review' ? '' : 'none';
        document.getElementById('pageAll').style.display = page === 'all' ? '' : 'none';
        document.getElementById('pageLogs').style.display = page === 'logs' ? '' : 'none';
        document.getElementById('pageSettings').style.display = page === 'settings' ? '' : 'none';

        if (page === 'review') loadReview();
        if (page === 'all') loadAll();
        if (page === 'logs') loadLogs();
        if (page === 'settings') loadSettings();
    }

    function switchStatus(status, el) {
        currentStatus = status;
        allPage = 1;
        document.querySelectorAll('#pageAll .tab').forEach(function(t) { t.classList.remove('active'); });
        if (el) el.classList.add('active');
        loadAll();
    }

    function loadReview() {
        document.getElementById('reviewGrid').innerHTML = '';
        api('/admin/api/review', { action: 'list', status: 'pending', page: reviewPage })
            .then(function(res) {
                if (res.code !== 0) return;
                renderReviewCards(res.data.list, 'reviewGrid');
                document.getElementById('reviewEmpty').style.display = res.data.list.length ? 'none' : '';
                document.getElementById('pendingBadge').textContent = res.data.total;
                renderPagination(res.data, 'reviewPagination', function(p) { reviewPage = p; loadReview(); });
            });
    }

    function loadAll() {
        document.getElementById('allGrid').innerHTML = '';
        api('/admin/api/review', { action: 'list', status: currentStatus, page: allPage })
            .then(function(res) {
                if (res.code !== 0) return;
                renderAllCards(res.data.list, 'allGrid');
                document.getElementById('allEmpty').style.display = res.data.list.length ? 'none' : '';
                renderPagination(res.data, 'allPagination', function(p) { allPage = p; loadAll(); });
            });
    }

    function loadLogs() {
        api('/admin/api/review', { action: 'logs', page: logsPage })
            .then(function(res) {
                if (res.code !== 0) return;
                var html = '';
                res.data.list.forEach(function(log) {
                    var actionLabel = log.action === 'approved' ? '<span style="color:#4caf50;">通过</span>' : 
                                     log.action === 'rejected' ? '<span style="color:#f44336;">拒绝</span>' :
                                     log.action === 'deleted' ? '<span style="color:#ff9800;">删除</span>' : '<span style="color:#b0b0b0;">' + log.action + '</span>';
                    html += '<tr>' +
                        '<td>' + log.created_at + '</td>' +
                        '<td>' + (log.sticker_title || '(已删除)') + '</td>' +
                        '<td>' + (log.admin_username || '-') + '</td>' +
                        '<td>' + actionLabel + '</td>' +
                        '<td>' + (log.reason || '-') + '</td>' +
                        '</tr>';
                });
                document.getElementById('logsBody').innerHTML = html;
                renderPagination(res.data, 'logsPagination', function(p) { logsPage = p; loadLogs(); });
            });
    }

    function loadSettings() {
        document.getElementById('settingTitle').value = '<?php echo siteTitle(); ?>';
        document.getElementById('settingAlias').value = '<?php echo siteAlias(); ?>';
        document.getElementById('settingDesc').value = '<?php echo siteDescription(); ?>';
        document.getElementById('settingUrl').value = '<?php echo siteUrl(); ?>';
    }

    function saveSettings() {
        var title = document.getElementById('settingTitle').value;
        var alias = document.getElementById('settingAlias').value;
        var desc = document.getElementById('settingDesc').value;
        var url = document.getElementById('settingUrl').value;

        var body = { site_title: title, site_alias: alias, site_description: desc, site_url: url };

        fetch('/admin/api/settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify(body)
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            var msgEl = document.getElementById('settingsMsg');
            if (res.code === 0) {
                msgEl.textContent = '✓ 保存成功，刷新后生效';
                msgEl.style.color = '#4caf50';
            } else {
                msgEl.textContent = '✗ ' + res.msg;
                msgEl.style.color = '#f44336';
            }
        });
    }

    function uploadLogo() {
        var input = document.getElementById('logoInput');
        if (!input.files || !input.files.length) {
            showToast('请先选择图片文件', 'error');
            return;
        }

        var formData = new FormData();
        formData.append('logo', input.files[0]);

        fetch('/admin/api/logo', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token },
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.code === 0) {
                showToast('Logo 上传成功，刷新首页查看效果', 'success');
                var preview = document.getElementById('logoPreview');
                preview.innerHTML = '<img src="/assets/uploads/site_logo.' + input.files[0].name.split('.').pop() + '?t=' + Date.now() + '" style="height:44px;border-radius:8px;">';
            } else {
                showToast(res.msg, 'error');
            }
        });
    }

    function renderReviewCards(list, gridId) {
        var html = '';
        list.forEach(function(s) {
            html += '<div class="review-card">' +
                '<img class="card-img" src="' + s.image_url + '" alt="' + s.title + '" loading="lazy">' +
                '<div class="card-body">' +
                '<div class="card-title">' + (s.title || '未命名') + '</div>' +
                '<div class="card-meta">' +
                '<span>' + s.type_label + '</span>' +
                '<span>' + s.size_formatted + '</span>' +
                '<span>' + s.created_at + '</span>' +
                '</div>' +
                '<div class="card-actions">' +
                '<button class="btn btn-success btn-sm" onclick="approveSticker(' + s.id + ')">✓ 通过</button>' +
                '<button class="btn btn-danger btn-sm" onclick="openRejectModal(' + s.id + ')">✗ 拒绝</button>' +
                '</div>' +
                '</div>' +
                '</div>';
        });
        document.getElementById(gridId).innerHTML = html;
    }

    function renderAllCards(list, gridId) {
        var html = '';
        list.forEach(function(s) {
            var badgeClass = s.status === 'approved' ? 'status-approved' : (s.status === 'pending' ? 'status-pending' : 'status-rejected');
            var badgeText = s.status === 'approved' ? '已通过' : (s.status === 'pending' ? '待审核' : '已拒绝');
            html += '<div class="review-card">' +
                '<img class="card-img" src="' + s.image_url + '" alt="' + s.title + '" loading="lazy">' +
                '<div class="card-body">' +
                '<div class="card-title">' + (s.title || '未命名') + ' <span class="status-badge ' + badgeClass + '">' + badgeText + '</span></div>' +
                '<div class="card-meta">' +
                '<span>' + s.type_label + '</span>' +
                '<span>' + s.size_formatted + '</span>' +
                '<span>👁 ' + s.views + '</span>' +
                '<span>⬇ ' + s.downloads + '</span>' +
                '</div>' +
                (s.reject_reason ? '<div style="font-size:0.8rem;color:#f44336;margin-top:4px;">拒绝原因: ' + s.reject_reason + '</div>' : '') +
                '<div class="card-actions">' +
                '<button class="btn btn-primary btn-sm" onclick="openEditTitleModal(' + s.id + ', \'' + escapeHtml(s.title || '') + '\')">✎ 编辑</button>' +
                '<button class="btn btn-danger btn-sm" onclick="openDeleteModal(' + s.id + ')">🗑 删除</button>' +
                '</div>' +
                '</div>' +
                '</div>';
        });
        document.getElementById(gridId).innerHTML = html;
    }

    function escapeHtml(text) {
        return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function renderPagination(data, containerId, callback) {
        var html = '';
        if (data.total_pages > 1) {
            if (data.page > 1) {
                html += '<button onclick="(' + callback.toString() + ')(' + (data.page - 1) + ')">‹ 上一页</button>';
            }
            for (var i = 1; i <= data.total_pages; i++) {
                if (i === data.page) {
                    html += '<button class="active">' + i + '</button>';
                } else if (Math.abs(i - data.page) <= 2 || i === 1 || i === data.total_pages) {
                    html += '<button onclick="(' + callback.toString() + ')(' + i + ')">' + i + '</button>';
                } else if (i === 2 || i === data.total_pages - 1) {
                    html += '<button disabled>...</button>';
                }
            }
            if (data.page < data.total_pages) {
                html += '<button onclick="(' + callback.toString() + ')(' + (data.page + 1) + ')">下一页 ›</button>';
            }
        }
        document.getElementById(containerId).innerHTML = html;
    }

    function approveSticker(id) {
        api('/admin/api/review', { action: 'approve', id: id })
            .then(function(res) {
                showToast(res.msg, res.code === 0 ? 'success' : 'error');
                if (res.code === 0) loadReview();
            });
    }

    function openRejectModal(id) {
        rejectId = id;
        document.getElementById('rejectReason').value = '';
        document.getElementById('rejectModal').classList.add('show');
    }

    function closeRejectModal() {
        rejectId = null;
        document.getElementById('rejectModal').classList.remove('show');
    }

    function confirmReject() {
        if (!rejectId) return;
        var reason = document.getElementById('rejectReason').value;
        api('/admin/api/review', { action: 'reject', id: rejectId, reason: reason })
            .then(function(res) {
                showToast(res.msg, res.code === 0 ? 'success' : 'error');
                closeRejectModal();
                if (res.code === 0) loadReview();
            });
    }

    function openDeleteModal(id) {
        deleteId = id;
        document.getElementById('deleteModal').classList.add('show');
    }

    function closeDeleteModal() {
        deleteId = null;
        document.getElementById('deleteModal').classList.remove('show');
    }

    function confirmDelete() {
        if (!deleteId) return;
        api('/admin/api/review', { action: 'delete', id: deleteId })
            .then(function(res) {
                showToast(res.msg, res.code === 0 ? 'success' : 'error');
                closeDeleteModal();
                if (res.code === 0) loadAll();
            });
    }

    function openEditTitleModal(id, title) {
        editTitleId = id;
        document.getElementById('editTitleInput').value = unescapeHtml(title);
        document.getElementById('editTitleModal').classList.add('show');
    }

    function unescapeHtml(text) {
        return text.replace(/&quot;/g, '"').replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&amp;/g, '&');
    }

    function closeEditTitleModal() {
        editTitleId = null;
        document.getElementById('editTitleModal').classList.remove('show');
    }

    function confirmEditTitle() {
        if (!editTitleId) return;
        var title = document.getElementById('editTitleInput').value.trim();
        if (!title) {
            showToast('标题不能为空', 'error');
            return;
        }
        api('/admin/api/review', { action: 'updateTitle', id: editTitleId, title: title })
            .then(function(res) {
                showToast(res.msg, res.code === 0 ? 'success' : 'error');
                closeEditTitleModal();
                if (res.code === 0) loadAll();
            });
    }

    function logout() {
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_username');
        window.location.href = '/admin/login';
    }

    loadReview();
    </script>
</body>
</html>
