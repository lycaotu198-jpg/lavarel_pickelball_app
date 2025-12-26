@extends('Admin.layout.app')

@section('title', 'Quản lý sân')

@section('content')

<div class="d-flex justify-content-between mb-4">
    <h3>🏓 Danh sách sân Pickleball</h3>
    <a href="{{ route('admin.courts.create') }}" class="btn btn-success">
        ➕ Thêm sân
    </a>
</div>

<div class="row">
    @foreach ($courts as $court)

        <div class="col-md-4 mb-4">
            <div class="card shadow border-{{ $court->status_color }}">

                {{-- Hình ảnh sân --}}
                <img
                    src="{{ $court->image_url }}"
                    class="card-img-top"
                    alt="Hình ảnh sân"
                    style="height: 200px; object-fit: cover;"
                >

                <div class="card-body text-center">

                    <h5 class="fw-bold">{{ $court->name }}</h5>

                    <p class="text-muted mb-1">
                        📍 {{ $court->location ?? 'Chưa cập nhật' }}
                    </p>

                    <p class="fw-bold mb-2">
                        💰 {{ number_format($court->price_per_hour) }} đ / giờ
                    </p>

                    <span class="badge bg-{{ $court->status_color }} mb-3">
                        {{ $court->status_label }}
                    </span>

                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <a href="{{ route('admin.courts.edit', $court) }}"
                           class="btn btn-sm btn-primary">
                            ✏️ Sửa
                        </a>

                        <a href="{{ route('admin.courts.delete', $court) }}"
                           class="btn btn-sm btn-danger">
                            🗑️ Xóa
                        </a>
                    </div>

                </div>
            </div>
        </div>

    @endforeach
</div>

@endsection
