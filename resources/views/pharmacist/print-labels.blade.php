<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ฉลากยาเคมีบำบัด (Medication Labels) - {{ $order->or_id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #e2e8f0;
            color: #000;
            font-size: 12px;
        }
        .labels-grid {
            max-width: 210mm;
            margin: 15px auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .chemo-label-card {
            background: #fff;
            border: 2px solid #0f172a;
            border-radius: 8px;
            padding: 10px 12px;
            page-break-inside: avoid;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .summary-label-card {
            background: #f8fafc;
            border: 2.5px solid #1e3a8a;
        }
        .chemo-banner {
            background-color: #0f172a;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 11px;
        }
        .summary-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 11px;
        }
        .drug-box {
            border: 1.5px solid #dc2626;
            background-color: #fff5f5;
            border-radius: 6px;
            padding: 6px 8px;
            margin: 6px 0;
        }
        .summary-table th, .summary-table td {
            padding: 2px 5px;
            font-size: 11px;
            border-color: #cbd5e1 !important;
        }
        @media print {
            body {
                background: #fff;
                margin: 0;
            }
            .no-print {
                display: none !important;
            }
            .labels-grid {
                max-width: 100%;
                margin: 0;
            }
            .chemo-label-card {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

@php
    $labelsByDate = $labels->groupBy('or_daterec');
    $totalStickersWithSummary = $labels->count() + $labelsByDate->count();
@endphp

<div class="container py-3 no-print" style="max-width: 210mm;">
    <div class="card border-0 shadow-sm p-3 bg-white">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1"><i class="bi bi-tags-fill text-primary me-2"></i>พิมพ์ฉลากยาเคมีบำบัด + สติกเกอร์สรุปรายการยาประจำวัน</h5>
                <small class="text-secondary">
                    ผู้ป่วย: <strong>{{ $order->or_patname }}</strong> (HN: {{ $order->or_hn }}) |
                    รวมทั้งหมด: <strong>{{ $totalStickersWithSummary }} ดวง</strong> (สติกเกอร์สรุป {{ $labelsByDate->count() }} ดวง + ฉลากยา {{ $labels->count() }} ดวง)
                </small>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                @if($availableDates->count() > 1)
                    <form method="GET" action="{{ route('pharmacist.print.labels', $order->or_id) }}" class="d-flex gap-1 align-items-center">
                        <select name="date" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- พิมพ์ทุกวัน ({{ $availableDates->count() }} วัน) --</option>
                            @foreach($availableDates as $dStr)
                                <option value="{{ $dStr }}" {{ $filterDate === $dStr ? 'selected' : '' }}>
                                    เฉพาะวันที่ {{ \Carbon\Carbon::parse($dStr)->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif

                <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold">
                    <i class="bi bi-printer-fill me-1"></i> สั่งพิมพ์ทั้งหมด ({{ $totalStickersWithSummary }} ดวง)
                </button>
                <a href="{{ route('pharmacist.print.working-formula', $order->or_id) }}" class="btn btn-outline-dark btn-sm">
                    <i class="bi bi-file-earmark-medical me-1"></i> ใบจัดยา (A5)
                </a>
                <a href="{{ route('pharmacist.show', $order->or_id) }}" class="btn btn-outline-secondary btn-sm">
                    กลับหน้าคำสั่งยา
                </a>
            </div>
        </div>
    </div>
</div>

<div class="labels-grid">
    @foreach($labelsByDate as $dateStr => $dayItems)
        <!-- 1. สติกเกอร์สรุปรายการยาของคนไข้ประจำวันนั้น (1 ดวง) -->
        <div class="chemo-label-card summary-label-card">
            <div class="d-flex justify-content-between align-items-center summary-banner mb-1">
                <span><i class="bi bi-card-checklist me-1"></i> สติกเกอร์สรุปรายการยาประจำวัน (DAILY SUMMARY)</span>
                <span>วันที่ให้ยา: {{ \Carbon\Carbon::parse($dateStr)->format('d/m/Y') }}</span>
            </div>

            <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                <div>
                    <div class="fw-bold fs-6">{{ $order->or_patname }}</div>
                    <div>
                        <strong>HN:</strong> {{ $order->or_hn }} |
                        <strong>หอผู้ป่วย:</strong> {{ optional($order->ward)->wd_name ?? $order->or_ward }} |
                        <strong>BSA:</strong> {{ number_format($order->or_bsa, 2) }} m&sup2;
                    </div>
                </div>
                <div class="text-end small">
                    <div><strong>Order:</strong> {{ $order->or_id }}</div>
                    <div><strong>Regimen:</strong> <span class="fw-bold">{{ $order->or_reg_code }}</span></div>
                </div>
            </div>

            <div class="bg-white border border-primary rounded-2 p-1 my-1">
                <div class="small fw-bold text-primary mb-1 px-1 d-flex justify-content-between">
                    <span>รายการยาที่ได้รับในวันนี้ทั้งหมด:</span>
                    <span class="badge bg-primary">รวม {{ $dayItems->count() }} รายการ</span>
                </div>
                <table class="table table-bordered summary-table mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 22px;">#</th>
                            <th>ชื่อยาเคมีบำบัด</th>
                            <th>ขนาดยา</th>
                            <th>สารน้ำ / ปริมาตรรวม</th>
                            <th>วิธีให้ / เวลา</th>
                            <th>เริ่ม &ndash; สิ้นสุด (Start &ndash; Stop)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dayItems as $dItem)
                            <tr>
                                <td class="text-center fw-bold">{{ $dItem->or_orderno }}</td>
                                <td class="fw-bold text-danger">
                                    {{ optional($dItem->medication)->dg_name ?? $dItem->or_medcode }}
                                </td>
                                <td class="text-end fw-bold">
                                    {{ number_format($dItem->or_meddose, 2) }} {{ optional($dItem->unit)->un_unit ?? 'mg' }}
                                </td>
                                <td>
                                    {{ $dItem->or_soln }} ({{ number_format($dItem->or_solvol, 0) }} mL)
                                    <small class="text-muted">รวม {{ number_format($dItem->or_totalvol, 1) }} mL</small>
                                </td>
                                <td>
                                    {{ optional($dItem->route)->rt_name ?? $dItem->or_medroute }} /
                                    {{ optional($dItem->timing)->tm_time ?? $dItem->or_medtime }}
                                </td>
                                <td class="text-center fw-semibold text-nowrap">
                                    {{ $dItem->or_start ? \Carbon\Carbon::parse($dItem->or_start)->format('d/m/y') : '-' }}
                                    &ndash;
                                    {{ $dItem->or_stop ? \Carbon\Carbon::parse($dItem->or_stop)->format('d/m/y') : '-' }}
                                    @if($dItem->or_text)
                                        <span class="badge bg-light text-dark border ms-1">{{ $dItem->or_text }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between border-top pt-1 mt-1 small text-secondary">
                <span>เภสัชกรผู้ส่งมอบ: ..............................</span>
                <span>พยาบาลผู้รับยา: ..............................</span>
                <span>เวลา: ............. น.</span>
            </div>
        </div>

        <!-- 2. สติกเกอร์ยารายขวด/ถุง ของคนไข้ในวันนั้น -->
        @foreach($dayItems->values() as $itemIdx => $lbl)
            <div class="chemo-label-card">
                <div class="d-flex justify-content-between align-items-center chemo-banner mb-1">
                    <span><i class="bi bi-radioactive me-1"></i> ยาเคมีบำบัด (ขวดที่ {{ $itemIdx + 1 }}/{{ $dayItems->count() }})</span>
                    <span>วันที่ให้ยา: {{ \Carbon\Carbon::parse($lbl->or_daterec)->format('d/m/Y') }} ({{ $lbl->or_text }})</span>
                </div>

                <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                    <div>
                        <div class="fw-bold fs-6">{{ $order->or_patname }}</div>
                        <div><strong>HN:</strong> {{ $order->or_hn }} | <strong>หอผู้ป่วย:</strong> {{ optional($order->ward)->wd_name ?? $order->or_ward }}</div>
                    </div>
                    <div class="text-end small">
                        <div><strong>Order:</strong> {{ $order->or_id }}</div>
                        <div><strong>Regimen:</strong> {{ $order->or_reg_code }}</div>
                    </div>
                </div>

                <div class="drug-box">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <div class="fw-bold fs-6 text-danger">
                            {{ $lbl->or_orderno }}. {{ optional($lbl->medication)->dg_name ?? $lbl->or_medcode }}
                        </div>
                        <div class="fw-bold fs-5 text-dark">
                            {{ number_format($lbl->or_meddose, 2) }} {{ optional($lbl->unit)->un_unit ?? 'mg' }}
                        </div>
                    </div>
                    <div class="mt-1">
                        <strong>ผสมในสารน้ำ:</strong> {{ optional($lbl->solution)->so_name ?? $lbl->or_soln }}
                        <strong>{{ number_format($lbl->or_solvol, 0) }} mL</strong>
                        (ดูดยา {{ number_format($lbl->or_working, 2) }} mL | รวม <strong>{{ number_format($lbl->or_totalvol, 2) }} mL</strong>)
                    </div>
                    <div>
                        <strong>วิธีบริหารยา:</strong> {{ optional($lbl->route)->rt_name ?? $lbl->or_medroute }}
                        &bull; <strong>อัตราเร็ว/เวลา:</strong> <span class="text-decoration-underline fw-bold">{{ optional($lbl->timing)->tm_time ?? $lbl->or_medtime }}</span>
                    </div>
                </div>

                @if(optional($lbl->medication)->dg_precaution)
                    <div class="small text-danger mb-1">
                        <strong>ข้อควรระวัง:</strong> {{ optional($lbl->medication)->dg_precaution }}
                    </div>
                @endif

                <div class="d-flex justify-content-between border-top pt-1 mt-1 small text-secondary">
                    <span>ผู้เตรียม: ..............................</span>
                    <span>ผู้ตรวจ: ..............................</span>
                    <span>เวลาเตรียม: ............. น.</span>
                </div>
            </div>
        @endforeach
    @endforeach
</div>

</body>
</html>
