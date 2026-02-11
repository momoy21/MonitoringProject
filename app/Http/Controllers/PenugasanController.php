<?php

namespace App\Http\Controllers;

use App\Models\Penugasan;
use App\Models\HistoryProyek;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PenugasanImport;

class PenugasanController extends Controller
{
    /* =========================
        UTILITAS BPS
    ========================== */

    private function generateIDPenugasan()
    {
        $today = date('Ymd');
        $last = Penugasan::where('IDPenugasan', 'LIKE', $today.'%')->max('IDPenugasan');
        $urut = $last ? intval(substr($last, -2)) + 1 : 1;
        return $today . str_pad($urut, 2, '0', STR_PAD_LEFT);
    }

    private function generateNoSurat()
    {
        $romawi = ["", "I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII"];
        return "…../SPK/DIR-KIT/".$romawi[date('n')]."/".date('Y');
    }

    /* =========================
        INDEX
    ========================== */

    public function index(Request $request)
    {
        $query = Penugasan::with(['karyawan','proyek']);

        if ($request->filled('search')) {
            $query->where('cost_center','like','%'.$request->search.'%')
                  ->orWhere('NIK','like','%'.$request->search.'%');
        }

        return view('penugasan.index', [
            'penugasan' => $query->get()
        ]);
    }

    /* =========================
        CREATE
    ========================== */

    public function create()
    {
        return view('penugasan.create', [
            'proyek' => HistoryProyek::all(),
            'karyawan' => Karyawan::where('Aktif','Y')->get(),
            'idPenugasan' => $this->generateIDPenugasan(),
            'noSurat' => $this->generateNoSurat(),
        ]);
    }

    /* =========================
        STORE MANUAL
    ========================== */

    public function store(Request $request)
    {
        $request->validate([
            'Costcenter'   => 'required',
            'NIK'          => 'required',
            'Jabatan'      => 'required',
            'Periodeawal'  => 'required|date',
            'Periodeakhir' => 'required|date|after_or_equal:Periodeawal',
            'Bobot'        => 'required|integer|min:0|max:100',
        ]);

        DB::transaction(function () use ($request) {

            $proyek = HistoryProyek::where('cost_center',$request->Costcenter)->first();

            $norut = Penugasan::where('IDPenugasan',$request->IDPenugasan)->max('Norut') + 1;

            Penugasan::create([
                'IDPenugasan'  => $request->IDPenugasan,
                'cost_center'  => $request->Costcenter,
                'Norut'        => $norut,
                'NIK'          => $request->NIK,
                'NoSurat'      => $this->generateNoSurat(),
                'Dokumen_IO'   => $proyek->Dokumen_IO ?? null,
                'Jabatan'      => $request->Jabatan,
                'Periodeawal'  => $request->Periodeawal,
                'Periodeakhir' => $request->Periodeakhir,
                'Bobot'        => $request->Bobot,
                'Status'       => 'A',
                'Keterangan'   => $request->Keterangan,
            ]);
        });

        return redirect()->route('penugasan.index')->with('success','Data tersimpan');
    }

    /* =========================
        UPLOAD EXCEL (BPS FIX)
    ========================== */

    public function uploadExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);
    
        $idPenugasan = $this->generateIDPenugasan();
        $noSurat = $this->generateNoSurat();
    
        Excel::import(new PenugasanImport($idPenugasan, $noSurat), $request->file('file_excel'));
    
        return back()->with('success','Upload berhasil');
    }
    

    /* =========================
        EDIT / UPDATE / DELETE
    ========================== */

public function edit($id)
{
    return view('penugasan.edit', [
        'penugasan' => Penugasan::where('IDPenugasan', $id)->firstOrFail(),
        'proyek'    => HistoryProyek::all(),
        'karyawan'  => Karyawan::where('Aktif','Y')->get()
    ]);
}

// UPDATE
public function update(Request $request, $id)
{
    $request->validate([
        'Jabatan'=>'required',
        'Periodeawal'=>'required|date',
        'Periodeakhir'=>'required|date|after_or_equal:Periodeawal',
        'Bobot'=>'required|integer|min:0|max:100'
    ]);

    $penugasan = Penugasan::where('IDPenugasan', $id)->firstOrFail();
    $penugasan->update([
        'Jabatan'      => $request->Jabatan,
        'Periodeawal'  => $request->Periodeawal,
        'Periodeakhir' => $request->Periodeakhir,
        'Bobot'        => $request->Bobot,
        'Keterangan'   => $request->Keterangan,
    ]);

    return redirect()->route('penugasan.index')->with('success','Data diperbarui');
}

// DESTROY
public function destroy($id)
{
    $penugasan = Penugasan::where('IDPenugasan', $id)->firstOrFail();
    $penugasan->delete();

    return back()->with('success','Data dihapus');
}






    public function downloadTemplate()
    {
        return redirect()->away('https://docs.google.com/spreadsheets/d/1yzuDaqVbGOq2NCANxzvnNAkEVQ-UA1ki/edit?usp=sharing');
    }
}