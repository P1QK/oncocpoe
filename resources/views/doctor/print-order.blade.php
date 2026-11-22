<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบสั่งใช้ยาเคมีบำบัด (A4) - {{ $order->or_id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #e2e8f0;
            color: #0f172a;
            font-size: 12.5px;
            line-height: 1.38;
        }
        .a4-sheet {
            width: 210mm;
            min-height: 297mm;
            margin: 14px auto;
            background: #fff;
            padding: 12mm 13mm;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.16);
            box-sizing: border-box;
        }
        .doc-header {
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .patient-box {
            border: 1.5px solid #334155;
            border-radius: 6px;
            padding: 8px 10px;
            background: #f8fafc;
            margin-bottom: 10px;
        }
        .section-block {
            margin-bottom: 10px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .section-title {
            font-weight: 700;
            font-size: 13px;
            padding: 4px 10px;
            border-radius: 4px 4px 0 0;
            border: 1px solid #cbd5e1;
            border-bottom: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .title-premed   { background: #e0f2fe; color: #0369a1; border-color: #7dd3fc; }
        .title-prehyd   { background: #dbeafe; color: #1d4ed8; border-color: #93c5fd; }
        .title-chemo    { background: #0f172a; color: #ffffff; border-color: #0f172a; }
        .title-posthyd  { background: #dcfce7; color: #15803d; border-color: #86efac; }
        .title-postmed  { background: #f3e8ff; color: #6b21a8; border-color: #d8b4fe; }
        .title-homemed  { background: #fef3c7; color: #92400e; border-color: #fcd34d; }

        .order-list {
            border: 1px solid #94a3b8;
            border-radius: 0 0 4px 4px;
            padding: 6px 10px;
            background: #fff;
        }
        .order-line {
            padding: 4px 0;
            border-bottom: 1px dashed #cbd5e1;
            font-size: 12.5px;
            line-height: 1.45;
        }
        .order-line:last-child {
            border-bottom: none;
            padding-bottom: 1px;
        }
        .order-subline {
            padding-left: 26px;
            font-size: 11.5px;
            color: #334155;
            margin-top: 1px;
        }
        .sp {
            display: inline-block;
            width: 10px;
        }
        .sig-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 16px;
            page-break-inside: avoid;
        }
        .sig-box {
            border: 1px solid #94a3b8;
            border-radius: 6px;
            padding: 8px 10px;
            font-size: 11.5px;
        }
        @media print {
            body {
                background: #fff !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .a4-sheet {
                width: 100% !important;
                min-height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>

@php
    $supportive     = $order->supportiveMeds ?? collect();
    $preMeds        = $supportive->whereIn('category', ['pre_med_antiemetic', 'pre_med_hypersensitivity'])->values();
    $preHydrations  = $supportive->where('category', 'pre_hydration')->values();
    $postHydrations = $supportive->where('category', 'post_hydration')->values();
    $postMeds       = $supportive->where('category', 'post_medication')->values();
    $homeMeds       = $supportive->where('category', 'home_med')->values();
    $chemoDetails   = $order->details->sortBy('or_orderno')->values();
    $stepNo         = 1;
@endphp

<!-- แถบปุ่มสั่งพิมพ์ (ไม่แสดงตอนพิมพ์) -->
<div class="container py-3 no-print" style="max-width: 210mm;">
    <div class="card border-0 shadow-sm p-3 bg-white d-flex flex-row justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="fw-bold text-primary mb-0">
                <i class="bi bi-printer-fill me-2"></i>ใบสั่งใช้ยาเคมีบำบัดเต็มรูปแบบ (ขนาด A4)
            </h5>
            <small class="text-secondary">
                Order ID: <strong>{{ $order->or_id }}</strong> | ผู้ป่วย: <strong>{{ $order->or_patname }}</strong> (HN: {{ $order->or_hn }})
            </small>
        </div>
        <div class="d-flex gap-2">
            <button type="button" onclick="window.print()" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> สั่งพิมพ์ใบสั่งยา (A4)
            </button>
            <a href="{{ route('doctor.show', $order->or_id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-eye me-1"></i> ดูหน้ารายละเอียด
            </a>
            <a href="{{ route('doctor.index') }}" class="btn btn-outline-dark">
                <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
            </a>
        </div>
    </div>
</div>

<!-- เอกสารใบสั่งยาขนาด A4 แนวตั้ง -->
<div class="a4-sheet">
    <!-- หัวเอกสารโรงพยาบาล -->
    <div class="doc-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('images/spr.jpg') }}" alt="SPR" width="48" height="48" class="rounded-circle border border-dark">
            <div>
                <div class="fw-bold fs-5 lh-1">ใบคำสั่งใช้ยาเคมีบำบัดชนิดฉีด (CHEMOTHERAPY ORDER SHEET)</div>
                <div class="small text-secondary mt-1">
                    Sterile Products Pharmacy Service &bull; COPS&trade; Oncology System (SPR Hospital)
                </div>
            </div>
        </div>
        <div class="text-end">
            <svg id="orderBarcode" data-code="{{ $order->or_id }}" style="max-height: 36px;"></svg>
            <div class="small fw-semibold">วันที่เริ่มให้ยา: {{ \Carbon\Carbon::parse($order->or_date)->format('d/m/Y') }}</div>
        </div>
    </div>

    <!-- ข้อมูลผู้ป่วยและค่าทางคลินิก -->
    <div class="patient-box">
        <div class="row g-2 align-items-center">
            <div class="col-5">
                <div class="fs-6 fw-bold text-dark">{{ $order->or_patname }}</div>
                <div>
                    <strong>HN:</strong> <span class="fw-bold">{{ $order->or_hn }}</span> |
                    <strong>อายุ:</strong> {{ $order->or_age ?? '-' }} ปี |
                    <strong>เพศ:</strong> {{ $order->or_sex === 'F' ? 'หญิง' : 'ชาย' }}
                </div>
                <div><strong>หอผู้ป่วย / หน่วยงาน:</strong> {{ optional($order->ward)->wd_name ?? $order->or_ward }}</div>
            </div>
            <div class="col-4 border-start border-end px-2">
                <div><strong>น้ำหนัก / ส่วนสูง:</strong> {{ number_format($order->or_wt, 1) }} kg / {{ number_format($order->or_ht, 0) }} cm</div>
                <div><strong>BSA (Mosteller):</strong> <span class="fw-bold text-primary">{{ number_format($order->or_bsa, 2) }} m&sup2;</span></div>
                <div><strong>GFR (SCr {{ $order->or_scr ?? '-' }}):</strong> <span class="fw-bold text-success">{{ $order->or_gfr ? number_format($order->or_gfr, 1) . ' mL/min' : '-' }}</span></div>
            </div>
            <div class="col-3 text-end">
                <div><strong>สูตรยา (Regimen):</strong></div>
                <div class="fs-6 fw-bold text-primary">{{ $order->or_reg_code ?? 'CUSTOM' }}</div>
                <div><strong>Cycle ที่:</strong> <span class="fw-bold">#{{ $order->or_cycle ?? 1 }}</span></div>
            </div>
        </div>

        @if($order->or_adjust_percent > 0 || $order->or_adjust_reason)
            <div class="mt-2 pt-2 border-top border-danger-subtle text-danger small">
                <strong><i class="bi bi-exclamation-triangle-fill me-1"></i>หมายเหตุการปรับลดขนาดยา ({{ number_format($order->or_adjust_percent, 0) }}%):</strong>
                {{ $order->or_adjust_reason ?: '-' }}
            </div>
        @endif
    </div>

    <!-- 1. PRE-MEDICATION -->
    <div class="section-block">
        <div class="section-title title-premed">
            <span>{{ $stepNo++ }}. Pre-medication (ยาเตรียมก่อนให้เคมีบำบัด)</span>
            <span class="small">{{ $preMeds->count() }} รายการ</span>
        </div>
        <div class="order-list">
            @forelse($preMeds as $idx => $pm)
                <div class="order-line">
                    <span class="fw-bold">{{ $idx + 1 }}.</span>
                    <span class="fw-bold text-dark">{{ $pm->sm_name }}</span><span class="sp"></span>
                    <span class="fw-bold text-primary">{{ $pm->dose }}</span><span class="sp"></span>
                    @if($pm->route)
                        <span>{{ $pm->route }}</span><span class="sp"></span>
                    @endif
                    @if($pm->instruction)
                        <span class="text-secondary">{{ $pm->instruction }}</span>
                    @endif
                </div>
            @empty
                <div class="order-line text-secondary text-center">ไม่ได้สั่งใช้ยา Pre-medication</div>
            @endforelse
        </div>
    </div>

    <!-- 2. PRE-HYDRATION (แสดงเมื่อมีรายการ) -->
    @if($preHydrations->isNotEmpty())
        <div class="section-block">
            <div class="section-title title-prehyd">
                <span>{{ $stepNo++ }}. Pre-hydration (สารน้ำและอิเล็กโทรไลต์ก่อนให้เคมีบำบัด)</span>
                <span class="small">{{ $preHydrations->count() }} รายการ</span>
            </div>
            <div class="order-list">
                @foreach($preHydrations as $idx => $ph)
                    <div class="order-line">
                        <span class="fw-bold">{{ $idx + 1 }}.</span>
                        <span class="fw-bold text-dark">{{ $ph->sm_name }}</span><span class="sp"></span>
                        <span class="fw-bold text-primary">{{ $ph->dose }}</span><span class="sp"></span>
                        @if($ph->route)
                            <span>{{ $ph->route }}</span><span class="sp"></span>
                        @endif
                        @if($ph->instruction)
                            <span class="text-secondary">{{ $ph->instruction }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 3. CHEMOTHERAPY (ยาเคมีบำบัดหลัก เรียงตามลำดับการให้ยา แบบข้อความต่อเนื่อง + วันที่เริ่ม-จบแยกบรรทัด) -->
    @php
        $chemoBags = collect();
        $bagMap = [];
        foreach ($chemoDetails as $cd) {
            $bKey = !empty($cd->or_bag_group)
                ? ('BAG:' . $cd->or_bag_group)
                : ('SINGLE:' . $cd->or_orderno . '_' . $cd->or_medcode . '_' . uniqid());
            if (!isset($bagMap[$bKey])) {
                $bagMap[$bKey] = $chemoBags->count();
                $chemoBags->push((object)[
                    'bag_group' => $cd->or_bag_group,
                    'items'     => collect([$cd]),
                ]);
            } else {
                $chemoBags[$bagMap[$bKey]]->items->push($cd);
            }
        }
    @endphp
    <div class="section-block">
        <div class="section-title title-chemo">
            <span>{{ $stepNo++ }}. Chemotherapy (รายการยาเคมีบำบัดหลัก &mdash; เรียงตามลำดับการให้ยา)</span>
            <span class="small">{{ $chemoBags->count() }} ถุง/ขวด ({{ $chemoDetails->count() }} ตัวยา)</span>
        </div>
        <div class="order-list">
            @foreach($chemoBags as $bIdx => $bag)
                @php
                    $first = $bag->items->first();
                    $isMultiBag = $bag->items->count() > 1;
                    $days = \Carbon\Carbon::parse($first->or_start)->diffInDays(\Carbon\Carbon::parse($first->or_stop)) + 1;
                    $solName = $first->or_soln === 'NONE' ? 'Undiluted (ไม่ผสมสารน้ำ)' : ($first->or_soln . ' ' . number_format($first->or_solvol, 0) . ' mL');
                    $routeTxt = optional($first->route)->rt_name ?? $first->or_medroute;
                    $timeTxt  = optional($first->timing)->tm_time ?? $first->or_medtime;

                    $sumWorking = $bag->items->sum('or_working');
                    $allAdditives = [];
                    $sumAddVol = 0;
                    foreach ($bag->items as $bi) {
                        if (!empty($bi->or_additives) && is_array($bi->or_additives)) {
                            foreach ($bi->or_additives as $ad) {
                                $allAdditives[] = $ad;
                                $sumAddVol += (float) ($ad['vol_ml'] ?? 0);
                            }
                        }
                    }
                    $bagTotalVol = (float) $first->or_solvol + (float) $sumWorking + $sumAddVol;
                    $precautions = $bag->items->map(fn($i) => optional($i->medication)->dg_precaution)->filter()->unique()->values();
                @endphp
                <div class="order-line">
                    <div>
                        <span class="fw-bold text-dark">{{ $bIdx + 1 }}/{{ $chemoBags->count() }}.</span>
                        @if($isMultiBag || !empty($bag->bag_group))
                            <span class="fw-bold" style="color: #6d28d9;">[ผสมรวมในขวดเดียวกัน: {{ $bag->bag_group }}]</span><span class="sp"></span>
                        @endif

                        @foreach($bag->items as $mIdx => $d)
                            @php
                                $drugName = optional($d->medication)->dg_name ?? $d->or_medcode;
                                $unitText = optional($d->unit)->un_unit ?? 'mg';
                            @endphp
                            @if($mIdx > 0)
                                <span class="fw-bold text-dark"> + </span>
                            @endif
                            <span class="fw-bold text-dark">{{ $drugName }}</span>
                            <span class="text-secondary">[{{ $d->or_medcode }}]</span>
                            <span class="fw-bold text-danger" style="font-size: 13.5px;"> {{ number_format($d->or_meddose, 2) }} {{ $unitText }}</span>
                            @if(($d->or_reduce_pct ?? 0) > 0)
                                <span class="fw-bold text-danger small">[ลด {{ number_format($d->or_reduce_pct, 0) }}%]</span>
                            @endif
                        @endforeach

                        @if(count($allAdditives) > 0)
                            @foreach($allAdditives as $ad)
                                @php
                                    $adText = trim((string) ($ad['name'] ?? ''));
                                    if (!empty($ad['dose'])) {
                                        $adText .= ' ' . trim($ad['dose'] . ' ' . ($ad['unit'] ?? ''));
                                    }
                                    if (!empty($ad['vol_ml']) && (float) $ad['vol_ml'] > 0) {
                                        $adText .= ' (' . $ad['vol_ml'] . ' mL)';
                                    }
                                @endphp
                                <span class="fw-bold" style="color: #0369a1;">
                                    + {{ $adText }}
                                </span>
                            @endforeach
                        @endif

                        <span class="sp"></span><span>in {{ $solName }}</span>
                        <span class="text-secondary small">(ดูดยา {{ number_format($sumWorking, 2) }} mL@if($sumAddVol > 0) + สารผสม {{ number_format($sumAddVol, 1) }} mL@endif / รวม {{ number_format($bagTotalVol, 2) }} mL)</span><span class="sp"></span>
                        <span class="fw-semibold">{{ $routeTxt }}</span><span class="sp"></span>
                        <span class="fw-bold text-primary">{{ $timeTxt }}</span>
                    </div>
                    <div class="order-subline">
                        <strong>วันที่เริ่มให้ยา:</strong> {{ \Carbon\Carbon::parse($first->or_start)->format('d/m/Y') }}
                        <span class="sp"></span><strong>ถึงวันที่:</strong> {{ \Carbon\Carbon::parse($first->or_stop)->format('d/m/Y') }}
                        <span class="sp"></span><span class="fw-bold text-dark">(รวม {{ $days }} วัน)</span>
                    </div>
                    @if($precautions->isNotEmpty())
                        <div class="order-subline text-danger" style="font-size: 11px;">
                            <strong>ข้อควรระวัง:</strong> {{ $precautions->implode(' | ') }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- 4. POST-HYDRATION (แสดงเมื่อมีรายการ) -->
    @if($postHydrations->isNotEmpty())
        <div class="section-block">
            <div class="section-title title-posthyd">
                <span>{{ $stepNo++ }}. Post-hydration (สารน้ำและอิเล็กโทรไลต์หลังให้เคมีบำบัด)</span>
                <span class="small">{{ $postHydrations->count() }} รายการ</span>
            </div>
            <div class="order-list">
                @foreach($postHydrations as $idx => $poh)
                    <div class="order-line">
                        <span class="fw-bold">{{ $idx + 1 }}.</span>
                        <span class="fw-bold text-dark">{{ $poh->sm_name }}</span><span class="sp"></span>
                        <span class="fw-bold text-success">{{ $poh->dose }}</span><span class="sp"></span>
                        @if($poh->route)
                            <span>{{ $poh->route }}</span><span class="sp"></span>
                        @endif
                        @if($poh->instruction)
                            <span class="text-secondary">{{ $poh->instruction }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 5. POST-MEDICATION (เช่น MESNA 0-3-6 ชม.) -->
    @if($postMeds->isNotEmpty())
        <div class="section-block">
            <div class="section-title title-postmed">
                <span>{{ $stepNo++ }}. Post-medication (ยาหลัง/ระหว่างให้เคมีบำบัด เช่น MESNA)</span>
                <span class="small">{{ $postMeds->count() }} รายการ</span>
            </div>
            <div class="order-list">
                @foreach($postMeds as $idx => $pom)
                    <div class="order-line">
                        <span class="fw-bold">{{ $idx + 1 }}.</span>
                        <span class="fw-bold text-dark">{{ $pom->sm_name }}</span><span class="sp"></span>
                        <span class="fw-bold text-danger">{{ $pom->dose }}</span><span class="sp"></span>
                        @if($pom->route)
                            <span>{{ $pom->route }}</span><span class="sp"></span>
                        @endif
                        @if($pom->instruction)
                            <span class="text-secondary">{{ $pom->instruction }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 6. HOME MEDICATION (ยากลับบ้าน) -->
    @if($homeMeds->isNotEmpty())
        <div class="section-block">
            <div class="section-title title-homemed">
                <span>{{ $stepNo++ }}. Home-medication (ยากลับบ้านหลังรับยาเคมีบำบัด)</span>
                <span class="small">{{ $homeMeds->count() }} รายการ</span>
            </div>
            <div class="order-list">
                @foreach($homeMeds as $idx => $hm)
                    <div class="order-line">
                        <span class="fw-bold">{{ $idx + 1 }}.</span>
                        <span class="fw-bold text-dark">{{ $hm->sm_name }}</span><span class="sp"></span>
                        <span class="fw-bold text-dark">{{ $hm->dose }}</span><span class="sp"></span>
                        @if($hm->route)
                            <span>{{ $hm->route }}</span><span class="sp"></span>
                        @endif
                        @if($hm->instruction)
                            <span class="text-secondary">{{ $hm->instruction }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- ส่วนลงนามรับรอง -->
    <div class="sig-grid">
        <div class="sig-box">
            <div class="fw-bold mb-2">1. แพทย์ผู้สั่งใช้ยา (Physician)</div>
            <div class="mb-2">ลงชื่อ: .............................................</div>
            <div>({{ $order->or_doctor_name ?: '.............................................' }})</div>
            <div class="text-secondary mt-1">วันที่: {{ \Carbon\Carbon::parse($order->or_date)->format('d/m/Y') }} เวลา: .......... น.</div>
        </div>
        <div class="sig-box">
            <div class="fw-bold mb-2">2. เภสัชกรผู้ตรวจสอบ (Pharmacist)</div>
            <div class="mb-2">ลงชื่อ: .............................................</div>
            <div>({{ $order->or_pharmacist_name ?: '.............................................' }})</div>
            <div class="text-secondary mt-1">วันที่: ....../....../...... เวลา: .......... น.</div>
        </div>
        <div class="sig-box">
            <div class="fw-bold mb-2">3. พยาบาลผู้บริหารยา (Double Check)</div>
            <div class="mb-2">คนที่ 1: ...........................................</div>
            <div class="mb-1">คนที่ 2: ...........................................</div>
            <div class="text-secondary">วันที่: ....../....../...... เวลา: .......... น.</div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('orderBarcode');
        if (el && typeof JsBarcode === 'function') {
            try {
                JsBarcode(el, el.getAttribute('data-code'), {
                    format: 'CODE128',
                    width: 1.3,
                    height: 24,
                    displayValue: true,
                    fontSize: 10,
                    margin: 0
                });
            } catch (e) {}
        }
    });
</script>
</body>
</html>
