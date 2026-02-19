<?php
require_once __DIR__ . '/admin/config.php';

// 관리자만 접근
if (!is_logged_in() || !is_admin()) {
    header('Location: /admin/login.php');
    exit;
}

$pdo = db_connect();

function ensure_columns_table(PDO $pdo): void {
    $check = $pdo->query("SHOW TABLES LIKE 'columns'");
    if ($check->rowCount() === 0) {
        $sql = "CREATE TABLE columns (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            is_notice TINYINT(1) NOT NULL DEFAULT 0,
            thumbnail_mode VARCHAR(20) NOT NULL DEFAULT 'default',
            thumbnail_url VARCHAR(500) NOT NULL DEFAULT '/images/col_img.jpg',
            content MEDIUMTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL
        ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $pdo->exec($sql);
    }
}

ensure_columns_table($pdo);

$default_thumb = '/images/col_img.jpg';
$editing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $editing_id = isset($_POST['id']) ? (int)$_POST['id'] : $editing_id;
}

$current = null;
if ($editing_id > 0) {
  $stmt = $pdo->prepare('SELECT * FROM columns WHERE id = ?');
  $stmt->execute([$editing_id]);
  $current = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$current) {
    http_response_code(404);
    echo '존재하지 않는 글입니다.';
    exit;
  }
}

$errors = [];
$title = $current['title'] ?? '';
$is_notice = isset($current['is_notice']) ? (int)$current['is_notice'] : 0;
$thumb_mode = $current['thumbnail_mode'] ?? 'default';
$content = $current['content'] ?? '';
$thumbnail_url = $current['thumbnail_url'] ?? $default_thumb;

// POST 값으로 덮어써서 사용자가 입력한 내용을 그대로 유지
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? $title);
  $is_notice = isset($_POST['is_notice']) ? 1 : 0;
  $thumb_mode = $_POST['thumb_mode'] ?? $thumb_mode;
  $content = $_POST['content'] ?? $content;
}

$allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'tif'];
$max_size = 50 * 1024 * 1024; // 50MB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 업로드 처리 전 현재 썸네일을 기본으로 설정 (수정 시 기존 유지)
  $thumbnail_url = $current['thumbnail_url'] ?? $default_thumb;

    if ($title === '') {
        $errors[] = '제목을 입력해주세요.';
    }

    if (trim(strip_tags($content)) === '') {
        $errors[] = '내용을 입력해주세요.';
    }

    // 썸네일 처리
    if ($thumb_mode === 'default') {
      $thumbnail_url = $default_thumb;
    } elseif ($thumb_mode === 'upload') {
      if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] === UPLOAD_ERR_NO_FILE) {
        if (!$current) {
          $errors[] = '썸네일 파일을 선택해주세요.';
        }
      } else {
            $file = $_FILES['thumbnail'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
              $msg = '썸네일 업로드 중 오류가 발생했습니다. (' . $file['error'] . ')';
              if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
                $msg = '서버 업로드 제한을 초과했습니다. (php.ini의 upload_max_filesize/post_max_size 확인)';
              }
              $errors[] = $msg;
            }

            if (empty($errors)) {
              if ($file['size'] > $max_size) {
                $errors[] = '썸네일 용량은 50MB 이하여야 합니다.';
              }

              $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
              if (!in_array($ext, $allowed_ext, true)) {
                $errors[] = '허용되지 않는 이미지 형식입니다.';
              }

              if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
                $errors[] = '유효한 업로드 파일이 아닙니다.';
              } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (strpos($mime, 'image/') !== 0) {
                  $errors[] = '이미지 파일만 업로드할 수 있습니다.';
                }
              }
            }

            if (empty($errors)) {
                $upload_dir = __DIR__ . '/uploaded_images';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $hash = hash_file('sha256', $file['tmp_name']);
                $safe_name = $hash . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
                $target_path = $upload_dir . '/' . $safe_name;

                if (!move_uploaded_file($file['tmp_name'], $target_path)) {
                    $errors[] = '썸네일 저장에 실패했습니다.';
                } else {
                    $thumbnail_url = '/uploaded_images/' . $safe_name;
                }
            }
        }
    }

    // 기본 썸네일 유지 시 thumbnail_url은 기본값 사용

    if (empty($errors)) {
      $now = date('Y-m-d H:i:s');

      if ($editing_id > 0) {
        $stmt = $pdo->prepare("UPDATE columns SET title = ?, is_notice = ?, thumbnail_mode = ?, thumbnail_url = ?, content = ?, updated_at = ? WHERE id = ?");
        $stmt->execute([
          $title,
          $is_notice,
          $thumb_mode,
          $thumbnail_url,
          $content,
          $now,
          $editing_id
        ]);
        header('Location: /column_view.php?id=' . urlencode((string)$editing_id));
      } else {
        $stmt = $pdo->prepare("INSERT INTO columns (title, is_notice, thumbnail_mode, thumbnail_url, content, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NULL)");
        $stmt->execute([
          $title,
          $is_notice,
          $thumb_mode,
          $thumbnail_url,
          $content,
          $now
        ]);
        $newId = (int)$pdo->lastInsertId();
        header('Location: /column_view.php?id=' . urlencode((string)$newId));
      }
      exit;
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
	<meta property="og:description" content="맑은숲구구팔한의원">
	<meta property="og:image" content="http://malgeunsup998.com/images/og_img.jpg">
	<meta property="og:url" content="http://malgeunsup998.com/">


  <link rel="stylesheet" href="./css/style.css" type="text/css"><!--  pc CSS -->
  <link rel="stylesheet" href="./css/style_tab.css" type="text/css"><!--  tab CSS -->
  <link rel="stylesheet" href="./css/style_mob.css" type="text/css"><!--  mobile CSS -->
  <link rel="stylesheet" href="./css/slick.css" type="text/css">
  <link rel="stylesheet" href="./css/swiper.min.css" type="text/css">
  <link rel="stylesheet" href="./css/pretendard.css" type="text/css">


  <!-- favicon -->
  <link rel="apple-touch-icon" sizes="57x57" href="./images/favicon.ico/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="./images/favicon.ico/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="./images/favicon.ico/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="./images/favicon.ico/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="./images/favicon.ico/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="./images/favicon.ico/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="./images/favicon.ico/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="./images/favicon.ico/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="./images/favicon.ico/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192"  href="./images/favicon.ico/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="./images/favicon.ico/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="./images/favicon.ico/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="./images/favicon.ico/favicon-16x16.png">
  <link rel="manifest" href="/manifest.json">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
  <meta name="theme-color" content="#ffffff">
  
	
  <!-- script -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
  <script src="./css/js/slick.min.js"></script>
  <script src="./css/js/swiper.min.js"></script>
  <script src="./css/js/jquery.anchor.js"></script>


  <!-- aos -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

  <title>칼럼 | 맑은숲구구팔한의원</title>
</head>
<body>
  
	<div id="wrap">
		<header id="header">
      <div class="inner">
        <h1><a href="/index.php">
          <img src="./images/logo.png" alt="맑은숲구구팔한의원" class="pc">
          <img src="./images/logo_w.png" alt="맑은숲구구팔한의원" class="mob">
        </a></h1>
        <ul class="gnb">
          <li><a href="#con3" class="anchorLink">의료진 소개</a></li>
          <li><a href="#con5" class="anchorLink">통증치료</a></li>
          <li><a href="#con9" class="anchorLink">체형교정</a></li>
          <li><a href="#con12" class="anchorLink">다이어트</a></li>
          <li><a href="/column.php">전문칼럼</a></li>
          <li><a href="#con18" class="anchorLink">입원치료</a></li>
          <li><a href="#con22" class="anchorLink">오시는길</a></li>
        </ul><!-- // gnb -->

        <div class="navi clfix">
          <div class="navi_wrap">
            <div class="lnb">
              <h3><a href="#con3" class="anchorLink">의료진 소개</a></h3>
              <h3><a href="#con5" class="anchorLink">통증치료</a></h3>
              <h3><a href="#con9" class="anchorLink">체형교정</a></h3>
              <h3><a href="#con12" class="anchorLink">다이어트</a></h3>
              <h3><a href="/column.php">전문칼럼</a></h3>
              <h3><a href="#con18" class="anchorLink">입원치료</a></h3>
              <h3><a href="#con22" class="anchorLink">오시는길</a></h3>
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


    <div id="quick_btn">
      <ul class="q_list">
        <li><a href="https://blog.naver.com/998hani" target="_blank">
          <img src="./images/q_blog.png" alt="">
          <p>블로그</p>
        </a></li>
        <li><a href="https://buly.kr/uVKT7P" target="_blank">
          <img src="./images/q_naver.png" alt="">
          <p>네이버 예약</p>
        </a></li>
        <li><a href="https://pf.kakao.com/_xeJKIG" target="_blank">
          <img src="./images/q_kakao.png" alt="">
          <p>카카오톡 문의</p>
        </a></li>
        <li><a href="https://buly.kr/AwgWNdj" target="_blank">
          <img src="./images/q_ntalk.png" alt="">
          <p>네이버 톡톡</p>
        </a></li>
        <li><a href="tel:051-752-9981">
          <img src="./images/q_tell.png" alt="">
          <p>전화 문의</p>
        </a></li>
        <li><a href="https://buly.kr/B7bHMRS" target="_blank">
          <img src="./images/q_map.png" alt="">
          <p>오시는 길</p>
        </a></li>
      </ul><!-- // q_list -->
      <button class="q_plus"><img src="./images/q_plus.png" alt=""></button>
      <button class="top_btn"><img src="./images/top_btn.png" alt=""></button>
    </div><!-- // quick_btn -->


    <div class="inquiry_wrap" style="bottom: 0px;">
      <div class="top_btn">
        <img src="./images/in_check.png" alt="">
        <p>빠른 상담 신청</p>
      </div><!-- // top_btn -->

      <div class="i_content">        
        <form method="post" action="/admin/submit_inquiry.php">
          <div class="i_inner">
            <div class="i_box">
              <ul>
                <li><input type="text" name="name" id="quick_name" required placeholder="이름" maxlength="10"></li>
                <li><input type="text" name="contact" id="quick_contact" required placeholder="연락처 (- 없이 숫자만 입력)" pattern="[0-9]{10,11}"></li>
                <li>
                  <select name="business_category" id="business_category" required>
                    <option value="">문의내용</option>
                    <option value="통증치료">통증치료</option>
                    <option value="체형교정">체형교정</option>
                    <option value="다이어트">다이어트</option>
                    <option value="입원치료">입원치료</option>
                    <option value="기타">기타</option>
                  </select>
                </li>
              </ul>
              <div class="privacy">
                <input type="checkbox" name="privacy_agree" id="quick_agree_privacy" value="Y" required>
                <p>개인정보 수집/이용 동의</p>
              </div>
            </div><!-- // i_box -->

            <button type="submit" class="in_btn" id="submit_quick_inquiry">상담신청</button>
          </div><!-- // i_inner -->
        </form>
      </div><!-- // i_content -->
    </div>


		<section id="sub_column_wrap">
      <div class="inner">
        <div class="title">
          <h2><?php echo $editing_id > 0 ? '칼럼 수정하기' : '칼럼 등록하기'; ?></h2>
          <p><?php echo $editing_id > 0 ? '기존 칼럼을 수정합니다.' : '새로운 칼럼을 등록해주세요.'; ?></p>
        </div><!-- // title -->

        <div class="cv_wrap">

          <?php if (!empty($errors)): ?>
            <div class="message error" style="margin-bottom:15px; color:#c62828;">
              <?php foreach ($errors as $err): ?>
                <div><?php echo h($err); ?></div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <form method="post" enctype="multipart/form-data" action="/column_write.php<?php echo $editing_id > 0 ? '?id=' . urlencode((string)$editing_id) : ''; ?>">
          <?php if ($editing_id > 0): ?>
            <input type="hidden" name="id" value="<?php echo $editing_id; ?>">
          <?php endif; ?>
          <table class="tbl_cwr">
            <colgroup>
              <col width="20%">
              <col width="80%">
            </colgroup>
            <tbody>
              <tr>
                <th>제목</th>
                <td><input type="text" name="title" value="<?php echo h($title); ?>" required></td>
              </tr>
              <tr>
                <th>썸네일</th>
                <td>
                  <label style="margin-right:10px;">
                    <input type="radio" name="thumb_mode" value="default" <?php echo $thumb_mode === 'upload' ? '' : 'checked'; ?>> 기본 썸네일 사용 (/images/col_img.jpg)
                  </label>
                  <label>
                    <input type="radio" name="thumb_mode" value="upload" <?php echo $thumb_mode === 'upload' ? 'checked' : ''; ?>> 직접 업로드
                  </label>
                  <div style="margin-top:10px;">
                    <input type="file" name="thumbnail" id="thumbnail" accept="image/jpeg,image/png,image/gif,image/bmp,image/webp,image/tiff">
                    <p class="r_txt">* 권장사이즈 : 600 x 420px</p>
                    <?php if ($editing_id > 0 && $thumbnail_url): ?>
                      <p class="r_txt">현재 썸네일: <?php echo h($thumbnail_url); ?></p>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <tr>
                <th>공지</th>
                <td>
                  <label>
                    <input type="checkbox" name="is_notice" <?php echo $is_notice ? 'checked' : ''; ?>>
                    <span class="label_txt">공지로 표시</span>
                  </label>
                </td>
              </tr>
              <tr>
                <th>내용</th>
                <td>
                  <textarea name="content" id="ck-editor" required><?php echo h($content); ?></textarea>
                </td>
              </tr>
            </tbody>
          </table><!-- // tbl_cwr -->

          <button type="submit" class="col_list_btn col_cmpl_btn"><?php echo $editing_id > 0 ? '수정' : '등록'; ?></button>
          </form>
        </div><!-- // cv_wrap -->
        



    </section>



    <footer id="footer">
      <div class="inner">
        <img src="./images/foot_logo.png" alt="" class="foot_logo">

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
          <button class="nonpay_btn">비급여 항목안내</button>
        </div><!-- // f_wrap -->

      </div><!-- // inner -->
    </footer><!-- // footer -->


    <!-- 팝업 시작 -->
    <div id="mainPopup" class="popup_overlay" aria-hidden="true" style="display:none;">
      <div class="popup_wrap" role="dialog" aria-modal="true" aria-label="이벤트 팝업">
        <div class="popup_content">
          <div class="pop_inner">
            <ul class="pop_list">
              <li><img src="./images/popup_img_01.jpg" alt=""></li>
              <li><img src="./images/popup_img_02.jpg" alt=""></li>
              <!-- <li><img src="./images/popup_img_03.jpg" alt=""></li>
              <li><img src="./images/popup_img_04.jpg" alt=""></li> -->
            </ul><!-- // pop_list -->
          </div><!-- // pop_inner -->

          <div class="popup_footer">
            <label class="today_close">
              <input type="checkbox" id="popupTodayClose" />
              <span>오늘 하루 열지 않음</span>
            </label>

            <button type="button" class="close_btn" aria-label="팝업 닫기">닫기</button>
          </div><!-- // popup_footer -->
        </div><!-- // popup_content -->
      </div><!-- // popup_wrap -->
    </div><!-- // mainPopup -->


    <script>
      (function () {
        const popup = document.getElementById("mainPopup");
        const closeBtn = popup.querySelector(".close_btn");
        const todayChk = document.getElementById("popupTodayClose");
        const openBtns = document.querySelectorAll(".nonpay_btn");

        const COOKIE_NAME = "popup_hide_until_midnight";

        /* === 쿠키 유틸 === */
        function setCookieUntilMidnight(name, value) {
          const now = new Date();
          const midnight = new Date();
          midnight.setHours(24, 0, 0, 0);

          document.cookie =
            name + "=" + value +
            "; expires=" + midnight.toUTCString() +
            "; path=/";
        }

        function getCookie(name) {
          return document.cookie
            .split("; ")
            .find(row => row.startsWith(name + "="))
            ?.split("=")[1];
        }

        /* === 팝업 열기 === */
        function openPopup() {
          popup.style.display = "block";
          popup.setAttribute("aria-hidden", "false");
          document.body.style.overflow = "hidden";
        }

        /* === 팝업 닫기 === */
        function closePopup() {
          popup.style.display = "none";
          popup.setAttribute("aria-hidden", "true");
          document.body.style.overflow = "";
        }

        /* === 오늘 하루 체크 === */
        closeBtn.addEventListener("click", () => {
          if (todayChk.checked) {
            setCookieUntilMidnight(COOKIE_NAME, "hide");
          }
          closePopup();
        });

        /* === 버튼 클릭으로 열기 === */
        openBtns.forEach(btn => {
          btn.addEventListener("click", e => {
            e.preventDefault();
            openPopup();
          });
        });

        /* === 초기 상태 (쿠키 확인) === */
        if (getCookie(COOKIE_NAME) === "hide") {
          popup.style.display = "none";
        }
      })();
    </script>


    <!-- 팝업 끝 -->




	</div><!-- // wrap -->



<script>
  AOS.init({
    duration: 1000,
    once : 1 ,
    offset: 300
  });

  $(document).ready(function() {
    // 탭모바일 네비 버튼
    $('.navi_btn').click(function() {
      $(this).toggleClass('on');
      $('.navi').stop().fadeToggle()
    });

    // 퀵버튼
    $('#quick_btn .top_btn').click(function(){
      $('html, body').animate({
        scrollTop:0
      },700)
      return false;
    });

    $('.q_btn').on('click', function () {
      $('.q_list').stop().slideToggle(300);
    });

    // 빠른상담
    let isOpen = false;

    function getDimensions() {
      const isMobile = window.innerWidth <= 768; // 모바일 기준 너비 설정
      return {
        totalHeight: isMobile ? 325 : 215,
        topBtnHeight: 50
      };
    }

    // 초기 상태 설정
    function setInitialPosition() {
      const { totalHeight, topBtnHeight } = getDimensions();
      $('.inquiry_wrap').css('bottom', -(totalHeight - topBtnHeight) + 'px');
    }
    
    $(function () {
      setInitialPosition(); // 페이지 로드시 초기 위치 설정

      $('.inquiry_wrap .top_btn').on('click', function () {
        const { totalHeight, topBtnHeight } = getDimensions();

        if (isOpen) {
          // 닫기
          $('.inquiry_wrap').animate({
            bottom: -(totalHeight - topBtnHeight)
          }, 300);
          isOpen = false;
        } else {
          // 열기
          $('.inquiry_wrap').animate({
            bottom: 0
          }, 300);
          isOpen = true;
        }
      });

      // 화면 크기 변경 시에도 대응
      $(window).on('resize', function () {
        // 이미 열려 있다면 bottom을 0으로 유지
        if (isOpen) {
          $('.inquiry_wrap').css('bottom', 0);
        } else {
          setInitialPosition(); // 닫힌 상태에서는 다시 높이 계산
        }
      });
    });











    function applyStyles() {
      if ($(window).width() <= 767) {
        
        $('.q_plus').click(function() {
          $('.q_list').slideToggle();
        });

      }
    }

    // 초기 실행
    applyStyles();

    // 창 크기가 변경될 때마다 실행
    $(window).resize(applyStyles);



  });




</script>

<script type="module" src="./css/js/ckeditor5/main.js"></script>
<script>
  // 썸네일 업로드 선택 시에만 파일 입력 활성화
  const thumbRadios = document.querySelectorAll('input[name="thumb_mode"]');
  const thumbInput = document.getElementById('thumbnail');
  function toggleThumbInput() {
    const isUpload = document.querySelector('input[name="thumb_mode"]:checked').value === 'upload';
    thumbInput.disabled = !isUpload;
  }
  thumbRadios.forEach(r => r.addEventListener('change', toggleThumbInput));
  toggleThumbInput();
</script>

</body>
</html>
