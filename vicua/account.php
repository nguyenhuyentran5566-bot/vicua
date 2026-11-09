<?php
require __DIR__ . '/includes/data.php';

// Hủy đặt bàn sắp tới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel'])) {
    $c = (string)$_POST['cancel'];
    if (($_SESSION['bookings'][$c]['status'] ?? '') === 'upcoming') {
        $_SESSION['bookings'][$c]['status'] = 'cancelled';
        set_flash('Đã hủy đặt bàn #' . $c);
    }
    header('Location: account.php');
    exit;
}

$title = 'Tài khoản'; $active = 'acc';
$me = profile();
$bookings = $_SESSION['bookings'] ?? [];
// Chuẩn hóa dữ liệu session cũ/không đúng cấu trúc để tránh lỗi khi render.
if (!is_array($bookings)) $bookings = [];
$bookings = array_filter($bookings, fn($b) => is_array($b) && isset($b['code'], $b['date'], $b['time'], $b['status']));
uasort($bookings, fn($a, $b) => strcmp(($b['date'] ?? '') . ($b['time'] ?? ''), ($a['date'] ?? '') . ($a['time'] ?? '')));
$label = ['upcoming' => 'Sắp tới', 'completed' => 'Đã hoàn thành', 'cancelled' => 'Đã hủy'];
$menu = [
    ['fa-ticket',       'Ưu đãi của tôi',        'promotions.php'],
    ['fa-address-book', 'Sổ địa chỉ',            ''],
    ['fa-wallet',       'Phương thức thanh toán', ''],
    ['fa-receipt',      'Lịch sử đơn hàng',      '#orders'],
    ['fa-star',         'Đánh giá của tôi',      ''],
    ['fa-headset',      'Hỗ trợ & liên hệ',      ''],
];
$tabs = ['all' => 'Tất cả', 'upcoming' => 'Sắp tới', 'completed' => 'Đã hoàn thành', 'cancelled' => 'Đã hủy'];
include __DIR__ . '/includes/header.php';
?>
<?= topbar('TÀI KHOẢN') ?>

<section class="profile">
  <div class="avatar"><i class="fa-solid fa-user"></i></div>
  <div class="pi">
    <h2><?= e($me['name']) ?></h2>
    <span class="badge"><i class="fa-solid fa-crown"></i> Thành viên</span>
    <p><i class="fa-solid fa-phone"></i> <?= e($me['phone']) ?></p>
    <p><i class="fa-regular fa-envelope"></i> <?= e($me['email']) ?></p>
  </div>
  <button class="btn-edit" data-toast="Tính năng đang phát triển"><i class="fa-solid fa-pen"></i> Chỉnh sửa</button>
</section>

<div class="sec"><h2>Đặt bàn của tôi</h2></div>
<div class="chips" data-filter="bk">
  <?php foreach ($tabs as $k => $t): ?>
    <button class="chip<?= $k === 'all' ? ' on' : '' ?>" data-f="<?= $k ?>"><?= e($t) ?></button>
  <?php endforeach; ?>
</div>

<div class="grid bks">
<?php foreach ($bookings as $b): ?>
  <article class="bk" data-g="bk" data-tags="<?= e($b['status']) ?>">
    <div class="row"><b>#<?= e($b['code']) ?></b><span class="status s-<?= e($b['status']) ?>"><?= e($label[$b['status']] ?? 'Không xác định') ?></span></div>
    <div class="meta">
      <span><i class="fa-regular fa-calendar"></i><?= e(vn_date($b['date'])) ?></span>
      <span><i class="fa-regular fa-clock"></i><?= e($b['time']) ?></span>
      <span><i class="fa-solid fa-users"></i><?= (int)$b['guests'] ?> người</span>
    </div>
    <small><i class="fa-solid fa-location-dot"></i><?= e($b['area']) ?> - <?= e($b['branch']) ?></small>
    <?php if ($b['status'] === 'upcoming'): ?>
      <form method="post" style="margin-top:8px" onsubmit="return confirm('Hủy đặt bàn này?')">
        <input type="hidden" name="cancel" value="<?= e($b['code']) ?>">
        <button class="link-btn" type="submit">Hủy đặt bàn</button>
      </form>
    <?php endif; ?>
  </article>
<?php endforeach; ?>
</div>
<p class="empty" data-empty="bk" <?= $bookings ? 'hidden' : '' ?>>Chưa có lịch đặt bàn nào.</p>

<div class="sec" id="orders" style="margin-top:18px"><h2>Đơn hàng gần đây</h2></div>
<?php foreach (array_reverse($_SESSION['orders']) as $o): ?>
  <a class="bk" href="order_success.php?code=<?= urlencode($o['code']) ?>">
    <div class="row"><b><?= e($o['code']) ?></b><span class="status s-upcoming"><?= e($o['status']) ?></span></div>
    <div class="meta"><span><i class="fa-regular fa-clock"></i><?= e($o['time']) ?></span><span class="price"><?= money($o['total']) ?></span></div>
  </a>
<?php endforeach; ?>
<?php if (!$_SESSION['orders']): ?><p class="empty">Bạn chưa có đơn hàng nào. <a class="link-btn" href="menu.php">Đặt món ngay</a></p><?php endif; ?>

<div class="card mlist" style="margin-top:8px">
  <?php foreach ($menu as [$icon, $text, $href]): ?>
    <a href="<?= $href ?: '#' ?>" <?= $href ? '' : 'data-toast="Tính năng đang phát triển"' ?>>
      <span class="ic"><i class="fa-solid <?= $icon ?>"></i></span><?= e($text) ?><i class="fa-solid fa-chevron-right go"></i>
    </a>
  <?php endforeach; ?>
</div>

<section class="refer">
  <i class="fa-solid fa-gift"></i>
  <div><h3>Giới thiệu bạn bè, nhận 50.000đ</h3><p>Gửi mã của bạn, hai bên cùng nhận ưu đãi.</p></div>
  <button class="btn-sm" data-copy="VICUA-MINHANH">Mời bạn</button>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
