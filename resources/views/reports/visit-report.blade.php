<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานประวัติการรับการรักษา & สรุปเวลา - {{ $order->or_id }}</title>
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
            line-height: 1.4;
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
            border-bottom: 2.5px solid #1e3a8a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .timeline-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-bottom: 12px;
        }
        .timeline-step {
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 6px;
            text-align: center;
            background: #f8fafc;
        }
        .timeline-step.done {
            border-color: #0284c7;
            background: #f0f9ff;
        }
        .timeline-step.done-final {
            border-color: #16a34a;
            background: #f0fdf4;
        }
        .step-num {
            width: 24px;
            height: 24px;
            line-height: 24px;
            border-radius: 50%;
            background: #64748b;
            color: #fff;
            font-weight: 700;
            font-size: 11px;
            margin: 0 auto 4px;
        }
        .timeline-step.done .step-num { background: #0284c7; }
        .timeline-step.done-final .step-num { background: #16a34a; }
        .tat-box {
            border: 1.5px solid #1e3a8a;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 12px;
        }
        .tat-box-header {
            background: #1e3a8a;
            color: #fff;
            padding: 5px 10px;
            font-weight: 700;
            font-size: 13px;
        }
        .table-report th, .table-report td {
            border: 1px solid #cbd5e1 !important;
            padding: 4px 6px;
            vertical-align: middle;
            font-size: 11.5px;
        }
        .table-report th {
            background-color: #f1f5f9 !important;
            font-weight: 700;
        }
        @media print {
            body { background: #fff !important; margin: 0 !important; }
            .no-print { display: none !important; }
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
    $isCancelled = (int) ($order->or_cancel ?? 0) === 1;
    $fmtMin = [\App\Http\Controllers\PatientReportController::class, 'formatMinutes'];
@endphp

<!-- แถบปุ่มควบคุม (ไม่พิมพ์ออกกระดาษ) -->
<div class="container py-3 no-print" style="max-width: 210mm;">
    <div class="card border-0 shadow-sm p-3 bg-white d-flex flex-row justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="fw-bold text-primary mb-0">
                <i class="bi bi-clock-history me-2"></i>รายงานประวัติการรับการรักษาต่อครั้ง &amp; สรุปเวลา (End-to-End TAT Report)
            </h5>
            <small class="text-secondary">
                Order ID: <strong>{{ $order->or_id }}</strong> | ผู้ป่วย: <strong>{{ $order->or_patname }}</strong> (HN: {{ $order->or_hn }})
            </small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" onclick="window.print()" class="btn btn-primary fw-bold shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> พิมพ์รายงาน (A4)
            </button>
            <a href="{{ route('reports.patients.profile', $order->or_hn) }}" class="btn btn-outline-info fw-semibold">
                <i class="bi bi-person-vcard me-1"></i> Profile ผู้ป่วย
            </a>
            <a href="{{ auth()->user()->canAccessPharmacist() ? route('pharmacist.show', $order->or_id) : route('doctor.show', $order->or_id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> กลับหน้าคำสั่งยา
            </a>
        </div>
    </div>
</div>

<!-- เอกสารรายงานขนาด A4 -->
<div class="a4-sheet">
    <!-- หัวรายงาน -->
    <div class="doc-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('images/spr.jpg') }}" alt="SPR" width="46" height="46" class="rounded-circle border border-dark">
            <div>
                <div class="fw-bold fs-5 lh-1 text-dark">รายงานประวัติการรับการรักษาด้วยยาเคมีบำบัดและสรุปเวลา (PER-VISIT TREATMENT REPORT)</div>
                <div class="small text-secondary mt-1">
                    COPS&trade; Oncology System &bull; Sterile Products Pharmacy Service, SPR Hospital
                </div>
            </div>
        </div>
        <div class="text-end">
            <div class="fw-bold text-primary">Order ID: {{ $order->or_id }}</div>
            <div class="small text-secondary">วันที่เริ่มให้ยา: {{ \Carbon\Carbon::parse($order->or_date)->format('d/m/Y') }}</div>
            <div class="small text-secondary">พิมพ์เมื่อ: {{ now()->format('d/m/Y H:i น.') }}</div>
        </div>
    </div>

    @if($isCancelled)
        <div class="alert alert-danger py-2 px-3 mb-3 border-danger small">
            <strong><i class="bi bi-x-octagon-fill me-1"></i>คำสั่งใช้ยานี้ถูกยกเลิกแล้ว (CANCELLED):</strong>
            โดย {{ $order->or_cancelled_by ?? '-' }}
            เมื่อ {{ $order->or_cancelled_at ? \Carbon\Carbon::parse($order->or_cancelled_at)->format('d/m/Y H:i น.') : '-' }} |
            <strong>เหตุผล:</strong> {{ $order->or_cancel_reason ?: '-' }}
        </div>
    @endif

    <!-- ข้อมูลผู้ป่วยและค่าทางคลินิก -->
    <div class="border border-secondary rounded-2 p-2 bg-light mb-3">
        <div class="row g-2">
            <div class="col-5">
                <div class="fw-bold fs-6">{{ $order->or_patname }}</div>
                <div><strong>HN:</strong> {{ $order->or_hn }} | <strong>อายุ:</strong> {{ $order->or_age ?? '-' }} ปี ({{ $order->or_sex === 'F' ? 'หญิง' : 'ชาย' }})</div>
                <div><strong>หอผู้ป่วย:</strong> {{ optional($order->ward)->wd_name ?? $order->or_ward }}</div>
            </div>
            <div class="col-4 border-start border-end px-2">
                <div><strong>น้ำหนัก/ส่วนสูง:</strong> {{ number_format($order->or_wt, 1) }} kg / {{ number_format($order->or_ht, 0) }} cm</div>
                <div><strong>BSA:</strong> <strong class="text-primary">{{ number_format($order->or_bsa, 2) }} m&sup2;</strong></div>
                <div><strong>SCr / GFR:</strong> {{ $order->or_scr ?? '-' }} / <strong class="text-success">{{ $order->or_gfr ? number_format($order->or_gfr, 1) . ' mL/min' : '-' }}</strong></div>
            </div>
            <div class="col-3 text-end">
                <div><strong>สูตร Regimen:</strong></div>
                <div class="fw-bold text-primary fs-6">{{ $order->or_reg_code ?? 'CUSTOM' }}</div>
                <div><strong>Cycle ที่:</strong> #{{ $order->or_cycle ?? 1 }}</div>
            </div>
        </div>
    </div>

    <!-- ส่วนที่ 1: เส้นเวลาการทำงาน 5 ขั้นตอน (แพทย์ -> เภสัชกร -> พยาบาล) -->
    <div class="fw-bold text-dark mb-2">
        <i class="bi bi-diagram-3-fill text-primary me-1"></i> 1. เส้นเวลาการรักษาครบวงจร (End-to-End Clinical Workflow Timeline)
    </div>
    <div class="timeline-grid">
        <div class="timeline-step {{ $orderedAt ? 'done' : '' }}">
            <div class="step-num">1</div>
            <div class="fw-bold small">แพทย์สั่งใช้ยา</div>
            <div class="fw-bold text-primary mt-1" style="font-size: 11px;">
                {{ $orderedAt ? $orderedAt->format('d/m/Y H:i') : \Carbon\Carbon::parse($order->or_date)->format('d/m/Y') }}
            </div>
            <div class="text-secondary text-truncate" style="font-size: 10px;">{{ $order->or_doctor_name ?: '-' }}</div>
        </div>
        <div class="timeline-step {{ $confirmedAt ? 'done' : '' }}">
            <div class="step-num">2</div>
            <div class="fw-bold small">เภสัชกรรับคำสั่ง</div>
            <div class="fw-bold text-primary mt-1" style="font-size: 11px;">
                {{ $confirmedAt ? $confirmedAt->format('d/m/Y H:i') : 'รอรับคำสั่ง' }}
            </div>
            <div class="text-secondary text-truncate" style="font-size: 10px;">{{ $order->or_pharmacist_name ?: '-' }}</div>
        </div>
        <div class="timeline-step {{ $preparedAt ? 'done' : '' }}">
            <div class="step-num">3</div>
            <div class="fw-bold small">เตรียมยาเสร็จ (รอส่ง)</div>
            <div class="fw-bold text-info-emphasis mt-1" style="font-size: 11px;">
                {{ $preparedAt ? $preparedAt->format('d/m/Y H:i') : '-' }}
            </div>
            <div class="text-secondary" style="font-size: 10px;">ห้องเตรียมยาเคมีบำบัด</div>
        </div>
        <div class="timeline-step {{ $dispatchedAt ? 'done' : '' }}">
            <div class="step-num">4</div>
            <div class="fw-bold small">กำลังส่งยาไปหอฯ</div>
            <div class="fw-bold mt-1" style="font-size: 11px; color: #7c3aed;">
                {{ $dispatchedAt ? $dispatchedAt->format('d/m/Y H:i') : '-' }}
            </div>
            <div class="text-secondary text-truncate" style="font-size: 10px;">{{ $dispatchedBy ?: '-' }}</div>
        </div>
        <div class="timeline-step {{ $administeredAt ? 'done-final' : '' }}">
            <div class="step-num">5</div>
            <div class="fw-bold small">พยาบาลรับ/บริหารยา</div>
            <div class="fw-bold text-success mt-1" style="font-size: 11px;">
                {{ $administeredAt ? $administeredAt->format('d/m/Y H:i') : '-' }}
            </div>
            <div class="text-secondary text-truncate" style="font-size: 10px;">{{ $administeredBy ?: '-' }}</div>
        </div>
    </div>

    <!-- ส่วนที่ 2: ตารางสรุปเวลาการให้บริการ (Turnaround Time Summary) -->
    <div class="tat-box">
        <div class="tat-box-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-stopwatch-fill me-1"></i> 2. สรุประยะเวลาในการเตรียมและให้ยา (Turnaround Time — TAT Summary)</span>
            <span class="badge bg-warning text-dark">
                เวลารวม (แพทย์สั่ง &rarr; พยาบาลบริหารยา): {{ $fmtMin($overallTat['total_end_to_end']) }}
            </span>
        </div>
        <div class="p-2">
            <table class="table table-report mb-2 text-center">
                <thead>
                    <tr>
                        <th>แพทย์สั่ง &rarr; เภสัชกรรับคำสั่ง</th>
                        <th>รับคำสั่ง &rarr; เตรียมยาเสร็จ</th>
                        <th>เตรียมยาเสร็จ &rarr; ส่งยาไปหอฯ</th>
                        <th>ส่งยาไปหอฯ &rarr; พยาบาลบริหารยา</th>
                        <th class="bg-warning-subtle text-dark">รวมทั้งหมด (แพทย์ &rarr; พยาบาล)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="fw-bold fs-6">
                        <td class="text-primary">{{ $fmtMin($overallTat['order_to_confirm']) }}</td>
                        <td class="text-info-emphasis">{{ $fmtMin($overallTat['confirm_to_prep']) }}</td>
                        <td style="color:#7c3aed;">{{ $fmtMin($overallTat['prep_to_dispatch']) }}</td>
                        <td class="text-success">{{ $fmtMin($overallTat['dispatch_to_admin']) }}</td>
                        <td class="bg-warning-subtle text-danger">{{ $fmtMin($overallTat['total_end_to_end']) }}</td>
                    </tr>
                </tbody>
            </table>

            @if($dailyTimelines->isNotEmpty())
                <div class="small fw-bold text-secondary mb-1">รายละเอียดเวลาแยกรายวัน (Daily Administration Breakdown):</div>
                <table class="table table-report mb-0">
                    <thead class="text-center">
                        <tr>
                            <th>วันที่ให้ยา</th>
                            <th>Day</th>
                            <th>จำนวนโดส</th>
                            <th>เตรียมเสร็จ</th>
                            <th>ส่งยาไปหอฯ</th>
                            <th>พยาบาลบริหารยา</th>
                            <th>ผู้บริหารยา</th>
                            <th>เวลาเตรียม &rarr; บริหารยา</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyTimelines as $dt)
                            <tr class="text-center">
                                <td class="fw-bold">{{ \Carbon\Carbon::parse($dt->date)->format('d/m/Y') }}</td>
                                <td>{{ $dt->day_label }}</td>
                                <td>{{ $dt->doses_count }}</td>
                                <td>{{ $dt->prepared_at ? $dt->prepared_at->format('H:i น.') : '-' }}</td>
                                <td>{{ $dt->dispatched_at ? $dt->dispatched_at->format('H:i น.') : '-' }}</td>
                                <td class="fw-bold text-success">{{ $dt->administered_at ? $dt->administered_at->format('H:i น.') : '-' }}</td>
                                <td class="text-start">{{ $dt->administered_by ?: '-' }}</td>
                                <td class="fw-bold text-primary">{{ $fmtMin($dt->prep_to_admin) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <!-- ส่วนที่ 3: รายการยาเคมีบำบัดที่ได้รับในการรักษาครั้งนี้ -->
    <div class="fw-bold text-dark mb-1">
        <i class="bi bi-prescription2 text-danger me-1"></i> 3. รายการยาเคมีบำบัดที่ได้รับในการรักษาครั้งนี้ ({{ $order->details->count() }} ตัวยา)
    </div>
    <table class="table table-report mb-3">
        <thead class="text-center">
            <tr>
                <th style="width: 30px;">#</th>
                <th>ชื่อยาเคมีบำบัด / ถุงรวม</th>
                <th class="text-end">ขนาดยาสุทธิ</th>
                <th class="text-center">ปรับลด</th>
                <th>สารน้ำ &amp; สารผสมในขวด</th>
                <th>วิถีทาง / เวลาให้</th>
                <th class="text-center">ช่วงวันที่</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->details->sortBy('or_orderno') as $d)
                <tr>
                    <td class="text-center fw-bold">{{ $d->or_orderno }}</td>
                    <td>
                        <span class="fw-bold text-dark">[{{ $d->or_medcode }}] {{ optional($d->medication)->dg_name ?? $d->or_medcode }}</span>
                        @if($d->or_bag_group)
                            <span class="badge text-white ms-1" style="background-color:#7c3aed; font-size:9.5px;">รวม {{ $d->or_bag_group }}</span>
                        @endif
                    </td>
                    <td class="text-end fw-bold text-danger">
                        {{ number_format($d->or_meddose, 2) }} {{ optional($d->unit)->un_unit ?? 'mg' }}
                    </td>
                    <td class="text-center">
                        {{ ($d->or_reduce_pct ?? 0) > 0 ? ('-' . number_format($d->or_reduce_pct, 0) . '%') : '0%' }}
                    </td>
                    <td>
                        {{ $d->or_soln }} {{ number_format($d->or_solvol, 0) }} mL
                        <span class="text-secondary">(รวม {{ number_format($d->or_totalvol, 1) }} mL)</span>
                        @if(!empty($d->or_additives) && is_array($d->or_additives))
                            <div class="text-primary fw-semibold" style="font-size: 10.5px;">
                                + {{ collect($d->or_additives)->map(fn($a) => trim(($a['name'] ?? '') . ' ' . ($a['dose'] ?? '') . ' ' . ($a['unit'] ?? '')))->implode(', ') }}
                            </div>
                        @endif
                    </td>
                    <td>
                        {{ optional($d->route)->rt_name ?? $d->or_medroute }} /
                        {{ optional($d->timing)->tm_time ?? $d->or_medtime }}
                    </td>
                    <td class="text-center text-nowrap">
                        {{ \Carbon\Carbon::parse($d->or_start)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($d->or_stop)->format('d/m/Y') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- ส่วนที่ 4: สรุปยาสนับสนุนที่ได้รับร่วมด้วย -->
    @if($order->supportiveMeds->isNotEmpty())
        @php
            $catLabels = [
                'pre_med_antiemetic'       => 'Pre-med (Antiemetics)',
                'pre_med_hypersensitivity' => 'Pre-med (Anti-HSR)',
                'pre_hydration'            => 'Pre-hydration',
                'post_hydration'           => 'Post-hydration',
                'post_medication'          => 'Post-medication',
                'home_med'                 => 'Home-medication',
            ];
            $groupedSup = $order->supportiveMeds->groupBy('category');
        @endphp
        <div class="fw-bold text-dark mb-1">
            <i class="bi bi-shield-plus text-info me-1"></i> 4. รายการยาสนับสนุนและสารน้ำร่วมรักษา ({{ $order->supportiveMeds->count() }} รายการ)
        </div>
        <div class="border rounded-2 p-2 mb-3 bg-light" style="font-size: 11px;">
            @foreach($catLabels as $catKey => $catTitle)
                @if($groupedSup->has($catKey))
                    <div class="mb-1">
                        <strong>{{ $catTitle }}:</strong>
                        {{ $groupedSup[$catKey]->map(fn($s) => $s->sm_name . ' ' . $s->dose . ($s->route ? ' (' . $s->route . ')' : ''))->implode(' | ') }}
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <!-- ส่วนที่ 5: บันทึกการปรับลด / Intervention Note -->
    @if($order->or_adjust_percent > 0 || $order->or_adjust_reason)
        <div class="border border-warning rounded-2 p-2 bg-warning-subtle small">
            <strong><i class="bi bi-chat-left-text-fill me-1"></i>บันทึกทางคลินิก / การปรับลดขนาดยา / การแก้ไขโดยเภสัชกร:</strong>
            {{ $order->or_adjust_reason ?: ('ปรับลดขนาดยา ' . number_format($order->or_adjust_percent, 0) . '%') }}
        </div>
    @endif
</div>
</body>
</html>
