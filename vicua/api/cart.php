<?php
/* API giỏ hàng — nhận POST từ js/main.js, trả JSON */
require __DIR__ . '/../includes/data.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('{"ok":false}'); }

$act  = $_POST['action'] ?? '';
$id   = (int)($_POST['id'] ?? 0);
$msg  = '';
$cart =& $_SESSION['cart'];

switch ($act) {
    case 'add':
        if (!$it = find_item($id)) break;
        $cart[$id] = ['qty' => min(99, ($cart[$id]['qty'] ?? 0) + 1), 'spicy' => $cart[$id]['spicy'] ?? true];
        $msg = 'Đã thêm ' . $it['name'] . ' vào giỏ';
        break;
    case 'qty':
        if (isset($cart[$id])) $cart[$id]['qty'] = max(1, min(99, $cart[$id]['qty'] + (int)($_POST['d'] ?? 0)));
        break;
    case 'spicy':
        if (isset($cart[$id])) $cart[$id]['spicy'] = ($_POST['val'] ?? '1') === '1';
        break;
    case 'remove':
        unset($cart[$id]);
        $msg = 'Đã xóa món khỏi giỏ';
        break;
    case 'clear':
        $cart = [];
        $_SESSION['promo'] = null;
        $msg = 'Đã xóa giỏ hàng';
        break;
    case 'promo':
        [$ok, $msg] = promo_apply($_POST['code'] ?? '');
        if (!$ok) exit(json_encode(['ok' => false, 'msg' => $msg], JSON_UNESCAPED_UNICODE));
        break;
    default:
        http_response_code(400);
        exit('{"ok":false}');
}

echo json_encode(cart_json($msg), JSON_UNESCAPED_UNICODE);
