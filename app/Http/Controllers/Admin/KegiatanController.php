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
        return view('guru.kegiatan', compact('kegiatans', 'kegiatan'));
    }

    public function detailKegiatan()
    {
        
    }
}
