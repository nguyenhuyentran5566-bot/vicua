/* ==========================================================
   VỊ CUA — main.js (Vanilla JS)
   ========================================================== */
(() => {
  const $  = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const fmt = n => String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.') + 'đ';

  /* ---------- Popup thông báo (toast) ---------- */
  const toastEl = $('#toast');
  let timer;
  function toast(msg) {
    if (!msg) return;
    toastEl.textContent = msg;
    toastEl.classList.add('show');
    clearTimeout(timer);
    timer = setTimeout(() => toastEl.classList.remove('show'), 2300);
  }
  toast(toastEl.dataset.flash);
  document.addEventListener('click', e => {
    const t = e.target.closest('[data-toast]');
    if (t) { e.preventDefault(); toast(t.dataset.toast); }
  });

  /* ---------- Sao chép mã ---------- */
  document.addEventListener('click', e => {
    const c = e.target.closest('[data-copy]');
    if (!c) return;
    (navigator.clipboard?.writeText(c.dataset.copy) ?? Promise.reject())
      .then(() => toast('Đã sao chép ' + c.dataset.copy), () => toast('Mã của bạn: ' + c.dataset.copy));
  });

  /* ---------- Giỏ hàng (gọi api/cart.php, server tính tiền) ---------- */
  async function cartApi(action, data = {}) {
    try {
      const res = await fetch('api/cart.php', { method: 'POST', body: new URLSearchParams({ action, ...data }) });
      const j = await res.json();
      if (j.ok) render(j);
      toast(j.msg);
    } catch (err) {
      toast('Không kết nối được máy chủ');
    }
  }

  function render(j) {
    $$('[data-cart-count]').forEach(el => el.textContent = j.count);
    $$('[data-cart-total]').forEach(el => el.textContent = j.sum.subtotal);
    $$('.cnt').forEach(el => el.hidden = !j.count);
    const bar = $('.cartbar');
    if (bar) bar.hidden = !j.count;

    // Trang giỏ hàng
    $$('[data-sum]').forEach(el => el.textContent = j.sum[el.dataset.sum]);
    $$('[data-line]').forEach(row => {
      const l = j.lines[row.dataset.line];
      if (!l) return row.remove();
      $('[data-q]', row).textContent = l.qty;
      $('[data-lt]', row).textContent = l.total;
      $$('[data-spicy]', row).forEach(b => b.classList.toggle('on', (b.dataset.spicy === '1') === l.spicy));
    });
    const pi = $('#promoInput');
    if (pi) pi.value = j.promo;
    if (!j.count && $('#cart-list')) location.reload();   // hiện trạng thái giỏ trống
  }

  document.addEventListener('click', e => {
    const b = e.target.closest('[data-add],[data-qty],[data-remove],[data-spicy],[data-clear],[data-apply],[data-code]');
    if (!b) return;
    const row = b.closest('[data-line]');
    const id = b.dataset.add || row?.dataset.line;
    if (b.dataset.add)            cartApi('add', { id });
    else if (b.dataset.qty)       cartApi('qty', { id, d: b.dataset.qty });
    else if ('remove' in b.dataset) cartApi('remove', { id });
    else if (b.dataset.spicy)     cartApi('spicy', { id, val: b.dataset.spicy });
    else if ('clear' in b.dataset) { if (confirm('Xóa toàn bộ giỏ hàng?')) cartApi('clear'); }
    else if ('apply' in b.dataset) cartApi('promo', { code: $('#promoInput').value });
    else if (b.dataset.code) { $('#promoInput').value = b.dataset.code; cartApi('promo', { code: b.dataset.code }); }
  });

  /* ---------- Chuyển tab / lọc danh mục ----------
     <div data-filter="x"> chứa các .chip[data-f]; phần tử bị lọc có data-g="x" data-tags="a|b" */
  $$('[data-filter]').forEach(box => box.addEventListener('click', e => {
    const c = e.target.closest('.chip');
    if (!c) return;
    const g = box.dataset.filter, f = c.dataset.f;
    $$('.chip', box).forEach(x => x.classList.toggle('on', x === c));
    const items = $$(`[data-g="${g}"]`);
    items.forEach(el => el.hidden = f !== 'all' && !el.dataset.tags.split('|').includes(f));
    $$(`[data-only-all="${g}"]`).forEach(el => el.hidden = f !== 'all');
    $$(`[data-empty="${g}"]`).forEach(el => el.hidden = items.some(x => !x.hidden));
  }));

  /* ---------- Slider banner trang chủ ---------- */
  const slides = $('.slides');
  if (slides) {
    const dots = $$('.dots i');
    let n = 0;
    const go = i => {
      n = (i + dots.length) % dots.length;
      slides.style.transform = `translateX(-${n * 100}%)`;
      dots.forEach((d, k) => d.classList.toggle('on', k === n));
    };
    dots.forEach((d, k) => d.addEventListener('click', () => go(k)));
    go(0);
    if (!matchMedia('(prefers-reduced-motion: reduce)').matches) setInterval(() => go(n + 1), 4500);
  }

  /* ---------- Đặt bàn: cập nhật bảng tóm tắt ---------- */
  const bf = $('#bookForm');
  if (bf) {
    const days = ['Chủ nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
    const show = () => {
      const f = new FormData(bf), d = new Date(f.get('date') + 'T00:00');
      const set = (k, v) => $(`[data-b="${k}"]`).textContent = v;
      set('date', isNaN(d) ? '—' : `${days[d.getDay()]}, ${f.get('date').split('-').reverse().join('/')}`);
      set('time', f.get('time') || '—');
      set('guests', f.get('guests') + ' người');
      set('area', f.get('area') || '—');
    };
    bf.addEventListener('change', show);
    bf.addEventListener('input', show);
    bf.addEventListener('click', e => {
      const b = e.target.closest('[data-guests]');
      if (!b) return;
      const g = bf.elements.guests;
      g.value = Math.min(20, Math.max(1, +g.value + +b.dataset.guests));
      show();
    });
    show();
  }

  /* ---------- Thanh toán: nhận hàng / giờ / tổng tiền ---------- */
  const cf = $('#checkoutForm');
  if (cf) {
    const upd = () => {
      const f = new FormData(cf), ship = f.get('receive') === 'delivery', later = f.get('when') === 'later';
      $('#addrBox').hidden = !ship;
      cf.elements.address.required = ship;
      $('#whenAt').hidden = !later;
      cf.elements.at.required = later;
      const fee = ship ? +cf.dataset.ship : 0;
      $('[data-fee-row]').hidden = !ship;
      $('[data-fee]').textContent = fee ? fmt(fee) : 'Miễn phí';
      $('[data-grand]').textContent = fmt(+cf.dataset.base + fee);
    };
    cf.addEventListener('change', upd);
    upd();
  }
})();
