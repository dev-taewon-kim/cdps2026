<?php
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'cdps2026');
define('DB_PASSWORD', 'malgeunsup13@');
define('DB_DATABASE', 'cdps2026');

// 데이터베이스 연결 함수
function db_connect() {
    // DB_HOST로 접속 실패하면 mariadb로 시도
    try {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_DATABASE.';charset=utf8', DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        try {
            $pdo = new PDO('mysql:host=mariadb;dbname='.DB_DATABASE.';charset=utf8', DB_USERNAME, DB_PASSWORD);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch(PDOException $e2) {
            die('데이터베이스 연결에 실패했습니다: ' . $e2->getMessage());
        }
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
