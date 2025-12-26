@extends('Admin.layout.app')

@section('title', 'Xóa sân')

@section('content')
<h3 class="mb-4 text-danger">⚠️ Xác nhận xóa sân</h3>

<p>Bạn có chắc chắn muốn xóa sân <strong>{{ $court->name }}</strong>?</p>

<form method="POST" action="{{ route('admin.courts.destroy', $court->id) }}">
    @csrf
    @method('DELETE')

    <button class="btn btn-danger">🗑 Xóa</button>
    <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary">Hủy</a>
</form>
@endsection
