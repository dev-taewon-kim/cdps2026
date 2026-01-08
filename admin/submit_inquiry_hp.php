<?php
require_once 'config.php';

header('Content-Type: text/html; charset=utf-8');

// POST 요청인지 확인
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<script>alert("잘못된 요청입니다."); location.href="/";</script>';
    exit;
}

// 입력값 받기
$name = $_POST['name'] ?? '';
$contact = $_POST['contact'] ?? '';
$reservation_date = $_POST['reservation_date'] ?? '';
$hp_type = $_POST['hp_type'] ?? '';
$content = $_POST['content'] ?? '';
$privacy_agree = $_POST['privacy_agree'] ?? '';

// 개인정보 동의 체크
if ($privacy_agree !== 'Y') {
    echo '<script>alert("개인정보 수집 및 이용에 동의해주세요."); location.href="/";</script>';
    exit;
}

// 필수 항목 체크
if (empty($name) || empty($contact) || empty($reservation_date) || empty($hp_type)) {
    echo '<script>alert("모든 필수 항목을 입력해주세요."); location.href="/";</script>';
    exit;
}

// 연락처에서 숫자만 추출
$contact_numbers_only = preg_replace('/[^0-9]/', '', $contact);
if (strlen($contact_numbers_only) < 10 || strlen($contact_numbers_only) > 11) {
    echo '<script>alert("올바른 연락처를 입력해주세요."); location.href="/";</script>';
    exit;
}

// XSS 방지를 위한 이스케이핑
$name = h($name);
$reservation_date = h($reservation_date);
$hp_type = h($hp_type);
$content = h($content);

try {
    $pdo = db_connect();
    
    // hp_inquiries 테이블에 데이터 삽입
    $sql = "INSERT INTO hp_inquiries (name, contact, reservation_date, hp_type, content, created_at) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $name,
        $contact_numbers_only,
        $reservation_date,
        $hp_type,
        $content,
        date('Y-m-d H:i:s')
    ]);
    
    if ($result) {
        echo '<script>alert("입원 상담 문의가 성공적으로 접수되었습니다."); location.href="/";</script>';
    } else {
        echo '<script>alert("문의 접수 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요."); location.href="/";</script>';
    }
    
} catch(PDOException $e) {
    echo '<script>alert("데이터베이스 오류가 발생했습니다. 잠시 후 다시 시도해주세요."); location.href="/";</script>';
}
exit;
?>
