/* =========================================================
   DIYU BOOKSTORE - MAIN JS
   ========================================================= */

// ---------- Toast Notification ----------
function showToast(message, type = 'success') {
    let toast = document.getElementById('globalToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'globalToast';
        toast.className = 'toast';
        document.body.appendChild(toast);
    }
    toast.style.background = type === 'error' ? '#DC2626' : '#4C2A85';
    toast.textContent = message;
    toast.classList.add('show');
    clearTimeout(window.__toastTimer);
    window.__toastTimer = setTimeout(() => toast.classList.remove('show'), 2500);
}

// ---------- Toggle Password Visibility ----------
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.toggle-eye');
    if (!btn) return;
    const input = btn.parentElement.querySelector('input');
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? '\u{1F441}' : '\u{1F576}';
});

// ---------- Generic Qty Stepper (data-target = input id) ----------
document.addEventListener('click', function (e) {
    const inc = e.target.closest('[data-qty-inc]');
    const dec = e.target.closest('[data-qty-dec]');
    if (!inc && !dec) return;
    const wrap = (inc || dec).closest('.qty-selector, .qty-mini');
    const input = wrap.querySelector('input, span[data-qty-value]');
    if (!input) return;
    let val = parseInt(input.value ?? input.textContent, 10) || 1;
    const min = parseInt(wrap.dataset.min || '1', 10);
    const max = parseInt(wrap.dataset.max || '999', 10);
    if (inc) val = Math.min(max, val + 1);
    if (dec) val = Math.max(min, val - 1);
    if (input.tagName === 'INPUT') input.value = val; else input.textContent = val;
    input.dispatchEvent(new Event('change', { bubbles: true }));
});

// ---------- Cart AJAX helpers ----------
function updateCartBadge(count) {
    document.querySelectorAll('.cart-badge').forEach(b => {
        b.textContent = count;
        b.style.display = count > 0 ? 'flex' : 'none';
    });
}

async function postJSON(url, data) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data),
    });
    return res.json();
}

// Add to cart buttons (data-book-id, optional data-qty-input)
document.addEventListener('click', async function (e) {
    const btn = e.target.closest('[data-add-cart]');
    if (!btn) return;
    e.preventDefault();
    const bookId = btn.dataset.addCart;
    let qty = 1;
    const qtyInputId = btn.dataset.qtyInput;
    if (qtyInputId) {
        const qtyInput = document.getElementById(qtyInputId);
        if (qtyInput) qty = parseInt(qtyInput.value, 10) || 1;
    }
    try {
        const result = await postJSON('ajax/cart_add.php', { buku_id: bookId, qty });
        if (result.success) {
            updateCartBadge(result.cartCount);
            showToast('Buku ditambahkan ke keranjang');
        } else {
            showToast(result.message || 'Silakan login terlebih dahulu', 'error');
            if (result.needLogin) setTimeout(() => (window.location.href = 'login.php'), 900);
        }
    } catch (err) {
        showToast('Terjadi kesalahan, coba lagi', 'error');
    }
});

// Cart quantity change on cart.php (data-cart-item)
document.addEventListener('change', async function (e) {
    const input = e.target.closest('[data-cart-qty]');
    if (!input) return;
    const cartId = input.dataset.cartQty;
    const qty = parseInt(input.value, 10) || 1;
    const result = await postJSON('ajax/cart_update.php', { keranjang_id: cartId, qty });
    if (result.success) {
        const subtotalEl = document.querySelector(`[data-subtotal-for="${cartId}"]`);
        if (subtotalEl) subtotalEl.textContent = result.subtotalFormatted;
        document.querySelectorAll('[data-cart-total]').forEach(el => el.textContent = result.totalFormatted);
        document.querySelectorAll('[data-cart-subtotal-all]').forEach(el => el.textContent = result.subtotalAllFormatted);
        updateCartBadge(result.cartCount);
    }
});

// Remove cart item
document.addEventListener('click', async function (e) {
    const btn = e.target.closest('[data-cart-remove]');
    if (!btn) return;
    if (!confirm('Hapus buku ini dari keranjang?')) return;
    const cartId = btn.dataset.cartRemove;
    const result = await postJSON('ajax/cart_remove.php', { keranjang_id: cartId });
    if (result.success) {
        const row = document.querySelector(`[data-row-for="${cartId}"]`);
        if (row) row.remove();
        updateCartBadge(result.cartCount);
        showToast('Buku dihapus dari keranjang');
        if (result.cartCount === 0) setTimeout(() => location.reload(), 600);
        else {
            document.querySelectorAll('[data-cart-total]').forEach(el => el.textContent = result.totalFormatted);
            document.querySelectorAll('[data-cart-subtotal-all]').forEach(el => el.textContent = result.subtotalAllFormatted);
        }
    }
});

// ---------- Confirm delete (admin tables) ----------
document.addEventListener('click', function (e) {
    const link = e.target.closest('[data-confirm-delete]');
    if (!link) return;
    if (!confirm(link.dataset.confirmDelete || 'Yakin ingin menghapus data ini?')) {
        e.preventDefault();
    }
});

// ---------- Simple modal open/close ----------
document.addEventListener('click', function (e) {
    const openBtn = e.target.closest('[data-modal-open]');
    if (openBtn) {
        const modal = document.getElementById(openBtn.dataset.modalOpen);
        if (modal) modal.classList.add('show');
    }
    const closeBtn = e.target.closest('[data-modal-close]');
    if (closeBtn) {
        const modal = closeBtn.closest('.modal-overlay');
        if (modal) modal.classList.remove('show');
    }
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('show');
    }
});

// ---------- Payment option selection (checkout.php) ----------
document.addEventListener('click', function (e) {
    const opt = e.target.closest('.payment-option');
    if (!opt) return;
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    opt.classList.add('selected');
    const radio = opt.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
});
