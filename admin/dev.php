<?php
require_once 'config.php';

// 세션 시작
session_start();

// 하드코딩된 비밀번호
$hard_password = "dos28dcba76fbc269287f3d4555de0c595f33cc6734b2dfde0fb06cdce0a4c6d966c4a88f9526d0569e4e52eeb40ba7c1c257b5273e96ee5bb7ad9e38cd0927b";

// 현재 로그인 상태 확인
$is_logged_in = isset($_SESSION['is_authenticated']) && $_SESSION['is_authenticated'] === true;

// 로그아웃 처리
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// 비밀번호 검증
if (isset($_POST['password'])) {
    if ($_POST['password'] === $hard_password) {
        $_SESSION['is_authenticated'] = true;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error_message = "비밀번호가 일치하지 않습니다.";
    }
}

// 데이터베이스 연결
$pdo = db_connect();

// 액션 처리 (로그인 상태에서만 실행)
if ($is_logged_in && isset($_GET['action'])) {
    $action = $_GET['action'];

    // 사용자 테이블 초기화
    if ($action === 'truncate') {
        try {
            $pdo->exec("TRUNCATE TABLE users");
            $success_message = "users 테이블이 성공적으로 초기화되었습니다.";
        } catch(PDOException $e) {
            $error_message = "테이블 초기화 중 오류가 발생했습니다: " . $e->getMessage();
        }
    }

    // 상담 테이블 초기화
    else if ($action === 'truncate_inquiry') {
        try {
            $pdo->exec("TRUNCATE TABLE inquiries");
            $success_message = "inquiries 테이블이 성공적으로 초기화되었습니다.";
        } catch(PDOException $e) {
            $error_message = "상담 테이블 초기화 중 오류가 발생했습니다: " . $e->getMessage();
        }
    }

    // 초기 데이터 생성
    else if ($action === 'init') {
        // 사용자 테이블 존재 여부 확인
        $checkTable = $pdo->query("SHOW TABLES LIKE 'users'");
        $tableExists = $checkTable->rowCount() > 0;

        // 사용자 테이블이 존재하지 않으면 생성
        if (!$tableExists) {
            $sql = "CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(20) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                name VARCHAR(10) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                contact VARCHAR(20) NULL,
                created_at DATETIME NOT NULL,
                last_login_at DATETIME NULL
            )";

            try {
                $pdo->exec($sql);
                $success_message = "users 테이블이 성공적으로 생성되었습니다.";
            } catch(PDOException $e) {
                $error_message = "테이블 생성 오류: " . $e->getMessage();
            }
        }

        // 상담 테이블 존재 여부 확인
        $checkInquiryTable = $pdo->query("SHOW TABLES LIKE 'inquiries'");
        $inquiryTableExists = $checkInquiryTable->rowCount() > 0;

        // 상담 테이블이 존재하지 않으면 생성
        if (!$inquiryTableExists) {
            $sql = "CREATE TABLE inquiries (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(50) NOT NULL,
                contact VARCHAR(20) NOT NULL,
                created_at DATETIME NOT NULL
            )";

            try {
                $pdo->exec($sql);
                $success_message = isset($success_message) ? $success_message . "<br>inquiries 테이블이 성공적으로 생성되었습니다." : "inquiries 테이블이 성공적으로 생성되었습니다.";
            } catch(PDOException $e) {
                $error_message = "상담 테이블 생성 오류: " . $e->getMessage();
            }
        }

        // 관리자 계정 존재 여부 확인
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute(['admin']);
        $adminExists = $stmt->fetch();

        // 관리자 계정이 없으면 생성
        if (!$adminExists) {
            $password = password_hash('singil12!@', PASSWORD_DEFAULT);
            $now = date('Y-m-d H:i:s');

            $stmt = $pdo->prepare("INSERT INTO users (username, password, name, email, contact, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute(['admin', $password, '관리자', 'admin@admin.com', '01000000000', $now]);

            if ($result) {
                $success_message = isset($success_message) ? $success_message . "<br>관리자 계정이 성공적으로 생성되었습니다." : "관리자 계정이 성공적으로 생성되었습니다.";
            } else {
                $error_message = "관리자 계정 생성 중 오류가 발생했습니다.";
            }
        } else {
            $info_message = "관리자 계정이 이미 존재합니다.";
        }
    }

    // 관리자 비밀번호 재설정
    else if ($action === 'resetpw' && isset($_POST['new_password'])) {
        $new_password = $_POST['new_password'];

        if (strlen($new_password) < 8) {
            $error_message = "비밀번호는 최소 8자 이상이어야 합니다.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            try {
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = 1");
                $result = $stmt->execute([$hashed_password]);

                if ($result) {
                    $success_message = "관리자 비밀번호가 성공적으로 재설정되었습니다.";
                } else {
                    $error_message = "비밀번호 재설정 중 오류가 발생했습니다.";
                }
            } catch(PDOException $e) {
                $error_message = "비밀번호 재설정 중 오류가 발생했습니다: " . $e->getMessage();
            }
        }
    }
}

