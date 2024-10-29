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
    public function kegiatan($id, $id_siswa)
    {
        $loginGuru = Auth::guard('guru')->user()->id_guru;
        $siswa = Siswa::find($id_siswa);

        if (!$siswa || !$siswa->id_pembimbing) {
            return back()->withErrors(['access' => 'Siswa tidak ditemukan atau tidak memiliki pembimbing']);
        }
        if ($siswa->id_pembimbing != $id) {
            return back()->withErrors(['access' => 'Pembimbing tidak sesuai']);
        }
        $pembimbing = Pembimbing::find($id);
        if (!$pembimbing || $pembimbing->id_guru !== $loginGuru) {
            return back()->withErrors(['access' => 'Akses anda di tolak, siswa ini tidak dibimbing oleh anda']);
        }
        $kegiatans = kegiatan::where('id_siswa', $id_siswa)->get();
        $kegiatan = kegiatan::where('id_siswa', $id_siswa)->first();
        $id_pembimbing = $id;

        return view('guru.kegiatan', compact('id_pembimbing', 'id_siswa', 'kegiatans', 'kegiatan'));
    }

    public function detailKegiatan($id, $id_siswa, $id_kegiatan)
    {
        $loginGuru = Auth::guard('guru')->user()->id_guru;
        $siswa = Siswa::find($id_siswa);

        if (!$siswa || !$siswa->id_pembimbing) {
            return back()->withErrors(['access' => 'Siswa tidak ditemukan atau tidak memiliki pembimbing']);
        }
        if ($siswa->id_pembimbing != $id) {
            return back()->withErrors(['access' => 'Pembimbing tidak sesuai']);
        }
        $pembimbing = Pembimbing::find($id);
        if (!$pembimbing || $pembimbing->id_guru !== $loginGuru) {
            return back()->withErrors(['access' => 'Akses anda di tolak, siswa ini tidak dibimbing oleh anda']);
        }


        $kegiatan = kegiatan::where('id_siswa', $id_siswa)
            ->where('id_kegiatan', $id_kegiatan)
            ->first();

        if (!$kegiatan) {
            return back()->withErrors(['access' => 'Kegiatan tidak tersedia']);
        }

        return view('guru.detail', compact('id', 'kegiatan'));
    }

    public function cariKegiatan(Request $request, $id, $id_siswa)
    {
        $request->validate([
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date|after_or_equal:tanggal_awal',
        ]);

        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');

        $loginGuru = Auth::guard('guru')->user()->id_guru;
        $siswa = Siswa::find($id_siswa);

        if (!$siswa || !$siswa->id_pembimbing) {
            return back()->withErrors(['access' => 'Siswa tidak ditemukan atau tidak memiliki pembimbing']);
        }
        if ($siswa->id_pembimbing != $id) {
            return back()->withErrors(['access' => 'Pembimbing tidak sesuai']);
        }
        $pembimbing = Pembimbing::find($id);
        if (!$pembimbing || $pembimbing->id_guru !== $loginGuru) {
            return back()->withErrors(['access' => 'Akses anda di tolak, siswa ini tidak dibimbing oleh anda']);
        }

        $kegiatans = kegiatan::where('id_siswa', $id_siswa)
            ->whereBetween('tanggal_kegiatan', [$tanggalAwal, $tanggalAkhir])
            ->get();

        $kegiatan = kegiatan::where('id_siswa', $id_siswa)
            ->whereBetween('tanggal_kegiatan', [$tanggalAwal, $tanggalAkhir])
            ->first();

        $id_pembimbing = $id;

        return view('guru.kegiatan', compact('kegiatans', 'kegiatan', 'id_pembimbing', 'id_siswa', 'tanggalAwal', 'tanggalAkhir'));
    }
}
