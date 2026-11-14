/*
 * خريطة المعالم المشتركة (الصفحة الرئيسية + صفحة الخريطة)
 * الاستخدام:
 *   const m = AtharMap.create('map-element-id', landmarksArray, { scrollWheelZoom: true });
 *   m.show('');            // إظهار كل المعالم
 *   m.show(categoryKey);   // إظهار تصنيف واحد
 */
(function () {

    var COLORS = {
        'معالمدينية':   '#176555',
        'معالمتاريخية': '#b08a3e',
        'معالمتارخية':  '#b08a3e',
        'متاحفوثقافة':  '#3b6ea5',
        'متاحف':        '#3b6ea5',
        'أماكنطبيعية':  '#6b9a3a'
    };

    function keyOf(cat) {
        return String(cat || '').replace(/\s+/g, '');
    }

    function colorOf(cat) {
        return COLORS[keyOf(cat)] || '#7a8a85';
    }

    function esc(text) {
        if (text === null || text === undefined) { return ''; }
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /* تنسيقات العلامات ونافذة المعلم (تُضاف مرة واحدة) */
    function injectStyles() {
        if (document.getElementById('athar-map-css')) { return; }
        var css = '' +
            '.leaflet-container{direction:ltr;font-family:"Cairo",sans-serif}' +
            '.map-dot-wrap{background:transparent;border:none}' +
            '.map-dot{display:block;width:20px;height:20px;border-radius:50%;border:3px solid #fff;' +
                'box-shadow:0 2px 8px rgba(0,0,0,.45);transition:transform .15s}' +
            '.map-dot-wrap:hover .map-dot{transform:scale(1.3)}' +
            '.athar-popup{width:240px;text-align:right;direction:rtl;font-family:"Cairo",sans-serif}' +
            '.athar-popup img{width:100%;height:130px;object-fit:cover;border-radius:9px;margin-bottom:8px;display:block}' +
            '.athar-popup h3{margin:4px 0;color:#194d44;font-size:16px;font-weight:800;line-height:1.5}' +
            '.athar-popup .popup-cat{display:inline-block;font-size:11px;font-weight:700;color:#fff;' +
                'padding:1px 10px;border-radius:12px;margin-bottom:6px}' +
            '.athar-popup p{color:#687773;font-size:12px;line-height:1.8;margin:4px 0 10px}' +
            '.athar-popup .popup-actions{display:flex;gap:6px;flex-wrap:wrap}' +
            '.athar-popup .popup-btn{display:inline-block;background:#103f36;color:#fff!important;' +
                'text-decoration:none;padding:6px 12px;border-radius:20px;font-size:11px}' +
            '.athar-popup .popup-btn.alt{background:#fff;color:#103f36!important;border:1px solid #103f36}' +
            '.athar-popup .popup-btn:hover{opacity:.85}' +
            '.leaflet-popup-content{margin:12px 14px}' +
            '.leaflet-popup-close-button{left:6px!important;right:auto!important}';
        var style = document.createElement('style');
        style.id = 'athar-map-css';
        style.textContent = css;
        document.head.appendChild(style);
    }

    function popupHtml(p) {
        var html = '<div class="athar-popup">';

        if (p.IMAGE_URL) {
            html += '<img src="' + esc(p.IMAGE_URL) + '" alt="' + esc(p.NAME) +
                    '" onerror="this.style.display=\'none\'">';
        }

        html += '<h3>' + esc(p.NAME) + '</h3>';

        if (p.CATEGORY) {
            html += '<span class="popup-cat" style="background:' + colorOf(p.CATEGORY) + '">' +
                    esc(String(p.CATEGORY).trim()) + '</span>';
        }

        var desc = String(p.DESCRIPTION || '');
        if (desc.length > 130) { desc = desc.slice(0, 130).trim() + '…'; }
        if (desc) { html += '<p>' + esc(desc) + '</p>'; }

        html += '<div class="popup-actions">';
        html += '<a class="popup-btn" href="places/details.php?id=' + encodeURIComponent(p.ID) + '">التفاصيل</a>';

        if (p.MAPS_URL && /^https?:\/\//i.test(p.MAPS_URL)) {
            html += '<a class="popup-btn alt" href="' + esc(p.MAPS_URL) +
                    '" target="_blank" rel="noopener noreferrer">Google Maps</a>';
        }
        html += '</div></div>';

        return html;
    }

    function create(elId, landmarks, opts) {
        opts = opts || {};
        injectStyles();

        var map = L.map(elId, {
            scrollWheelZoom: opts.scrollWheelZoom !== false,
            zoomControl: false
        }).setView([24.4672, 39.6111], 12);

        /* أزرار التكبير أسفل اليمين حتى لا يغطيها صندوق الترجمة أعلى اليسار */
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var group = L.featureGroup().addTo(map);
        var items = [];

        landmarks.forEach(function (p) {
            var lat = parseFloat(p.latitude);
            var lng = parseFloat(p.longitude);
            if (isNaN(lat) || isNaN(lng)) { return; }

            var marker = L.marker([lat, lng], {
                title: p.NAME,
                icon: L.divIcon({
                    className: 'map-dot-wrap',
                    html: '<span class="map-dot" style="background:' + colorOf(p.CATEGORY) + '"></span>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10],
                    popupAnchor: [0, -12]
                })
            });

            marker.bindPopup(popupHtml(p), { maxWidth: 270, autoPanPadding: [30, 30] });
            items.push({ marker: marker, place: p, key: keyOf(p.CATEGORY) });
        });

        /* إظهار المعالم (كلها أو تصنيف واحد) وضبط الخريطة عليها */
        function show(filterKey) {
            group.clearLayers();
            var count = 0;

            items.forEach(function (it) {
                if (!filterKey || it.key === filterKey) {
                    group.addLayer(it.marker);
                    count++;
                }
            });

            if (count > 0) {
                map.fitBounds(group.getBounds().pad(0.15), { maxZoom: 15 });
            }
            return count;
        }

        show('');

        /* الخريطة تُرسم أحيانًا قبل أن يستقر حجم الحاوية، فنعيد حساب الحجم */
        setTimeout(function () { map.invalidateSize(); }, 150);
        window.addEventListener('load', function () { map.invalidateSize(); });
        window.addEventListener('resize', function () { map.invalidateSize(); });

        return { map: map, show: show, items: items };
    }

    window.AtharMap = { create: create, keyOf: keyOf, colorOf: colorOf, esc: esc };

})();
