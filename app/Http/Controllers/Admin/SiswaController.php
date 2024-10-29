<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\kegiatan;
use App\Models\Admin\Pembimbing;
use App\Models\Admin\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function siswa($id)
    {
        $siswas = Siswa::where('id_pembimbing', $id)->get();
        $siswa = Siswa::where('id_pembimbing', $id)->first();
        return view('admin.siswa', compact('siswas', 'siswa', 'id'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($id)
    {
        return view('admin.tambah_siswa', compact('id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'nisn' => 'required|unique:siswa,nisn|digits:10',
            'password' => 'required|min:6',
            'nama_siswa' => 'required',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $foto = null;

        if ($request->hasFile('foto')) {
            $uniqueFile = uniqid() . '_' . $request->file('foto')->getClientOriginalName();

            $request->file('foto')->storeAs('foto_siswa', $uniqueFile, 'public');

            $foto = 'foto_siswa/' . $uniqueFile;
        }

        Siswa::create([
            'id_pembimbing' => $id,
            'nisn' => $request->nisn,
            'password' => Hash::make($request->password),
            'nama_siswa' => $request->nama_siswa,
            'foto' => $foto,
        ]);

        return redirect()->route('admin.pembimbing.siswa', $id)->with('success', 'Data siswa berhasil di tambahkan');
    }


    /**
     * Display the specified resource.
     */
    public function delete($id, $id_siswa)
    {
        $siswa = Siswa::find($id_siswa);

        if ($siswa->foto) {
            $foto = $siswa->foto;

            if (Storage::disk('public')->exists($foto)) {
                Storage::disk('public')->delete($foto);
            }
        }
        $siswa->delete();
        return redirect()->route('admin.pembimbing.siswa', $id)->with('success', 'Data siswa berhasil di hapus');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id, $id_siswa)
    {
        $siswa = Siswa::find($id_siswa);
        return view('admin.edit_siswa', compact('siswa', 'id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, $id_siswa)
    {
        $siswa = Siswa::find($id_siswa);

        $request->validate([
            'nisn' => 'required|digits:10|unique:siswa,nisn,' . $siswa->id_siswa . ',id_siswa',
            'nama_siswa' => 'required',
            'password' => 'nullable|min:6',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $foto = $siswa->foto;
        if ($request->hasFile('foto')) {
            if ($foto) {
                Storage::disk('public')->delete($foto);
            }
            $uniqueFile = uniqid() . '_' . $request->file('foto')->getClientOriginalName();
            $request->file('foto')->storeAs('foto_siswa', $uniqueFile, 'public');
            $foto = 'foto_siswa/' . $uniqueFile;
        }

        $siswa->update([
            'nisn' => $request->nisn,
            'nama_siswa' => $request->nama_siswa,
            'password' => $request->filled('password') ? Hash::make($request->password) : $siswa->password,
            'foto' => $foto,
        ]);

        return redirect()->route('admin.pembimbing.siswa', $id)->with('success', 'Data siswa berhasil di update');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function siswaGuru($id)
    {

        $loginGuru = Auth::guard('guru')->user()->id_guru;
        $pembimbing = Pembimbing::find($id);

        if (!$pembimbing || $pembimbing->id_guru !== $loginGuru) {
            return back()->withErrors(['access' => 'Akses anda di tolak']);
        }

        $siswas = Siswa::where('id_pembimbing', $id)->get();
        $siswa = Siswa::where('id_pembimbing', $id)->first();
        return view('guru.siswa', compact('siswas', 'siswa', 'id'));
    }

    public function dashboard()
    {
        return view('siswa.dashboard');
    }

    public function kegiatanSiswa()
    {
        $id_siswa = Auth::guard('siswa')->user()->id_siswa;
        $kegiatans = kegiatan::where('id_siswa', $id_siswa)->get();
        return view('siswa.kegiatan', compact('kegiatans'));
    }

    public function createKegiatan()
    {
        return view('siswa.tambah_kegiatan');
    }

    public function storeKegiatan(Request $request)
    {
        $id_siswa = Auth::guard('siswa')->user()->id_siswa;

        $request->validate([
            'tanggal_kegiatan' => 'required',
            'nama_kegiatan' => 'required',
            'ringkasan_kegiatan' => 'required',
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $foto = null;

        if ($request->hasFile('foto')) {
            $uniqueFile = uniqid() . '_' . $request->file('foto')->getClientOriginalName();

            $request->file('foto')->storeAs('foto_kegiatan', $uniqueFile, 'public');

            $foto = 'foto_kegiatan/' . $uniqueFile;
        }

        kegiatan::create([
            'id_siswa' => $id_siswa,
            'tanggal_kegiatan' => $request->tanggal_kegiatan,
            'nama_kegiatan' => $request->nama_kegiatan,
            'ringkasan_kegiatan' => $request->ringkasan_kegiatan,
            'foto' => $foto,
        ]);

        return redirect()->route('siswa.kegiatan')->with('success', 'Data kegiatan berhasil di tambahkan');
    }

    public function deleteKegiatan($id_kegiatan)
    {
        $id_siswa = Auth::guard('siswa')->user()->id_siswa;
        $kegiatan = kegiatan::find($id_kegiatan);

        if ($kegiatan->foto) {
            $foto = $kegiatan->foto;

            if (Storage::disk('public')->exists($foto)) {
                Storage::disk('public')->delete($foto);
            }
        }

        $kegiatan->delete();

        return redirect()->route('siswa.kegiatan')->with('success', 'Data Kegiatan berhasil di hapus');
    }

    public function editKegiatan(string $id_kegiatan)
    {
        $siswa = Auth::guard('siswa')->user()->id_siswa;

        $kegiatan = kegiatan::where('id_siswa', $siswa)
            ->where('id_kegiatan', $id_kegiatan)
            ->first();

        if (!$kegiatan) {
            return back()->withErrors(['access' => 'Kegiatan tidak ditemukan atau tidak memiliki akses']);
        }
        return view('siswa.edit_kegiatan', compact('kegiatan', 'siswa', 'id_kegiatan'));
    }

    public function updateKegiatan(Request $request, string $id_kegiatan)
    {
        // $id_siswa = Auth::guard('siswa')->user()->id_siswa;
        $kegiatan = kegiatan::find($id_kegiatan);

        $request->validate([
            'tanggal_kegiatan' => 'required',
            'nama_kegiatan' => 'required',
            'ringkasan_kegiatan' => 'required',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        $foto = $kegiatan->foto;
        if ($request->hasFile('foto')) {
            if ($foto) {
                Storage::disk('public')->delete($foto);
            }
            $uniqueFile = uniqid() . '_' . $request->file('foto')->getClientOriginalName();
            $request->file('foto')->storeAs('foto_kegiatan', $uniqueFile, 'public');
            $foto = 'foto_kegiatan/' . $uniqueFile;
        }

        $kegiatan->update([
            'tanggal_kegiatan' => $request->tanggal_kegiatan,
            'nama_kegiatan' => $request->nama_kegiatan,
            'ringkasan_kegiatan' => $request->ringkasan_kegiatan,
            'foto' => $foto,

        ]);

        return redirect()->route('siswa.kegiatan')->with('success', 'Data anda berhasil di update');
    }

    public function profile()
    {
        $profile = Auth::guard('siswa')->user();
        return view('siswa.profile', compact('profile'));
    }

    public function updateSiswa(Request $request)
    {
        $id_siswa = Auth::guard('siswa')->user()->id_siswa;
        $siswa = Siswa::find($id_siswa);

        $request->validate([
            'nama_siswa' => 'required',
            'password' => 'nullable|min:6',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $foto = $siswa->foto;
        if ($request->hasFile('foto')) {
            if ($foto) {
                Storage::disk('public')->delete($foto);
            }
            $uniqueFile = uniqid() . '_' . $request->file('foto')->getClientOriginalName();
            $request->file('foto')->storeAs('foto_siswa', $uniqueFile, 'public');
            $foto = 'foto_siswa/' . $uniqueFile;
        }

        $siswa->update([
            'nama_siswa' => $request->nama_siswa,
            'password' => $request->filled('password') ? Hash::make($request->password) : $siswa->password,
            'foto' => $foto,
        ]);

        return redirect()->back()->with('success', 'Data siswa berhasil di update');
    }

    public function detailKegiatan($id_kegiatan)
    {
        $id_siswa = Auth::guard('siswa')->user()->id_siswa;
        $kegiatan = kegiatan::find($id_kegiatan);

        $kegiatan = kegiatan::where('id_siswa', $id_siswa)
            ->where('id_kegiatan', $id_kegiatan)
            ->first();

        if (!$kegiatan) {
            return back()->withErrors(['access' => 'Kegiatan tidak tersedia']);
        }

        return view('siswa.detail', compact('kegiatan'));
    }

    public function logout(Request $request)
    {
        Auth::guard('siswa')->logout();
        $request->session()->invalidate();
        $request->session()->regeneratetoken();
        return redirect()->route('siswa.login');
    }
}
