</main>
<footer class="foot">© 2026 Nhà hàng Vị Cua · <?= e(BRANCH['addr']) ?> · 0853 123 123</footer>
<nav class="bottom-nav" aria-label="Điều hướng chính">
<?php foreach ($nav as [$k, $href, $icon, $label]): ?>
  <a href="<?= $href ?>" class="<?= ($active ?? '') === $k ? 'active' : '' ?>"><i class="fa-solid <?= $icon ?>"></i><span><?= $label ?></span></a>
<?php endforeach; ?>
</nav>
<div id="toast" class="toast" role="status" data-flash="<?= e(pull_flash()) ?>"></div>
<script src="js/main.js"></script>
</body>
</html>
