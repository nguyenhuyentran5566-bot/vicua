<?php
require __DIR__ . '/includes/data.php';

$areas = [   // tên khu => [icon, số bàn còn trống (dữ liệu mẫu)]
    'Khu cửa sổ'      => ['fa-window-maximize', 'còn 5 bàn'],
    'Khu trong nhà'   => ['fa-couch',           'còn 12 bàn'],
    'Khu ngoài trời'  => ['fa-tree',            'còn 8 bàn'],
    'Phòng riêng VIP' => ['fa-crown',           'còn 2 phòng'],
];
$slots = [];   // 10:00 → 22:00, mỗi 30 phút
for ($m = 600; $m <= 1320; $m += 30) $slots[] = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);

$in = [
    'date'   => date('Y-m-d', strtotime('next saturday')),
    'guests' => 4,
    'time'   => '19:00',
    'area'   => 'Khu cửa sổ',
    'note'   => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($in as $k => $v) $in[$k] = trim((string)($_POST[$k] ?? $v));
    $in['guests'] = (int)$in['guests'];
    $ts = preg_match('/^\d{4}-\d{2}-\d{2}$/', $in['date']) ? strtotime($in['date'] . ' ' . $in['time']) : false;

    if (!$ts)                               $errors[] = 'Ngày đặt bàn chưa hợp lệ.';
    elseif ($ts < time())                   $errors[] = 'Vui lòng chọn thời gian trong tương lai.';
    if (!in_array($in['time'], $slots, true)) $errors[] = 'Giờ đặt bàn chưa hợp lệ.';
    if ($in['guests'] < 1 || $in['guests'] > 20) $errors[] = 'Số khách từ 1 đến 20 người.';
    if (!isset($areas[$in['area']]))        $errors[] = 'Vui lòng chọn khu vực ngồi.';

    if (!$errors) {
        $code = 'CB' . date('dmy') . '-' . sprintf('%03d', count($_SESSION['bookings']) + 1);
        $_SESSION['bookings'][$code] = $in + [
            'code' => $code, 'status' => 'upcoming', 'branch' => BRANCH['name'] . ' - ' . BRANCH['addr'],
        ];
        header('Location: booking_success.php?code=' . urlencode($code));
        exit;
    }
}

$title = 'Đặt bàn'; $active = 'book';
include __DIR__ . '/includes/header.php';
echo topbar('ĐẶT BÀN');
?>
<?php foreach ($errors as $er): ?><div class="err"><?= e($er) ?></div><?php endforeach; ?>

<form id="bookForm" method="post">
<div class="cols"><div class="col">

  <div class="card" id="branch">
    <div class="lb">Chi nhánh</div>
    <div class="opt" style="cursor:default;border-color:var(--or);background:#fff3ec">
      <i class="fa-solid fa-location-dot"></i>
      <span><?= e(BRANCH['name']) ?><small><?= e(BRANCH['addr']) ?></small></span>
    </div>
  </div>

  <div class="card">
    <div class="two">
      <label class="f"><span>Ngày đặt bàn</span>
        <input type="date" name="date" value="<?= e($in['date']) ?>" min="<?= date('Y-m-d') ?>" required></label>
      <div class="f"><span>Số khách</span>
        <div class="qty big">
          <button type="button" data-guests="-1" aria-label="Giảm số khách"><i class="fa-solid fa-minus"></i></button>
          <input name="guests" value="<?= (int)$in['guests'] ?>" readonly aria-label="Số khách">
          <button type="button" data-guests="1" aria-label="Tăng số khách"><i class="fa-solid fa-plus"></i></button>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="lb">Chọn giờ</div>
    <div class="opts slots">
      <?php foreach ($slots as $t): ?>
        <label class="opt slot"><input type="radio" name="time" value="<?= $t ?>" <?= chk($in['time'], $t) ?>><?= $t ?></label>
      <?php endforeach; ?>
    </div>
    <p class="hint" style="margin:10px 0 0"><i class="fa-regular fa-clock"></i> Nhà hàng giữ bàn tối đa 15 phút so với giờ đã đặt.</p>
  </div>

  <div class="card">
    <div class="lb">Khu vực ngồi</div>
    <div class="opts">
      <?php foreach ($areas as $name => [$icon, $left]): ?>
        <label class="opt"><input type="radio" name="area" value="<?= e($name) ?>" <?= chk($in['area'], $name) ?>>
          <i class="fa-solid <?= $icon ?>"></i><span><?= e($name) ?><small><?= $left ?></small></span></label>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card">
    <label class="f" style="margin:0"><span>Ghi chú (không bắt buộc)</span>
      <textarea name="note" rows="2" placeholder="Ví dụ: tiệc sinh nhật, không cay, ghế cho em bé..."><?= e($in['note']) ?></textarea></label>
  </div>

</div><div class="col side">
  <div class="card sum">
    <div class="lb">Thông tin đặt bàn</div>
    <div><span>Chi nhánh</span><b><?= e(BRANCH['name']) ?></b></div>
    <div><span>Ngày</span><b data-b="date">—</b></div>
    <div><span>Giờ</span><b data-b="time">—</b></div>
    <div><span>Số khách</span><b data-b="guests">—</b></div>
    <div><span>Khu vực</span><b data-b="area">—</b></div>
  </div>

  <button class="btn" type="submit">Xác nhận đặt bàn</button>
</div></div>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
