(function() {
    'use strict';

    var currentSort = 'latest';
    var currentPage = 1;
    var hasMore = true;
    var isLoading = false;
    var allStickers = [];

    function showToast(msg, type) {
        var t = document.getElementById('toast');
        if (!t) return;
        t.textContent = msg;
        t.className = 'toast ' + (type || 'success');
        t.style.display = 'block';
        clearTimeout(t._timeout);
        t._timeout = setTimeout(function() {
            t.style.display = 'none';
        }, 2000);
    }

    function copyToClipboard(imageUrl) {
        if (!navigator.clipboard) {
            showToast('⚠️ 浏览器不支持剪贴板功能，请右键保存图片', 'info');
            return Promise.resolve(false);
        }

        return fetch(imageUrl, { mode: 'cors', credentials: 'same-origin' })
            .then(function(res) {
                if (!res.ok) {
                    throw new Error('图片加载失败: ' + res.status);
                }
                return res.blob();
            })
            .then(function(blob) {
                var mimeType = blob.type || 'image/png';
                return navigator.clipboard.write([
                    new ClipboardItem({ [mimeType]: blob })
                ]);
            })
            .then(function() {
                showToast('✅ 复制成功！图片已复制到剪贴板', 'success');
                return true;
            })
            .catch(function(err) {
                console.error('Clipboard copy error:', err);
                return fallbackCopyToClipboard(imageUrl);
            });
    }

    function fallbackCopyToClipboard(imageUrl) {
        return new Promise(function(resolve) {
            var img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = function() {
                try {
                    var canvas = document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    var ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    
                    canvas.toBlob(function(blob) {
                        if (navigator.clipboard && blob) {
                            navigator.clipboard.write([
                                new ClipboardItem({ 'image/png': blob })
                            ]).then(function() {
                                showToast('✅ 复制成功！图片已复制到剪贴板', 'success');
                                resolve(true);
                            }).catch(function() {
                                showToast('⚠️ 复制失败，请右键保存图片', 'error');
                                resolve(false);
                            });
                        } else {
                            showToast('⚠️ 当前环境不支持自动复制，请右键保存图片', 'info');
                            resolve(false);
                        }
                    }, 'image/png');
                } catch (e) {
                    console.error('Fallback copy error:', e);
                    showToast('⚠️ 复制失败，请右键保存图片', 'error');
                    resolve(false);
                }
            };
            img.onerror = function() {
                showToast('⚠️ 图片加载失败，请右键保存图片', 'error');
                resolve(false);
            };
            img.src = imageUrl;
        });
    }

    function trackStats(id, action) {
        fetch('/api/stats', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, action: action })
        }).catch(function() {});
    }

    function downloadSticker(sticker) {
        var a = document.createElement('a');
        a.href = sticker.original_url;
        a.download = sticker.filename || ('sticker_' + sticker.id);
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        trackStats(sticker.id, 'download');
        showToast('⬇ 下载中...', 'success');
    }

    function createStickerCard(sticker) {
        var card = document.createElement('div');
        card.className = 'sticker-card';
        card.setAttribute('data-id', sticker.id);

        card.innerHTML =
            '<div class="card-img-wrapper">' +
                '<img class="card-img lazy-img" data-src="' + sticker.image_url + '" src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 400 400%22%3E%3Crect fill=%22%231a1a2e%22 width=%22400%22 height=%22400%22/%3E%3C/svg%3E" alt="' + (sticker.title || 'sticker') + '" loading="lazy">' +
            '</div>' +
            '<div class="card-info">' +
                '<div class="card-title">' + (sticker.title || '未命名') + '</div>' +
                '<div class="card-meta">' +
                    '<span>👁 ' + sticker.views_formatted + '</span>' +
                    '<span>⬇ ' + sticker.downloads_formatted + '</span>' +
                '</div>' +
            '</div>';

        card.querySelector('.card-img').addEventListener('click', function(e) {
            e.stopPropagation();
            copyToClipboard(sticker.original_url);
            trackStats(sticker.id, 'view');
        });

        return card;
    }

    function renderStickers(stickers, append) {
        var grid = document.getElementById('stickerGrid');
        if (!grid) return;

        if (!append) {
            grid.innerHTML = '';
        }

        if (stickers.length === 0 && !append) {
            document.getElementById('emptyState').style.display = '';
            document.getElementById('loadingState').style.display = 'none';
            return;
        }

        document.getElementById('emptyState').style.display = 'none';

        stickers.forEach(function(sticker) {
            grid.appendChild(createStickerCard(sticker));
        });

        initLazyLoad();
    }

    function initLazyLoad() {
        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        var src = img.getAttribute('data-src');
                        if (src) {
                            img.src = src;
                            img.onload = function() {
                                img.classList.add('loaded');
                            };
                            img.removeAttribute('data-src');
                            observer.unobserve(img);
                        }
                    }
                });
            }, { rootMargin: '200px' });

            document.querySelectorAll('.lazy-img[data-src]').forEach(function(img) {
                observer.observe(img);
            });
        } else {
            document.querySelectorAll('.lazy-img[data-src]').forEach(function(img) {
                img.src = img.getAttribute('data-src');
                img.classList.add('loaded');
                img.removeAttribute('data-src');
            });
        }
    }

    function loadStickers(page, sort, search, append) {
        if (isLoading) return;
        isLoading = true;

        var loadingEl = document.getElementById('loadingState');
        if (loadingEl && !append) {
            loadingEl.style.display = '';
        }

        var url = '/api/list?page=' + page + '&sort=' + sort;
        if (search) {
            url = '/api/search?q=' + encodeURIComponent(search) + '&page=' + page;
        }

        fetch(url)
            .then(function(res) {
                return res.text().then(function(text) {
                    try { return JSON.parse(text); } catch(e) { return { code: 500, msg: '服务器响应异常' }; }
                });
            })
            .then(function(res) {
                isLoading = false;
                if (loadingEl) loadingEl.style.display = 'none';

                if (res.code === 0) {
                    var list = res.data.list || [];
                    if (page === 1) {
                        allStickers = list;
                    } else {
                        allStickers = allStickers.concat(list);
                    }

                    hasMore = list.length >= 10;

                    renderStickers(append ? list : allStickers, append);

                    var loadMoreEl = document.getElementById('loadMore');
                    if (loadMoreEl) {
                        loadMoreEl.style.display = hasMore ? '' : 'none';
                    }
                }
            })
            .catch(function() {
                isLoading = false;
                if (loadingEl) loadingEl.style.display = 'none';
            });
    }

    function loadMoreStickers() {
        currentPage++;
        loadStickers(currentPage, currentSort, document.getElementById('searchInput').value.trim(), true);
    }

    function switchSort(sort, el) {
        currentSort = sort;
        currentPage = 1;
        allStickers = [];
        hasMore = true;

        document.querySelectorAll('.sort-tab').forEach(function(tab) {
            tab.classList.remove('active');
        });
        if (el) el.classList.add('active');

        document.getElementById('stickerGrid').innerHTML = '';
        loadStickers(currentPage, currentSort, '');
    }

    function doSearch() {
        var q = document.getElementById('searchInput').value.trim();
        currentPage = 1;
        allStickers = [];
        hasMore = true;

        document.getElementById('stickerGrid').innerHTML = '';
        if (q) {
            loadStickers(currentPage, 'latest', q, false);
        } else {
            loadStickers(currentPage, currentSort, '', false);
        }
    }

    window.switchSort = switchSort;
    window.doSearch = doSearch;
    window.loadMoreStickers = loadMoreStickers;

    var searchInput = document.getElementById('searchInput');
    if (searchInput) {
        var debounceTimer = null;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                doSearch();
            }, 500);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                clearTimeout(debounceTimer);
                doSearch();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('stickerGrid')) {
            loadStickers(1, currentSort, '', false);
        }
    });
})();
