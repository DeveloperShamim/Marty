<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductAttributeType;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VariationController extends Controller
{
    public function index(Request $request)
    {
        $attributeTypes = ProductAttributeType::with('values')->orderBy('position')->orderBy('name')->get();
        $selectedId = (int) ($request->query('selected') ?? session('selected_type_id'));
        $selectedType = $attributeTypes->firstWhere('id', $selectedId) ?? $attributeTypes->first();

        return view('admin.variations.index', compact('attributeTypes', 'selectedType'));
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $name = trim($request->input('name'));
        $slug = Str::slug($name);

        $type = ProductAttributeType::updateOrCreate(
            ['slug' => $slug],
            [
                'name'      => $name,
                'is_active' => true,
            ]
        );

        return redirect()->route('admin.variations.index', ['selected' => $type->id])
            ->with('status', "Attribute category '{$type->name}' created successfully.");
    }

    public function updateType(Request $request, ProductAttributeType $type)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $name = trim($request->input('name'));

        $type->update([
            'name'      => $name,
            'slug'      => Str::slug($name),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.variations.index', ['selected' => $type->id])
            ->with('status', "Attribute category '{$type->name}' updated.");
    }

    public function destroyType(ProductAttributeType $type)
    {
        $name = $type->name;
        $type->delete();
        return redirect()->route('admin.variations.index')
            ->with('status', "Attribute category '{$name}' deleted.");
    }

    public function storeValue(Request $request, ProductAttributeType $type)
    {
        $request->validate([
            'value' => ['required', 'string', 'max:500'],
        ]);

        $rawInput = trim($request->input('value'));
        $values = array_filter(array_map('trim', explode(',', $rawInput)));

        $addedCount = 0;
        foreach ($values as $val) {
            if ($val !== '') {
                ProductAttributeValue::firstOrCreate([
                    'product_attribute_type_id' => $type->id,
                    'value'                     => substr($val, 0, 100),
                ]);
                $addedCount++;
            }
        }

        $msg = $addedCount > 1 
            ? "{$addedCount} preset options added to {$type->name}." 
            : "Option '{$rawInput}' added to {$type->name}.";

        return redirect()->route('admin.variations.index', ['selected' => $type->id])
            ->with('status', $msg);
    }

    /** Exact shade for a colour option (empty = back to the built-in shade for that name). */
    public function updateValue(Request $request, ProductAttributeValue $value)
    {
        $data = $request->validate([
            'color_hex' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $value->update(['color_hex' => isset($data['color_hex']) ? strtolower($data['color_hex']) : null]);

        return redirect()->route('admin.variations.index', ['selected' => $value->product_attribute_type_id])
            ->with('status', $value->color_hex ? "Colour for '{$value->value}' saved." : "Colour for '{$value->value}' reset.");
    }

        public function destroyValue(ProductAttributeValue $value)
    {
        $typeId = $value->product_attribute_type_id;
        $valName = $value->value;
        $value->delete();

        return redirect()->route('admin.variations.index', ['selected' => $typeId])
            ->with('status', "Option '{$valName}' deleted.");
    }
}
