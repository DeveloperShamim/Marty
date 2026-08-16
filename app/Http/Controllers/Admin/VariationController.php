<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductAttributeType;
use App\Models\ProductAttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VariationController extends Controller
{
    public function index()
    {
        $attributeTypes = ProductAttributeType::with('values')->orderBy('position')->orderBy('name')->get();
        return view('admin.variations.index', compact('attributeTypes'));
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $name = trim($request->input('name'));
        $slug = Str::slug($name);

        ProductAttributeType::updateOrCreate(
            ['slug' => $slug],
            [
                'name'      => $name,
                'is_active' => true,
            ]
        );

        return redirect()->back()->with('status', 'Variation Attribute Type created successfully.');
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

        return redirect()->back()->with('status', 'Variation Attribute Type updated.');
    }

    public function destroyType(ProductAttributeType $type)
    {
        $type->delete();
        return redirect()->back()->with('status', 'Variation Attribute Type deleted.');
    }

    public function storeValue(Request $request, ProductAttributeType $type)
    {
        $request->validate([
            'value' => ['required', 'string', 'max:100'],
        ]);

        $val = trim($request->input('value'));

        ProductAttributeValue::firstOrCreate([
            'product_attribute_type_id' => $type->id,
            'value'                     => $val,
        ]);

        return redirect()->back()->with('status', "Option '{$val}' added to {$type->name}.");
    }

    public function destroyValue(ProductAttributeValue $value)
    {
        $value->delete();
        return redirect()->back()->with('status', 'Preset value deleted.');
    }
}
