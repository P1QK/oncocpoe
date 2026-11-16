<?php

namespace App\Http\Controllers;

use App\Models\OncDayRecord;
use App\Models\OrderHeader;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NurseController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date');
        $selectedWard = $request->input('ward');

        $query = OncDayRecord::with([
            'orderHeader.ward',
            'medication',
            'unit',
            'solution',
            'route',
            'timing',
        ])->orderBy('or_daterec')->orderBy('or_id')->orderBy('or_orderno');

        if ($selectedDate) {
            $query->whereDate('or_daterec', $selectedDate);
        }

        if ($selectedWard) {
            $query->whereHas('orderHeader', function ($q) use ($selectedWard) {
                $q->where('or_ward', $selectedWard);
            });
        }

        if ($request->filled('search')) {
            $kw = trim($request->input('search'));
            $query->whereHas('orderHeader', function ($q) use ($kw) {
                $q->where('or_hn', 'like', "%{$kw}%")
                  ->orWhere('or_patname', 'like', "%{$kw}%")
                  ->orWhere('or_id', 'like', "%{$kw}%");
            });
        }

        $dayRecords = $query->paginate(20)->withQueryString();
        $wards = Ward::orderBy('wd_code')->get();

        $stats = [
            'confirmed_orders' => OrderHeader::where('or_confirm', 1)->count(),
            'today_doses'      => OncDayRecord::whereDate('or_daterec', today())->count(),
            'ready_to_admin'   => OncDayRecord::where('or_prep', 1)->count(),
            'delivered'        => OncDayRecord::where('or_send', 1)->count(),
        ];

        return view('nurse.index', compact('dayRecords', 'wards', 'stats', 'selectedDate', 'selectedWard'));
    }

    public function acknowledge(Request $request)
    {
        $request->validate([
            'or_id'      => ['required', 'string'],
            'or_daterec' => ['required', 'date'],
            'or_orderno' => ['required', 'integer'],
        ]);

        DB::table('onc_dayrecord')
            ->where('or_id', $request->input('or_id'))
            ->where('or_daterec', $request->input('or_daterec'))
            ->where('or_orderno', $request->input('or_orderno'))
            ->update([
                'or_send' => 1,
            ]);

        return back()->with('success', 'พยาบาลยืนยันการรับยาและตรวจสอบ 5 Rights เรียบร้อยแล้ว');
    }
}
