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
// 기존 썸네일 경로 조회 (업로드된 경우만 파일 삭제)
$stmt = $pdo->prepare('SELECT thumbnail_url FROM columns WHERE id = ?');
$stmt->execute([$id]);
$thumb = $stmt->fetchColumn();

// 하드 삭제
$stmt = $pdo->prepare('DELETE FROM columns WHERE id = ?');
$stmt->execute([$id]);

// 업로드된 썸네일 파일 삭제 (기본 썸네일은 건너뜀)
if ($thumb && str_starts_with($thumb, '/uploaded_images/')) {
    $filePath = __DIR__ . $thumb;
    if (is_file($filePath)) {
        @unlink($filePath);
    }
}

header('Location: /column.php');
exit;
?>
