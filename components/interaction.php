<?php
/**
 * Salem Dominion Ministries - Interaction Bar Component
 * Include this in any page to add Like/Share/Comment functionality.
 * Requires: $interaction_type (sermon|news|event|gallery) and $interaction_id (content ID)
 */
if (!isset($interaction_type) || !isset($interaction_id)) return;

$userLogged = !empty($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
$userName   = $_SESSION['user_name'] ?? '';
$csrfToken  = csrfToken();
?>

<style>
.sdm-interactions { border-top: 1px solid #e2e8f0; padding-top: 16px; margin-top: 16px; }
.sdm-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.sdm-action-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border: 2px solid #e2e8f0; border-radius: 10px;
    background: #fff; color: #475569; font-size: 0.85rem; font-weight: 600;
    font-family: 'Montserrat', sans-serif; cursor: pointer; transition: all 0.3s;
    text-decoration: none; white-space: nowrap;
}
.sdm-action-btn:hover { border-color: #0ea5e9; color: #0ea5e9; background: rgba(14,165,233,0.05); }
.sdm-action-btn.liked { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.05); }
.sdm-action-btn.liked i { animation: sdmHeartPop 0.3s ease; }
@keyframes sdmHeartPop { 0%{transform:scale(1)} 50%{transform:scale(1.3)} 100%{transform:scale(1)} }
.sdm-action-btn i { font-size: 0.9rem; }
.sdm-share-menu { position: relative; display: inline-block; }
.sdm-share-dropdown {
    display: none; position: absolute; bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%);
    background: #fff; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    padding: 8px; z-index: 100; min-width: 180px;
}
.sdm-share-dropdown.show { display: block; animation: sdmFadeIn 0.2s ease; }
@keyframes sdmFadeIn { from{opacity:0;transform:translateX(-50%) translateY(4px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
.sdm-share-dropdown a {
    display: flex; align-items: center; gap: 10px; padding: 10px 14px;
    border-radius: 8px; font-size: 0.85rem; font-family: 'Montserrat', sans-serif;
    color: #475569; text-decoration: none; transition: background 0.2s;
}
.sdm-share-dropdown a:hover { background: #f1f5f9; }
.sdm-share-dropdown a i { width: 20px; text-align: center; }
.sdm-share-dropdown .fa-whatsapp { color: #25d366; }
.sdm-share-dropdown .fa-facebook-f { color: #1877f2; }
.sdm-share-dropdown .fa-twitter { color: #1da1f2; }
.sdm-share-dropdown .fa-telegram { color: #0088cc; }
.sdm-share-dropdown .fa-link { color: #64748b; }

.sdm-comments-section { margin-top: 20px; }
.sdm-comments-header { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
.sdm-comments-header span { background: #0ea5e9; color: #fff; font-size: 0.75rem; padding: 2px 8px; border-radius: 10px; font-family: 'Montserrat', sans-serif; font-weight: 600; }
.sdm-comment-form { display: flex; gap: 12px; margin-bottom: 20px; }
.sdm-comment-form .avatar {
    width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #0ea5e9, #0284c7);
    display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700;
    font-size: 0.85rem; flex-shrink: 0; font-family: 'Montserrat', sans-serif;
}
.sdm-comment-form .form-body { flex: 1; }
.sdm-comment-form textarea {
    width: 100%; padding: 12px 14px; border: 2px solid #e2e8f0; border-radius: 12px;
    font-size: 0.9rem; font-family: 'Montserrat', sans-serif; resize: vertical;
    min-height: 60px; transition: border-color 0.3s; outline: none; color: #0f172a;
}
.sdm-comment-form textarea:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.1); }
.sdm-comment-form .char-count { font-size: 0.75rem; color: #94a3b8; text-align: right; margin-top: 4px; }
.sdm-comment-form .form-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; }
.sdm-comment-form .btn-post {
    padding: 8px 20px; background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff;
    border: none; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    font-family: 'Montserrat', sans-serif; transition: all 0.3s;
}
.sdm-comment-form .btn-post:hover { background: linear-gradient(135deg, #0284c7, #0369a1); }
.sdm-comment-form .btn-post:disabled { opacity: 0.5; cursor: not-allowed; }
.sdm-comment-form .btn-cancel {
    padding: 8px 16px; background: transparent; color: #64748b; border: 2px solid #e2e8f0;
    border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer;
    font-family: 'Montserrat', sans-serif;
}
.sdm-comment-item { display: flex; gap: 12px; padding: 14px 0; border-bottom: 1px solid #f1f5f9; animation: sdmFadeIn 0.3s ease; }
.sdm-comment-item .avatar {
    width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #fbbf24, #f59e0b);
    display: flex; align-items: center; justify-content: center; color: #0f172a; font-weight: 700;
    font-size: 0.75rem; flex-shrink: 0; font-family: 'Montserrat', sans-serif;
}
.sdm-comment-item .comment-body { flex: 1; }
.sdm-comment-item .comment-author { font-weight: 700; color: #0f172a; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; }
.sdm-comment-item .comment-time { color: #94a3b8; font-size: 0.75rem; margin-left: 8px; }
.sdm-comment-item .comment-text { color: #475569; font-size: 0.9rem; line-height: 1.6; margin-top: 4px; word-wrap: break-word; }
.sdm-comment-item .comment-delete { color: #94a3b8; font-size: 0.75rem; cursor: pointer; margin-top: 4px; border: none; background: none; padding: 0; font-family: 'Montserrat', sans-serif; }
.sdm-comment-item .comment-delete:hover { color: #ef4444; }
.sdm-login-prompt { text-align: center; padding: 16px; background: #f8fafc; border-radius: 12px; color: #64748b; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; }
.sdm-login-prompt a { color: #0ea5e9; font-weight: 700; text-decoration: none; }
.sdm-login-prompt a:hover { text-decoration: underline; }
.sdm-load-more { display: block; width: 100%; padding: 10px; border: 2px dashed #e2e8f0; border-radius: 10px; background: transparent; color: #64748b; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; cursor: pointer; transition: all 0.3s; text-align: center; }
.sdm-load-more:hover { border-color: #0ea5e9; color: #0ea5e9; }
.sdm-copied-toast { position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background: #0f172a; color: #fff; padding: 10px 24px; border-radius: 10px; font-size: 0.85rem; font-family: 'Montserrat', sans-serif; z-index: 9999; animation: sdmToastIn 0.3s ease; }
@keyframes sdmToastIn { from{opacity:0;transform:translateX(-50%) translateY(10px)} to{opacity:1;transform:translateX(-50%) translateY(0)} }
@media(max-width:480px) {
    .sdm-actions { gap: 8px; }
    .sdm-action-btn { padding: 7px 12px; font-size: 0.8rem; }
    .sdm-comment-form textarea { min-height: 50px; font-size: 0.85rem; }
}
</style>

<script>
(function() {
    const TYPE = '<?= $interaction_type ?>';
    const ID   = <?= intval($interaction_id) ?>;
    const CSRF = '<?= $csrfToken ?>';
    const LOGGED_IN = <?= $userLogged ? 'true' : 'false' ?>;
    const CURRENT_URL = encodeURIComponent(window.location.href);
    let commentsPage = 1;
    let loadingComments = false;

    window.sdmInteraction = { loadComments, refreshCounts };

    function getHeaders() {
        return { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF };
    }

    function getVisitorHash() {
        let hash = localStorage.getItem('sdm_visitor_hash');
        if (!hash) {
            hash = 'v_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
            localStorage.setItem('sdm_visitor_hash', hash);
        }
        return hash;
    }

    // ── LIKE ──
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.sdm-like-btn');
        if (!btn) return;
        e.preventDefault();
        fetch('api.php?action=toggle_like', {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({ content_type: TYPE, content_id: ID, visitor_hash: getVisitorHash() })
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success) { if (res.message) alert(res.message); return; }
            const countEl = btn.querySelector('.like-count');
            if (countEl) countEl.textContent = res.count || 0;
            btn.classList.toggle('liked', res.liked);
            const icon = btn.querySelector('i');
            if (icon) {
                icon.className = res.liked ? 'fas fa-heart' : 'far fa-heart';
            }
        })
        .catch(() => {});
    });

    // ── SHARE ──
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.sdm-share-btn');
        if (btn) {
            e.preventDefault();
            const dd = btn.parentElement.querySelector('.sdm-share-dropdown');
            document.querySelectorAll('.sdm-share-dropdown.show').forEach(d => { if (d !== dd) d.classList.remove('show'); });
            dd.classList.toggle('show');
            return;
        }
        if (!e.target.closest('.sdm-share-dropdown')) {
            document.querySelectorAll('.sdm-share-dropdown.show').forEach(d => d.classList.remove('show'));
        }
    });

    document.addEventListener('click', function(e) {
        const link = e.target.closest('.sdm-share-link');
        if (!link) return;
        e.preventDefault();
        const platform = link.dataset.platform;
        const shareText = document.title;
        const shareUrl = window.location.href;
        let url = '';

        switch(platform) {
            case 'whatsapp': url = 'https://wa.me/?text=' + encodeURIComponent(shareText + ' ' + shareUrl); break;
            case 'facebook': url = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(shareUrl); break;
            case 'twitter':  url = 'https://twitter.com/intent/tweet?url=' + encodeURIComponent(shareUrl) + '&text=' + encodeURIComponent(shareText); break;
            case 'telegram': url = 'https://t.me/share/url?url=' + encodeURIComponent(shareUrl) + '&text=' + encodeURIComponent(shareText); break;
            case 'link':
                navigator.clipboard.writeText(shareUrl).then(() => {
                    showCopiedToast();
                });
                document.querySelectorAll('.sdm-share-dropdown.show').forEach(d => d.classList.remove('show'));
                fetch('api.php?action=record_share', {
                    method: 'POST',
                    headers: getHeaders(),
                    body: JSON.stringify({ content_type: TYPE, content_id: ID, platform: 'link' })
                }).then(r => r.json()).then(res => {
                    if (res.success) {
                        const countEl = document.querySelector('.sdm-share-count');
                        if (countEl) countEl.textContent = res.count || 0;
                    }
                });
                return;
        }

        if (url) {
            window.open(url, '_blank', 'width=600,height=400,scrollbars=yes');
        }

        fetch('api.php?action=record_share', {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({ content_type: TYPE, content_id: ID, platform: platform })
        }).then(r => r.json()).then(res => {
            if (res.success) {
                const countEl = document.querySelector('.sdm-share-count');
                if (countEl) countEl.textContent = res.count || 0;
            }
        });

        document.querySelectorAll('.sdm-share-dropdown.show').forEach(d => d.classList.remove('show'));
    });

    function showCopiedToast() {
        const existing = document.querySelector('.sdm-copied-toast');
        if (existing) existing.remove();
        const toast = document.createElement('div');
        toast.className = 'sdm-copied-toast';
        toast.textContent = 'Link copied to clipboard!';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }

    // ── COMMENTS ──
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.sdm-toggle-comments');
        if (!btn) return;
        e.preventDefault();
        const section = btn.closest('.sdm-interactions').querySelector('.sdm-comments-section');
        if (section) {
            section.style.display = section.style.display === 'none' ? 'block' : 'none';
            if (section.style.display !== 'none' && !section.dataset.loaded) {
                loadComments(1);
            }
        }
    });

    document.addEventListener('submit', function(e) {
        const form = e.target.closest('.sdm-comment-form-form');
        if (!form) return;
        e.preventDefault();
        if (!LOGGED_IN) { alert('Please log in to comment.'); return; }
        const textarea = form.querySelector('textarea');
        const btn = form.querySelector('.btn-post');
        const text = textarea.value.trim();
        if (!text) return;
        btn.disabled = true;
        fetch('api.php?action=add_comment', {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({ content_type: TYPE, content_id: ID, comment: text })
        })
        .then(r => r.json())
        .then(res => {
            btn.disabled = false;
            if (!res.success) { alert(res.message || 'Failed to post comment.'); return; }
            textarea.value = '';
            const counter = form.querySelector('.char-count');
            if (counter) counter.textContent = '0/2000';
            loadComments(1);
            refreshCounts();
        })
        .catch(() => { btn.disabled = false; });
    });

    document.addEventListener('input', function(e) {
        const ta = e.target.closest('.sdm-comment-form textarea');
        if (!ta) return;
        const counter = ta.closest('.sdm-comment-form').querySelector('.char-count');
        if (counter) counter.textContent = ta.value.length + '/2000';
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.sdm-load-more');
        if (!btn) return;
        e.preventDefault();
        commentsPage++;
        loadComments(commentsPage);
    });

    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.sdm-comment-delete');
        if (!btn) return;
        e.preventDefault();
        if (!confirm('Delete this comment?')) return;
        const commentId = btn.dataset.id;
        fetch('api.php?action=delete_comment', {
            method: 'POST',
            headers: getHeaders(),
            body: JSON.stringify({ comment_id: commentId })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                loadComments(1);
                refreshCounts();
            } else {
                alert(res.message || 'Failed to delete.');
            }
        });
    });

    function loadComments(page) {
        if (loadingComments) return;
        loadingComments = true;
        fetch(`api.php?action=get_comments&content_type=${TYPE}&content_id=${ID}&page=${page}&limit=10`)
        .then(r => r.json())
        .then(res => {
            loadingComments = false;
            const list = document.querySelector(`#sdm-comments-list-${TYPE}-${ID}`);
            const header = document.querySelector(`#sdm-comments-header-${TYPE}-${ID}`);
            if (!list) return;
            if (page === 1) list.innerHTML = '';
            if (header) header.innerHTML = `<i class="fas fa-comments"></i> Comments <span>${res.total || 0}</span>`;
            if (!res.data || res.data.length === 0) {
                if (page === 1) list.innerHTML = '<p style="text-align:center;color:#94a3b8;padding:16px;font-size:0.85rem;font-family:Montserrat,sans-serif;">No comments yet. Be the first to share your thoughts!</p>';
                const moreBtn = list.parentElement.querySelector('.sdm-load-more');
                if (moreBtn) moreBtn.style.display = 'none';
                return;
            }
            const userId = <?= $_SESSION['user_id'] ?? 0 ?>;
            res.data.forEach(c => {
                const initials = c.user_name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
                let html = `<div class="sdm-comment-item">
                    <div class="avatar">${initials}</div>
                    <div class="comment-body">
                        <div><span class="comment-author">${escapeHtml(c.user_name)}</span><span class="comment-time">${timeAgo(c.created_at)}</span></div>
                        <div class="comment-text">${escapeHtml(c.comment)}</div>
                        ${userId && c.user_id == userId ? `<button class="sdm-comment-delete comment-delete" data-id="${c.id}"><i class="fas fa-trash-alt me-1"></i>Delete</button>` : ''}
                    </div>
                </div>`;
                list.insertAdjacentHTML('beforeend', html);
            });
            list.dataset.loaded = 'true';
            const moreBtn = list.parentElement.querySelector('.sdm-load-more');
            if (moreBtn) {
                const totalPages = res.pagination ? res.pagination.total_pages : 1;
                moreBtn.style.display = (page >= totalPages) ? 'none' : 'block';
            }
        })
        .catch(() => { loadingComments = false; });
    }

    function refreshCounts() {
        fetch(`api.php?action=get_counts&content_type=${TYPE}&content_id=${ID}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const likeEl = document.querySelector('.sdm-like-count');
            const commentEl = document.querySelector('.sdm-comment-count');
            const shareEl = document.querySelector('.sdm-share-count');
            if (likeEl) likeEl.textContent = res.likes || 0;
            if (commentEl) commentEl.textContent = res.comments || 0;
            if (shareEl) shareEl.textContent = res.shares || 0;
            if (res.user_liked) {
                const btn = document.querySelector('.sdm-like-btn');
                if (btn) { btn.classList.add('liked'); btn.querySelector('i').className = 'fas fa-heart'; }
            }
        });
    }

    function timeAgo(dt) {
        const diff = Math.floor(Date.now() / 1000) - Math.floor(new Date(dt).getTime() / 1000);
        if (diff < 60) return 'Just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
        return new Date(dt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function escapeHtml(t) {
        const d = document.createElement('div');
        d.textContent = t;
        return d.innerHTML;
    }

    // Load initial counts on page load
    refreshCounts();
})();
</script>
