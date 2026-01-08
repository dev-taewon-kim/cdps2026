<?php
define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'cdps2025');
define('MYSQL_PASSWORD', 'singil12!@');
define('MYSQL_DB', 'cdps2025');

// 데이터베이스 연결 함수
function db_connect() {
    try {
        $pdo = new PDO('mysql:host='.MYSQL_HOST.';dbname='.MYSQL_DB.';charset=utf8', MYSQL_USER, MYSQL_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die('데이터베이스 연결에 실패했습니다: ' . $e->getMessage());
    }
}

// 세션 시작
session_start();

// 로그인 상태 확인 함수
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// 관리자 여부 확인 함수
function is_admin() {
    return isset($_SESSION['username']) && $_SESSION['username'] === 'admin';
}

// XSS 방지를 위한 이스케이핑 함수
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
