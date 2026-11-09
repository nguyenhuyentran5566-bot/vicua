<?php
/* ============================================================
   VỊ CUA — dữ liệu & hàm dùng chung (mọi trang đều require file này)
   Dữ liệu demo lưu trong $_SESSION. Muốn dùng MySQL: thay các hàm
   menu_items(), cart_summary(), và các chỗ ghi $_SESSION['orders'|'bookings'].
   ============================================================ */
const DEMO_SEED    = true;     // true: lần đầu vào site có sẵn giỏ hàng + lịch đặt bàn mẫu
const SERVICE_RATE = 0.01;     // phí phục vụ = 1% tạm tính
const SHIP_FEE     = 20000;    // phí giao hàng
const CATS         = ['Cua', 'Hải sản', 'Món nóng', 'Món xào', 'Món lẩu', 'Đồ uống'];
const BRANCH       = ['name' => 'Nhà hàng Vị Cua', 'addr' => '105A Trần Hưng Đạo, Cà Mau'];

date_default_timezone_set('Asia/Ho_Chi_Minh');
if (session_status() === PHP_SESSION_NONE) session_start();

/* ---------- Khởi tạo session lần đầu ---------- */
if (!isset($_SESSION['init'])) {
    $_SESSION = ['init' => 1, 'cart' => [], 'promo' => null, 'orders' => [], 'bookings' => []];
    if (DEMO_SEED) {
        $_SESSION['cart'] = [
            1 => ['qty' => 1, 'spicy' => true],
            2 => ['qty' => 1, 'spicy' => true],
            3 => ['qty' => 1, 'spicy' => false],
            4 => ['qty' => 1, 'spicy' => true],
        ];
        $_SESSION['promo'] = 'CUANGON01';
        $demo = [
            ['next saturday', '19:00', 4, 'Khu cửa sổ',      'upcoming'],
            ['-20 days',      '18:30', 2, 'Khu trong nhà',   'completed'],
            ['-35 days',      '12:00', 6, 'Phòng riêng VIP', 'cancelled'],
        ];
        foreach ($demo as $i => [$d, $t, $g, $a, $st]) {
            $date = date('Y-m-d', strtotime($d));
            $code = 'CB' . date('dmy', strtotime($date)) . '-00' . ($i + 1);
            $_SESSION['bookings'][$code] = [
                'code' => $code, 'date' => $date, 'time' => $t, 'guests' => $g, 'area' => $a,
                'note' => '', 'status' => $st, 'branch' => BRANCH['name'] . ' - ' . BRANCH['addr'],
            ];
        }
    }
}

