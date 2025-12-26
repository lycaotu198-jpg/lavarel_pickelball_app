<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'price_per_hour',
        'status',
        'image', // 👉 thêm cột ảnh
        'latitude',
        'longitude',
        'address',
    ];

    /* =====================
       ACCESSORS
    ===================== */

    /**
     * Lấy URL hình ảnh sân
     * Nếu chưa có ảnh → dùng ảnh mặc định
     */
    public function getImageUrlAttribute()
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/default-court.jpg');
    }

    /**
     * Lấy nhãn trạng thái (Hiển thị)
     */
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'available'   => 'Hoạt động',
            'maintenance' => 'Bảo trì',
            'inactive'    => 'Dừng hoạt động',
            default       => 'Không xác định',
        };
    }

    /**
     * Lấy màu badge theo trạng thái
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'available'   => 'success',
            'maintenance' => 'warning',
            'inactive'    => 'danger',
            default       => 'secondary',
        };
    }

    /* =====================
       RELATIONSHIPS
    ===================== */

    // 1 Court → nhiều Booking
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
