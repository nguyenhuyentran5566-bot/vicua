<?php
require __DIR__ . '/includes/data.php';

$s = cart_summary();
if (!$s['count']) { header('Location: cart.php'); exit; }

$me      = profile();
$shipFee = ($s['promo']['ship'] ?? false) ? 0 : SHIP_FEE;   // mã FREESHIP
$in = [
    'receive' => $_GET['receive'] ?? 'delivery',
    'name'    => $me['name'],
    'phone'   => $me['phone'],
    'address' => $me['address'],
    'note'    => '',
    'when'    => 'now',
    'at'      => '',
    'pay'     => 'cod',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($in as $k => $v) $in[$k] = trim((string)($_POST[$k] ?? $v));
}
if (!in_array($in['receive'], ['delivery', 'pickup'], true)) $in['receive'] = 'delivery';
if (!in_array($in['when'], ['now', 'later'], true))           $in['when'] = 'now';
if (!in_array($in['pay'], ['cod', 'online'], true))           $in['pay'] = 'cod';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (mb_strlen($in['name']) < 2) $errors[] = 'Vui lòng nhập họ tên.';
    if (!preg_match('/^(0|\+84)\d{9}$/', preg_replace('/[\s.\-]/', '', $in['phone']))) $errors[] = 'Số điện thoại chưa đúng (ví dụ 0912 345 678).';
    if ($in['receive'] === 'delivery' && mb_strlen($in['address']) < 8) $errors[] = 'Vui lòng nhập địa chỉ giao hàng.';
    if ($in['when'] === 'later' && !preg_match('/^\d{2}:\d{2}$/', $in['at'])) $errors[] = 'Vui lòng chọn giờ nhận món.';

    if (!$errors) {
        $code  = 'OD' . date('ymd') . '-' . sprintf('%03d', count($_SESSION['orders']) + 1);
        $fee   = $in['receive'] === 'delivery' ? $shipFee : 0;
        $items = [];
        foreach ($s['lines'] as $id => $l) {
            $items[] = ['name' => find_item($id)['name'], 'qty' => $l['qty'], 'spicy' => $l['spicy'], 'total' => $l['total']];
        }
        $_SESSION['orders'][$code] = $in + [
            'code' => $code, 'time' => date('H:i d/m/Y'), 'items' => $items,
            'sub' => $s['sub'], 'disc' => $s['disc'], 'svc' => $s['svc'], 'fee' => $fee, 'total' => $s['total'] + $fee,
            'status' => 'Đang chuẩn bị',
        ];
        $_SESSION['cart']  = [];
        $_SESSION['promo'] = null;
        header('Location: order_success.php?code=' . urlencode($code));
        exit;
    }
}

$title = 'Đặt món'; $active = 'menu';
include __DIR__ . '/includes/header.php';
echo topbar('ĐẶT MÓN', 'cart.php');
?>
<div class="steps">
  <div class="step done"><span class="n"><i class="fa-solid fa-check"></i></span>Giỏ hàng</div>
  <div class="step on"><span class="n">2</span>Đặt món</div>
  <div class="step"><span class="n">3</span>Thanh toán</div>
  <div class="step"><span class="n">4</span>Xác nhận</div>
</div>

<?php foreach ($errors as $er): ?><div class="err"><?= e($er) ?></div><?php endforeach; ?>

<form id="checkoutForm" method="post" data-base="<?= $s['total'] ?>" data-ship="<?= $shipFee ?>">
<div class="cols"><div class="col">

  <div class="card">
    <div class="lb">Hình thức nhận món</div>
    <div class="opts">
      <label class="opt"><input type="radio" name="receive" value="delivery" <?= chk($in['receive'], 'delivery') ?>>
        <i class="fa-solid fa-truck-fast"></i><span>Giao hàng tận nơi</span></label>
      <label class="opt"><input type="radio" name="receive" value="pickup" <?= chk($in['receive'], 'pickup') ?>>
        <i class="fa-solid fa-store"></i><span>Tự đến lấy tại nhà hàng</span></label>
    </div>
  </div>

  <div class="card">
    <div class="lb">Thông tin nhận món</div>
    <label class="f"><span>Họ và tên</span><input name="name" value="<?= e($in['name']) ?>" required></label>
    <label class="f"><span>Số điện thoại</span><input name="phone" type="tel" value="<?= e($in['phone']) ?>" required></label>
    <label class="f" id="addrBox"><span>Địa chỉ giao hàng</span><input name="address" value="<?= e($in['address']) ?>"></label>
    <label class="f"><span>Ghi chú cho tài xế</span><textarea name="note" rows="2" placeholder="Ví dụ: gọi trước khi đến, để ở bảo vệ..."><?= e($in['note']) ?></textarea></label>
  </div>

  <div class="card">
    <div class="lb">Thời gian nhận món</div>
    <div class="opts">
      <label class="opt"><input type="radio" name="when" value="now" <?= chk($in['when'], 'now') ?>>
        <i class="fa-regular fa-clock"></i><span>Giao ngay<small>30-45 phút</small></span></label>
      <label class="opt"><input type="radio" name="when" value="later" <?= chk($in['when'], 'later') ?>>
        <i class="fa-regular fa-calendar"></i><span>Đặt trước<small>Chọn giờ nhận</small></span></label>
    </div>
    <label class="f" id="whenAt" style="margin:12px 0 0"><span>Giờ nhận món</span><input type="time" name="at" value="<?= e($in['at']) ?>"></label>
  </div>

</div><div class="col side">
  <div class="card sum">
    <div class="lb">Tóm tắt đơn hàng</div>
    <?php foreach ($s['lines'] as $id => $l): ?>
      <div><span><?= $l['qty'] ?> × <?= e(find_item($id)['name']) ?> <small class="mut">(<?= $l['spicy'] ? 'Cay' : 'Không cay' ?>)</small></span><b><?= money($l['total']) ?></b></div>
    <?php endforeach; ?>
    <div style="border-top:1px dashed var(--line);margin-top:6px;padding-top:10px"><span>Tạm tính</span><b><?= money($s['sub']) ?></b></div>
    <div><span>Giảm giá</span><b class="neg"><?= $s['disc'] ? '-' . money($s['disc']) : '0đ' ?></b></div>
    <div><span>Phí phục vụ (1%)</span><b><?= money($s['svc']) ?></b></div>
    <div data-fee-row><span>Phí giao hàng</span><b data-fee><?= $shipFee ? money($shipFee) : 'Miễn phí' ?></b></div>
    <div class="total"><span>Tổng cộng</span><b data-grand><?= money($s['total'] + $shipFee) ?></b></div>
  </div>

  <div class="card">
    <div class="lb">Phương thức thanh toán</div>
    <div class="opts one">
      <label class="opt"><input type="radio" name="pay" value="cod" <?= chk($in['pay'], 'cod') ?>>
        <i class="fa-solid fa-money-bill-wave"></i><span>Thanh toán khi nhận hàng (COD)<small>Trả tiền mặt cho tài xế hoặc tại quầy</small></span></label>
      <label class="opt"><input type="radio" name="pay" value="online" <?= chk($in['pay'], 'online') ?>>
        <i class="fa-solid fa-credit-card"></i><span>Thanh toán online<small>Thẻ ngân hàng hoặc ví điện tử</small></span></label>
    </div>
  </div>

  <button class="btn" type="submit">Xác nhận đặt món</button>
</div></div>
</form>

<?php include __DIR__ . '/includes/footer.php'; ?>
