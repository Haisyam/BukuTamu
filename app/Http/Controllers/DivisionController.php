<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Division;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DivisionController extends Controller
{
    /**
     * Display a listing of divisions.
     */
    public function index(): View
    {
        $divisions = Division::withCount('guestLogs')
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.divisions.index', compact('divisions'));
    }

    /**
     * Store a newly created division in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:divisions,name'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama bagian / bidang wajib diisi.',
            'name.unique' => 'Nama bagian / bidang tersebut sudah ada.',
            'name.max' => 'Nama bagian / bidang maksimal 100 karakter.',
        ]);

        Division::create([
            'name' => strip_tags(trim($validated['name'])),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Master bagian / bidang baru berhasil ditambahkan.');
    }

    /**
     * Update the specified division in storage.
     */
    public function update(Request $request, Division $division): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:divisions,name,' . $division->id],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Nama bagian / bidang wajib diisi.',
            'name.unique' => 'Nama bagian / bidang tersebut sudah terdaftar.',
            'name.max' => 'Nama bagian / bidang maksimal 100 karakter.',
        ]);

        $division->update([
            'name' => strip_tags(trim($validated['name'])),
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $division->is_active,
        ]);

        return back()->with('success', 'Data master bagian / bidang berhasil diperbarui.');
    }

    /**
     * Toggle active status.
     */
    public function toggle(Division $division): RedirectResponse
    {
        $division->update([
            'is_active' => !$division->is_active,
        ]);

        $statusText = $division->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Status bagian '{$division->name}' berhasil {$statusText}.");
    }

    /**
     * Remove the specified division from storage.
     */
    public function destroy(Division $division): RedirectResponse
    {
        $division->delete();

        return back()->with('success', 'Master bagian / bidang berhasil dihapus.');
    }
}