// 사용자 데이터 조회 (로그인 상태에서만)
$users = [];
if ($is_logged_in) {
    try {
        $checkTable = $pdo->query("SHOW TABLES LIKE 'users'");
        $tableExists = $checkTable->rowCount() > 0;

        if ($tableExists) {
            $stmt = $pdo->query("SELECT * FROM users ORDER BY id");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        $error_message = "데이터 조회 중 오류가 발생했습니다: " . $e->getMessage();
    }
}

// 상담 데이터 조회 (로그인 상태에서만)
$inquiries = [];
if ($is_logged_in) {
    try {
        $checkInquiryTable = $pdo->query("SHOW TABLES LIKE 'inquiries'");
        $inquiryTableExists = $checkInquiryTable->rowCount() > 0;

        if ($inquiryTableExists) {
            $stmt = $pdo->query("SELECT * FROM inquiries ORDER BY id DESC");
            $inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch(PDOException $e) {
        $error_message = "상담 데이터 조회 중 오류가 발생했습니다: " . $e->getMessage();
    }
}

// 연락처 포맷팅 함수
function format_phone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) == 11) {
        return substr($phone, 0, 3) . '-' . substr($phone, 3, 4) . '-' . substr($phone, 7);
    } elseif (strlen($phone) == 10) {
        return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6);
    }
    return $phone;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>데이터베이스 관리</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
        .login-form {
            margin: 20px 0;
        }
        input[type="text"], input[type="password"] {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 3px;
            width: 200px;
        }
        button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
        }
        button:hover {
            background-color: #45a049;
        }
        .menu {
            margin: 20px 0;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .menu button {
            background-color: #2196F3;
        }
        .menu button:hover {
            background-color: #0b7dda;
        }
        .logout {
            background-color: #f44336 !important;
        }
        .logout:hover {
            background-color: #d32f2f !important;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 3px;
        }
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .info {
            background-color: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            border-radius: 5px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        .section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        .section:last-child {
            border-bottom: none;
        }
        .section h2 {
            color: #333;
            margin-bottom: 15px;
        }
        .inquiry-content {
            max-width: 500px;
            word-wrap: break-word;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>데이터베이스 관리</h1>

        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($info_message)): ?>
            <div class="message info"><?php echo $info_message; ?></div>
        <?php endif; ?>

        <?php if (!$is_logged_in): ?>
            <!-- 로그인 폼 -->
            <div class="login-form">
                <form method="post" action="">
                    <input type="password" name="password" placeholder="비밀번호를 입력하세요" required autofocus autocomplete="dev_password">
                    <button type="submit">로그인</button>
                </form>
            </div>
        <?php else: ?>
            <!-- 메뉴 버튼 -->
            <div class="menu">
                <button onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>'">현재 테이블 조회</button>
                <button onclick="confirmAction('<?php echo $_SERVER['PHP_SELF']; ?>?action=truncate', '정말로 사용자 테이블을 초기화하시겠습니까?')">사용자 테이블 초기화</button>
                <button onclick="confirmAction('<?php echo $_SERVER['PHP_SELF']; ?>?action=truncate_inquiry', '정말로 상담 테이블을 초기화하시겠습니까?')">상담 테이블 초기화</button>
                <button onclick="confirmAction('<?php echo $_SERVER['PHP_SELF']; ?>?action=init', '초기 데이터를 생성하시겠습니까?')">초기 데이터 생성</button>
                <button onclick="openResetModal()">관리자 비밀번호 재설정</button>
                <button class="logout" onclick="window.location.href='<?php echo $_SERVER['PHP_SELF']; ?>?action=logout'">Logout</button>
            </div>

            <!-- 사용자 테이블 표시 -->
            <div class="section">
                <?php if (!empty($users)): ?>
                    <h2>사용자 목록</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>사용자명</th>
                                <th>이름</th>
                                <th>이메일</th>
                                <th>연락처</th>
                                <th>생성일</th>
                                <th>마지막 로그인</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['contact']); ?></td>
                                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($user['last_login_at'] ?: '-'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>표시할 사용자 데이터가 없습니다.</p>
                <?php endif; ?>
            </div>

            <!-- 상담 테이블 표시 -->
            <div class="section">
                <?php if (!empty($inquiries)): ?>
                    <h2>상담 신청 내역</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>이름</th>
                                <th>연락처</th>
                                <th>신청일시</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $inquiry): ?>
                            <tr>
                                <td><?php echo $inquiry['id']; ?></td>
                                <td><?php echo $inquiry['name']; ?></td>
                                <td><?php echo format_phone($inquiry['contact']); ?></td>
                                <td><?php echo $inquiry['created_at']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>표시할 상담 데이터가 없습니다.</p>
                <?php endif; ?>
            </div>

            <!-- 비밀번호 재설정 모달 -->
            <div id="resetModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeResetModal()">&times;</span>
                    <h3>관리자 비밀번호 재설정</h3>
                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?action=resetpw" onsubmit="return validatePassword()" id="resetPasswordForm">
                        <p>
                            <input type="password" id="new_password" name="new_password" placeholder="새 비밀번호" required>
                        </p>
                        <p>
                            <input type="password" id="confirm_password" placeholder="비밀번호 확인" required>
                        </p>
                        <p id="password_error" style="color: red; display: none;">비밀번호가 일치하지 않습니다.</p>
                        <button type="submit">비밀번호 변경</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // 엔터 키 입력 시 폼 제출
        document.addEventListener('DOMContentLoaded', function() {
            // 로그인 폼에 대한 엔터 키 처리
            const passwordInput = document.querySelector('input[name="password"]');
            if (passwordInput) {
                passwordInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this.form.submit();
                    }
                });
            }

            // 비밀번호 재설정 모달에 대한 엔터 키 처리
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');

            if (newPasswordInput) {
                newPasswordInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        confirmPasswordInput.focus();
                    }
                });
            }

            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (validatePassword()) {
                            this.form.submit();
                        }
                    }
                });
            }
        });

        // 작업 확인 함수
        function confirmAction(url, message) {
            if (confirm(message)) {
                window.location.href = url;
            }
        }

        // 모달 제어 함수
        function openResetModal() {
            document.getElementById('resetModal').style.display = 'block';
        }

        function closeResetModal() {
            document.getElementById('resetModal').style.display = 'none';
        }

        // 비밀번호 유효성 검사
        function validatePassword() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const errorElement = document.getElementById('password_error');

            if (newPassword !== confirmPassword) {
                errorElement.style.display = 'block';
                return false;
            }

            if (newPassword.length < 8) {
                errorElement.textContent = '비밀번호는 최소 8자 이상이어야 합니다.';
                errorElement.style.display = 'block';
                return false;
            }

            return true;
        }

        // 모달 외부 클릭 시 닫기
        window.onclick = function(event) {
            const modal = document.getElementById('resetModal');
            if (event.target == modal) {
                closeResetModal();
            }
        }
    </script>
</body>
</html>