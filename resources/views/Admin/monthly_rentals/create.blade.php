@extends('Admin.layout.app')

@section('content')
<h3>Thuê sân theo tháng</h3>

@if ($errors->has('time'))
    <div class="alert alert-danger">{{ $errors->first('time') }}</div>
@endif

<form method="POST" action="{{ route('admin.monthly-rentals.store') }}">
    @csrf

    <div class="mb-3">
        <label>Khách hàng</label>
        <select name="user_id" class="form-control">
            @foreach($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label>Sân</label>
        <select name="court_id" class="form-control">
            @foreach($courts as $court)
                <option value="{{ $court->id }}">{{ $court->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="row">
        <div class="col">
            <label>Từ ngày</label>
            <input type="date" name="start_date" class="form-control">
        </div>
        <div class="col">
            <label>Đến ngày</label>
            <input type="date" name="end_date" class="form-control">
        </div>
    </div>

    <div class="mt-3">
        <label>Ngày trong tuần</label><br>
        @foreach(['mon'=>'Thứ 2','tue'=>'Thứ 3','wed'=>'Thứ 4','thu'=>'Thứ 5','fri'=>'Thứ 6','sat'=>'Thứ 7','sun'=>'Chủ nhật'] as $k => $v)
            <label class="me-3">
                <input type="checkbox" name="week_days[]" value="{{ $k }}"> {{ $v }}
            </label>
        @endforeach
    </div>

    <div class="row mt-3">
        <div class="col">
            <label>Giờ bắt đầu</label>
            <input type="time" name="start_time" class="form-control">
        </div>
        <div class="col">
            <label>Giờ kết thúc</label>
            <input type="time" name="end_time" class="form-control">
        </div>
    </div>

    <div class="mt-3">
        <label>Giá thuê tháng</label>
        <input type="number" name="monthly_price" class="form-control">
    </div>

    <button class="btn btn-success mt-3">Xác nhận thuê tháng</button>
</form>
@endsection
