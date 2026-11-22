<?php

namespace App\Http\Controllers;

use App\Models\OrderHeader;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PatientReportController extends Controller
{
    /**
     * หน้ารายชื่อผู้ป่วยทั้งหมดสำหรับค้นหาและเปิดดู Patient Chemotherapy Profile (แพทย์ & เภสัชกร)
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));

        $query = OrderHeader::with(['ward'])
            ->orderByDesc('or_date')
            ->orderByDesc('or_id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('or_hn', 'like', "%{$search}%")
                  ->orWhere('or_patname', 'like', "%{$search}%")
                  ->orWhere('or_reg_code', 'like', "%{$search}%")
                  ->orWhere('or_id', 'like', "%{$search}%");
            });
        }

        $allOrders = $query->get();

        // จัดกลุ่มตาม HN เพื่อแสดง 1 แถวต่อผู้ป่วย 1 ราย
        $patients = $allOrders->groupBy('or_hn')->map(function ($orders, $hn) {
            $latest = $orders->first();
            $activeOrders = $orders->where('or_cancel', 0);
            $cancelledOrders = $orders->where('or_cancel', 1);

            return (object) [
                'or_hn'           => $hn,
                'or_patname'      => $latest->or_patname,
                'or_age'          => $latest->or_age,
                'or_sex'          => $latest->or_sex,
                'or_wt'           => $latest->or_wt,
                'or_ht'           => $latest->or_ht,
                'or_bsa'          => $latest->or_bsa,
                'or_gfr'          => $latest->or_gfr,
                'ward_name'       => optional($latest->ward)->wd_name ?? $latest->or_ward,
                'latest_reg_code' => $latest->or_reg_code,
                'latest_cycle'    => $latest->or_cycle ?? 1,
                'latest_date'     => $latest->or_date,
                'total_orders'    => $orders->count(),
                'active_count'    => $activeOrders->count(),
                'cancelled_count' => $cancelledOrders->count(),
                'regimens_used'   => $activeOrders->pluck('or_reg_code')->filter()->unique()->values(),
            ];
        })->values();

        return view('reports.patients-index', compact('patients', 'search'));
    }

    /**
     * หน้า Profile การรับยาเคมีบำบัดของผู้ป่วยรายบุคคล (Patient Chemotherapy Profile + Cumulative Dose)
     */
    public function profile(string $hn)
    {
        $orders = OrderHeader::with([
            'details.medication',
            'details.unit',
            'details.solution',
            'details.route',
            'details.timing',
            'dayRecords',
            'supportiveMeds',
            'ward',
        ])
            ->where('or_hn', $hn)
            ->orderByDesc('or_date')
            ->orderByDesc('or_id')
            ->get();

        if ($orders->isEmpty()) {
            abort(404, 'ไม่พบประวัติการรับยาเคมีบำบัดของ HN: ' . $hn);
        }

        $latest = $orders->first();
        $activeOrders = $orders->where('or_cancel', 0);
        $cancelledOrders = $orders->where('or_cancel', 1);

        // คำนวณขนาดยาสะสม (Cumulative Dose) จากเฉพาะคำสั่งที่ไม่ถูกยกเลิก
        $cumulativeMap = [];
        foreach ($activeOrders as $ord) {
            $bsa = (float) ($ord->or_bsa ?: 0);
            foreach ($ord->details as $d) {
                if ((int) ($d->or_discont ?? 0) === 1) {
                    continue;
                }
                $code = $d->or_medcode;
                $days = 1;
                if ($d->or_start && $d->or_stop) {
                    $days = max(1, Carbon::parse($d->or_start)->diffInDays(Carbon::parse($d->or_stop)) + 1);
                }
                $dosePerDay = (float) $d->or_meddose;
                $totalMgInOrder = $dosePerDay * $days;
                $totalMgPerM2 = $bsa > 0 ? ($totalMgInOrder / $bsa) : 0;

                if (!isset($cumulativeMap[$code])) {
                    $cumulativeMap[$code] = [
                        'medcode'       => $code,
                        'dg_name'       => optional($d->medication)->dg_name ?? $code,
                        'unit'          => optional($d->unit)->un_unit ?? 'mg',
                        'total_mg'      => 0.0,
                        'total_mg_m2'   => 0.0,
                        'cycles_count'  => 0,
                        'total_days'    => 0,
                        'first_date'    => $ord->or_date,
                        'last_date'     => $ord->or_date,
                    ];
                }

                $cumulativeMap[$code]['total_mg'] += $totalMgInOrder;
                $cumulativeMap[$code]['total_mg_m2'] += $totalMgPerM2;
                $cumulativeMap[$code]['cycles_count'] += 1;
                $cumulativeMap[$code]['total_days'] += $days;
                if ($ord->or_date < $cumulativeMap[$code]['first_date']) {
                    $cumulativeMap[$code]['first_date'] = $ord->or_date;
                }
                if ($ord->or_date > $cumulativeMap[$code]['last_date']) {
                    $cumulativeMap[$code]['last_date'] = $ord->or_date;
                }
            }
        }

        $cumulativeDoses = collect($cumulativeMap)->sortByDesc('total_mg')->values();

        // สรุปประวัติค่าทางคลินิก (Wt, BSA, SCr, GFR) ย้อนหลังตามเวลา
        $clinicalTrend = $activeOrders->sortBy('or_date')->values();

        return view('reports.patient-profile', compact(
            'hn',
            'latest',
            'orders',
            'activeOrders',
            'cancelledOrders',
            'cumulativeDoses',
            'clinicalTrend'
        ));
    }

    /**
     * รายงานประวัติการรับการรักษาของผู้ป่วยต่อครั้ง (ตั้งแต่แพทย์ -> เภสัชกร -> พยาบาล พร้อมสรุปเวลา TAT)
     */
    public function visitReport(string $orId)
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
            'supportiveMeds',
            'ward',
        ])->findOrFail($orId);

        $orderedAt   = $order->or_created_at ? Carbon::parse($order->or_created_at) : null;
        $confirmedAt = $order->or_confirmed_at ? Carbon::parse($order->or_confirmed_at) : null;

        // หาเวลาสรุประดับใบสั่ง (ใช้เวลาก่อนสุด/ล่าสุดจาก onc_dayrecord)
        $dayRecords = $order->dayRecords->sortBy('or_daterec')->values();
        $firstPreparedAt    = $dayRecords->pluck('or_prepared_at')->filter()->sort()->first();
        $firstDispatchedAt  = $dayRecords->pluck('or_dispatched_at')->filter()->sort()->first();
        $lastAdministeredAt = $dayRecords->pluck('or_administered_at')->filter()->sort()->last();
        $dispatchedBy       = $dayRecords->pluck('or_dispatched_by')->filter()->first();
        $administeredBy     = $dayRecords->pluck('or_administered_by')->filter()->unique()->implode(', ');

        $preparedAt     = $firstPreparedAt ? Carbon::parse($firstPreparedAt) : null;
        $dispatchedAt   = $firstDispatchedAt ? Carbon::parse($firstDispatchedAt) : null;
        $administeredAt = $lastAdministeredAt ? Carbon::parse($lastAdministeredAt) : null;

        // คำนวณ Turnaround Time (นาที) ของแต่ละช่วงในภาพรวม
        $overallTat = [
            'order_to_confirm'    => $this->diffMinutes($orderedAt, $confirmedAt),
            'confirm_to_prep'     => $this->diffMinutes($confirmedAt, $preparedAt),
            'prep_to_dispatch'    => $this->diffMinutes($preparedAt, $dispatchedAt),
            'dispatch_to_admin'   => $this->diffMinutes($dispatchedAt, $administeredAt),
            'total_end_to_end'    => $this->diffMinutes($orderedAt, $administeredAt),
            'confirm_to_admin'    => $this->diffMinutes($confirmedAt, $administeredAt),
        ];

        // คำนวณสรุปเวลารายวัน (กรณีคอร์สให้ยาหลายวัน เช่น Day 1, Day 2)
        $dailyTimelines = $dayRecords->groupBy('or_daterec')->map(function ($recs, $dateStr) use ($orderedAt, $confirmedAt) {
            $prepAt  = $recs->pluck('or_prepared_at')->filter()->sort()->first();
            $dispAt  = $recs->pluck('or_dispatched_at')->filter()->sort()->first();
            $admAt   = $recs->pluck('or_administered_at')->filter()->sort()->last();

            $pCarbon = $prepAt ? Carbon::parse($prepAt) : null;
            $dCarbon = $dispAt ? Carbon::parse($dispAt) : null;
            $aCarbon = $admAt ? Carbon::parse($admAt) : null;

            return (object) [
                'date'               => $dateStr,
                'day_label'          => optional($recs->first())->or_text ?: '-',
                'doses_count'        => $recs->count(),
                'prepared_at'        => $pCarbon,
                'dispatched_at'      => $dCarbon,
                'dispatched_by'      => $recs->pluck('or_dispatched_by')->filter()->first(),
                'administered_at'    => $aCarbon,
                'administered_by'    => $recs->pluck('or_administered_by')->filter()->unique()->implode(', '),
                'prep_to_dispatch'   => $this->diffMinutes($pCarbon, $dCarbon),
                'dispatch_to_admin'  => $this->diffMinutes($dCarbon, $aCarbon),
                'prep_to_admin'      => $this->diffMinutes($pCarbon, $aCarbon),
                'confirm_to_admin'   => $this->diffMinutes($confirmedAt, $aCarbon),
                'order_to_admin'     => $this->diffMinutes($orderedAt, $aCarbon),
                'records'            => $recs->sortBy('or_orderno')->values(),
            ];
        })->values();

        return view('reports.visit-report', compact(
            'order',
            'orderedAt',
            'confirmedAt',
            'preparedAt',
            'dispatchedAt',
            'dispatchedBy',
            'administeredAt',
            'administeredBy',
            'overallTat',
            'dailyTimelines'
        ));
    }

    private function diffMinutes(?Carbon $start, ?Carbon $end): ?int
    {
        if (!$start || !$end) {
            return null;
        }
        if ($end->lt($start)) {
            return 0;
        }
        return (int) $start->diffInMinutes($end);
    }

    public static function formatMinutes(?int $minutes): string
    {
        if ($minutes === null) {
            return '-';
        }
        if ($minutes < 60) {
            return $minutes . ' นาที';
        }
        $hrs = intdiv($minutes, 60);
        $mins = $minutes % 60;
        return $mins > 0 ? "{$hrs} ชม. {$mins} นาที" : "{$hrs} ชม.";
    }
}