/* ---------- Helper chung ---------- */
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money(int $n): string { return number_format($n, 0, ',', '.') . 'đ'; }
function chk($a, $b): string { return $a === $b ? 'checked' : ''; }
function set_flash(string $m): void { $_SESSION['flash'] = $m; }
function pull_flash(): string { $m = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']); return $m; }
function vn_date(string $ymd): string {
    $t = strtotime($ymd);
    $w = ['Chủ nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'][(int)date('w', $t)];
    return $w . ', ' . date('d/m/Y', $t);
}
function profile(): array {
    return ['name' => 'Nguyễn Minh Anh', 'phone' => '0912 345 678', 'email' => 'minhanh@gmail.com',
            'address' => '123 Nguyễn Huệ, Phường An Xuyên, Cà Mau'];
}
function topbar(string $title, string $back = 'index.php', string $right = ''): string {
    return '<header class="topbar"><a class="icon-btn" href="' . e($back) . '" aria-label="Quay lại">'
         . '<i class="fa-solid fa-arrow-left"></i></a><h1>' . e($title) . '</h1>' . $right . '</header>';
}
function crab_svg(int $size = 36): string {
    return <<<SVG
<svg viewBox="0 0 48 48" width="$size" height="$size" aria-hidden="true">
<g stroke="#b71c1c" stroke-width="2.4" stroke-linecap="round" fill="none"><path d="M12 30l-7 4M13 35l-6 6M36 30l7 4M35 35l6 6M16 20l-6-8M32 20l6-8"/></g>
<ellipse cx="24" cy="29" rx="14" ry="10" fill="#e53935"/>
<circle cx="9" cy="10" r="5" fill="#e53935"/><circle cx="39" cy="10" r="5" fill="#e53935"/>
<path d="M19 20v-4M29 20v-4" stroke="#b71c1c" stroke-width="2"/>
<circle cx="19" cy="14" r="3" fill="#fff"/><circle cx="29" cy="14" r="3" fill="#fff"/>
<circle cx="19" cy="14" r="1.4" fill="#3e2723"/><circle cx="29" cy="14" r="1.4" fill="#3e2723"/>
</svg>
SVG;
}

/* ---------- Thực đơn ---------- */
function menu_items(): array {
    static $items = null;
    if ($items === null) {
        $rows = [
            [1,  'Cua',      'Cua sốt ớt Singapore',  'Cua thịt chắc, sốt ớt Singapore cay ngọt đậm đà', 850000, '🦀'],
            [2,  'Cua',      'Cua rang me',           'Vị chua ngọt hài hòa, sốt me sánh thơm',          620000, '🦀'],
            [3,  'Cua',      'Cua hấp bia',           'Hấp bia sả gừng, giữ trọn vị ngọt tự nhiên',      690000, '🦀'],
            [4,  'Món lẩu',  'Lẩu riêu cua giấm bỗng', 'Nước lẩu riêu cua chua thanh, thơm dịu giấm bỗng, ăn kèm rau tươi', 980000, '🍲'],
            [5,  'Cua',      'Cua rang muối HongKong', 'Cua rang vàng giòn thơm, đậm vị muối HongKong', 510000, '🦀'],
            [6,  'Hải sản',  'Tôm nướng muối ớt',     'Tôm sú tươi nướng than, chấm muối ớt xanh',       320000, '🦐'],
            [7,  'Hải sản',  'Ghẹ hấp sả',            'Ghẹ chắc thịt, hấp sả lá chanh',                  360000, '🦞'],
            [8,  'Hải sản',  'Mực hấp gừng',          'Mực ống tươi giòn ngọt, hấp gừng',                380000, '🦑'],
            [9,  'Món nóng', 'Cháo cua gạch',         'Cháo sánh mịn, gạch cua béo bùi',                  100000, '🥣'],
            [10, 'Món nóng', 'Súp cua',               'Súp cua trứng bắc thảo, thơm nhẹ',                 75000, '🍜'],
            [11, 'Món xào',  'Miến xào cua',          'Miến xào cùng cua tươi, hành lá và rau thơm',     280000, '🍝'],
            [12, 'Món xào',  'Rau muống xào tỏi',     'Rau muống giòn xanh, xào tỏi lửa lớn',             55000, '🥬'],
            [13, 'Món lẩu',  'Lẩu hải sản thập cẩm',  'Tôm, mực, nghẹ, cá và rau củ theo mùa',          920000, '🍲'],
            [14, 'Đồ uống',  'Trà đào cam sả',        'Mát lạnh, thơm sả, vị đào chua nhẹ',               45000, '🍹'],
            [15, 'Đồ uống',  'Trà vải',               'Trà vải thơm dịu, ngọt lành, hậu vị chua chua',                   45000, '🥥'],
        ];
        $items = [];
        foreach ($rows as [$id, $cat, $name, $desc, $price, $emo]) {
            $items[$id] = compact('id', 'cat', 'name', 'desc', 'price', 'emo');
        }
    }
    return $items;
}
function find_item(int $id): ?array { return menu_items()[$id] ?? null; }

/* Ảnh món từ Wikimedia Commons (ảnh tham khảo món ăn, không phải ảnh chụp tại Vị Cua). */
function thumb(array $it): string {
    $photos = [
      1 => 'cua-sot-ot-singapore.png',
      2 => 'cua-rang-me.png',
      3 => 'cua-hap-bia.png',
      4 => 'lau-rieu-cua-giam-bong.png',
      5 => 'cua-rang-muoi-hongkong.png',
      6 => 'tom-nuong-muoi-ot.png',
      7 => 'ghe-hap-sa.png',
      8 => 'muc-hap-gung.png',
      9 => 'chao-cua.jpg',
      10 => 'sup-cua.png',
      11 => 'mien-xao-cua.jpg',
      12 => 'rau-muong-xao-toi.jpg',
      13 => 'lau-hai-san-thap-cam.png',
      14 => 'tra-dao-cam-xa.jpg',
      15 => 'tra-vai.jpg'
    ];
    $file = $photos[$it['id']] ?? '';
    // Ảnh món 1-8 là ảnh đã lưu trực tiếp trong assets/img.
    // Dùng đường dẫn local để tránh ảnh bị lỗi do Wikimedia hoặc mạng ngoài.
    $local_ids = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13,14,15,16,17,18,19,20];
    $src = $file ? (in_array((int)$it['id'], $local_ids, true)
        ? 'assets/img/' . rawurlencode($file)
        : 'https://commons.wikimedia.org/wiki/Special:FilePath/' . rawurlencode($file) . '?width=700') : '';
    $inner = $src ? '<img loading="lazy" src="' . e($src) . '" alt="' . e($it['name']) . '" onerror="this.style.display=\'none\';this.parentNode.classList.add(\'no-photo\')">' : $it['emo'];
    return '<div class="thumb">' . $inner . '</div>';
}
function dish_row(array $it): string {
    return '<article class="dish" data-g="menu" data-tags="' . e($it['cat']) . '">' . thumb($it)
         . '<div class="info"><h3>' . e($it['name']) . '</h3><p>' . e($it['desc']) . '</p>'
         . '<b class="price">' . money($it['price']) . '</b></div>'
         . '<button class="add" data-add="' . $it['id'] . '" aria-label="Thêm ' . e($it['name']) . '">'
         . '<i class="fa-solid fa-plus"></i></button></article>';
}

