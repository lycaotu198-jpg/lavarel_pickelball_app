@extends('Admin.layout.app')

@section('title', 'Xác nhận xóa người dùng')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3>🗑️ Xóa người dùng</h3>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">⬅️ Quay lại</a>
</div>

<div class="card shadow-sm border-danger">
    <div class="card-body">
        <div class="alert alert-warning">
            <strong>⚠️ Cảnh báo:</strong> Hành động này không thể hoàn tác. Bạn có chắc chắn muốn xóa người dùng này?
        </div>

        <div class="row">
            <div class="col-md-6">
                <h5>Thông tin người dùng:</h5>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>ID:</strong> {{ $user->id }}</li>
                    <li class="list-group-item"><strong>Tên:</strong> {{ $user->name }}</li>
                    <li class="list-group-item"><strong>Email:</strong> {{ $user->email }}</li>
                    <li class="list-group-item"><strong>Vai trò:</strong> {{ $user->role === 'admin' ? 'Admin' : 'Khách hàng' }}</li>
                    <li class="list-group-item"><strong>Số điện thoại:</strong> {{ $user->phone ?? 'N/A' }}</li>
                    <li class="list-group-item"><strong>Ngày tạo:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</li>
                </ul>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">🗑️ Xóa vĩnh viễn</button>
            </form>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Hủy</a>
        </div>
    </div>
</div>

@endsection
