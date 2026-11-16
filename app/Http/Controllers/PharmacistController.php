<?php

namespace App\Http\Controllers;

use App\Models\OncDayRecord;
use App\Models\OrderHeader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacistController extends Controller
{
    public function index(Request $request)
    {
        $query = OrderHeader::with(['details.medication', 'details.solution', 'ward'])
            ->orderBy('or_confirm', 'asc') // แสดงคำสั่งที่ยังไม่รับคำสั่งขึ้นก่อน
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

        $stats = $this->getStats();
        $latestToken = $this->getStateToken();

        return view('pharmacist.index', compact('orders', 'stats', 'latestToken'));
    }

    /**
     * AJAX Polling Endpoint สำหรับตรวจสอบคำสั่งใหม่แบบ Real-time
     */
    public function poll(Request $request)
    {
        return response()->json([
            'token'   => $this->getStateToken(),
            'stats'   => $this->getStats(),
            'time'    => now()->format('H:i:s'),
        ]);
    }

    private function getStats(): array
    {
        return [
            'total'       => OrderHeader::count(),
            'pending'     => OrderHeader::where('or_confirm', 0)->count(),
            'confirmed'   => OrderHeader::where('or_confirm', 1)->count(),
            'today_doses' => OncDayRecord::whereDate('or_daterec', today())->count(),
        ];
    }

    private function getStateToken(): string
    {
        $count = OrderHeader::count();
        $pending = OrderHeader::where('or_confirm', 0)->count();
        $latestId = OrderHeader::orderByDesc('or_id')->value('or_id') ?? 'none';
        return md5("{$count}|{$pending}|{$latestId}");
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
            'dayRecords.unit',
            'dayRecords.solution',
            'dayRecords.route',
            'dayRecords.timing',
            'ward',
        ])->findOrFail($orId);

        $groupedDayRecords = $order->dayRecords->groupBy('or_daterec');

        return view('pharmacist.show', compact('order', 'groupedDayRecords'));
    }

    /**
     * เภสัชกรกด Confirm รับคำสั่งใช้ยา และแตก order_d ออกเป็นรายวันลงในตาราง onc_dayrecord
     */
    public function confirm(string $orId)
    {
        $order = OrderHeader::with(['details.medication'])->findOrFail($orId);

        DB::transaction(function () use ($order) {
            // 1. Update order_h status -> รับคำสั่งแล้ว
            $order->update([
                'or_confirm'         => 1,
                'or_chkflag'         => 1,
                'or_daily'           => 1,
                'or_pharmacist_id'   => Auth::id(),
                'or_pharmacist_name' => Auth::user()->name,
                'or_confirmed_at'    => now(),
            ]);

            // 2. ล้างรายการรายวันเดิมของ or_id นี้ (กรณีเคยแตกไปแล้วเพื่อป้องกันซ้ำซ้อน)
            DB::table('onc_dayrecord')->where('or_id', $order->or_id)->delete();

            // 3. แตกคำสั่งใช้ยา (order_d) เป็นรายวันตามช่วงวันที่ or_start ถึง or_stop
            foreach ($order->details as $detail) {
                $startDate = Carbon::parse($detail->or_start ?: $order->or_date);
                $stopDate  = Carbon::parse($detail->or_stop ?: $startDate);

                if ($stopDate->lt($startDate)) {
                    $stopDate = $startDate->copy();
                }

                $totalDays = $startDate->diffInDays($stopDate) + 1;
                $currentDate = $startDate->copy();
                $dayIndex = 1;

                while ($currentDate->lte($stopDate)) {
                    OncDayRecord::create([
                        'or_check'    => 1,
                        'or_prep'     => 0,
                        'or_send'     => 0,
                        'or_id'       => $order->or_id,
                        'or_medcode'  => $detail->or_medcode,
                        'or_meddose'  => $detail->or_meddose,
                        'or_medunit'  => $detail->or_medunit,
                        'or_soln'     => $detail->or_soln,
                        'or_medroute' => $detail->or_medroute,
                        'or_solvol'   => $detail->or_solvol,
                        'or_medtime'  => $detail->or_medtime,
                        'or_orderno'  => $detail->or_orderno,
                        'or_daterec'  => $currentDate->format('Y-m-d'),
                        'or_start'    => $startDate->format('Y-m-d'),
                        'or_stop'     => $stopDate->format('Y-m-d'),
                        'or_working'  => $detail->or_working ?? 0,
                        'or_totalvol' => $detail->or_totalvol ?? 0,
                        'or_text'     => "Day {$dayIndex}/{$totalDays}",
                        'or_recstamp' => now(),
                        'or_hold'     => 0,
                        'or_discont'  => 0,
                    ]);

                    $currentDate->addDay();
                    $dayIndex++;
                }

                // Update or_cf in order_d
                DB::table('order_d')
                    ->where('or_id', $order->or_id)
                    ->where('or_orderno', $detail->or_orderno)
                    ->update(['or_cf' => 1]);
            }
        });

        return redirect()
            ->route('pharmacist.show', $order->or_id)
            ->with('success', 'ยืนยันรับคำสั่งใช้ยา ("รับคำสั่งแล้ว") และแตกคำสั่งใช้ยาเป็นรายวันลงตาราง onc_dayrecord เรียบร้อยแล้ว');
    }

    /**
     * อัปเดตสถานะการเตรียมยา / ส่งมอบยา ของแต่ละวัน
     */
    public function updateDayStatus(Request $request, string $orId)
    {
        $daterec = $request->input('or_daterec');
        $action  = $request->input('action'); // 'prep' or 'send'

        $query = DB::table('onc_dayrecord')->where('or_id', $orId);
        if ($daterec) {
            $query->where('or_daterec', $daterec);
        }

        if ($action === 'prep') {
            $query->update(['or_prep' => 1]);
            OrderHeader::where('or_id', $orId)->update(['or_prepflag' => 1]);
            $msg = 'บันทึกสถานะ "เตรียมยาเสร็จสิ้น" เรียบร้อยแล้ว';
        } elseif ($action === 'send') {
            $query->update(['or_prep' => 1, 'or_send' => 1]);
            OrderHeader::where('or_id', $orId)->update(['or_prepflag' => 1, 'or_sendflag' => 1]);
            $msg = 'บันทึกสถานะ "ส่งมอบยาไปยังหอผู้ป่วย" เรียบร้อยแล้ว';
        } else {
            $msg = 'อัปเดตสถานะเรียบร้อยแล้ว';
        }

        return back()->with('success', $msg);
    }

    /**
     * พิมพ์ใบจัดยา (Working Formula) ขนาด A5 แนวนอน
     */
    public function workingFormula(string $orId)
    {
        $order = OrderHeader::with([
            'details.medication',
            'details.unit',
            'details.solution',
            'details.route',
            'details.timing',
            'dayRecords',
            'ward',
        ])->findOrFail($orId);

        return view('pharmacist.print-working-formula', compact('order'));
    }

    /**
     * พิมพ์ฉลากยาเคมีบำบัด (Medication Labels / Stickers)
     */
    public function medicationLabels(Request $request, string $orId)
    {
        $order = OrderHeader::with([
            'details.medication',
            'details.unit',
            'details.solution',
            'details.route',
            'details.timing',
            'dayRecords.medication',
            'dayRecords.unit',
            'dayRecords.solution',
            'dayRecords.route',
            'dayRecords.timing',
            'ward',
        ])->findOrFail($orId);

        $filterDate = $request->query('date');
        $filterOrderNo = $request->query('orderno');

        $labels = $order->dayRecords;

        if ($filterDate) {
            $labels = $labels->where('or_daterec', $filterDate);
        }
        if ($filterOrderNo) {
            $labels = $labels->where('or_orderno', (int) $filterOrderNo);
        }

        // หากเพิ่งดูตัวอย่างก่อนกด Confirm (ยังไม่มี dayRecords) ให้สร้างจำลองจาก details เพื่อแสดงฉลากได้ทันที
        if ($labels->isEmpty() && $order->details->isNotEmpty()) {
            $virtualLabels = collect();
            foreach ($order->details as $d) {
                $start = Carbon::parse($d->or_start ?: $order->or_date);
                $stop  = Carbon::parse($d->or_stop ?: $start);
                $totalDays = $start->diffInDays($stop) + 1;
                $curr = $start->copy();
                $dayIdx = 1;
                while ($curr->lte($stop)) {
                    $virtualLabels->push((object) [
                        'or_id'       => $order->or_id,
                        'or_medcode'  => $d->or_medcode,
                        'or_meddose'  => $d->or_meddose,
                        'or_medunit'  => $d->or_medunit,
                        'or_soln'     => $d->or_soln,
                        'or_solvol'   => $d->or_solvol,
                        'or_medroute' => $d->or_medroute,
                        'or_medtime'  => $d->or_medtime,
                        'or_orderno'  => $d->or_orderno,
                        'or_daterec'  => $curr->format('Y-m-d'),
                        'or_start'    => $d->or_start,
                        'or_stop'     => $d->or_stop,
                        'or_working'  => $d->or_working,
                        'or_totalvol' => $d->or_totalvol,
                        'or_text'     => "Day {$dayIdx}/{$totalDays}",
                        'medication'  => $d->medication,
                        'unit'        => $d->unit,
                        'solution'    => $d->solution,
                        'route'       => $d->route,
                        'timing'      => $d->timing,
                    ]);
                    $curr->addDay();
                    $dayIdx++;
                }
            }
            $labels = $virtualLabels;
        }

        $availableDates = $order->dayRecords->pluck('or_daterec')->unique()->sort()->values();

        return view('pharmacist.print-labels', compact('order', 'labels', 'filterDate', 'availableDates'));
    }

    /**
     * หน้าพิมพ์สติกเกอร์ยาเคมีบำบัดประจำวัน (รวมคนไข้ทุกรายตามวันที่ให้ยา เลือกพิมพ์รายคนไข้ / ทั้งหมดได้)
     */
    public function dailyStickers(Request $request)
    {
        $selectedDate = $request->query('date', today()->format('Y-m-d'));
        $selectedOrId = $request->query('or_id', '');

        // 1. ดึงรายการจาก onc_dayrecord ตามวันที่ให้ยา (or_daterec)
        $dayRecords = OncDayRecord::with([
            'orderHeader.ward',
            'medication',
            'unit',
            'solution',
            'route',
            'timing',
        ])
            ->whereDate('or_daterec', $selectedDate)
            ->orderBy('or_id')
            ->orderBy('or_orderno')
            ->get();

        $existingOrIds = $dayRecords->pluck('or_id')->unique()->toArray();

        // 2. ดึงรายการจาก order_d ที่อยู่ในช่วงวันที่ให้ยา (or_start <= selectedDate <= or_stop) แต่ยังไม่ได้กด Confirm แตกลง onc_dayrecord
        $pendingOrders = OrderHeader::with([
            'details.medication',
            'details.unit',
            'details.solution',
            'details.route',
            'details.timing',
            'ward',
        ])
            ->whereNotIn('or_id', $existingOrIds)
            ->whereHas('details', function ($q) use ($selectedDate) {
                $q->whereDate('or_start', '<=', $selectedDate)
                  ->whereDate('or_stop', '>=', $selectedDate);
            })
            ->orderBy('or_id')
            ->get();

        $allStickersOnDate = collect($dayRecords);

        foreach ($pendingOrders as $order) {
            foreach ($order->details as $d) {
                $start = Carbon::parse($d->or_start ?: $order->or_date);
                $stop  = Carbon::parse($d->or_stop ?: $start);
                $target = Carbon::parse($selectedDate);

                if ($target->betweenIncluded($start, $stop)) {
                    $totalDays = $start->diffInDays($stop) + 1;
                    $dayIdx = $start->diffInDays($target) + 1;

                    $allStickersOnDate->push((object) [
                        'or_id'        => $order->or_id,
                        'or_medcode'   => $d->or_medcode,
                        'or_meddose'   => $d->or_meddose,
                        'or_medunit'   => $d->or_medunit,
                        'or_soln'      => $d->or_soln,
                        'or_solvol'    => $d->or_solvol,
                        'or_medroute'  => $d->or_medroute,
                        'or_medtime'   => $d->or_medtime,
                        'or_orderno'   => $d->or_orderno,
                        'or_daterec'   => $selectedDate,
                        'or_start'     => $d->or_start,
                        'or_stop'      => $d->or_stop,
                        'or_working'   => $d->or_working,
                        'or_totalvol'  => $d->or_totalvol,
                        'or_text'      => "Day {$dayIdx}/{$totalDays}",
                        'orderHeader'  => $order,
                        'medication'   => $d->medication,
                        'unit'         => $d->unit,
                        'solution'     => $d->solution,
                        'route'        => $d->route,
                        'timing'       => $d->timing,
                    ]);
                }
            }
        }

        // 3. จัดกลุ่มรายชื่อคนไข้ทั้งหมดที่มีคิวรับยาในวันที่เลือก (เพิ่มสติกเกอร์สรุปรายการยาประจำวันอีก 1 ดวงต่อคนไข้)
        $patientsGroup = $allStickersOnDate->groupBy('or_id')->map(function ($items, $orId) {
            $first = $items->first();
            $header = $first->orderHeader;
            return (object) [
                'or_id'          => $orId,
                'or_hn'          => optional($header)->or_hn ?? '-',
                'or_patname'     => optional($header)->or_patname ?? '-',
                'or_reg_code'    => optional($header)->or_reg_code ?? '-',
                'or_confirm'     => optional($header)->or_confirm ?? 0,
                'ward_name'      => optional(optional($header)->ward)->wd_name ?? optional($header)->or_ward ?? '-',
                'doses_count'    => $items->count(),
                'stickers_count' => $items->count() + 1, // ยารายขวด + 1 ดวงสรุปรายการยาประจำวัน
                'orderHeader'    => $header,
                'items'          => $items->values(),
            ];
        });

        // 4. กรองรายการสติกเกอร์ที่จะแสดงผล (ทั้งหมด หรือ เฉพาะคนไข้ที่เลือก)
        $stickers = $selectedOrId
            ? $allStickersOnDate->where('or_id', $selectedOrId)->values()
            : $allStickersOnDate->values();

        // 5. รวบรวมวันที่ที่มีรายการยาเพื่อแสดงปุ่มเลือกวันที่ด่วน
        $datesFromDayRecord = OncDayRecord::select('or_daterec')
            ->distinct()
            ->orderBy('or_daterec')
            ->pluck('or_daterec');

        $datesFromOrderD = DB::table('order_d')
            ->select('or_start', 'or_stop')
            ->whereNotNull('or_start')
            ->get();

        $dateSet = collect($datesFromDayRecord);
        foreach ($datesFromOrderD as $od) {
            $s = Carbon::parse($od->or_start);
            $e = Carbon::parse($od->or_stop ?: $od->or_start);
            while ($s->lte($e)) {
                $dateSet->push($s->format('Y-m-d'));
                $s->addDay();
            }
        }
        $activeDates = $dateSet->unique()->sort()->values();

        return view('pharmacist.daily-stickers', compact(
            'selectedDate',
            'selectedOrId',
            'patientsGroup',
            'stickers',
            'allStickersOnDate',
            'activeDates'
        ));
    }
}
