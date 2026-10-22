<?php

namespace App\Http\Controllers\Sob;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SobZone;

class ZoneController extends Controller
{
    public function index()
    {
        $zones = SobZone::withCount(['assignments', 'sweepHeaders'])
            ->orderBy('branch_code')
            ->orderBy('zone_code')
            ->paginate(20);

        return view('sob.zones.index', compact('zones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_code' => 'required|string|max:50',
            'zone_code' => 'required|string|max:50',
            'zone_name' => 'required|string|max:100',
            'zone_type' => 'required|in:GUDANG,DISPLAY,RETUR_BS,TRANSIT,OTHER',
            'description' => 'nullable|string',
        ]);

        SobZone::create([
            'branch_code' => strtoupper($request->branch_code),
            'zone_code' => strtoupper($request->zone_code),
            'zone_name' => $request->zone_name,
            'zone_type' => $request->zone_type,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->route('sob.zones.index')->with('success', 'Area/Zona cabang berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $zone = SobZone::findOrFail($id);
        $request->validate([
            'zone_name' => 'required|string|max:100',
            'zone_type' => 'required|in:GUDANG,DISPLAY,RETUR_BS,TRANSIT,OTHER',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $zone->update([
            'zone_name' => $request->zone_name,
            'zone_type' => $request->zone_type,
            'description' => $request->description,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('sob.zones.index')->with('success', 'Area/Zona cabang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $zone = SobZone::findOrFail($id);
        $zone->delete();
        return redirect()->route('sob.zones.index')->with('success', 'Area/Zona cabang berhasil dihapus.');
    }
}
