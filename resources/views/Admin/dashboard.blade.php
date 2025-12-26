@extends('Admin.layout.app')

@section('title','Dashboard')

@section('content')
<h3>📊 Thống kê doanh thu</h3>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card p-3">💰 Hôm nay<br><strong>{{ number_format($todayRevenue) }}đ</strong></div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">📅 Tháng này<br><strong>{{ number_format($monthRevenue) }}đ</strong></div>
    </div>
    <div class="col-md-4">
        <div class="card p-3">🏦 Tổng<br><strong>{{ number_format($totalRevenue) }}đ</strong></div>
    </div>
</div>

<table class="table table-bordered bg-white">
    <thead>
        <tr>
            <th>Ngày</th>
            <th>Doanh thu</th>
        </tr>
    </thead>
    <tbody>
        @foreach($dailyRevenue as $row)
        <tr>
            <td>{{ $row->date }}</td>
            <td>{{ number_format($row->total) }}đ</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
