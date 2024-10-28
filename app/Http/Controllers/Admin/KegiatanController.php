<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\kegiatan;
use App\Models\Admin\Pembimbing;
use App\Models\Admin\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KegiatanController extends Controller
{
    public function Kegiatan($id, $id_siswa)
    {
        // ambil data guru yang sedang login
        $loginGuru = Auth::guard('guru')->user()->id_guru;

        // ambil data siswa berdasarkan id_siswa
        $siswa = Siswa::find($id_siswa);

        // jika siswa tidak ditemukan atau id_pembimbing dari siswa tida ada, kembalikan error
        if (!$siswa || !$siswa->id_pembimbing) {
            return back()->withErrors(['access' => 'siswa tidak ditemukan atau tidak memiliki pembimbing.']);
        }

        if ($siswa->id_pembimbing != $id) {
            return back()->withErrors(['access' => 'pembimbing tidak sesuai.']);
        }

        $pembimbing = Pembimbing::find($id);

        if (!$pembimbing || $pembimbing->id_guru !== $loginGuru) {
            return back()->withErrors(['access' => 'akses anda ditolak. siswa ini tidak dibimbing oleh anda.']);
        }

        $kegiatans = kegiatan::where('id_siswa', $id_siswa)->get();
        $kegiatan = kegiatan::where('id_siswa', $id_siswa)->first();
        $id_pembimbing = $id;
        return view('guru.kegiatan', compact('id_pembimbing','kegiatans', 'kegiatan'));
    }

    public function detailKegiatan($id, $id_siswa, $id_kegiatan)
    {

        $loginGuru = Auth::guard('guru')->user()->id_guru;

        $siswa = Siswa::find($id_siswa);

        if (!$siswa || !$siswa->id_pembimbing) {
            return back()->withErrors(['access' => 'siswa tidak ditemukan atau tidak memiliki pembimbing.']);
        }

        if ($siswa->id_pembimbing != $id) {
            return back()->withErrors(['access' => 'pembimbing tidak sesuai.']);
        }

        $pembimbing = Pembimbing::find($id);

        if (!$pembimbing || $pembimbing->id_guru !== $loginGuru) {
            return back()->withErrors(['access' => 'akses anda ditolak. siswa ini tidak dibimbing oleh anda.']);
        }

        $kegiatan = Kegiatan::where('id_kegiatan', $id_kegiatan)
                             ->where('id_siswa', $id_siswa)
                             ->first();
        if (!$kegiatan) {
            return back()->withErrors(['access' => 'kegiatan tidak tersedia']);
        }

        return view('guru.detail_kegiatan', compact('id', 'kegiatan'));
    }

    public function kegiatanSiswa()
    {
        $id_siswa = Auth::guard('siswa')->user()->id_siswa;
        $kegiatans = Kegiatan::where('id_siswa', $id_siswa)->get();
        return view('siswa.kegiatan', compact('kegiatans'));
    }
}
