<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Mapel::orderBy('nama_mapel')->get();

        return view('admin.subjects.index', compact('subjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_mapel' => ['required', 'string', 'max:255'],
        ]);

        Mapel::create($data);

        return back()->with('status', 'Mapel berhasil ditambahkan.');
    }

    public function edit(Mapel $subject): View
    {
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Mapel $subject): RedirectResponse
    {
        $data = $request->validate([
            'nama_mapel' => ['required', 'string', 'max:255'],
        ]);

        $subject->update($data);

        return redirect()->route('subjects.index')->with('status', 'Mapel diperbarui.');
    }

    public function destroy(Mapel $subject): RedirectResponse
    {
        $subject->delete();

        return back()->with('status', 'Mapel dihapus.');
    }
}
