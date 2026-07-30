<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    public function index()
    {
        return view('admin.features.index', [
            'features' => Feature::orderBy('position')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.features.form', ['feature' => new Feature(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');
        Feature::create($data);

        return redirect()->route('admin.features.index')->with('status', 'Feature created.');
    }

    public function edit(Feature $feature)
    {
        return view('admin.features.form', compact('feature'));
    }

    public function update(Request $request, Feature $feature)
    {
        $data = $this->validateData($request);
        $data['is_active'] = $request->boolean('is_active');
        $feature->update($data);

        return redirect()->route('admin.features.index')->with('status', 'Feature updated.');
    }

    public function destroy(Feature $feature)
    {
        $feature->delete();

        return redirect()->route('admin.features.index')->with('status', 'Feature deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title'    => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:160'],
            'icon'     => ['nullable', 'string', 'max:1000'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
