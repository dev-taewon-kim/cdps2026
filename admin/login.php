<?php
require_once 'config.php';

// 이미 로그인되어 있으면 문의 목록 페이지로 리다이렉트
if (is_logged_in()) {
    header('Location: /admin/inquiry_list.php');
    exit;
}

$error = '';

// 로그인 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = '아이디와 비밀번호를 모두 입력해주세요.';
    } else {
        $pdo = db_connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // 로그인 성공 - 세션에 사용자 정보 저장
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];

            // 마지막 로그인 시간 업데이트
            $updateStmt = $pdo->prepare("UPDATE users SET last_login_at = ? WHERE id = ?");
            $updateStmt->execute([date('Y-m-d H:i:s'), $user['id']]);

            // 관리자면 문의 목록 페이지로, 일반 사용자면 메인 페이지로 리다이렉트
            if ($user['username'] === 'admin') {
                header('Location: /admin/inquiry_list.php');
            } else {
                header('Location: /index.php');
            }
            exit;
        } else {
            $error = '아이디 또는 비밀번호가 올바르지 않습니다.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="맑은숲구구팔한의원">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="viewport" content="user-scalable=yes, maximum-scale=1.0, minimum-scale=0.25, width=1200">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
  <meta name="format-detection" content="telephone=no" />

  <!-- 오픈그래프 -->
  <meta property="og:type" content="website"> 
  <meta property="og:title" content="맑은숲구구팔한의원">
  <meta property="og:description" content="신뢰와 정성을 담아 함께하는 신길플란트치과">
  <meta property="og:image" content="https://dsan2025.mycafe24.com/og_image.jpg">
  <meta property="og:url" content="">


  <link rel="stylesheet" href="/css/style.css" type="text/css"><!--  pc CSS -->
  <link rel="stylesheet" href="/css/style_tab.css" type="text/css"><!--  tab CSS -->
  <link rel="stylesheet" href="/css/style_mob.css" type="text/css"><!--  mobile CSS -->
  <link rel="stylesheet" href="/css/slick.css" type="text/css">
  <link rel="stylesheet" href="/css/swiper.min.css" type="text/css">


  <!-- favicon -->
  <link rel="apple-touch-icon" sizes="57x57" href="/images/favicon.ico/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="/images/favicon.ico/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="/images/favicon.ico/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="/images/favicon.ico/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="/images/favicon.ico/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="/images/favicon.ico/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="/images/favicon.ico/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/images/favicon.ico/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/images/favicon.ico/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192"  href="/images/favicon.ico/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon.ico/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/images/favicon.ico/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon.ico/favicon-16x16.png">
  <link rel="manifest" href="/manifest.json">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
  <meta name="theme-color" content="#ffffff">
  

  <!-- font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  
  <!-- script -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  <script src="/css/js/slick.min.js"></script>
  <script src="/css/js/swiper.min.js"></script>
  <script src="/css/js/common.js"></script>
  <script src="/css/js/jquery.anchor.js"></script>


  <!-- aos -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

  <title>맑은숲구구팔한의원 | 관리자 로그인</title>
</head>
<body>
	<div id="wrap">
		<header id="header">
      <h1><a href="/index.html"><img src="/images/logo.png" alt="맑은숲구구팔한의원"></a></h1>
      <ul class="gnb">
        <li><a href="#con1" class="anchorLink">병원소개</a></li>
        <li><a href="#con2" class="anchorLink">진료과목</a></li>
        <li><a href="#con4" class="anchorLink">임플란트</a></li>
        <li><a href="#con11" class="anchorLink">자연치아 살리기</a></li>
        <li><a href="#con12" class="anchorLink">사랑니 발치</a></li>
        <li><a href="#con13" class="anchorLink">둘러보기</a></li>
        <li><a href="#con14" class="anchorLink">오시는 길</a></li>
      </ul><!-- // gnb -->

      <div class="h_tel"><a href="tel:02-832-2275">
        <img src="/images/h_tel.png" alt="">
        <p>02.832.2275</p>
      </a></div>
		</header><!-- // header -->


    <section id="login_wrap">
      <div class="inner">
        <div class="l_tit">
          <h3><b>맑은숲구구팔한의원</b> 관리자 로그인</h3>
        </div><!-- // l_tit -->

        <div class="login_box">
          <form method="post" action="login.php">
            <input type="text" name="username" placeholder="아이디" value="<?php echo isset($_POST['username']) ? h($_POST['username']) : ''; ?>" required>
            <input type="password" name="password" placeholder="비밀번호" required>
            <button type="submit" class="login_btn">로그인</button>
          </form>
          <!-- <button class="j_btn"><a href="/admin/join.php">회원가입</a></button>
          <p class="findpw"><a href="/admin/find_password.php">비밀번호 찾기</a></p> -->
        </div><!-- // login_box -->
      </div><!-- // inner -->
    </section><!-- // login_wrap -->



    <footer id="footer">
      <div class="inner">
        <img src="/images/foot_logo.png" alt="" class="foot_logo">

        <div class="f_wrap">
          <div class="f_box">
            <ul class="f_info">
              <li>상호명 : 맑은숲구구팔한의원</li>
              <li>대표자 : 김준현</li>
            </ul>
            <ul class="f_info">
              <li>사업자등록번호 : 671-98-01735</li>
              <li>대표번호 : 02-832-2275</li>
            </ul>
          </div><!-- // f_box -->
          <button>비급여 항목안내</button>
        </div><!-- // f_wrap -->
        
        <p class="copyright">COPYRIGHT ⓒ 2025 맑은숲구구팔한의원 ALL RIGHTS RESERVED.</p>
      </div><!-- // inner -->
    </footer><!-- // footer -->



    


    
    

	</div><!-- // wrap -->



  <script>
    $(document).ready(function() {
      // 탭모바일 네비 버튼
      $('.navi_btn').click(function() {
        $(this).toggleClass('on');
        $('.navi').stop().fadeToggle()
      });

    });
  </script>

<?php
if (!empty($error)) {
  echo "<script>alert('$error');</script>";
}
?>



</body>
</html>
