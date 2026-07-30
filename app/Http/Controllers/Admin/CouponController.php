<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query()->orderByDesc('created_at');

        if ($term = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        return view('admin.coupons.index', [
            'coupons' => $query->paginate(15)->withQueryString(),
            'q'       => $term ?? '',
        ]);
    }

    public function create()
    {
        return view('admin.coupons.form', [
            'coupon' => new Coupon([
                'type'      => 'percentage',
                'is_active' => true,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');
        Coupon::create($data);

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon created.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.form', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validateData($request, $coupon);
        $data['is_active'] = $request->boolean('is_active');
        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('status', 'Coupon deleted.');
    }

    private function validateData(Request $request, ?Coupon $coupon = null): array
    {
        $data = $request->validate([
            'code'             => ['required', 'string', 'max:40', Rule::unique('coupons', 'code')->ignore($coupon)],
            'description'      => ['nullable', 'string', 'max:255'],
            'type'             => ['required', Rule::in(Coupon::TYPES)],
            'value'            => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount'     => ['nullable', 'numeric', 'min:0'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'starts_at'        => ['nullable', 'date'],
            'expires_at'       => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        if ($data['type'] === 'percentage' && (float) $data['value'] > 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'value' => 'Percentage cannot exceed 100.',
            ]);
        }

        return $data;
    }
}
