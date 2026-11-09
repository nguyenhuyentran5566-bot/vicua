<?php
require __DIR__ . '/includes/data.php';
$title = 'Thực đơn'; $active = 'menu'; $bar = true;
$featured = array_map('find_item', [1, 4, 6]);
include __DIR__ . '/includes/header.php';
echo topbar('THỰC ĐƠN');
?>
<section class="hero sm">
  <div class="slide" data-emoji="🦀">
    <small>THƯƠNG HIỆU</small>
    <h2>Tươi ngon<br>Chất lượng<br><b>Đậm vị cua</b></h2>
  </div>
</section>

<div class="chips" data-filter="menu">
  <button class="chip on" data-f="all">Tất cả</button>
  <?php foreach (CATS as $c): ?>
    <button class="chip" data-f="<?= e($c) ?>"><?= e($c) ?></button>
  <?php endforeach; ?>
</div>

<div data-only-all="menu">
  <div class="sec"><h2>Món nổi bật</h2></div>
  <div class="hscroll">
    <?php foreach ($featured as $it): ?>
      <article class="feat">
        <?= thumb($it) ?>
        <div class="b"><h3><?= e($it['name']) ?></h3><b class="price"><?= money($it['price']) ?></b></div>
        <button class="add" data-add="<?= $it['id'] ?>" aria-label="Thêm <?= e($it['name']) ?>"><i class="fa-solid fa-plus"></i></button>
      </article>
    <?php endforeach; ?>
  </div>
</div>

<div class="sec"><h2>Tất cả món</h2></div>
<div class="grid"><?php foreach (menu_items() as $it) echo dish_row($it); ?></div>
<p class="empty" data-empty="menu" hidden>Danh mục này chưa có món.</p>

<?= cartbar() ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
