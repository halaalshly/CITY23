<?php
session_start();
require_once __DIR__ . "/lang.php";
require_once __DIR__ . "/database/db.php";
require_once __DIR__ . "/helpers.php";

/* معالم الخريطة */
$landmarks = [];
$res = mysqli_query($conn, "SELECT ID, NAME, CATEGORY, DESCRIPTION, IMAGE_URL, MAPS_URL,
                                   LATITUDE AS latitude, LONGITUDE AS longitude
                            FROM madinah_landmarks
                            WHERE LATITUDE IS NOT NULL AND LONGITUDE IS NOT NULL");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $row["IMAGE_URL"] = image_url(resolve_image($row["IMAGE_URL"], __DIR__));
        $landmarks[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($LANG) ?>" dir="<?= htmlspecialchars($LANG_DIR) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>أثر المدينة | اكتشف المدينة المنورة</title>

    <!-- الخط -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap"
          rel="stylesheet">

    <!-- ملف CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <?= lang_switcher_css() ?>
    <style>
        /* ===== القوائم المنسدلة في شريط التنقل ===== */
        .nav-dd { position: relative; display: flex; align-items: center; }
        .nav-dd > a { padding-inline-end: 4px; }
        .dd-arrow {
            background: none; border: 0; color: inherit; cursor: pointer;
            font-size: 14px; padding: 25px 6px 20px; line-height: 1;
            border-bottom: 2px solid transparent; transition: .3s;
        }
        .dd-arrow span { display: inline-block; transition: transform .25s; }
        .nav-dd.open .dd-arrow span { transform: rotate(180deg); }
        .nav-dd:hover .dd-arrow, .nav-dd.open .dd-arrow { border-color: #d4bd83; }
        .dd-menu {
            display: none; position: absolute; top: 100%; inset-inline-start: 0;
            min-width: 200px; background: #fff; border-radius: 12px; padding: 6px;
            box-shadow: 0 10px 28px rgba(0,0,0,.22); z-index: 3000;
        }
        .nav-dd.open .dd-menu { display: block; }
        .nav-links .dd-menu a {
            display: block; padding: 10px 14px; color: #174c4c; border: 0;
            border-radius: 8px; font-size: 13px; white-space: nowrap;
        }
        .nav-links .dd-menu a:hover { background: #eef4f1; }
    </style>
    <style>
        .navbar .lang-switcher { flex-wrap: nowrap; }
        .navbar .lang-switcher a { padding: 3px 8px; font-size: 11px; white-space: nowrap; }
    </style>
</head>

<body>

<!-- ================= HEADER ================= -->

<header class="navbar">

    <div class="logo">
        <img src="images/logo.jpeg" alt="أثر المدينة" class="logo-image">

        <div class="logo-text">
        <div class="logo-ar">أثَر المدينة</div>
        <div class="logo-en">ATHAR AL-MADINA</div>
</div>
    </div>

   <nav class="nav-links">

        <a href="#" class="active"><?= htmlspecialchars(t('nav_home')) ?></a>

        <div class="nav-dd">
            <a href="#categories"><?= htmlspecialchars(t('nav_places')) ?></a>
            <button type="button" class="dd-arrow" aria-label="<?= htmlspecialchars(t('nav_places')) ?>"><span>⌄</span></button>
            <div class="dd-menu">
                <a href="places/religious.php"><?= htmlspecialchars(t('cat_religious')) ?></a>
                <a href="places/historical.php"><?= htmlspecialchars(t('cat_historical')) ?></a>
                <a href="places/museums.php"><?= htmlspecialchars(t('cat_museums_culture')) ?></a>
                <a href="places/nature.php"><?= htmlspecialchars(t('cat_nature')) ?></a>
            </div>
        </div>

        <div class="nav-dd">
            <a href="visit_planner.php"><?= htmlspecialchars(t('nav_planner')) ?></a>
            <button type="button" class="dd-arrow" aria-label="<?= htmlspecialchars(t('nav_planner')) ?>"><span>⌄</span></button>
            <div class="dd-menu">
                <a href="visit_planner.php#manual"><?= htmlspecialchars(t('pl_manual')) ?></a>
                <a href="smart_planner.php"><?= htmlspecialchars(t('pl_smart')) ?></a>
            </div>
        </div>

        <a href="map.php"><?= htmlspecialchars(t('nav_map')) ?></a>

        <a href="#about"><?= htmlspecialchars(t('nav_about')) ?></a>

        <a href="add_place.php"><?= htmlspecialchars(t('nav_add')) ?></a>
    </nav>

    <!-- أزرار تبديل اللغة -->
    <?= lang_switcher() ?>

    <div class="account">

    <?php if (isset($_SESSION["user_id"])): ?>

        <span>
            <?= htmlspecialchars(t('acc_welcome')) ?> <?php echo htmlspecialchars($_SESSION["name"]); ?>
        </span>

        <a href="logout.php" class="account-btn">
            <?= htmlspecialchars(t('acc_logout')) ?>
        </a>

    <?php else: ?>

        <a href="login.php">
            <?= htmlspecialchars(t('acc_login')) ?>
        </a>

        <span class="user">●</span>

        <a href="register.php" class="account-btn">
            <?= htmlspecialchars(t('acc_register')) ?>
        </a>

    <?php endif; ?>

</div>

</header>


<!-- ================= HERO ================= -->

<section class="hero">

    <div class="hero-overlay"></div>

    <div class="hero-content">

        <span class="small-title">
            دليلك إلى تاريخ المدينة المنورة
        </span>

        <h1>
            أثَر المدينة
        </h1>

        <h2>
            اكتشف تاريخ المدينة المنورة .. بطريقة أذكى
        </h2>

        <p>
            منصة تجمع لك أهم المعالم التاريخية والدينية والثقافية
            في المدينة المنورة، مع إمكانية تنظيم زيارتك
            واستكشافها بسهولة.
        </p>

        <div class="hero-buttons">

            <a href="#places" class="main-btn">
                استكشف المعالم ✧
            </a>
            
            <a href="visit_planner.php" class="second-btn" >
             منظم الزيارة
             </a>

        </div>

    </div>


    <!-- SEARCH -->

    <div class="search-box">

        <button class="search-icon">
            ⌕
        </button>

        <input
            type="text"
            placeholder="ابحث عن معلم .."
        >

        <div class="category">

            <span>
                جميع التصنيفات
            </span>

            <span>
                ⌄
            </span>

        </div>

    </div>

</section>


<!-- ================= PLACES ================= -->

<section class="section places" id="places">

    <div class="section-heading">

        <div>

            <span>
                أماكن لا تفوّت زيارتها
            </span>

            <h2>
                أبرز المعالم
            </h2>

        </div>

        <a href="#" class="view-all">
            عرض الكل ←
        </a>

    </div>


    <div class="places-grid">

<?php
/* ==========================================================
   أبرز المعالم - عدّل هنا:
   id    = رقم المعلم في قاعدة البيانات
   image = رابط الصورة بنفسك (مثل: images/logo.jpeg أو رابط https://...)
           اتركه فارغًا '' لاستخدام صورة قاعدة البيانات
   ========================================================== */
$featured = [
    ["id" => 1, "image" => ""],
    ["id" => 2, "image" => ""],
    ["id" => 3, "image" => ""],
    ["id" => 4, "image" => ""],
];
$byId = [];
foreach ($landmarks as $lm) { $byId[(int)$lm["ID"]] = $lm; }
foreach ($featured as $item):
    $fid = $item["id"];
    if (!isset($byId[$fid])) { continue; }
    $f = $byId[$fid];
    $img = trim($item["image"]) !== "" ? $item["image"] : $f["IMAGE_URL"];
    $desc = trim((string)$f["DESCRIPTION"]);
    if (mb_strlen($desc) > 120) { $desc = mb_substr($desc, 0, 120) . "…"; }
?>
        <a href="places/details.php?id=<?= (int)$f["ID"] ?>" class="place-card">
            <div class="place-image"
                 style="background-image:url('<?= htmlspecialchars($img, ENT_QUOTES) ?>');background-color:#dfe8e3;"></div>
            <div class="place-content">
                <h3><?= htmlspecialchars($f["NAME"]) ?></h3>
                <p><?= htmlspecialchars($desc) ?></p>
                <small>📍 المدينة المنورة</small>
            </div>
        </a>
<?php endforeach; ?>

    </div>
    </section>

<!-- ================= PLANNER ================= -->

<section class="planner" id="planner">

    <div class="planner-content">

        <div class="planner-icon">
            ⌖
        </div>

        <div class="planner-text">

            <h2>
                منظم الزيارة
            </h2>

            <p>
                اختر الأماكن التي تود زيارتها،
                وسنساعدك في إنشاء مسارك السياحي المثالي
                مع عرضه على الخريطة.
            </p>

        </div>

        <a href="visit_planner.php" >
              منظم الزيارة
</a>

    </div>

</section>


<!-- ================= MAP ================= -->

<section class="section map-section" id="map">

    <div class="section-heading">
        <div>
            <span>استكشف مواقع المعالم</span>
            <h2>الخريطة</h2>
        </div>
        <div class="line"></div>
    </div>

    <div id="home-map"></div>

    <div class="map-bottom">
        <span>📍 عدد المعالم: <strong><?= count($landmarks) ?></strong></span>
        <a href="map.php" class="map-full-button">فتح الخريطة كاملة ←</a>
    </div>

</section>


<!-- ================= CATEGORIES ================= -->

<section class="section categories" id="categories">

    <div class="section-heading">

        <div>

            <span>
                اكتشف أكثر
            </span>

            <h2>
                تصفح حسب التصنيف
            </h2>

        </div>

        <div class="line"></div>

    </div>


    <div class="category-grid">


        <!-- دينية -->

        <a href="places/religious.php" class="category-card religious">

            <div class="category-icon">
                🕌
            </div>

            <div>

                <h3>
                    معالم دينية
                </h3>

                <p>
                    أماكن مباركة وذات قيمة عظيمة
                </p>

            </div>

        </a>


        <!-- تاريخية -->

        <a href="places/historical.php" class="category-card historical">

            <div class="category-icon">
                🏰
            </div>

            <div>

                <h3>
                    معالم تاريخية
                </h3>

                <p>
                    آثار تحكي قصص الماضي
                </p>

            </div>

        </a>


        <!-- متاحف -->

        <a href="places/museums.php" class="category-card museums">

            <div class="category-icon">
                🏛
            </div>

            <div>

                <h3>
                    متاحف وثقافة
                </h3>

                <p>
                    تعرّف على تاريخ وثقافة المدينة
                </p>

            </div>

        </a>


        <!-- طبيعة -->

        <a href="places/nature.php" class="category-card nature">

            <div class="category-icon">
                🌿
            </div>

            <div>

                <h3>
                    أماكن طبيعية
                </h3>

                <p>
                    اكتشف جمال الطبيعة في المدينة
                </p>

            </div>

        </a>

    </div>

</section>


<!-- ================= FOOTER ================= -->

<footer class="footer" id="about">


    <div class="footer-column brand-column">

        <div class="footer-logo">
            أثَر المدينة
        </div>

        <p>
            المدينة .. تاريخ يُروى.
        </p>

    </div>


    <div class="footer-column">

        <h4>
            معلومات
        </h4>

        <a href="#">
            عن المشروع
        </a>

        <a href="#">
            تواصل معنا
        </a>

        <a href="#">
            سياسة الخصوصية
        </a>

    </div>

<div class="footer-column">

        <h4>
            روابط سريعة
        </h4>

        <a href="#">
            الرئيسية
        </a>

        <a href="#places">
            المعالم
        </a>

        <a href="#planner">
            منظم الزيارة
        </a>

        <a href="map.php">
            الخريطة
        </a>

        <a href="add_place.php" class="add-place-link">
        + إضافة معلم
    </a>

    </div>
    


    <div class="footer-column">

        

        <div class="social">

            

        </div>

        <small>
            جميع الحقوق محفوظة ©️ 2025 - أثر المدينة
        </small>

    </div>


</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="map_common.js"></script>
<script>
(function () {
    var landmarks = <?= json_encode($landmarks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    if (window.L && window.AtharMap && document.getElementById('home-map')) {
        AtharMap.create('home-map', landmarks, { scrollWheelZoom: false });
    }

    /* القوائم المنسدلة: السهم يفتح/يغلق القائمة، والضغط على الاسم ينتقل للصفحة */
    var dds = document.querySelectorAll('.nav-dd');
    dds.forEach(function (dd) {
        dd.querySelector('.dd-arrow').addEventListener('click', function (e) {
            e.stopPropagation();
            var wasOpen = dd.classList.contains('open');
            dds.forEach(function (o) { o.classList.remove('open'); });
            if (!wasOpen) { dd.classList.add('open'); }
        });
    });
    document.addEventListener('click', function () {
        dds.forEach(function (o) { o.classList.remove('open'); });
    });
})();
</script>
<?php include __DIR__ . "/translate_widget.php"; ?>
</body>
</html>