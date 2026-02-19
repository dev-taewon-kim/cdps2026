<?php
require_once __DIR__ . '/admin/config.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => ['message' => '권한이 없습니다. 로그인 후 다시 시도해주세요.']]);
    exit;
}

if (!isset($_FILES['upload'])) {
    http_response_code(400);
    echo json_encode(['error' => ['message' => '업로드된 파일이 없습니다.']]);
    exit;
}

$file = $_FILES['upload'];
$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tif', 'tiff'];
$maxSize = 50 * 1024 * 1024; // 50MB

if ($file['error'] === UPLOAD_ERR_NO_FILE) {
    http_response_code(400);
    echo json_encode(['error' => ['message' => '파일이 선택되지 않았습니다.']]);
    exit;
}

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    echo json_encode(['error' => ['message' => '업로드 중 오류가 발생했습니다. (' . $file['error'] . ')']]);
    exit;
}

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => ['message' => '파일 크기는 50MB 이하여야 합니다.']]);
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt, true)) {
    http_response_code(400);
    echo json_encode(['error' => ['message' => '허용되지 않는 이미지 형식입니다.']]);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (strpos($mime, 'image/') !== 0) {
    http_response_code(400);
    echo json_encode(['error' => ['message' => '이미지 파일만 업로드할 수 있습니다.']]);
    exit;
}

$uploadDir = __DIR__ . '/uploaded_images';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$hash = hash_file('sha256', $file['tmp_name']);
$cleanName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
$targetName = $hash . '_' . $cleanName;
$targetPath = $uploadDir . '/' . $targetName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['error' => ['message' => '파일 저장에 실패했습니다.']]);
    exit;
}

$url = '/uploaded_images/' . $targetName;
echo json_encode(['url' => $url]);
?>
