<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$alias = siteAlias();
$title = siteTitle();
$searchPlaceholder = "搜索可爱的{$alias}";
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - 最大(?)的<?php echo $alias; ?>图片网站</title>
    <meta name="description" content="<?php echo siteDescription(); ?>">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <?php echo renderLogo(); ?>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="<?php echo $searchPlaceholder; ?>" autocomplete="off">
                <button class="search-btn" onclick="doSearch()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
            </div>
            <nav class="nav-links">
                <a href="/" class="nav-link">精选<?php echo $alias; ?></a>
                <a href="/" class="nav-link active">全部<?php echo $alias; ?></a>
                <a href="/upload" class="nav-link nav-btn-primary">添加<?php echo $alias; ?></a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="section-header">
            <h2 class="section-title">热门<?php echo $alias; ?>推荐</h2>
            <div class="tabs">
                <button class="sort-tab active" data-sort="latest" onclick="switchSort('latest', this)">最新</button>
                <button class="sort-tab" data-sort="hottest" onclick="switchSort('hottest', this)">最热</button>
                <button class="sort-tab" data-sort="random" onclick="switchSort('random', this)">随机</button>
            </div>
        </div>

        <div class="sticker-grid" id="stickerGrid"></div>

        <div class="loading-state" id="loadingState">
            <div class="spinner"></div>
            <p>正在加载更多<?php echo $alias; ?>...</p>
        </div>

        <div class="empty-state" id="emptyState" style="display:none;">
            <div class="icon">📭</div>
            <p>还没有表情包，快来<a href="/upload">添加第一个</a>吧！</p>
        </div>

        <div class="load-more" id="loadMore" style="display:none;">
            <button class="btn btn-outline" onclick="loadMoreStickers()">加载更多可爱<?php echo $alias; ?></button>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-inner">
            <span>最大(?)的<?php echo $alias; ?>图片分享平台</span>
            <a href="/api-guide">📡 API</a>
        </div>
        <p class="copyright">&copy; 2077 <?php echo $title; ?>. 并不保留所有权利。</p>
    </footer>

    <div class="toast" id="toast"></div>

    <script src="assets/js/main.js"></script>
</body>
</html>
