@extends('user.layout.app')

@section('title', 'Bản đồ sân')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Map Controls -->
        <div class="col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-search me-2"></i>Tìm kiếm sân
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="searchInput" class="form-label">Tên sân</label>
                        <input type="text" class="form-control" id="searchInput" placeholder="Nhập tên sân...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Trạng thái</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="filterAvailable" checked>
                            <label class="form-check-label" for="filterAvailable">
                                <span class="badge bg-success me-1">●</span> Hoạt động
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="filterMaintenance">
                            <label class="form-check-label" for="filterMaintenance">
                                <span class="badge bg-warning me-1">●</span> Bảo trì
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="filterInactive">
                            <label class="form-check-label" for="filterInactive">
                                <span class="badge bg-danger me-1">●</span> Dừng hoạt động
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100" id="resetFilters">
                            <i class="fas fa-undo me-1"></i>Đặt lại bộ lọc
                        </button>
                    </div>
                </div>
            </div>

            <!-- Legend -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Chú thích</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="badge bg-success me-2">●</span>
                        <small>Sân hoạt động</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-warning me-2">●</span>
                        <small>Sân bảo trì</small>
                    </div>
                    <div class="mb-2">
                        <span class="badge bg-danger me-2">●</span>
                        <small>Sân dừng hoạt động</small>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <i class="fas fa-clock text-info me-2"></i>
                        <small>Giờ trống hôm nay</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Bản đồ vị trí các sân</h4>
                </div>
                <div class="card-body">
                    <div id="map" style="height: 600px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .leaflet-popup-content-wrapper {
        border-radius: 8px;
    }

    .court-popup {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.4;
    }

    .court-popup h5 {
        margin-bottom: 8px;
        color: #2c3e50;
    }

    .court-popup p {
        margin-bottom: 4px;
        font-size: 14px;
    }

    .court-popup .hours {
        color: #27ae60;
        font-weight: 500;
    }

    .custom-court-marker {
        border: none !important;
        background: none !important;
    }

    .custom-court-marker div {
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
</style>
@endpush

@push('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize map centered on Vietnam
    const map = L.map('map').setView([21.0285, 105.8542], 10);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Court data from PHP
    const allCourts = @json($courts);
    let visibleCourts = [...allCourts];
    let markers = [];

    // Create custom icons for different court statuses
    const createIcon = (status) => {
        let color;
        switch(status) {
            case 'available':
                color = '#27ae60';
                break;
            case 'maintenance':
                color = '#f39c12';
                break;
            case 'inactive':
                color = '#e74c3c';
                break;
            default:
                color = '#95a5a6';
        }

        return L.divIcon({
            className: 'custom-court-marker',
            html: `<div style="background-color: ${color}; border: 3px solid white; border-radius: 50%; width: 24px; height: 24px; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"></div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
    };

    // Function to add markers to map
    function addMarkersToMap(courts) {
        // Clear existing markers
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];

    courts.forEach(court => {
        if (court.latitude && court.longitude) {
                const marker = L.marker([court.latitude, court.longitude], {
                    icon: createIcon(court.status)
                }).addTo(map);

                // Create enhanced popup content
            const popupContent = `
                <div class="court-popup">
                    <h5>${court.name}</h5>
                    <p><i class="fas fa-map-marker-alt me-1"></i>${court.address || court.location}</p>
                        <p><i class="fas fa-dollar-sign me-1"></i><strong>${court.price_per_hour?.toLocaleString('vi-VN')} VND/giờ</strong></p>
                    <p class="hours"><i class="fas fa-clock me-1"></i>${court.available_hours}</p>
                        <div class="mt-2">
                            <a href="${window.location.origin}/user/courts/${court.id}" class="btn btn-sm btn-primary me-1">
                                <i class="fas fa-eye me-1"></i>Xem chi tiết
                            </a>
                            ${court.status === 'available' ?
                                `<a href="${window.location.origin}/user/courts/${court.id}/book" class="btn btn-sm btn-success">
                                    <i class="fas fa-calendar-plus me-1"></i>Đặt sân
                                </a>` : ''
                            }
                        </div>
                </div>
            `;

            marker.bindPopup(popupContent);
                markers.push(marker);
        }
    });
    }

    // Function to filter courts
    function filterCourts() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const showAvailable = document.getElementById('filterAvailable').checked;
        const showMaintenance = document.getElementById('filterMaintenance').checked;
        const showInactive = document.getElementById('filterInactive').checked;

        visibleCourts = allCourts.filter(court => {
            // Text search
            const matchesSearch = court.name.toLowerCase().includes(searchTerm) ||
                                (court.address || court.location).toLowerCase().includes(searchTerm);

            // Status filter
            let matchesStatus = false;
            if (court.status === 'available' && showAvailable) matchesStatus = true;
            if (court.status === 'maintenance' && showMaintenance) matchesStatus = true;
            if (court.status === 'inactive' && showInactive) matchesStatus = true;

            return matchesSearch && matchesStatus;
        });

        addMarkersToMap(visibleCourts);
        fitMapToVisibleCourts();
    }

    // Function to fit map to visible courts
    function fitMapToVisibleCourts() {
        if (visibleCourts.length > 0) {
            const validCourts = visibleCourts.filter(court => court.latitude && court.longitude);
            if (validCourts.length > 0) {
                const group = new L.featureGroup(validCourts.map(court =>
                    L.marker([court.latitude, court.longitude])
                ));

                map.fitBounds(group.getBounds().pad(0.1));
            }
        }
    }

    // Event listeners for filters
    document.getElementById('searchInput').addEventListener('input', filterCourts);
    document.getElementById('filterAvailable').addEventListener('change', filterCourts);
    document.getElementById('filterMaintenance').addEventListener('change', filterCourts);
    document.getElementById('filterInactive').addEventListener('change', filterCourts);

    // Reset filters
    document.getElementById('resetFilters').addEventListener('click', function() {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterAvailable').checked = true;
        document.getElementById('filterMaintenance').checked = false;
        document.getElementById('filterInactive').checked = false;
        filterCourts();
    });

    // Initial setup
    addMarkersToMap(visibleCourts);
    fitMapToVisibleCourts();
});
</script>
@endpush
