// ============================================================
//  MAIN.JS - GlowViet Frontend Logic
// ============================================================

// ── Helpers ──────────────────────────────────────────────────

function formatPrice(value) {
    return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
}

function showToast(message, type = 'success') {
    // Remove existing toasts
    document.querySelectorAll('.glow-toast').forEach(t => t.remove());

    const icons = { success: '✓', error: '✗', warning: '⚠', info: 'ℹ' };
    const colors = { success: '#10B981', error: '#EF4444', warning: '#F59E0B', info: '#6366F1' };

    const toast = document.createElement('div');
    toast.className = 'glow-toast';
    toast.innerHTML = `<span class="toast-icon">${icons[type]}</span> ${message}`;
    toast.style.cssText = `
        position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        background: #fff; color: #1E1B4B; padding: 14px 20px;
        border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,.15);
        display: flex; align-items: center; gap: 10px; font-weight: 600;
        border-left: 4px solid ${colors[type]};
        animation: slideInRight .3s ease;
        max-width: 360px; font-size: .9rem;
    `;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .4s'; setTimeout(() => toast.remove(), 400); }, 3500);
}

// ── Navbar Scroll Effect ─────────────────────────────────────

const mainNav = document.getElementById('mainNav');
if (mainNav) {
    window.addEventListener('scroll', () => {
        mainNav.classList.toggle('scrolled', window.scrollY > 50);
    });
}

// ── AJAX Add to Cart (intercept form submit) ─────────────────

document.addEventListener('submit', async function(e) {
    const form = e.target;
    if (!form.classList.contains('add-cart-form') && !form.classList.contains('add-to-cart-form')) return;
    if (!IS_LOGGED_IN) {
        window.location = APP_URL + '/auth/login';
        return;
    }

    e.preventDefault();
    const formData = new FormData(form);
    console.log("Adding to cart:", Object.fromEntries(formData)); // Debug line
    const btn = form.querySelector('button[type=submit]');
    const origText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
    btn.disabled = true;

    try {
        const res = await fetch(APP_URL + '/cart/add', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        });
        const data = await res.json();
        if (data.success) {
            // Update cart badge
            const badge = document.getElementById('cartBadge');
            if (badge) { badge.textContent = data.count; badge.classList.add('bump'); setTimeout(() => badge.classList.remove('bump'), 400); }
            showToast('Đã thêm vào giỏ hàng!', 'success');
            btn.innerHTML = '<i class="bi bi-check-lg me-1"></i>Đã thêm!';
            setTimeout(() => { btn.innerHTML = origText; btn.disabled = false; }, 1800);
        } else {
            showToast(data.message || 'Có lỗi xảy ra', 'error');
            btn.innerHTML = origText; btn.disabled = false;
        }
    } catch {
        showToast('Không thể kết nối máy chủ', 'error');
        btn.innerHTML = origText; btn.disabled = false;
    }
});

// ── Wishlist Toggle ───────────────────────────────────────────

async function toggleWishlist(productId, btn) {
    if (!IS_LOGGED_IN) { window.location = APP_URL + '/auth/login'; return; }

    try {
        const formData = new FormData();
        formData.append('product_id', productId);

        const res = await fetch(APP_URL + '/wishlist/toggle', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData,
        });
        const data = await res.json();
        if (data.success) {
            const icon = btn.querySelector('i');
            if (data.action === 'added') {
                btn.classList.add('active');
                if (icon) { icon.classList.remove('bi-heart'); icon.classList.add('bi-heart-fill'); }
                showToast('Đã thêm vào yêu thích 💕', 'success');
            } else {
                btn.classList.remove('active');
                if (icon) { icon.classList.remove('bi-heart-fill'); icon.classList.add('bi-heart'); }
                showToast('Đã xoá khỏi yêu thích', 'info');
            }
        }
    } catch { showToast('Lỗi kết nối', 'error'); }
}

// ── Coupon Apply ─────────────────────────────────────────────

document.getElementById('btnApplyCoupon')?.addEventListener('click', async function() {
    const code    = document.getElementById('couponInput')?.value;
    const msgEl   = document.getElementById('couponMessage');
    const totalEl = document.getElementById('summaryTotal');

    if (!code?.trim()) { msgEl.innerHTML = '<span class="text-danger">Vui lòng nhập mã</span>'; return; }

    try {
        const res = await fetch(APP_URL + '/order/validateCoupon', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ code, total: parseFloat(totalEl?.textContent) }),
        });
        const data = await res.json();
        if (data.valid) {
            msgEl.innerHTML = `<span class="text-success">✓ Giảm ${formatPrice(data.discount)}</span>`;
            document.getElementById('discountRow')?.classList.remove('d-none');
            document.getElementById('discountAmt').textContent = '-' + formatPrice(data.discount);
            showToast('Áp dụng mã giảm giá thành công!', 'success');
        } else {
            msgEl.innerHTML = `<span class="text-danger">${data.message}</span>`;
        }
    } catch { msgEl.innerHTML = '<span class="text-danger">Lỗi kết nối</span>'; }
});

// ── Image lazy loading enhancement ──────────────────────────

if ('IntersectionObserver' in window) {
    const imgObs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.classList.add('fade-in');
                    imgObs.unobserve(img);
                }
            }
        });
    }, { rootMargin: '100px' });

    document.querySelectorAll('img[data-src]').forEach(img => imgObs.observe(img));
}

// ── Smooth scroll for anchors ─────────────────────────────────

document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const target = document.querySelector(a.getAttribute('href'));
        if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
    });
});

// ── Scroll reveal animation ───────────────────────────────────

if ('IntersectionObserver' in window) {
    const revealObs = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.product-card, .feature-card, .category-card, .admin-stat-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity .5s ease, transform .5s ease';
        revealObs.observe(el);
    });
}

// ── Toast animation keyframe ─────────────────────────────────

const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to   { transform: translateX(0); opacity: 1; }
    }
    .bump { animation: bump .3s ease; }
    @keyframes bump {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.4); background: #ff0080; }
    }
`;
document.head.appendChild(style);
