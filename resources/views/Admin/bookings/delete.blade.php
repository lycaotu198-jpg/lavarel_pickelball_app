@extends('Admin.layout.app')

@section('title', 'Xoá đặt sân')

@section('content')
<h3>🗑️ Xoá đặt sân</h3>

<p>Bạn có chắc muốn xoá đặt sân của <strong>{{ $booking->user->name }}</strong>?</p>

<form method="POST" action="{{ route('admin.bookings.destroy', $booking->id) }}">
@csrf
@method('DELETE')

<button class="btn btn-danger">Xoá</button>
<a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Huỷ</a>
</form>
@endsection
