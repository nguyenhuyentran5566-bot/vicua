<?php /* Biến cần có trước khi include: $title, $active; tuỳ chọn: $bar (chừa chỗ cho thanh giỏ hàng), $narrow (trang hẹp) */
$nav = [
    ['home',  'index.php',      'fa-house',          'Trang chủ'],
    ['book',  'booking.php',    'fa-calendar-check', 'Đặt bàn'],
    ['menu',  'menu.php',       'fa-utensils',       'Thực đơn'],
    ['promo', 'promotions.php', 'fa-ticket',         'Ưu đãi'],
    ['acc',   'account.php',    'fa-user',           'Tài khoản'],
];
$cartCount = cart_summary()['count'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#e8501c">
<title><?= e($title ?? 'VỊ CUA') ?> | VỊ CUA</title>
<link rel="icon" href="assets/img/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site"><div class="wrap">
  <a class="brand" href="index.php" aria-label="Vị Cua - Trang chủ"><img src="assets/img/logo.png" alt="Vị Cua"></a>
  <nav class="links" aria-label="Điều hướng chính">
<?php foreach ($nav as [$k, $href, $icon, $label]): ?>
    <a href="<?= $href ?>" class="<?= ($active ?? '') === $k ? 'active' : '' ?>"><?= $label ?></a>
<?php endforeach; ?>
  </nav>
  <span class="sp"></span>
  <button class="icon-btn" data-toast="Bạn chưa có thông báo mới" aria-label="Thông báo"><i class="fa-regular fa-bell"></i><span class="dot"></span></button>
  <a class="icon-btn" href="cart.php" aria-label="Giỏ hàng"><i class="fa-solid fa-bag-shopping"></i><span class="cnt" data-cart-count <?= $cartCount ? '' : 'hidden' ?>><?= $cartCount ?></span></a>
</div></header>
<main class="screen<?= !empty($bar) ? ' has-bar' : '' ?><?= !empty($narrow) ? ' narrow' : '' ?>">
