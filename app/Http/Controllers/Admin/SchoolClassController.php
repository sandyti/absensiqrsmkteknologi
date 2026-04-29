<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:school_classes,nama'],
            'tingkat' => ['required', 'string', 'max:50'],
        ]);

        Kelas::create($data);

        return back()->with('status', 'Kelas berhasil ditambahkan.');
    }
}
