<?php
require __DIR__ . '/includes/data.php';

$o = $_SESSION['orders'][$_GET['code'] ?? ''] ?? null;
if (!$o) { header('Location: index.php'); exit; }

$narrow = true; $title = 'Đặt món thành công'; $active = 'menu';
$delivery = $o['receive'] === 'delivery';
include __DIR__ . '/includes/header.php';
?>
<div class="steps" style="margin-top:14px">
  <div class="step done"><span class="n"><i class="fa-solid fa-check"></i></span>Giỏ hàng</div>
  <div class="step done"><span class="n"><i class="fa-solid fa-check"></i></span>Đặt món</div>
  <div class="step done"><span class="n"><i class="fa-solid fa-check"></i></span>Thanh toán</div>
  <div class="step on"><span class="n"><i class="fa-solid fa-check"></i></span>Xác nhận</div>
</div>

<section class="ok">
  <div class="tick"><i class="fa-solid fa-check"></i></div>
  <h1>Đặt món thành công!</h1>
  <p>Nhà hàng đã nhận đơn và đang chuẩn bị món cho bạn.</p>
  <div class="code-big"><?= e($o['code']) ?></div>
</section>

<div class="card">
  <div class="kv"><span>Thời gian đặt</span><b><?= e($o['time']) ?></b></div>
  <div class="kv"><span>Hình thức</span><b><?= $delivery ? 'Giao hàng tận nơi' : 'Tự đến lấy tại nhà hàng' ?></b></div>
  <div class="kv"><span>Thời gian nhận</span><b><?= $o['when'] === 'later' ? 'Đặt trước lúc ' . e($o['at']) : 'Ngay (30-45 phút)' ?></b></div>
  <div class="kv"><span><?= $delivery ? 'Giao đến' : 'Nhận tại' ?></span><b><?= $delivery ? e($o['address']) : e(BRANCH['name'] . ', ' . BRANCH['addr']) ?></b></div>
  <div class="kv"><span>Người nhận</span><b><?= e($o['name']) ?> - <?= e($o['phone']) ?></b></div>
  <div class="kv"><span>Thanh toán</span><b><?= $o['pay'] === 'cod' ? 'Khi nhận hàng (COD)' : 'Online' ?></b></div>
  <?php if ($o['note'] !== ''): ?><div class="kv"><span>Ghi chú</span><b><?= e($o['note']) ?></b></div><?php endif; ?>
</div>

<div class="card">
  <div class="lb">Chi tiết món</div>
  <?php foreach ($o['items'] as $i): ?>
    <div class="kv it">
      <span><?= $i['qty'] ?> × <?= e($i['name']) ?> <small class="mut">(<?= $i['spicy'] ? 'Cay' : 'Không cay' ?>)</small></span>
      <b><?= money($i['total']) ?></b>
    </div>
  <?php endforeach; ?>
</div>

<div class="card sum">
  <div><span>Tạm tính</span><b><?= money($o['sub']) ?></b></div>
  <div><span>Giảm giá</span><b class="neg"><?= $o['disc'] ? '-' . money($o['disc']) : '0đ' ?></b></div>
  <div><span>Phí phục vụ</span><b><?= money($o['svc']) ?></b></div>
  <?php if ($delivery): ?><div><span>Phí giao hàng</span><b><?= $o['fee'] ? money($o['fee']) : 'Miễn phí' ?></b></div><?php endif; ?>
  <div class="total"><span>Tổng cộng</span><b><?= money($o['total']) ?></b></div>
</div>

<div class="btns">
  <a class="btn" href="account.php#orders">Xem đơn hàng</a>
  <a class="btn ghost" href="index.php">Về trang chủ</a>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
