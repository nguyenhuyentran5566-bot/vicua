<?php
require __DIR__ . '/includes/data.php';

// Link "Dùng ngay" từ trang Ưu đãi: cart.php?code=cuangon01
if (isset($_GET['code'])) {
    [, $m] = promo_apply((string)$_GET['code']);
    set_flash($m);
    header('Location: cart.php');
    exit;
}

$title = 'Giỏ hàng'; $active = 'menu';
$s = cart_summary();
include __DIR__ . '/includes/header.php';
echo topbar('GIỎ HÀNG', 'menu.php',
    $s['count'] ? '<button class="link-btn" data-clear><i class="fa-regular fa-trash-can"></i> Xóa giỏ hàng</button>' : '');
?>

<?php if (!$s['count']): ?>
  <div class="empty-box">
    <div class="big">🦀</div>
    <h3>Giỏ hàng đang trống</h3>
    <p>Chọn vài món ngon từ thực đơn để bắt đầu.</p>
    <a class="btn" href="menu.php">Xem thực đơn</a>
  </div>
<?php else: ?>

<div class="cols"><div class="col">
  <div id="cart-list">
    <?php foreach ($s['lines'] as $id => $l): $it = find_item($id); ?>
      <article class="line" data-line="<?= $id ?>">
        <?= thumb($it) ?>
        <div class="info">
          <div class="row">
            <h3><?= e($it['name']) ?></h3>
            <button class="del" data-remove aria-label="Xóa <?= e($it['name']) ?>"><i class="fa-regular fa-trash-can"></i></button>
          </div>
          <small class="mut"><?= money($it['price']) ?> / phần</small>
          <div class="seg">
            <button data-spicy="1" class="<?= $l['spicy'] ? 'on' : '' ?>">Cay</button>
            <button data-spicy="0" class="<?= $l['spicy'] ? '' : 'on' ?>">Không cay</button>
          </div>
          <div class="row">
            <div class="qty">
              <button data-qty="-1" aria-label="Giảm"><i class="fa-solid fa-minus"></i></button>
              <b data-q><?= $l['qty'] ?></b>
              <button data-qty="1" aria-label="Tăng"><i class="fa-solid fa-plus"></i></button>
            </div>
            <b class="price" data-lt><?= money($l['total']) ?></b>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="promo-box">
      <i class="fa-solid fa-ticket"></i>
      <input id="promoInput" placeholder="Chọn hoặc nhập mã giảm giá" autocomplete="off"
             value="<?= e(strtolower((string)$_SESSION['promo'])) ?>">
      <button class="btn-sm" data-apply>Áp dụng</button>
    </div>
    <div class="tags">
      <?php foreach (promos() as $c => $p): if ($p['gift']) continue; ?>
        <button class="code" data-code="<?= e(strtolower($c)) ?>"><?= e(strtolower($c)) ?></button>
      <?php endforeach; ?>
    </div>
  </div>

</div><div class="col side">
  <div class="card sum">
    <div><span>Tạm tính</span><b data-sum="subtotal"><?= money($s['sub']) ?></b></div>
    <div><span>Giảm giá</span><b class="neg" data-sum="discount"><?= $s['disc'] ? '-' . money($s['disc']) : '0đ' ?></b></div>
    <div><span>Phí phục vụ (1%)</span><b data-sum="service"><?= money($s['svc']) ?></b></div>
    <div class="total"><span>Tổng cộng</span><b data-sum="total"><?= money($s['total']) ?></b></div>
  </div>

  <div class="card pledge">
    <div><i class="fa-solid fa-leaf"></i>Hải sản tươi sống mỗi ngày</div>
    <div><i class="fa-solid fa-shield-halved"></i>Cam kết đúng món, đủ phần</div>
    <div><i class="fa-solid fa-headset"></i>Hỗ trợ đổi món nếu chưa vừa ý</div>
  </div>

  <a class="btn" href="checkout.php">Tiến hành đặt món</a>

</div></div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
