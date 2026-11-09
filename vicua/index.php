<?php
require __DIR__ . '/includes/data.php';
$title = 'Trang chủ'; $active = 'home'; $bar = true;
$best  = array_map('find_item', [1, 2, 3, 4]);
$quick = [
    ['booking.php',                'fa-calendar-check', 'Đặt bàn'],
    ['menu.php',                   'fa-utensils',       'Thực đơn'],
    ['promotions.php',             'fa-ticket',         'Ưu đãi'],
    ['checkout.php?receive=pickup', 'fa-bag-shopping',  'Đặt món mang về'],
    ['booking.php#branch',         'fa-location-dot',   'Chi nhánh'],
];
include __DIR__ . '/includes/header.php';
?>

<section class="hero" aria-label="Khuyến mãi">
  <div class="slides">
    <div class="slide" data-emoji="🦀">
      <h2>Tươi ngon<br><b>ĐẬM VỊ CUA</b></h2>
      <p>Chất lượng làm nên thương hiệu</p>
      <a class="btn-light" href="booking.php">ĐẶT BÀN NGAY</a>
    </div>
    <div class="slide" data-emoji="🍲">
      <h2>Lẩu riêu cua giấm bổng<br><b>nóng hổi</b></h2>
      <p>Nước lẩu riêu cua chua thanh, thơm dịu giấm bổng</p>
      <a class="btn-light" href="menu.php">XEM THỰC ĐƠN</a>
    </div>
    <div class="slide" data-emoji="🎂">
      <h2>Sinh nhật tại Vị Cua<br><b>tặng bánh miễn phí</b></h2>
      <p>Ghi chú "sinh nhật" khi đặt bàn</p>
      <a class="btn-light" href="booking.php">ĐẶT BÀN NGAY</a>
    </div>
  </div>
  <div class="dots"><i></i><i></i><i></i></div>
</section>

<nav class="quick" aria-label="Danh mục nhanh">
  <?php foreach ($quick as [$href, $icon, $label]): ?>
    <a href="<?= $href ?>"><span><i class="fa-solid <?= $icon ?>"></i></span><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>

<section class="promo-banner" aria-label="Ưu đãi giảm 10 phần trăm">
  <div class="promo-banner__copy">
    <p class="promo-banner__eyebrow">ƯU ĐÃI HẤP DẪN</p>
    <div class="promo-banner__saving"><span>Giảm ngay</span><strong>10%</strong></div>
    <p class="promo-banner__threshold">Cho đơn từ 500.000đ</p>
    <button class="promo-banner__code" type="button" data-copy="cuangon01" aria-label="Sao chép mã ưu đãi cuangon01" title="Nhấn để sao chép mã">
      <span>Nhập mã:</span> <strong>cuangon01</strong>
    </button>
    <small class="promo-banner__date">*Áp dụng đến: 31/12/2026</small>
  </div>
  <img class="promo-banner__photo" src="assets/img/promo-crab-right.png" alt="Cua sốt ớt Singapore trên đĩa" loading="eager">
  <span class="promo-banner__spark" aria-hidden="true">✦</span>
</section>

<div class="sec"><h2>Món bán chạy</h2><a class="link-btn" href="menu.php">Xem tất cả</a></div>
<div class="grid"><?php foreach ($best as $it) echo dish_row($it); ?></div>

<?= cartbar() ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
