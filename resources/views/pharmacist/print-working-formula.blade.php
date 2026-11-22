<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบจัดยาเคมีบำบัด (Working Formula) - {{ $order->or_id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A5 landscape;
            margin: 6mm;
        }
        body {
            font-family: 'Sarabun', sans-serif;
            background: #e2e8f0;
            color: #000;
            font-size: 11.5px;
        }
        .a5-landscape-sheet {
            width: 210mm;
            min-height: 148mm;
            background: #fff;
            margin: 15px auto;
            padding: 8mm;
            box-sizing: border-box;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .table-wf th, .table-wf td {
            border: 1px solid #1e293b !important;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .table-wf th {
            background-color: #f1f5f9 !important;
            font-weight: 700;
            text-align: center;
        }
        .bag-group-row td {
            background-color: #faf5ff;
        }
        .bag-group-badge {
            display: inline-block;
            background-color: #6d28d9;
            color: #fff;
            font-weight: 700;
            font-size: 9.5px;
            padding: 2px 5px;
            border-radius: 4px;
            line-height: 1.2;
        }
        .bag-alert-box {
            background-color: #fef3c7;
            border: 1.5px solid #d97706;
            color: #92400e;
            font-weight: 700;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 4px;
            margin-top: 3px;
            display: inline-block;
        }
        .additive-chip {
            display: block;
            background-color: #e0f2fe;
            border: 1px solid #0284c7;
            color: #0c4a6e;
            font-weight: 700;
            font-size: 10px;
            padding: 1px 4px;
            border-radius: 3px;
            margin-top: 3px;
        }
        .header-box {
            border: 1.5px solid #0f172a;
            border-radius: 6px;
            padding: 6px 10px;
            margin-bottom: 8px;
        }
        @media print {
            body {
                background: #fff;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .a5-landscape-sheet {
                width: 100%;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

<div class="container py-2 no-print d-flex justify-content-between align-items-center" style="max-width: 210mm;">
    <div>
        <span class="badge bg-dark">ขนาดกระดาษ: A5 แนวนอน (210 &times; 148 mm)</span>
        <span class="text-secondary small ms-2">ใบจัดยาและผสมยาเคมีบำบัด (Working Formula)</span>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold">
            <i class="bi bi-printer-fill me-1"></i> สั่งพิมพ์ใบจัดยา (A5 แนวนอน)
        </button>
        <a href="{{ route('pharmacist.print.labels', $order->or_id) }}" class="btn btn-outline-dark btn-sm">
            <i class="bi bi-tags-fill me-1"></i> ไปหน้าพิมพ์ฉลากยา
        </a>
        <a href="{{ route('pharmacist.show', $order->or_id) }}" class="btn btn-outline-secondary btn-sm">
            กลับหน้าคำสั่งยา
        </a>
    </div>
</div>

<div class="a5-landscape-sheet">
    <div>
        <!-- หัวใบจัดยา Working Formula -->
        <div class="d-flex justify-content-between align-items-center mb-2 border-bottom border-2 border-dark pb-1">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ asset('images/spr.jpg') }}" alt="Logo" width="34" height="34">
                <div>
                    <div class="fw-bold fs-6 lh-1">ใบจัดยาและเตรียมยาเคมีบำบัด (CHEMOTHERAPY WORKING FORMULA)</div>
                    <small class="text-secondary">หน่วยผลิตยาปราศจากเชื้อและยาเคมีบำบัด กลุ่มงานเภสัชกรรม โรงพยาบาล</small>
                </div>
            </div>
            <div class="text-end">
                <div class="fw-bold">เลขที่คำสั่ง: <span class="text-primary">{{ $order->or_id }}</span></div>
                <small>วันที่สั่ง: {{ \Carbon\Carbon::parse($order->or_date)->format('d/m/Y') }} | สถานะ: {{ $order->or_confirm == 1 ? 'รับคำสั่งแล้ว' : 'รอรับคำสั่ง' }}</small>
            </div>
        </div>

        <!-- ข้อมูลผู้ป่วย สูตรยา และการคำนวณ BSA/GFR -->
        <div class="header-box bg-light-subtle">
            <div class="row g-1">
                <div class="col-4">
                    <strong>ชื่อ-สกุลผู้ป่วย:</strong> {{ $order->or_patname }}<br>
                    <strong>HN:</strong> <span class="fw-bold">{{ $order->or_hn }}</span> |
                    <strong>อายุ/เพศ:</strong> {{ $order->or_age ?? '-' }} ปี ({{ $order->or_sex === 'F' ? 'หญิง' : 'ชาย' }})
                </div>
                <div class="col-4">
                    <strong>หอผู้ป่วย:</strong> {{ optional($order->ward)->wd_name ?? $order->or_ward }}<br>
                    <strong>น้ำหนัก:</strong> {{ number_format($order->or_wt, 1) }} kg |
                    <strong>ส่วนสูง:</strong> {{ number_format($order->or_ht, 0) }} cm |
                    <strong>BSA:</strong> <span class="fw-bold text-decoration-underline">{{ number_format($order->or_bsa, 2) }} m&sup2;</span>
                </div>
                <div class="col-4">
                    <strong>สูตร Regimen:</strong> <span class="fw-bold">{{ $order->or_reg_code }}</span> (Cycle #{{ $order->or_cycle ?? 1 }})<br>
                    <strong>Serum Cr:</strong> {{ $order->or_scr ?? '-' }} mg/dL |
                    <strong>GFR:</strong> {{ $order->or_gfr ? number_format($order->or_gfr, 1) . ' mL/min' : '-' }}
                </div>
            </div>
            @if($order->or_adjust_percent > 0 || $order->or_adjust_reason)
                <div class="border-top border-dark-subtle mt-1 pt-1 text-danger">
                    <strong>* การปรับลดขนาดยา:</strong> ลดลง {{ number_format($order->or_adjust_percent, 0) }}%
                    &mdash; <strong>เหตุผลแพทย์:</strong> {{ $order->or_adjust_reason ?: '-' }}
                </div>
            @endif
        </div>

        <!-- ตารางรายการยาที่ผู้ป่วยได้รับ จำนวนวัน และช่วงวันที่รับยา (จัดกลุ่มตามถุงจริงป้องกันจัดสารน้ำแยกขวด) -->
        @php
            $sortedDetails = $order->details->sortBy('or_orderno')->values();
            $wfBags = collect();
            $wfBagMap = [];
            foreach ($sortedDetails as $sIdx => $sd) {
                $bKey = !empty($sd->or_bag_group)
                    ? ('BAG:' . $sd->or_bag_group)
                    : ('SINGLE:' . $sd->or_orderno . '_' . $sIdx);
                if (!isset($wfBagMap[$bKey])) {
                    $wfBagMap[$bKey] = $wfBags->count();
                    $wfBags->push((object)[
                        'bag_group' => $sd->or_bag_group,
                        'items'     => collect([$sd]),
                    ]);
                } else {
                    $wfBags[$wfBagMap[$bKey]]->items->push($sd);
                }
            }
            $hasAnyMultiBag = $wfBags->contains(fn($b) => $b->items->count() > 1);
        @endphp

        @if($hasAnyMultiBag)
            <div class="alert alert-warning py-1 px-2 mb-2 border-warning d-flex align-items-center gap-2" style="font-size: 11px;">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-6"></i>
                <div>
                    <strong>คำเตือนสำหรับพนักงานจัดยาและผู้เตรียมยา:</strong>
                    คำสั่งนี้มีรายการยาที่ <strong>ผสมรวมลงในขวด/ถุงสารน้ำเดียวกัน</strong> (ช่องสารน้ำถูกรวมเป็น 1 เซลล์)
                    กรุณาจัดสารน้ำเจือจาง <strong>เพียง 1 ขวด/ถุง</strong> ต่อกลุ่มถุงรวมตามที่ระบุ
                </div>
            </div>
        @endif

        <table class="table table-wf mb-2">
            <thead>
                <tr>
                    <th style="width: 58px;">ลำดับถุง<br>(Bag)</th>
                    <th>รายการยาเคมีบำบัด (ความเข้มข้น)</th>
                    <th style="width: 82px;">ขนาดยาที่ได้รับ<br>(Dose)</th>
                    <th style="width: 76px;">ปริมาตรเตรียม<br>(Working mL)</th>
                    <th style="width: 145px;">สารน้ำเจือจาง &amp; สารผสมในขวด</th>
                    <th style="width: 72px;">ปริมาตรรวม<br>(Total mL)</th>
                    <th style="width: 105px;">วิถีทาง / เวลาให้ยา</th>
                    <th style="width: 50px;">จำนวน<br>วัน</th>
                    <th style="width: 115px;">วันที่เริ่มยา &ndash; สิ้นสุด<br>(Start &ndash; Stop)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wfBags as $bIdx => $bag)
                    @php
                        $rowspan = $bag->items->count();
                        $isMulti = $rowspan > 1;
                        $first = $bag->items->first();
                        $daysCount = \Carbon\Carbon::parse($first->or_start)->diffInDays(\Carbon\Carbon::parse($first->or_stop)) + 1;

                        $sumWorking = (float) $bag->items->sum('or_working');
                        $allAdditives = [];
                        $sumAddVol = 0;
                        foreach ($bag->items as $bi) {
                            if (!empty($bi->or_additives) && is_array($bi->or_additives)) {
                                foreach ($bi->or_additives as $ad) {
                                    $t = trim((string) ($ad['name'] ?? ''));
                                    if (!empty($ad['dose'])) {
                                        $t .= ' ' . trim($ad['dose'] . ' ' . ($ad['unit'] ?? ''));
                                    }
                                    if (!empty($ad['vol_ml']) && (float) $ad['vol_ml'] > 0) {
                                        $t .= ' (' . $ad['vol_ml'] . ' mL)';
                                    }
                                    if ($t !== '') {
                                        $allAdditives[] = $t;
                                    }
                                    $sumAddVol += (float) ($ad['vol_ml'] ?? 0);
                                }
                            }
                        }
                        $bagTotalVol = (float) $first->or_solvol + $sumWorking + $sumAddVol;
                    @endphp

                    @foreach($bag->items as $mIdx => $d)
                        @php
                            $conc = optional($d->medication)->dg_conc ?? 1;
                        @endphp
                        <tr class="{{ $isMulti ? 'bag-group-row' : '' }}">
                            @if($mIdx === 0)
                                <td class="text-center fw-bold" rowspan="{{ $rowspan }}"
                                    style="{{ $isMulti ? 'border-left: 4px solid #6d28d9 !important;' : '' }}">
                                    <div class="fs-6">{{ $bIdx + 1 }}/{{ $wfBags->count() }}</div>
                                    @if($isMulti || !empty($bag->bag_group))
                                        <span class="bag-group-badge mt-1">รวมขวด<br>{{ $bag->bag_group }}</span>
                                        <div class="small fw-bold mt-1" style="color:#6d28d9; font-size:9px;">({{ $rowspan }} ตัวยา)</div>
                                    @endif
                                </td>
                            @endif

                            <td>
                                <div class="fw-bold">
                                    @if($isMulti && $mIdx > 0)<span style="color:#6d28d9;">+ </span>@endif
                                    [{{ $d->or_medcode }}] {{ optional($d->medication)->dg_name ?? $d->or_medcode }}
                                </div>
                                <small class="text-secondary">
                                    Conc: {{ number_format($conc, 2) }} mg/mL
                                    @if(($d->or_reduce_pct ?? 0) > 0)
                                        (ลด {{ number_format($d->or_reduce_pct, 0) }}% จาก {{ number_format($d->or_std_dose, 2) }} mg)
                                    @endif
                                </small>
                            </td>
                            <td class="text-end fw-bold fs-6">
                                {{ number_format($d->or_meddose, 2) }} {{ optional($d->unit)->un_unit ?? 'mg' }}
                            </td>
                            <td class="text-end fw-bold fs-6 bg-warning-subtle">
                                {{ number_format($d->or_working, 2) }} mL
                            </td>

                            @if($mIdx === 0)
                                <td rowspan="{{ $rowspan }}">
                                    <div class="fw-bold">
                                        {{ optional($first->solution)->so_name ?? $first->or_soln }}
                                        <span class="text-primary">{{ number_format($first->or_solvol, 0) }} mL</span>
                                    </div>
                                    @if($isMulti)
                                        <div class="bag-alert-box">
                                            <i class="bi bi-layers-fill me-1"></i>ผสมรวมในขวด/ถุงนี้ (จัด 1 ถุง)
                                        </div>
                                    @endif
                                    @foreach($allAdditives as $adText)
                                        <span class="additive-chip">+ {{ $adText }}</span>
                                    @endforeach
                                </td>
                                <td class="text-end fw-bold" rowspan="{{ $rowspan }}">
                                    <div>{{ number_format($bagTotalVol, 2) }} mL</div>
                                    @if($isMulti || $sumAddVol > 0)
                                        <small class="text-secondary fw-normal" style="font-size: 9px;">
                                            (ดูดยารวม {{ number_format($sumWorking, 2) }} mL)
                                        </small>
                                    @endif
                                </td>
                                <td rowspan="{{ $rowspan }}">
                                    {{ optional($first->route)->rt_name ?? $first->or_medroute }}<br>
                                    <small class="fw-semibold">{{ optional($first->timing)->tm_time ?? $first->or_medtime }}</small>
                                </td>
                                <td class="text-center fw-bold fs-6" rowspan="{{ $rowspan }}">
                                    {{ $daysCount }} วัน
                                </td>
                                <td class="text-center fw-semibold" rowspan="{{ $rowspan }}">
                                    {{ \Carbon\Carbon::parse($first->or_start)->format('d/m/Y') }}<br>
                                    ถึง {{ \Carbon\Carbon::parse($first->or_stop)->format('d/m/Y') }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- ส่วนท้ายใบจัดยา สำหรับลงนามตรวจสอบ -->
    <div class="border-top border-dark pt-2 mt-2">
        <div class="row text-center small">
            <div class="col-3">
                <div>แพทย์ผู้สั่งใช้ยา: <u>{{ $order->or_doctor_name ?? '....................................' }}</u></div>
                <div class="text-secondary mt-1">วันที่ {{ \Carbon\Carbon::parse($order->or_date)->format('d/m/Y') }}</div>
            </div>
            <div class="col-3">
                <div>เภสัชกรผู้รับคำสั่ง: <u>{{ $order->or_pharmacist_name ?? '....................................' }}</u></div>
                <div class="text-secondary mt-1">ผู้ตรวจสอบใบจัดยา (Check)</div>
            </div>
            <div class="col-3">
                <div>เภสัชกรผู้เตรียมยา: ....................................</div>
                <div class="text-secondary mt-1">เวลาเตรียม: ........ : ........ น.</div>
            </div>
            <div class="col-3">
                <div>ผู้ตรวจสอบซ้ำ / ส่งมอบ: ....................................</div>
                <div class="text-secondary mt-1">พิมพ์เมื่อ: {{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
