<?php
require_once 'config.php';

// 세션 삭제
session_unset();
session_destroy();

// 쿠키 삭제
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// 로그인 페이지로 리다이렉트
header('Location: /');
exit;
?>
