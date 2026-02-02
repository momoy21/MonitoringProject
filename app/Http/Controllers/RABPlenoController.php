<?php

namespace App\Http\Controllers;

use App\Models\RABPleno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RABPlenoController extends Controller
{
    public function index(Request $request)
    {
        $query = RABPleno::select('rab_pleno.*', 'master_konsumen.nama_konsumen')
            ->leftJoin('master_konsumen', 'rab_pleno.idkonsumen', '=', 'master_konsumen.id');

        // Pencarian responsif
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('rab_pleno.nopengajuan', 'LIKE', "%{$search}%")
                  ->orWhere('namaproject', 'LIKE', "%{$search}%")
                  ->orWhere('master_konsumen.nama_konsumen', 'LIKE', "%{$search}%");
            });
        }

        $dataPleno = $query->orderBy('tglinput', 'desc')->paginate(10);
        return view('rabpleno.index', compact('dataPleno'));
    }

    public function create()
    {
        $konsumens = DB::table('master_konsumen')->get();
        return view('rabpleno.create', compact('konsumens'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            // Generate Nomor Pengajuan: YYYYMM + Urutan
            $prefix = Carbon::now()->format('Ym');
            $last = RABPleno::where('nopengajuan', 'LIKE', $prefix . '%')->orderBy('nopengajuan', 'desc')->first();
            $num = $last ? intval(substr($last->nopengajuan, 6)) + 1 : 1;
            $noPengajuan = $prefix . str_pad($num, 2, '0', STR_PAD_LEFT);

            $data = $request->all();
            $data['nopengajuan'] = $noPengajuan;
            $data['tglinput'] = Carbon::now();

            RABPleno::create($data);
            DB::commit();
            return redirect()->route('rabpleno.index')->with('success', 'Data disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $item = RABPleno::where('nopengajuan', $id)->firstOrFail();
        $konsumens = DB::table('master_konsumen')->get();
        return view('rabpleno.edit', compact('item', 'konsumens'));
    }

    public function update(Request $request, $id)
    {
        $item = RABPleno::where('nopengajuan', $id)->firstOrFail();
        $data = $request->all();

        if ($request->hasFile('hasilupload')) {
            $file = $request->file('hasilupload');
            $filename = 'RAB_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/rab_pleno'), $filename);
            $data['hasilupload'] = $filename;
        }

        $item->update($data);
        return redirect()->route('rabpleno.index')->with('success', 'Data diperbarui');
    }
}