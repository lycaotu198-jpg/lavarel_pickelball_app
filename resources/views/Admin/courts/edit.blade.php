@extends('Admin.layout.app')

@section('title', 'Sửa sân')

@section('content')

<h3 class="mb-4">✏️ Sửa thông tin sân Pickleball</h3>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>⚠️ {{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card shadow-sm">
<div class="card-body">

<form method="POST"
      action="{{ route('admin.courts.update', $court) }}"
      enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="row">

{{-- TÊN --}}
<div class="col-md-6 mb-3">
    <label class="fw-bold">Tên sân</label>
    <input type="text" name="name"
           class="form-control"
           value="{{ old('name', $court->name) }}"
           required>
</div>

{{-- GIÁ --}}
<div class="col-md-6 mb-3">
    <label class="fw-bold">Giá / giờ</label>
    <input type="number" name="price_per_hour"
           class="form-control"
           value="{{ old('price_per_hour', $court->price_per_hour) }}"
           required>
</div>

{{-- ADDRESS --}}
<div class="col-md-12 mb-3">
    <label class="fw-bold">📍 Địa chỉ sân</label>
    <textarea name="address"
              class="form-control"
              rows="2"
              required>{{ old('address', $court->address) }}</textarea>
</div>

{{-- LOCATION --}}
<div class="col-md-12 mb-3">
    <label class="fw-bold">Vị trí (mô tả ngắn)</label>
    <input type="text"
           name="location"
           class="form-control"
           value="{{ old('location', $court->location) }}">
</div>

{{-- STATUS --}}
<div class="col-md-6 mb-3">
    <label class="fw-bold">Trạng thái</label>
    <select name="status" class="form-select">
        <option value="available" {{ $court->status=='available'?'selected':'' }}>🟢 Hoạt động</option>
        <option value="maintenance" {{ $court->status=='maintenance'?'selected':'' }}>🟡 Bảo trì</option>
        <option value="inactive" {{ $court->status=='inactive'?'selected':'' }}>🔴 Dừng</option>
    </select>
</div>

{{-- LAT --}}
<div class="col-md-3 mb-3">
    <label class="fw-bold">Latitude</label>
    <input type="text"
           id="latitude"
           name="latitude"
           class="form-control"
           value="{{ old('latitude', $court->latitude) }}"
           required>
</div>

{{-- LNG --}}
<div class="col-md-3 mb-3">
    <label class="fw-bold">Longitude</label>
    <input type="text"
           id="longitude"
           name="longitude"
           class="form-control"
           value="{{ old('longitude', $court->longitude) }}"
           required>
</div>

{{-- MAP --}}
<div class="col-md-12 mb-3">
    <div id="map" style="height:350px;border-radius:8px;"></div>
    <small class="text-muted">👉 Click bản đồ để đổi vị trí sân</small>
</div>

{{-- IMAGE --}}
<div class="col-md-12 mb-3">
    <label class="fw-bold">Đổi hình ảnh</label>
    <input type="file" name="image" class="form-control">
</div>

</div>

<div class="text-end">
    <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary">
        ⬅ Quay lại
    </a>
    <button class="btn btn-success">💾 Cập nhật</button>
</div>

</form>
</div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    const lat = parseFloat(latInput.value);
    const lng = parseFloat(lngInput.value);

    const map = L.map('map').setView([lat, lng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    let marker = L.marker([lat, lng]).addTo(map);

    map.on('click', function (e) {
        latInput.value = e.latlng.lat.toFixed(6);
        lngInput.value = e.latlng.lng.toFixed(6);
        marker.setLatLng(e.latlng);
    });
});
</script>
@endpush
