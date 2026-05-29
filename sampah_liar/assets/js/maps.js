// SiPAL - Maps JavaScript (Leaflet.js)

// Inisialisasi peta untuk form laporan (user)
function initMapPicker(latInputId, lngInputId, mapId) {
    const defaultLat = -6.2088;
    const defaultLng = 106.8456;

    const map = L.map(mapId).setView([defaultLat, defaultLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const marker = L.marker([defaultLat, defaultLng], {
        draggable: true
    }).addTo(map);

    // Update input saat marker dipindah
    marker.on('dragend', function(e) {
        const pos = e.target.getLatLng();
        document.getElementById(latInputId).value = pos.lat.toFixed(8);
        document.getElementById(lngInputId).value = pos.lng.toFixed(8);
    });

    // Update marker saat peta diklik
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById(latInputId).value = e.latlng.lat.toFixed(8);
        document.getElementById(lngInputId).value = e.latlng.lng.toFixed(8);
    });

    // GPS otomatis
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            map.setView([lat, lng], 16);
            marker.setLatLng([lat, lng]);
            document.getElementById(latInputId).value = lat.toFixed(8);
            document.getElementById(lngInputId).value = lng.toFixed(8);
        }, function() {
            console.log('GPS tidak tersedia, menggunakan lokasi default');
        });
    }

    return map;
}

// Inisialisasi peta untuk detail laporan (tampilan statis)
function initDetailMap(lat, lng, mapId, status) {
    const map = L.map(mapId).setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const colors = {
        'menunggu': '#f59e0b',
        'diproses': '#3b82f6',
        'selesai': '#10b981',
        'ditolak': '#ef4444'
    };

    const icon = L.divIcon({
        className: 'custom-marker',
        html: `<div style="
            width: 36px; height: 36px; 
            background: ${colors[status] || '#10b981'}; 
            border-radius: 50% 50% 50% 0; 
            transform: rotate(-45deg); 
            border: 3px solid white; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            display: flex; align-items: center; justify-content: center;
        "><span style="transform: rotate(45deg); font-size: 16px;">📍</span></div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 36]
    });

    L.marker([lat, lng], { icon: icon }).addTo(map);

    return map;
}

// Inisialisasi peta admin dengan semua marker
function initAdminMap(laporanData, mapId) {
    const map = L.map(mapId).setView([-6.2088, 106.8456], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    const colors = {
        'menunggu': '#f59e0b',
        'diproses': '#3b82f6',
        'selesai': '#10b981',
        'ditolak': '#ef4444'
    };

    const statusLabels = {
        'menunggu': 'Menunggu',
        'diproses': 'Diproses',
        'selesai': 'Selesai',
        'ditolak': 'Ditolak'
    };

    laporanData.forEach(item => {
        const color = colors[item.status] || '#10b981';

        const icon = L.divIcon({
            className: 'custom-marker',
            html: `<div style="
                width: 32px; height: 32px; 
                background: ${color}; 
                border-radius: 50% 50% 50% 0; 
                transform: rotate(-45deg); 
                border: 3px solid white; 
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
                display: flex; align-items: center; justify-content: center;
            "><span style="transform: rotate(45deg); font-size: 14px;">🗑️</span></div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 32]
        });

        const popupContent = `
            <div style="min-width: 200px; font-family: 'Poppins', sans-serif;">
                <h4 style="margin: 0 0 8px; font-size: 14px; font-weight: 700;">${item.judul}</h4>
                <p style="margin: 0 0 8px; font-size: 12px; color: #64748b;">${item.alamat_lokasi}</p>
                <span style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; background: ${color}22; color: ${color};">
                    ${statusLabels[item.status]}
                </span>
                <br><br>
                <a href="detail.php?id=${item.id}" style="display: inline-block; padding: 6px 14px; background: #10b981; color: white; text-decoration: none; border-radius: 8px; font-size: 12px; font-weight: 600;">Lihat Detail</a>
            </div>
        `;

        L.marker([item.latitude, item.longitude], { icon: icon })
            .addTo(map)
            .bindPopup(popupContent);
    });

    // Legend
    const legend = L.control({ position: 'bottomright' });
    legend.onAdd = function() {
        const div = L.DomUtil.create('div', 'legend');
        div.style.cssText = 'background: white; padding: 12px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); font-size: 12px;';
        div.innerHTML = '<h5 style="margin: 0 0 8px; font-weight: 700;">Status</h5>';

        Object.keys(colors).forEach(status => {
            div.innerHTML += `
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <span style="width: 12px; height: 12px; background: ${colors[status]}; border-radius: 50%; display: inline-block;"></span>
                    <span>${statusLabels[status]}</span>
                </div>
            `;
        });
        return div;
    };
    legend.addTo(map);

    return map;
}

// Peta mini untuk dashboard user
function initMiniMap(laporanData, mapId) {
    if (laporanData.length === 0) {
        document.getElementById(mapId).innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #94a3b8; font-size: 14px;">Belum ada laporan</div>';
        return;
    }

    const first = laporanData[0];
    const map = L.map(mapId).setView([first.latitude, first.longitude], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19
    }).addTo(map);

    const colors = {
        'menunggu': '#f59e0b',
        'diproses': '#3b82f6',
        'selesai': '#10b981',
        'ditolak': '#ef4444'
    };

    laporanData.forEach(item => {
        L.circleMarker([item.latitude, item.longitude], {
            radius: 8,
            fillColor: colors[item.status] || '#10b981',
            color: '#fff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(map).bindPopup(`<b>${item.judul}</b>`);
    });

    return map;
}