/* ---------- Mã giảm giá ---------- */
function promos(): array {
    $def = ['pct' => 0, 'min' => 0, 'cat' => null, 'exp' => '2026-12-31', 'ship' => false, 'gift' => false, 'tags' => []];
    $rows = [
        'CUANGON01' => ['title' => 'Giảm 10% cho đơn từ 500.000đ',  'desc' => 'Áp dụng cho mọi món khi đặt món tại Vị Cua',
                        'pct' => 10, 'min' => 500000, 'tags' => ['hot', 'code']],
        'CUA20'     => ['title' => 'Giảm 20% toàn bộ thực đơn cua', 'desc' => 'Áp dụng cho tất cả món cua trong thực đơn',
                        'pct' => 20, 'cat' => 'Cua', 'tags' => ['hot', 'code']],
        'LAUCUA15'  => ['title' => 'Giảm 15% món lẩu cua',          'desc' => 'Áp dụng cho các món lẩu trong thực đơn',
                        'pct' => 15, 'cat' => 'Món lẩu', 'tags' => ['code']],
        'DON1TR'    => ['title' => 'Giảm 10% đơn từ 1.000.000đ',    'desc' => 'Ưu đãi dành riêng cho thành viên Vị Cua',
                        'pct' => 10, 'min' => 1000000, 'tags' => ['member', 'code']],
        'SINHNHAT'  => ['title' => 'Tặng bánh sinh nhật miễn phí',  'desc' => 'Ghi chú "sinh nhật" khi đặt bàn, nhà hàng tặng bánh',
                        'gift' => true, 'tags' => ['member', 'hot']],
        'FREESHIP'  => ['title' => 'Miễn phí giao hàng',            'desc' => 'Áp dụng cho đơn giao hàng tận nơi',
                        'ship' => true, 'tags' => ['code']],
    ];
    foreach ($rows as $k => $r) $rows[$k] = $r + $def;
    return $rows;
}
function promo_find(?string $code): ?array {
    $code = strtoupper(trim((string)$code));
    $p = promos()[$code] ?? null;
    return $p ? $p + ['code' => $code] : null;
}

/* ---------- Giỏ hàng ---------- */
function cart_summary(): array {
    $sub = $count = 0; $by = $lines = [];
    foreach ($_SESSION['cart'] as $id => $l) {
        if (!($it = find_item((int)$id))) continue;
        $t = $it['price'] * $l['qty'];
        $sub += $t; $count += $l['qty'];
        $by[$it['cat']] = ($by[$it['cat']] ?? 0) + $t;
        $lines[$id] = $l + ['total' => $t];
    }
    $p = promo_find($_SESSION['promo']);
    $disc = 0;
    if ($p && $p['pct'] && $sub >= $p['min'] && date('Y-m-d') <= $p['exp']) {
        $disc = (int)round(($p['cat'] ? ($by[$p['cat']] ?? 0) : $sub) * $p['pct'] / 100);
    }
    $svc = (int)round($sub * SERVICE_RATE);
    return ['sub' => $sub, 'disc' => $disc, 'svc' => $svc, 'total' => $sub - $disc + $svc,
            'count' => $count, 'lines' => $lines, 'promo' => $p];
}
/* Trả về [thành công?, thông báo] */
function promo_apply(string $code): array {
    $p = promo_find($code);
    if (!$p) return [false, 'Mã giảm giá không hợp lệ'];
    if (date('Y-m-d') > $p['exp']) return [false, 'Mã giảm giá đã hết hạn'];
    if (cart_summary()['sub'] < $p['min']) return [false, 'Đơn tối thiểu ' . money($p['min']) . ' mới dùng được mã này'];
    $old = $_SESSION['promo'];
    $_SESSION['promo'] = $p['code'];
    if ($p['pct'] && !cart_summary()['disc']) {
        $_SESSION['promo'] = $old;
        return [false, 'Giỏ hàng chưa có món áp dụng mã này'];
    }
    return [true, 'Đã áp dụng mã ' . strtolower($p['code'])];
}
function cart_json(string $msg = ''): array {
    $s = cart_summary();
    $lines = [];
    foreach ($s['lines'] as $id => $l) {
        $lines[$id] = ['qty' => $l['qty'], 'spicy' => $l['spicy'], 'total' => money($l['total'])];
    }
    return [
        'ok' => true, 'msg' => $msg, 'count' => $s['count'], 'lines' => $lines,
        'promo' => strtolower((string)$_SESSION['promo']),
        'sum' => ['subtotal' => money($s['sub']), 'discount' => $s['disc'] ? '-' . money($s['disc']) : '0đ',
                  'service' => money($s['svc']), 'total' => money($s['total'])],
    ];
}
function cartbar(): string {
    $s = cart_summary();
    return '<a class="cartbar" href="cart.php"' . ($s['count'] ? '' : ' hidden') . '>'
         . '<i class="fa-solid fa-bag-shopping"></i>'
         . '<span>Giỏ hàng (<b data-cart-count>' . $s['count'] . '</b> món)<br>'
         . '<b class="tot" data-cart-total>' . money($s['sub']) . '</b></span>'
         . '<span class="go">Xem giỏ hàng</span></a>';
}
