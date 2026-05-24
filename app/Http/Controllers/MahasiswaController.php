<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MahasiswaController extends Controller
{
    public function index(): View
    {
        $mahasiswas = Mahasiswa::query()
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('project.mahasiswa_nfc', compact('mahasiswas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nim' => ['required', 'string', 'max:30', 'unique:mahasiswas,nim'],
            'serial_number_nfc' => ['required', 'string', 'max:100', 'unique:mahasiswas,serial_number_nfc'],
        ]);

        $validated['serial_number_nfc'] = strtoupper(trim($validated['serial_number_nfc']));

        Mahasiswa::create($validated);

        return back()->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function update(Request $request, Mahasiswa $mahasiswa): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'nim' => ['required', 'string', 'max:30', Rule::unique('mahasiswas', 'nim')->ignore($mahasiswa->id)],
            'serial_number_nfc' => [
                'required',
                'string',
                'max:100',
                Rule::unique('mahasiswas', 'serial_number_nfc')->ignore($mahasiswa->id),
            ],
        ]);

        $validated['serial_number_nfc'] = strtoupper(trim($validated['serial_number_nfc']));

        $mahasiswa->update($validated);

        return back()->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy(Mahasiswa $mahasiswa): RedirectResponse
    {
        $mahasiswa->delete();

        return back()->with('success', 'Data mahasiswa berhasil dihapus (soft-deleted).');
    }
}
