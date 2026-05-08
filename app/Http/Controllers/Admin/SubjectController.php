<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Mapel::with('kelas')->orderBy('nama_mapel')->get();
        $classes = Kelas::orderBy('nama')->get();

        return view('admin.subjects.index', compact('subjects', 'classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_mapel' => ['required', 'string', 'max:255'],
            'id_kelas' => ['nullable', 'array'],
            'id_kelas.*' => ['exists:kelas,id_kelas'],
            'jam_pelajaran' => ['nullable', 'string', 'max:100'],
        ]);

        $subject = Mapel::create([
            'nama_mapel' => $data['nama_mapel'],
            'jam_pelajaran' => $data['jam_pelajaran'] ?? null,
        ]);
        $subject->kelas()->sync($data['id_kelas'] ?? []);

        return back()->with('status', 'Mapel berhasil ditambahkan.');
    }

    public function edit(Mapel $subject): View
    {
        $classes = Kelas::orderBy('nama')->get();
        $subject->load('kelas');

        return view('admin.subjects.edit', compact('subject', 'classes'));
    }

    public function update(Request $request, Mapel $subject): RedirectResponse
    {
        $data = $request->validate([
            'nama_mapel' => ['required', 'string', 'max:255'],
            'id_kelas' => ['nullable', 'array'],
            'id_kelas.*' => ['exists:kelas,id_kelas'],
            'jam_pelajaran' => ['nullable', 'string', 'max:100'],
        ]);

        $subject->update([
            'nama_mapel' => $data['nama_mapel'],
            'jam_pelajaran' => $data['jam_pelajaran'] ?? null,
        ]);
        $subject->kelas()->sync($data['id_kelas'] ?? []);

        return redirect()->route('subjects.index')->with('status', 'Mapel diperbarui.');
    }

    public function destroy(Mapel $subject): RedirectResponse
    {
        $subject->delete();

        return back()->with('status', 'Mapel dihapus.');
    }
}
