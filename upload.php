<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
$alias = siteAlias();
$title = siteTitle();
$maxSize = getConfig('upload_max_size') ?: env('UPLOAD_MAX_SIZE', 10485760);
$maxSizeMB = round($maxSize / 1048576, 0);
$allowedExts = getConfig('allowed_extensions') ?: env('ALLOWED_EXTENSIONS', 'png,gif,jpg,jpeg,webp');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>添加<?php echo $alias; ?> - <?php echo $title; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <?php echo renderLogo(); ?>
            <nav class="nav-links">
                <a href="/" class="nav-link">全部<?php echo $alias; ?></a>
                <a href="/upload" class="nav-link nav-btn-primary active">添加<?php echo $alias; ?></a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="upload-container">
            <div class="upload-card">
                <h1>添加<?php echo $alias; ?>表情包</h1>
                <p class="upload-desc">上传你喜欢的<?php echo $alias; ?>表情包，审核通过后将展示在网站上</p>

                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="upload-zone" id="uploadZone">
                        <div class="upload-zone-inner">
                            <div class="upload-icon">📁</div>
                            <p>点击或拖拽图片到此区域上传</p>
                            <p class="upload-hint">支持 <?php echo strtoupper($allowedExts); ?> 格式，单文件 ≤ <?php echo $maxSizeMB; ?>MB</p>
                        </div>
                        <input type="file" name="image" id="fileInput" accept=".<?php echo str_replace(',', ',.', $allowedExts); ?>" style="display:none;">
                        <div class="upload-preview" id="uploadPreview" style="display:none;">
                            <img id="previewImg" src="" alt="">
                            <div class="preview-overlay">
                                <button type="button" class="btn btn-sm btn-outline" onclick="resetUpload()">重新选择</button>
                            </div>
                        </div>
                    </div>

                    <div class="upload-progress" id="uploadProgress" style="display:none;">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <span class="progress-text" id="progressText">0%</span>
                    </div>

                    <div class="form-group">
                        <label>标题（可选）</label>
                        <input type="text" name="title" id="titleInput" placeholder="给这张<?php echo $alias; ?>取个名字吧" maxlength="100">
                    </div>

                    <div class="form-group">
                        <label>标签（可选，逗号分隔）</label>
                        <input type="text" name="tags" id="tagsInput" placeholder="如：搞笑,可爱,日常">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="submitBtn" disabled style="margin-top:20px;">提交审核</button>
                </form>

                <div class="upload-tips">
                    <h3>📌 上传须知</h3>
                    <ul>
                        <li>请上传与<?php echo $alias; ?>相关的表情包图片</li>
                        <li>不支持色情、暴力、违法内容</li>
                        <li>单文件大小不超过 <?php echo $maxSizeMB; ?>MB</li>
                        <li>提交后需要管理员审核才能展示</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p class="copyright" style="border:none;padding:0;">&copy; 2077 <?php echo $title; ?>. 并不保留所有权利。</p>
    </footer>

    <div class="toast" id="toast"></div>

    <script>
    var selectedFile = null;
    var zone = document.getElementById('uploadZone');
    var fileInput = document.getElementById('fileInput');
    var preview = document.getElementById('uploadPreview');
    var previewImg = document.getElementById('previewImg');
    var progressDiv = document.getElementById('uploadProgress');
    var progressFill = document.getElementById('progressFill');
    var progressText = document.getElementById('progressText');
    var submitBtn = document.getElementById('submitBtn');
    var form = document.getElementById('uploadForm');

    zone.addEventListener('click', function(e) {
        if (e.target.tagName !== 'BUTTON') {
            fileInput.click();
        }
    });

    zone.addEventListener('dragover', function(e) {
        e.preventDefault();
        zone.classList.add('drag-over');
    });

    zone.addEventListener('dragleave', function() {
        zone.classList.remove('drag-over');
    });

    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        zone.classList.remove('drag-over');
        handleFile(e.dataTransfer.files[0]);
    });

    fileInput.addEventListener('change', function() {
        if (fileInput.files.length) {
            handleFile(fileInput.files[0]);
        }
    });

    function handleFile(file) {
        var maxSize = <?php echo $maxSize; ?>;
        var allowedExts = '<?php echo $allowedExts; ?>'.split(',');

        if (!file) return;

        var ext = file.name.split('.').pop().toLowerCase();
        if (allowedExts.indexOf(ext) === -1) {
            showToast('不支持的文件类型: .' + ext, 'error');
            return;
        }

        if (file.size > maxSize) {
            showToast('文件过大，不能超过 <?php echo $maxSizeMB; ?>MB', 'error');
            return;
        }

        selectedFile = file;
        submitBtn.disabled = false;

        var reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
            document.querySelector('.upload-zone-inner').style.display = 'none';
        };
        reader.readAsDataURL(file);
    }

    function resetUpload() {
        selectedFile = null;
        fileInput.value = '';
        preview.style.display = 'none';
        document.querySelector('.upload-zone-inner').style.display = '';
        submitBtn.disabled = true;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!selectedFile) return;

        submitBtn.disabled = true;
        submitBtn.textContent = '提交中...';
        progressDiv.style.display = 'block';

        var xhr = new XMLHttpRequest();
        var formData = new FormData();
        formData.append('image', selectedFile);
        formData.append('title', document.getElementById('titleInput').value);
        formData.append('tags', document.getElementById('tagsInput').value);

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                var pct = Math.round((e.loaded / e.total) * 100);
                progressFill.style.width = pct + '%';
                progressText.textContent = pct + '%';
            }
        });

        xhr.addEventListener('load', function() {
            progressDiv.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.textContent = '提交审核';

            try {
                var res = JSON.parse(xhr.responseText);
                if (res.code === 0) {
                    showToast(res.msg, 'success');
                    resetUpload();
                    document.getElementById('titleInput').value = '';
                    document.getElementById('tagsInput').value = '';
                } else {
                    showToast(res.msg || '上传失败', 'error');
                }
            } catch (e) {
                showToast('服务器响应异常，请稍后重试（错误码: ' + xhr.status + '）', 'error');
            }
        });

        xhr.addEventListener('error', function() {
            progressDiv.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.textContent = '提交审核';
            showToast('上传失败，网络错误', 'error');
        });

        xhr.open('POST', '/api/upload');
        xhr.send(formData);
    });

    function showToast(msg, type) {
        var t = document.getElementById('toast');
        t.textContent = msg;
        t.className = 'toast ' + (type || 'success');
        t.style.display = 'block';
        setTimeout(function() { t.style.display = 'none'; }, 2500);
    }
    </script>
</body>
</html>
