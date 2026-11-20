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
        // แสดงเฉพาะยาที่ให้ตรงวัน (ค่าเริ่มต้นคือวันนี้) ไม่ดึงของวันถัดไปมาปน
        $selectedDate = $request->input('date') ?: today()->format('Y-m-d');
        $selectedWard = $request->input('ward');

        $query = OncDayRecord::with([
            'orderHeader.ward',
            'orderHeader.supportiveMeds',
            'medication',
            'unit',
            'solution',
            'route',
            'timing',
        ])
            ->whereDate('or_daterec', $selectedDate)
            ->orderBy('or_id')
            ->orderBy('or_orderno');

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

        $dayRecords = $query->get();

        // จัดกลุ่มตามผู้ป่วย (Order / HN) เพื่อแสดง HN ชื่อ-นามสกุลผู้ป่วยเป็นหัวข้อหลัก และรายการยาย่อยอยู่ด้านใต้
        $patientsGroup = $dayRecords->groupBy('or_id')->map(function ($items, $orId) {
            $first = $items->first();
            $header = $first->orderHeader;

            return (object) [
                'or_id'             => $orId,
                'or_hn'             => optional($header)->or_hn ?? '-',
                'or_patname'        => optional($header)->or_patname ?? '-',
                'or_age'            => optional($header)->or_age,
                'or_sex'            => optional($header)->or_sex,
                'or_wt'             => optional($header)->or_wt,
                'or_ht'             => optional($header)->or_ht,
                'or_bsa'            => optional($header)->or_bsa,
                'or_gfr'            => optional($header)->or_gfr,
                'or_reg_code'       => optional($header)->or_reg_code ?? '-',
                'or_cycle'          => optional($header)->or_cycle ?? 1,
                'or_doctor_name'    => optional($header)->or_doctor_name,
                'or_adjust_percent' => optional($header)->or_adjust_percent ?? 0,
                'or_adjust_reason'  => optional($header)->or_adjust_reason,
                'ward_code'         => optional($header)->or_ward ?? '-',
                'ward_name'         => optional(optional($header)->ward)->wd_name ?? optional($header)->or_ward ?? '-',
                'doses_count'       => $items->count(),
                'prep_count'        => $items->where('or_prep', 1)->count(),
                'send_count'        => $items->where('or_send', 1)->count(),
                'all_sent'          => $items->every(fn ($item) => (int) $item->or_send === 1),
                'pre_meds'          => optional($header)->supportiveMeds
                    ? $header->supportiveMeds->whereIn('category', ['pre_med_antiemetic', 'pre_med_hypersensitivity'])->values()
                    : collect(),
                'pre_hydrations'    => optional($header)->supportiveMeds
                    ? $header->supportiveMeds->where('category', 'pre_hydration')->values()
                    : collect(),
                'items'             => $items->sortBy('or_orderno')->values(),
            ];
        })->values();

        $wards = Ward::orderBy('wd_code')->get();

        $stats = [
            'patients_today'   => $patientsGroup->count(),
            'today_doses'      => OncDayRecord::whereDate('or_daterec', $selectedDate)->count(),
            'ready_to_admin'   => OncDayRecord::whereDate('or_daterec', $selectedDate)->where('or_prep', 1)->count(),
            'delivered'        => OncDayRecord::whereDate('or_daterec', $selectedDate)->where('or_send', 1)->count(),
        ];

        return view('nurse.index', compact(
            'dayRecords',
            'patientsGroup',
            'wards',
            'stats',
            'selectedDate',
            'selectedWard'
        ));
    }

    public function acknowledge(Request $request)
    {
        $request->validate([
            'or_id'      => ['required', 'string'],
            'or_daterec' => ['required', 'date'],
            'or_orderno' => ['nullable', 'integer'],
        ]);

        $query = DB::table('onc_dayrecord')
            ->where('or_id', $request->input('or_id'))
            ->whereDate('or_daterec', $request->input('or_daterec'));

        if ($request->filled('or_orderno')) {
            $query->where('or_orderno', $request->input('or_orderno'));
        }

        $query->update([
            'or_send' => 1,
        ]);

        return back()->with('success', 'พยาบาลยืนยันการรับยาและตรวจสอบ 5 Rights เรียบร้อยแล้ว');
    }
}
