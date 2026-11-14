<?php

include("database/db.php");
include(__DIR__ . "/helpers.php");

/* ==========================================
   جلب المعالم من قاعدة البيانات
   ========================================== */

$sql = "
    SELECT
        ID,
        NAME,
        CATEGORY,
        DESCRIPTION,
        IMAGE_URL,
        MAPS_URL,
        latitude,
        longitude
    FROM madinah_landmarks
    WHERE latitude IS NOT NULL
      AND longitude IS NOT NULL
";


$result = mysqli_query($conn, $sql);


$landmarks = [];


if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        // تصحيح مسار الصورة ليطابق الملف الفعلي داخل مجلد images
        $row["IMAGE_URL"] = image_url(resolve_image($row["IMAGE_URL"], __DIR__));

        $landmarks[] = $row;

    }

}

?>

<!DOCTYPE html>

<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        أثر المدينة | خريطة المعالم
    </title>


    <!-- Google Font -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <!-- Leaflet CSS -->

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    >


    <style>

        * {
            box-sizing: border-box;
        }


        html,
        body {
            margin: 0;
            padding: 0;

            width: 100%;
            height: 100%;

            font-family: "Cairo", sans-serif;

            background: #f8f6f0;
        }


        body {
            direction: rtl;

            display: flex;

            flex-direction: column;
        }


        /* ================= HEADER ================= */

        .map-header {

            height: 72px;

            background: #103f36;

            color: white;

            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 0 6%;

            box-shadow:
                0 3px 12px rgba(0,0,0,.15);

            position: relative;

            z-index: 1000;
        }


        .map-title {

            display: flex;

            flex-direction: column;
        }


        .map-title-ar {

            font-size: 20px;

            font-weight: 800;

            line-height: 1.2;
        }


        .map-title-en {

            font-family: Arial, sans-serif;

            font-size: 8px;

            letter-spacing: 2px;

            opacity: .75;

            margin-top: 4px;
        }


        .back-button {

            text-decoration: none;

            color: white;

            border: 1px solid #719a8e;

            padding: 7px 18px;

            border-radius: 25px;

            font-size: 12px;

            transition: .3s;
        }


        .back-button:hover {

            background: #176555;

        }


        /* ================= MAP ================= */

        #map {

            width: 100%;

            flex: 1;

            height: calc(100vh - 72px);
            min-height: 400px;
            position: relative;
            z-index: 1;
        }


        /* ================= COUNT ================= */

        .count-box {

            position: absolute;

            top: 90px;

            right: 20px;

            z-index: 999;

            background: white;

            color: #183c35;

            padding: 9px 16px;

            border-radius: 10px;

            box-shadow:
                0 3px 12px rgba(0,0,0,.18);

            font-size: 13px;

            font-weight: 600;
        }


        /* ================= POPUP ================= */

        .popup {

            width: 250px;

            text-align: right;

            direction: rtl;
        }


        .popup img {

            width: 100%;

            height: 140px;

            object-fit: cover;

            border-radius: 9px;

            margin-bottom: 8px;
        }


        .popup h3 {

            margin: 4px 0;

            color: #194d44;

            font-size: 17px;

            font-weight: 800;
        }


        .popup .category {

            color: #a48343;

            background: transparent;

            margin: 0 0 7px;

            padding: 0;

            height: auto;

            min-width: 0;

            font-size: 11px;
        }


        .popup p {

            color: #687773;

            font-size: 11px;

            line-height: 1.8;

            margin: 5px 0 10px;
        }


        .popup a {

            display: inline-block;

            background: #103f36;

            color: white;

            text-decoration: none;

            padding: 7px 11px;

            border-radius: 20px;

            font-size: 10px;

        }


        .popup a:hover {

            background: #176555;

        }


        /* ================= MOBILE ================= */

        @media (max-width: 600px) {

            .map-header {

                height: 65px;

                padding: 0 4%;

            }


            .map-title-ar {

                font-size: 17px;
            }


            .back-button {

                padding: 6px 12px;

                font-size: 10px;
            }


            .count-box {

                top: 78px;

                right: 10px;

                font-size: 11px;

            }

        }

    </style>

</head>


<body>


<!-- ================= HEADER ================= -->

<header class="map-header">


    <div class="map-title">

        <div class="map-title-ar">
            أثَر المدينة
        </div>

        <div class="map-title-en">
            ATHAR AL-MADINA
        </div>

    </div>


    <a
        href="home.php"
        class="back-button"
    >
        العودة للرئيسية
    </a>


</header>


<!-- ================= COUNT ================= -->

<div class="count-box">

    📍 عدد المعالم:
    <?php echo count($landmarks); ?>

</div>


<!-- ================= MAP ================= -->

<div id="map"></div>


<!-- Leaflet JavaScript -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="map_common.js"></script>
<script>
var landmarks = <?= json_encode($landmarks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
AtharMap.create('map', landmarks, { scrollWheelZoom: true });
</script>
<?php include __DIR__ . "/translate_widget.php"; ?>
</body>

</html>