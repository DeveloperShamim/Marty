<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blacklist;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlacklistController extends Controller
{
    public function index(Request $request)
    {
        $type   = $request->input('type', 'all');
        $search = trim((string) $request->input('q'));

        $query = Blacklist::latest();

        if ($type !== 'all' && in_array($type, ['phone', 'ip', 'email'], true)) {
            $query->where('type', $type);
        }

        if ($search !== '') {
            $query->where('value', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%");
        }

        $items = $query->paginate(20)->withQueryString();

        return view('admin.blacklist.index', [
            'items'       => $items,
            'type'        => $type,
            'search'      => $search,
            'totalCount'  => Blacklist::count(),
            'phoneCount'  => Blacklist::where('type', 'phone')->count(),
            'ipCount'     => Blacklist::where('type', 'ip')->count(),
            'emailCount'  => Blacklist::where('type', 'email')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'   => ['required', Rule::in(['phone', 'ip', 'email'])],
            'value'  => ['required', 'string', 'max:180'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        Blacklist::add($validated['type'], $validated['value'], $validated['reason'] ?? null);

        return back()->with('status', "Added {$validated['type']} '{$validated['value']}' to fraud blacklist.");
    }

    public function destroy(Blacklist $blacklist)
    {
        $value = $blacklist->value;
        $blacklist->delete();

        return back()->with('status', "Removed '{$value}' from blacklist.");
    }
}
