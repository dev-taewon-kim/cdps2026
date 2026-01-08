<?php
require_once 'config.php';

// 관리자 로그인 확인
if (!is_logged_in() || !is_admin()) {
    header('Location: /admin/login.php');
    exit;
}

// ID 파라미터 확인
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: /admin/inquiry_list.php');
    exit;
}

// 데이터베이스 연결
$pdo = db_connect();

// 문의 정보 조회
$sql = "SELECT * FROM inquiries WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$inquiry = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$inquiry) {
    header('Location: /admin/inquiry_list.php');
    exit;
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
	<meta property="og:description" content="맑은숲구구팔한의원">
	<meta property="og:image" content="">
	<meta property="og:url" content="">


  <link rel="stylesheet" href="../css/style.css" type="text/css"><!--  pc CSS -->
  <link rel="stylesheet" href="../css/style_tab.css" type="text/css"><!--  tab CSS -->
  <link rel="stylesheet" href="../css/style_mob.css" type="text/css"><!--  mobile CSS -->
  <link rel="stylesheet" href="../css/slick.css" type="text/css">
  <link rel="stylesheet" href="../css/swiper.min.css" type="text/css">


  <!-- favicon -->
  <link rel="apple-touch-icon" sizes="57x57" href="../images/favicon.ico/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="../images/favicon.ico/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="../images/favicon.ico/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="../images/favicon.ico/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="../images/favicon.ico/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="../images/favicon.ico/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="../images/favicon.ico/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="../images/favicon.ico/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../images/favicon.ico/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192"  href="../images/favicon.ico/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="../images/favicon.ico/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="../images/favicon.ico/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="../images/favicon.ico/favicon-16x16.png">
  <link rel="manifest" href="/manifest.json">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
  <meta name="theme-color" content="#ffffff">
  
	
  <!-- script -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  <script src="../css/js/slick.min.js"></script>
  <script src="../css/js/swiper.min.js"></script>
  <script src="../css/js/jquery.anchor.js"></script>


  <!-- aos -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

  <title>맑은숲구구팔한의원 | 문의 상세보기</title>
</head>
<body>
	<div id="wrap" class="admin_in_wrap">
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


    <section id="admin_inq">
      <div class="inner">
        <h2>맑은숲구구팔한의원 문의내역</h2>

        <table class="admin_tbl">
          <colgroup>
            <col width="20%">
            <col width="30%">
            <col width="20%">
            <col width="30%">
          </colgroup>
          <tbody>
            <tr>
              <th>No.</th>
              <td><?php echo $inquiry['id']; ?></td>
              <th>날짜</th>
              <td><?php echo date('Y.m.d', strtotime($inquiry['created_at'])); ?></td>
            </tr>
            <tr>
              <th>성명</th>
              <td><?php echo $inquiry['name']; ?></td>
              <th>연락처</th>
              <td><?php echo format_phone($inquiry['contact']); ?></td>
            </tr>
          </tbody>
        </table>

        <button class="list_btn"><a href="./inquiry_list.php">목록</a></button>


      </div><!-- // inner -->
    </section><!-- // admin_inq -->


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

    AOS.init({
      duration: 500,
      once : 1 ,
      offset: 100
    });
  });

</script>



</body>
</html>