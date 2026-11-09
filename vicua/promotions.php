<?php
require __DIR__ . '/includes/data.php';
$title = 'Ưu đãi'; $active = 'promo';
$tabs = ['all' => 'Tất cả ưu đãi', 'hot' => 'Ưu đãi nổi bật', 'member' => 'Thành viên', 'code' => 'Mã giảm giá'];
include __DIR__ . '/includes/header.php';
echo topbar('ƯU ĐÃI');
?>
<div class="chips" data-filter="promo">
  <?php foreach ($tabs as $k => $t): ?>
    <button class="chip<?= $k === 'all' ? ' on' : '' ?>" data-f="<?= $k ?>"><?= e($t) ?></button>
  <?php endforeach; ?>
</div>

<div class="grid vcs">
<?php foreach (promos() as $code => $p): $lc = strtolower($code); ?>
  <article class="vc" data-g="promo" data-tags="<?= e(implode('|', $p['tags'])) ?>">
    <div class="l">
      <?php if ($p['pct']): ?>
        <div><small>GIẢM</small><strong><?= $p['pct'] ?>%</strong></div>
      <?php else: ?>
        <i class="fa-solid <?= $p['gift'] ? 'fa-cake-candles' : 'fa-truck-fast' ?>"></i>
      <?php endif; ?>
    </div>
    <div class="r">
      <h3><?= e($p['title']) ?></h3>
      <p><?= e($p['desc']) ?></p>
      <div class="row">
        <div>
          <small>Hạn dùng <?= date('d/m/Y', strtotime($p['exp'])) ?><?= $p['min'] ? ' - đơn từ ' . money($p['min']) : '' ?></small>
          <span class="code"><?= e($lc) ?></span>
        </div>
        <div class="acts">
          <button class="btn-sm ghost" data-copy="<?= e($lc) ?>" aria-label="Sao chép mã <?= e($lc) ?>"><i class="fa-regular fa-copy"></i></button>
          <a class="btn-sm" href="<?= $p['gift'] ? 'booking.php' : 'cart.php?code=' . e($lc) ?>">Dùng ngay</a>
        </div>
      </div>
    </div>
  </article>
<?php endforeach; ?>
</div>
<p class="empty" data-empty="promo" hidden>Mục này chưa có ưu đãi.</p>

<?php include __DIR__ . '/includes/footer.php'; ?>
