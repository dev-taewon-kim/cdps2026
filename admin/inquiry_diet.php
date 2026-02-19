<?php
// 오류 표시 (임시)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

require_once 'config.php';

// 관리자 로그인 확인
if (!is_logged_in() || !is_admin()) {
    header('Location: /admin/login.php');
    exit;
}

// 페이지 번호 받기
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // 최소 1페이지

// 페이지당 항목 수
$per_page = 10;
$offset = ($page - 1) * $per_page;

// 데이터베이스 연결
$pdo = db_connect();

try {
    // diet_inquiries 테이블 존재 여부 확인
    $checkTable = $pdo->query("SHOW TABLES LIKE 'diet_inquiries'");
    $tableExists = $checkTable->rowCount() > 0;
    
    if (!$tableExists) {
        // 테이블이 없으면 빈 결과로 처리
        $inquiries = [];
        $total_inquiries = 0;
        $total_pages = 1;
    } else {
        // 전체 문의 수 조회
        $count_sql = "SELECT COUNT(*) FROM diet_inquiries";
        $count_stmt = $pdo->query($count_sql);
        $total_inquiries = $count_stmt->fetchColumn();

        // 전체 페이지 수 계산
        $total_pages = ceil($total_inquiries / $per_page);
        $total_pages = max(1, $total_pages); // 최소 1페이지

        // 현재 페이지 문의 조회
        $sql = "SELECT * FROM diet_inquiries ORDER BY id DESC LIMIT :offset, :per_page";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
        $stmt->execute();
        $inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    // 에러 발생 시 빈 결과로 처리
    $inquiries = [];
    $total_inquiries = 0;
    $total_pages = 1;
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

// 페이지네이션 범위 계산
$page_group = ceil($page / 10);
$group_start = ($page_group - 1) * 10 + 1;
$group_end = min($page_group * 10, $total_pages);
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

  <title>맑은숲구구팔한의원 | 문의내역</title>
</head>
<body>
	<div id="wrap" class="admin_in_wrap">
		<header id="header">
      <div class="inner">
        <h1><a href="/index.php"><img src="/images/logo.png" alt="맑은숲구구팔한의원"></a></h1>
        <ul class="gnb">
          <li><a href="#con" class="anchorLink">의료진 소개</a></li>
          <li><a href="#con" class="anchorLink">통증치료</a></li>
          <li><a href="#con" class="anchorLink">체형교정</a></li>
          <li><a href="#con" class="anchorLink">다이어트</a></li>
          <li><a href="/column.php">전문칼럼</a></li>
          <li><a href="#con" class="anchorLink">입원치료</a></li>
          <li><a href="#con" class="anchorLink">오시는길</a></li>
        </ul><!-- // gnb -->

        <div class="navi clfix">
          <div class="navi_wrap">
            <div class="lnb">
              <h3><a href="#con" class="anchorLink">의료진 소개</a></h3>
              <h3><a href="#con" class="anchorLink">통증치료</a></h3>
              <h3><a href="#con" class="anchorLink">체형교정</a></h3>
              <h3><a href="#con" class="anchorLink">다이어트</a></h3>
              <h3><a href="/column.php">전문칼럼</a></h3>
              <h3><a href="#con" class="anchorLink">입원치료</a></h3>
              <h3><a href="#con" class="anchorLink">오시는길</a></h3>
            </div><!-- // lnb -->
          </div><!-- // navi_wrap -->
        </div><!-- // navi -->
      </div><!-- // inner -->
      
      <div class="navi_btn">
        <span class="line01"></span>
        <span class="line02"></span>
        <span class="line03"></span>
      </div>

		</header><!-- // header -->


    <section id="admin_inq">
      <div class="inner">
        <h2>맑은숲구구팔한의원 문의내역</h2>
        
        <div style="text-align: right; margin-bottom: 10px;">
          <a href="/admin/logout.php" style="color: #666; text-decoration: none;">로그아웃</a>
        </div>

        <ul class="admin_tab_btn">
          <li><a href="/admin/inquiry_list.php">빠른상담신청</a></li>
          <li class="on"><a href="/admin/inquiry_diet.php">다이어트</a></li>
          <li><a href="/admin/inquiry_hp.php">입원치료</a></li>
        </ul><!-- // admin_tab_btn -->

        <table class="admin_tbl">
          <colgroup>
            <col width="5%">
            <col width="10%">
            <col width="15%">
            <col width="20%">
            <col width="20%">
            <col width="15%">
            <col width="15%">
          </colgroup>
          <tbody>
            <tr>
              <th>No.</th>
              <th>성명</th>
              <th>연락처</th>
              <th>비만유형</th>
              <th>희망진료일</th>
              <th>작성일</th>
              <th>자세히보기</th>
            </tr>
            <?php if (!empty($inquiries)): ?>
              <?php 
              $no = $total_inquiries - $offset;
              foreach ($inquiries as $inquiry): 
              ?>
              <tr>
                <td><?php echo $no--; ?></td>
                <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                <td><?php echo htmlspecialchars(format_phone($inquiry['contact'])); ?></td>
                <td><?php echo htmlspecialchars($inquiry['diet_type']); ?></td>
                <td><?php echo htmlspecialchars($inquiry['reservation_date']); ?></td>
                <td><?php echo date('Y-m-d', strtotime($inquiry['created_at'])); ?></td>
                <td><a href="./inquiry_diet_view.php?id=<?php echo $inquiry['id']; ?>" class="view_btn">확인하기</a></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" style="text-align: center; padding: 50px 0;">등록된 문의가 없습니다.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="paging_wrap">
          <ul class="paging">
            <?php if ($page > 1): ?>
              <li class="page_arrow"><a href="?page=1"><img src="../images/btn_first.png" alt="맨 앞으로"></a></li>
              <li class="page_arrow"><a href="?page=<?php echo $page - 1; ?>"><img src="../images/btn_prev.png" alt="앞으로"></a></li>
            <?php endif; ?>
            
            <?php for ($i = $group_start; $i <= $group_end; $i++): ?>
              <li <?php echo $i == $page ? 'class="on"' : ''; ?>><a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
              <li class="page_arrow"><a href="?page=<?php echo $page + 1; ?>"><img src="../images/btn_next.png" alt="뒤로"></a></li>
              <li class="page_arrow"><a href="?page=<?php echo $total_pages; ?>"><img src="../images/btn_last.png" alt="맨 뒤로"></a></li>
            <?php endif; ?>
          </ul>
        </div>
        <?php endif; ?>


      </div><!-- // inner -->
    </section><!-- // admin_inq -->


    <footer id="footer">
      <div class="inner">
        <img src="/images/foot_logo.png" alt="" class="foot_logo">

        <div class="f_wrap">
          <div class="f_box">
            <ul class="f_info">
              <li>대표자명 : 윤성식</li>
              <li>주소 : 부산광역시 수영구 수영로 697, 3,4층(수영동, 홍인빌딩)</li>
            </ul>
            <ul class="f_info">
              <li>사업자등록번호 : 580-93-01642</li>
              <li>Copylightⓒ2025 맑은숲구구팔한의원 All Rights Reserved.</li>
            </ul>
          </div><!-- // f_box -->
          <button>비급여 항목안내</button>
        </div><!-- // f_wrap -->

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