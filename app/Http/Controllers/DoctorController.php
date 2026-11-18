<?php

namespace App\Http\Controllers;

use App\Models\Medcyto;
use App\Models\OrderDetail;
use App\Models\OrderHeader;
use App\Models\RegimenHeader;
use App\Models\Route;
use App\Models\Solution;
use App\Models\Timing;
use App\Models\Unit;
use App\Models\Ward;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        // แสดงเฉพาะคำสั่งใช้ยาตามวันที่ที่เลือก (ค่าเริ่มต้น = วันที่ปัจจุบัน)
        $selectedDate = $request->input('date') ?: today()->format('Y-m-d');

        $query = OrderHeader::with(['details.medication', 'details.unit', 'details.solution', 'ward'])
            ->whereDate('or_date', $selectedDate)
            ->orderByDesc('or_date')
            ->orderByDesc('or_id');

        if ($request->filled('search')) {
            $kw = trim($request->input('search'));
            $query->where(function ($q) use ($kw) {
                $q->where('or_hn', 'like', "%{$kw}%")
                  ->orWhere('or_patname', 'like', "%{$kw}%")
                  ->orWhere('or_id', 'like', "%{$kw}%")
                  ->orWhere('or_reg_code', 'like', "%{$kw}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'pending') {
                $query->where('or_confirm', 0);
            } elseif ($request->input('status') === 'confirmed') {
                $query->where('or_confirm', 1);
            }
        }

        $orders = $query->paginate(15)->withQueryString();

        $stats = [
            'total'     => OrderHeader::whereDate('or_date', $selectedDate)->count(),
            'pending'   => OrderHeader::whereDate('or_date', $selectedDate)->where('or_confirm', 0)->count(),
            'confirmed' => OrderHeader::whereDate('or_date', $selectedDate)->where('or_confirm', 1)->count(),
            'today'     => OrderHeader::whereDate('or_date', today())->count(),
        ];

        return view('doctor.index', compact('orders', 'stats', 'selectedDate'));
    }

    public function create()
    {
        $regimens = RegimenHeader::where('rh_used', 1)
            ->with(['details.medication', 'details.unit', 'details.solution', 'details.route', 'details.timing'])
            ->orderBy('rh_code')
            ->get();

        $wards     = Ward::orderBy('wd_code')->get();
        $meds      = Medcyto::orderBy('dg_code')->get()->keyBy('dg_code');
        $solutions = Solution::orderBy('so_code')->get();
        $routes    = Route::orderBy('rt_code')->get();
        $timings   = Timing::orderBy('tm_code')->get();
        $units     = Unit::orderBy('un_code')->get();

        // Prepare JSON structure for front-end dynamic calculation
        $regimensJson = $regimens->map(function ($reg) {
            return [
                'rh_id'   => $reg->rh_id,
                'rh_code' => $reg->rh_code,
                'items'   => $reg->details->values()->map(function ($d, $idx) {
                    return [
                        'rd_orderno'    => (int) ($d->rd_orderno ?: ($idx + 1)),
                        'rd_medcode'    => $d->rd_medcode,
                        'dg_name'       => optional($d->medication)->dg_name ?? $d->rd_medcode,
                        'dg_conc'       => (float) (optional($d->medication)->dg_conc ?? 1),
                        'dg_precaution' => optional($d->medication)->dg_precaution ?? '',
                        'rd_meddose'    => (float) $d->rd_meddose,
                        'rd_medunit'    => $d->rd_medunit,
                        'un_unit'       => optional($d->unit)->un_unit ?? 'mg/m2',
                        'rd_sol'        => $d->rd_sol,
                        'so_name'       => optional($d->solution)->so_name ?? $d->rd_sol,
                        'rd_solvol'     => (float) $d->rd_solvol,
                        'rd_medroute'   => $d->rd_medroute,
                        'rt_name'       => optional($d->route)->rt_name ?? $d->rd_medroute,
                        'rd_medtime'    => $d->rd_medtime,
                        'tm_time'       => optional($d->timing)->tm_time ?? $d->rd_medtime,
                        'rd_start'      => (int) ($d->rd_start ?: 1),
                        'rd_duration'   => (int) ($d->rd_duration ?: 1),
                    ];
                })->values(),
            ];
        })->keyBy('rh_id');

        return view('doctor.create', compact(
            'regimens',
            'regimensJson',
            'wards',
            'meds',
            'solutions',
            'routes',
            'timings',
            'units'
        ));
    }

    /**
     * ตรวจสอบประวัติการได้รับยาเคมีบำบัดและคำนวณ Cycle ถัดไปอัตโนมัติตาม HN และสูตรยา
     */
    public function patientHistory(Request $request)
    {
        $hn = trim((string) $request->query('hn', ''));
        $regCode = trim((string) $request->query('reg_code', ''));

        if ($hn === '') {
            return response()->json([
                'found'      => false,
                'next_cycle' => 1,
            ]);
        }

        $orders = OrderHeader::where('or_hn', $hn)
            ->orderByDesc('or_date')
            ->orderByDesc('or_id')
            ->get([
                'or_id', 'or_hn', 'or_patname', 'or_age', 'or_sex',
                'or_wt', 'or_ht', 'or_scr', 'or_bsa', 'or_gfr',
                'or_ward', 'or_reg_code', 'or_cycle', 'or_date',
                'or_adjust_percent', 'or_adjust_reason',
            ]);

        if ($orders->isEmpty()) {
            return response()->json([
                'found'      => false,
                'hn'         => $hn,
                'next_cycle' => 1,
                'message'    => 'ไม่พบประวัติการรับยาเคมีบำบัดเดิม (ผู้ป่วยรายใหม่ -> เริ่ม Cycle #1)',
            ]);
        }

        $latest = $orders->first();

        // คำนวณ Cycle ถัดไปแยกตามแต่ละสูตรยาที่ผู้ป่วยเคยได้รับ
        $cyclesByRegimen = $orders->groupBy(fn ($o) => strtoupper((string) $o->or_reg_code))
            ->map(function ($group) {
                $maxCycle = (int) $group->max('or_cycle');
                $latestInReg = $group->first();
                return [
                    'reg_code'   => $latestInReg->or_reg_code,
                    'last_cycle' => $maxCycle,
                    'next_cycle' => $maxCycle + 1,
                    'last_date'  => Carbon::parse($latestInReg->or_date)->format('d/m/Y'),
                    'count'      => $group->count(),
                ];
            });

        $targetRegKey = strtoupper($regCode);
        if ($targetRegKey !== '' && $targetRegKey !== 'CUSTOM' && $cyclesByRegimen->has($targetRegKey)) {
            $regInfo = $cyclesByRegimen->get($targetRegKey);
            $nextCycle = $regInfo['next_cycle'];
            $lastCycle = $regInfo['last_cycle'];
            $matchedRegCode = $regInfo['reg_code'];
            $lastDate = $regInfo['last_date'];
        } elseif ($targetRegKey !== '' && $targetRegKey !== 'CUSTOM' && !$cyclesByRegimen->has($targetRegKey)) {
            // เลือกสูตรยาใหม่ที่ผู้ป่วยยังไม่เคยได้รับมาก่อน -> เริ่ม Cycle 1 สำหรับสูตรนี้
            $nextCycle = 1;
            $lastCycle = 0;
            $matchedRegCode = $regCode;
            $lastDate = null;
        } else {
            // ยังไม่ได้เลือกสูตรยา -> อ้างอิงจากคำสั่งล่าสุดของผู้ป่วย
            $maxLatestRegCycle = (int) $orders->where('or_reg_code', $latest->or_reg_code)->max('or_cycle');
            $nextCycle = max(1, $maxLatestRegCycle) + 1;
            $lastCycle = $maxLatestRegCycle;
            $matchedRegCode = $latest->or_reg_code;
            $lastDate = Carbon::parse($latest->or_date)->format('d/m/Y');
        }

        return response()->json([
            'found'             => true,
            'hn'                => $latest->or_hn,
            'patient'           => [
                'or_patname' => $latest->or_patname,
                'or_age'     => $latest->or_age,
                'or_sex'     => $latest->or_sex,
                'or_wt'      => (float) $latest->or_wt,
                'or_ht'      => (float) $latest->or_ht,
                'or_scr'     => (float) $latest->or_scr,
                'or_ward'    => $latest->or_ward,
                'last_reg'   => $latest->or_reg_code,
                'last_cycle' => (int) $latest->or_cycle,
                'last_date'  => Carbon::parse($latest->or_date)->format('d/m/Y'),
            ],
            'matched_reg_code'  => $matchedRegCode,
            'last_cycle'        => $lastCycle,
            'next_cycle'        => $nextCycle,
            'last_date'         => $lastDate,
            'cycles_by_regimen' => $cyclesByRegimen->values(),
            'history'           => $orders->take(5)->map(fn ($o) => [
                'or_id'       => $o->or_id,
                'or_date'     => Carbon::parse($o->or_date)->format('d/m/Y'),
                'or_reg_code' => $o->or_reg_code,
                'or_cycle'    => (int) $o->or_cycle,
            ])->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'or_hn'             => ['required', 'string', 'max:10'],
            'or_patname'        => ['required', 'string', 'max:250'],
            'or_age'            => ['required', 'integer', 'min:1', 'max:120'],
            'or_sex'            => ['required', 'in:M,F'],
            'or_ward'           => ['required', 'string', 'max:4'],
            'or_date'           => ['required', 'date'],
            'or_cycle'          => ['required', 'integer', 'min:1', 'max:99'],
            'or_wt'             => ['required', 'numeric', 'min:1', 'max:300'],
            'or_ht'             => ['required', 'numeric', 'min:30', 'max:250'],
            'or_scr'            => ['required', 'numeric', 'min:0.1', 'max:30'],
            'or_bsa'            => ['required', 'numeric', 'min:0.1', 'max:5'],
            'or_gfr'            => ['required', 'numeric', 'min:1', 'max:400'],
            'or_reg_code'       => ['required', 'string', 'max:30'],
            'or_adjust_percent' => ['nullable', 'numeric', 'min:0', 'max:90'],
            'or_adjust_reason'  => ['nullable', 'string', 'max:1000'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.orderno'   => ['nullable', 'integer', 'min:1', 'max:99'],
            'items.*.medcode'   => ['required', 'string', 'max:8'],
            'items.*.std_dose'  => ['required', 'numeric', 'min:0'],
            'items.*.reduce_pct'=> ['nullable', 'numeric', 'min:0', 'max:90'],
            'items.*.meddose'   => ['required', 'numeric', 'min:0.01'],
            'items.*.medunit'   => ['required', 'string', 'max:2'],
            'items.*.soln'      => ['required', 'string', 'max:8'],
            'items.*.solvol'    => ['required', 'numeric', 'min:0'],
            'items.*.medroute'  => ['required', 'string', 'max:2'],
            'items.*.medtime'   => ['required', 'string', 'max:3'],
            'items.*.start_day' => ['required', 'integer', 'min:1'],
            'items.*.duration'  => ['required', 'integer', 'min:1', 'max:30'],
        ], [
            'or_hn.required'      => 'กรุณาระบุ HN ผู้ป่วย',
            'or_patname.required' => 'กรุณาระบุชื่อ-สกุลผู้ป่วย',
            'items.required'      => 'กรุณาเลือก Regimen และตรวจสอบรายการยาอย่างน้อย 1 รายการ',
        ]);

        // Check if any reduction was applied
        $headerReducePct = (float) ($validated['or_adjust_percent'] ?? 0);
        $maxItemReducePct = collect($validated['items'])->max(fn ($i) => (float) ($i['reduce_pct'] ?? 0));
        $effectiveReducePct = max($headerReducePct, $maxItemReducePct);

        if ($effectiveReducePct > 0 && empty(trim($validated['or_adjust_reason'] ?? ''))) {
            return back()
                ->withErrors(['or_adjust_reason' => 'กรุณาระบุเหตุผลในการปรับลดขนาดยา (เนื่องจากมีการปรับลดขนาดยา ' . number_format($effectiveReducePct, 0) . '%)'])
                ->withInput();
        }

        // Generate unique or_id (max 20 chars)
        $prefix = 'OR' . now()->format('ymdHis');
        $orId = $prefix . rand(10, 99);
        while (OrderHeader::where('or_id', $orId)->exists()) {
            $orId = $prefix . rand(10, 99);
        }

        $medsMap = Medcyto::whereIn('dg_code', collect($validated['items'])->pluck('medcode'))
            ->get()
            ->keyBy('dg_code');

        DB::transaction(function () use ($validated, $orId, $effectiveReducePct, $medsMap) {
            OrderHeader::create([
                'or_id'             => $orId,
                'or_guid'           => (string) Str::uuid(),
                'or_hn'             => $validated['or_hn'],
                'or_patname'        => $validated['or_patname'],
                'or_age'            => (int) $validated['or_age'],
                'or_sex'            => $validated['or_sex'],
                'or_wt'             => (float) $validated['or_wt'],
                'or_ht'             => (float) $validated['or_ht'],
                'or_bsa'            => (float) $validated['or_bsa'],
                'or_scr'            => (float) $validated['or_scr'],
                'or_gfr'            => (float) $validated['or_gfr'],
                'or_reg_code'       => $validated['or_reg_code'],
                'or_cycle'          => (int) $validated['or_cycle'],
                'or_date'           => $validated['or_date'],
                'or_prnflag'        => 1,
                'or_chkflag'        => 0,
                'or_prepflag'       => 0,
                'or_sendflag'       => 0,
                'or_ward'           => $validated['or_ward'],
                'or_daily'          => 0,
                'or_hold'           => 0,
                'or_confirm'        => 0,
                'or_adjust_percent' => $effectiveReducePct,
                'or_adjust_reason'  => $validated['or_adjust_reason'] ?? null,
                'or_doctor_id'      => Auth::id(),
                'or_doctor_name'    => Auth::user()->name,
            ]);

            $baseDate = Carbon::parse($validated['or_date']);

            foreach (array_values($validated['items']) as $idx => $item) {
                $med = $medsMap->get($item['medcode']);
                $conc = ($med && $med->dg_conc > 0) ? (float) $med->dg_conc : 1.0;

                $finalDose = round((float) $item['meddose'], 2);
                $solVol    = round((float) $item['solvol'], 2);
                $workingVol = round($finalDose / $conc, 2);
                $totalVol   = round($solVol + $workingVol, 2);

                $startDayOffset = max(0, ((int) $item['start_day']) - 1);
                $durationDays   = max(1, (int) $item['duration']);

                $orStart = $baseDate->copy()->addDays($startDayOffset);
                $orStop  = $orStart->copy()->addDays($durationDays - 1);

                $reducePct = (float) ($item['reduce_pct'] ?? 0);
                $stdDose   = round((float) $item['std_dose'], 2);
                $rawReduced = round($stdDose * (1 - $reducePct / 100), 2);
                $bandDiffPct = $rawReduced > 0 ? round((($finalDose - $rawReduced) / $rawReduced) * 100, 1) : 0;

                $noteText = $reducePct > 0
                    ? "ลด {$reducePct}% (จาก " . number_format($stdDose, 2) . " -> " . number_format($rawReduced, 2) . " mg)"
                    : "คำนวณ " . number_format($stdDose, 2) . " mg";

                if (abs($finalDose - $rawReduced) >= 0.01) {
                    $sign = $bandDiffPct > 0 ? '+' : '';
                    $noteText .= " | Banded: " . number_format($finalDose, 2) . " mg ({$sign}{$bandDiffPct}%)";
                }

                OrderDetail::create([
                    'or_id'         => $orId,
                    'or_medcode'    => $item['medcode'],
                    'or_std_dose'   => round((float) $item['std_dose'], 2),
                    'or_reduce_pct' => $reducePct,
                    'or_meddose'    => $finalDose,
                    'or_medunit'    => '01', // Final dose is always in mg (01)
                    'or_soln'       => $item['soln'],
                    'or_medroute'   => substr($item['medroute'], 0, 2),
                    'or_solvol'     => $solVol,
                    'or_medtime'    => $item['medtime'],
                    'or_hold'       => 0,
                    'or_discont'    => 0,
                    'or_orderno'    => (int) ($item['orderno'] ?? ($idx + 1)),
                    'or_start'      => $orStart->format('Y-m-d'),
                    'or_stop'       => $orStop->format('Y-m-d'),
                    'or_duration'   => $durationDays,
                    'or_working'    => $workingVol,
                    'or_totalvol'   => $totalVol,
                    'or_addition'   => '00',
                    'or_text'       => $noteText,
                    'or_cf'         => 0,
                ]);
            }
        });

        return redirect()
            ->route('doctor.show', $orId)
            ->with('success', "บันทึกและส่งคำสั่งใช้ยาเคมีบำบัดเลขที่ {$orId} ไปยังห้องเตรียมยาเภสัชกรรมเรียบร้อยแล้ว");
    }

    public function show(string $orId)
    {
        $order = OrderHeader::with([
            'details.medication',
            'details.unit',
            'details.solution',
            'details.route',
            'details.timing',
            'dayRecords.medication',
            'dayRecords.solution',
            'dayRecords.route',
            'dayRecords.timing',
            'ward',
        ])->findOrFail($orId);

        return view('doctor.show', compact('order'));
    }
}
