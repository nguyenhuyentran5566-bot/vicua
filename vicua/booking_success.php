<?php
require __DIR__ . '/includes/data.php';

$b = $_SESSION['bookings'][$_GET['code'] ?? ''] ?? null;
if (!$b) { header('Location: booking.php'); exit; }

$narrow = true; $title = 'Đặt bàn thành công'; $active = 'book';
include __DIR__ . '/includes/header.php';
?>
<section class="ok" style="padding-top:36px">
  <div class="tick"><i class="fa-solid fa-check"></i></div>
  <h1>Đặt bàn thành công!</h1>
  <p>Nhà hàng sẽ giữ bàn cho bạn tối đa 15 phút so với giờ đã đặt.</p>
  <div class="code-big">#<?= e($b['code']) ?></div>
</section>

<div class="card">
  <div class="kv"><span>Chi nhánh</span><b><?= e($b['branch']) ?></b></div>
  <div class="kv"><span>Ngày</span><b><?= e(vn_date($b['date'])) ?></b></div>
  <div class="kv"><span>Giờ</span><b><?= e($b['time']) ?></b></div>
  <div class="kv"><span>Số khách</span><b><?= (int)$b['guests'] ?> người</b></div>
  <div class="kv"><span>Khu vực</span><b><?= e($b['area']) ?></b></div>
  <?php if ($b['note'] !== ''): ?><div class="kv"><span>Ghi chú</span><b><?= e($b['note']) ?></b></div><?php endif; ?>
</div>

<div class="btns">
  <a class="btn" href="menu.php">Đặt món ngay</a>
  <a class="btn ghost" href="account.php">Xem đặt bàn của tôi</a>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
