<?php
require_once __DIR__ . '/admin/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /column.php');
    exit;
}

if (!is_logged_in() || !is_admin()) {
    header('Location: /admin/login.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    header('Location: /column.php');
    exit;
}

$pdo = db_connect();
$stmt = $pdo->prepare('DELETE FROM columns WHERE id = ?');
$stmt->execute([$id]);

header('Location: /column.php');
exit;
?>
