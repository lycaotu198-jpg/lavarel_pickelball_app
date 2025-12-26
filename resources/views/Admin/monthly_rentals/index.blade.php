@extends('Admin.layout.app')

@section('content')
<h3>Danh sách thuê sân theo tháng</h3>

<a href="{{ route('admin.monthly-rentals.create') }}" class="btn btn-primary mb-3">Thêm mới</a>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Khách hàng</th>
            <th>Sân</th>
            <th>Thời gian</th>
            <th>Ngày trong tuần</th>
            <th>Giá tháng</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rentals as $rental)
        <tr>
            <td>{{ $rental->id }}</td>
            <td>{{ $rental->user->name }}</td>
            <td>{{ $rental->court->name }}</td>
            <td>{{ $rental->start_date }} - {{ $rental->end_date }}</td>
            <td>{{ implode(', ', array_map(fn($d) => ['mon'=>'T2','tue'=>'T3','wed'=>'T4','thu'=>'T5','fri'=>'T6','sat'=>'T7','sun'=>'CN'][$d] ?? $d, $rental->week_days)) }}</td>
            <td>{{ number_format($rental->monthly_price) }} VND</td>
            <td>{{ $rental->status }}</td>
            <td>
                <!-- Add actions if needed -->
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
