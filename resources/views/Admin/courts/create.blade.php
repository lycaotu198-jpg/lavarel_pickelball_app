@extends('Admin.layout.app')

@section('title', 'Thêm sân mới')

@section('content')
<h3 class="mb-4">➕ Thêm sân Pickleball</h3>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>⚠️ {{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card">
<div class="card-body">

<form method="POST" action="{{ route('admin.courts.store') }}" enctype="multipart/form-data">
@csrf

<div class="row">
    <div class="col-md-6 mb-3">
        <label>Tên sân</label>
        <input type="text" name="name" class="form-control" required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Vị trí (mô tả)</label>
        <input type="text" name="location" class="form-control">
    </div>

    <div class="col-md-12 mb-3">
        <label>📍 Địa chỉ</label>
        <textarea name="address" class="form-control" rows="2" required></textarea>
    </div>

    <div class="col-md-6 mb-3">
        <label>Giá / giờ</label>
        <input type="number" name="price_per_hour" class="form-control" required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Trạng thái</label>
        <select name="status" class="form-select">
            <option value="available">Hoạt động</option>
            <option value="maintenance">Bảo trì</option>
            <option value="inactive">Dừng</option>
        </select>
    </div>

    {{-- LAT LNG --}}
    <div class="col-md-6 mb-3">
        <label>Latitude</label>
        <input type="text" name="latitude" id="latitude" class="form-control" required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Longitude</label>
        <input type="text" name="longitude" id="longitude" class="form-control" required>
    </div>

    <div class="col-md-12 mb-3">
        <div id="map" style="height:300px;"></div>
        <small class="text-muted">👉 Click vào bản đồ để lấy tọa độ</small>
    </div>

    <div class="col-md-12 mb-3">
        <label>Hình ảnh</label>
        <input type="file" name="image" class="form-control">
    </div>
    </div>

        <div class="text-end">
            <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary">⬅ Quay lại</a>
            <button class="btn btn-success">💾 Lưu</button>
        </div>

    </form>

@endsection
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const map = L.map('map').setView([21.0285, 105.8542], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        let marker;

        map.on('click', function(e) {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });

    });
</script>
@endpush
