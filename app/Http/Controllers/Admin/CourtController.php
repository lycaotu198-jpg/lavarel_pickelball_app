<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Court;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CourtController extends Controller
{
    /* =====================
        LIST
    ===================== */
    public function index()
    {
        $courts = Court::latest()->get();
        return view('admin.courts.index', compact('courts'));
    }
    /* =====================
        CREATE
    ===================== */
    public function create()
    {
        return view('admin.courts.create');
    }
    /* =====================
        STORE
    ===================== */
    public function store(Request $request)
    {
        // 1️⃣ Validate
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'location'        => 'nullable|string|max:255',
            'price_per_hour'  => 'required|numeric|min:0',
            'status'          => 'required|in:available,maintenance,inactive',

            // Địa chỉ + tọa độ
            'address'         => 'required|string|max:255',
            'latitude'        => 'required|numeric|between:-90,90',
            'longitude'       => 'required|numeric|between:-180,180',

            // Ảnh
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        // 2️⃣ Upload ảnh
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('courts', 'public');
        }
        // 3️⃣ Lưu DB
        $court = Court::create($data);

        Log::info("Thêm sân mới", ['court_id' => $court->id]);

        return redirect()
            ->route('admin.courts.index')
            ->with('success', '✅ Thêm sân thành công');
    }
    /* =====================
        EDIT
    ===================== */
    public function edit($id)
    {
        $court = Court::findOrFail($id);
        return view('admin.courts.edit', compact('court'));
    }
     public function delete($id)
    {
        $court = Court::findOrFail($id);
        return view('admin.courts.delete', compact('court'));
    }
    /* =====================
        UPDATE
    ===================== */
    public function update(Request $request, $id)
    {
        $court = Court::findOrFail($id);

        // 1️⃣ Validate
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'location'        => 'nullable|string|max:255',
            'price_per_hour'  => 'required|numeric|min:0',
            'status'          => 'required|in:available,maintenance,inactive',

            'address'         => 'required|string|max:255',
            'latitude'        => 'required|numeric|between:-90,90',
            'longitude'       => 'required|numeric|between:-180,180',

            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // 2️⃣ Upload ảnh mới
        if ($request->hasFile('image')) {

            // ❌ Xóa ảnh cũ
            if ($court->image && Storage::disk('public')->exists($court->image)) {
                Storage::disk('public')->delete($court->image);
            }

            // ✅ Lưu ảnh mới
            $data['image'] = $request->file('image')
                ->store('courts', 'public');
        }

        // 3️⃣ Update
        $court->update($data);

        Log::info("Cập nhật sân", ['court_id' => $court->id]);

        return redirect()
            ->route('admin.courts.index')
            ->with('success', '✅ Cập nhật thành công');
    }

    /* =====================
        DELETE
    ===================== */
    public function destroy($id)
    {
        $court = Court::findOrFail($id);

        if ($court->image && Storage::disk('public')->exists($court->image)) {
            Storage::disk('public')->delete($court->image);
        }

        $court->delete();

        return redirect()
            ->route('admin.courts.index')
            ->with('success', '🗑️ Đã xóa sân');
    }
}
