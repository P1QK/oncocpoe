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
            font-size: 9.5px;
        }
        .labels-grid {
            max-width: 19cm;
            margin: 15px auto;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-start;
        }
        /* กำหนดขนาดสติกเกอร์ทุกดวง: กว้าง 9 cm x สูง 7 cm */
        .chemo-label-card {
            width: 9cm;
            height: 7cm;
            min-width: 9cm;
            max-width: 9cm;
            min-height: 7cm;
            max-height: 7cm;
            box-sizing: border-box;
            background: #fff;
            border: 1.8px solid #0f172a;
            border-radius: 6px;
            padding: 5px 7px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            font-size: 9.5px;
            line-height: 1.22;
            page-break-inside: avoid;
            break-inside: avoid;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .summary-label-card {
            background: #f8fafc;
            border: 2px solid #1e3a8a;
        }
        .chemo-banner {
            background-color: #0f172a;
            color: #fff;
            padding: 2.5px 6px;
            border-radius: 3px;
            font-weight: 700;
            font-size: 9.5px;
            line-height: 1.2;
            flex-shrink: 0;
        }
        .summary-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #0284c7 100%);
            color: #fff;
            padding: 2.5px 6px;
            border-radius: 3px;
            font-weight: 700;
            font-size: 9.5px;
            line-height: 1.2;
            flex-shrink: 0;
        }
        .sticker-patient-box {
            font-size: 9.5px;
            line-height: 1.2;
            padding-bottom: 3px;
            margin-bottom: 3px;
            border-bottom: 1px solid #cbd5e1;
            flex-shrink: 0;
        }
        .sticker-patname {
            font-size: 11px;
            font-weight: 700;
        }
        .drug-box {
            border: 1.5px solid #dc2626;
            background-color: #fff5f5;
            border-radius: 4px;
            padding: 4px 6px;
            margin: 2px 0;
            font-size: 9.5px;
            line-height: 1.25;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .drug-title {
            font-size: 11px;
            font-weight: 700;
        }
        .drug-dose {
            font-size: 12px;
            font-weight: 700;
        }
        .precaution-line {
            font-size: 8.5px;
            line-height: 1.15;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-shrink: 0;
        }
        .summary-body-box {
            flex-grow: 1;
            overflow: hidden;
        }
        .summary-table th, .summary-table td {
            padding: 1.5px 3px !important;
            font-size: 8.2px;
            line-height: 1.15;
            border-color: #cbd5e1 !important;
        }
        .sticker-footer {
            font-size: 8.5px;
            line-height: 1.15;
            padding-top: 3px;
            border-top: 1px solid #cbd5e1;
            flex-shrink: 0;
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
                gap: 4mm;
            }
            .chemo-label-card {
                width: 9cm !important;
                height: 7cm !important;
                min-width: 9cm !important;
                max-width: 9cm !important;
                min-height: 7cm !important;
                max-height: 7cm !important;
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
        <!-- 1. สติกเกอร์สรุปรายการยาของคนไข้ประจำวันนั้น (ขนาด 9 cm x 7 cm) -->
        <div class="chemo-label-card summary-label-card">
            <div class="d-flex justify-content-between align-items-center summary-banner mb-1">
                <span><i class="bi bi-card-checklist me-1"></i> สรุปรายการยาประจำวัน</span>
                <span>วันที่: {{ \Carbon\Carbon::parse($dateStr)->format('d/m/Y') }}</span>
            </div>

            <div class="d-flex justify-content-between sticker-patient-box">
                <div class="text-truncate pe-1">
                    <div class="sticker-patname text-truncate">{{ $order->or_patname }}</div>
                    <div>
                        <strong>HN:</strong> {{ $order->or_hn }} |
                        <strong>Ward:</strong> {{ optional($order->ward)->wd_name ?? $order->or_ward }} |
                        <strong>BSA:</strong> {{ number_format($order->or_bsa, 2) }}
                    </div>
                </div>
                <div class="text-end text-nowrap">
                    <div><strong>ID:</strong> {{ $order->or_id }}</div>
                    <div><strong>Reg:</strong> <span class="fw-bold">{{ $order->or_reg_code }}</span></div>
                </div>
            </div>

            <div class="bg-white border border-primary rounded-1 p-1 summary-body-box">
                <div class="fw-bold text-primary mb-1 d-flex justify-content-between align-items-center" style="font-size: 8.5px;">
                    <span>รายการยาที่ได้รับวันนี้:</span>
                    <span class="badge bg-primary" style="font-size: 7.5px;">รวม {{ $dayItems->count() }} รายการ</span>
                </div>
                <table class="table table-bordered summary-table mb-0">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 30px;">ลำดับ</th>
                            <th>ชื่อยา</th>
                            <th>ขนาด</th>
                            <th>สารน้ำ/รวม</th>
                            <th>วิธี/เวลา</th>
                            <th>Day</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dayItems->sortBy('or_orderno')->values() as $dIdx => $dItem)
                            <tr>
                                <td class="text-center fw-bold text-primary text-nowrap">{{ $dIdx + 1 }}/{{ $dayItems->count() }}</td>
                                <td class="fw-bold text-danger text-truncate" style="max-width: 70px;">
                                    {{ optional($dItem->medication)->dg_name ?? $dItem->or_medcode }}
                                </td>
                                <td class="text-end fw-bold text-nowrap">
                                    {{ number_format($dItem->or_meddose, 1) }} {{ optional($dItem->unit)->un_unit ?? 'mg' }}
                                </td>
                                <td class="text-nowrap">
                                    {{ $dItem->or_soln }} ({{ number_format($dItem->or_totalvol, 0) }}mL)
                                </td>
                                <td class="text-truncate" style="max-width: 65px;">
                                    {{ optional($dItem->timing)->tm_time ?? $dItem->or_medtime }}
                                </td>
                                <td class="text-center text-nowrap">
                                    {{ $dItem->or_text ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between sticker-footer text-secondary">
                <span>ผู้ส่ง: .........................</span>
                <span>ผู้รับ: .........................</span>
                <span>เวลา: .......... น.</span>
            </div>
        </div>

        <!-- 2. สติกเกอร์ยารายขวด/ถุง ของคนไข้ในวันนั้น (ขนาด 9 cm x 7 cm) -->
        @foreach($dayItems->sortBy('or_orderno')->values() as $itemIdx => $lbl)
            @php
                $seqText = ($itemIdx + 1) . '/' . $dayItems->count();
            @endphp
            <div class="chemo-label-card">
                <div class="d-flex justify-content-between align-items-center chemo-banner mb-1">
                    <span><i class="bi bi-radioactive me-1"></i> ลำดับการให้ยา: {{ $seqText }}</span>
                    <span>{{ \Carbon\Carbon::parse($lbl->or_daterec)->format('d/m/Y') }} ({{ $lbl->or_text }})</span>
                </div>

                <div class="d-flex justify-content-between sticker-patient-box">
                    <div class="text-truncate pe-1">
                        <div class="sticker-patname text-truncate">{{ $order->or_patname }}</div>
                        <div><strong>HN:</strong> {{ $order->or_hn }} | <strong>หอผู้ป่วย:</strong> {{ optional($order->ward)->wd_name ?? $order->or_ward }}</div>
                    </div>
                    <div class="text-end text-nowrap">
                        <div><strong>ID:</strong> {{ $order->or_id }}</div>
                        <div><strong>Reg:</strong> {{ $order->or_reg_code }}</div>
                    </div>
                </div>

                <div class="drug-box">
                    <div class="d-flex justify-content-between align-items-baseline">
                        <div class="drug-title text-danger text-truncate pe-1">
                            <span class="badge bg-danger me-1" style="font-size: 9px;">{{ $seqText }}</span>{{ optional($lbl->medication)->dg_name ?? $lbl->or_medcode }}
                        </div>
                        <div class="drug-dose text-dark text-nowrap">
                            {{ number_format($lbl->or_meddose, 2) }} {{ optional($lbl->unit)->un_unit ?? 'mg' }}
                        </div>
                    </div>
                    <div class="mt-1">
                        <strong>สารน้ำ:</strong> {{ optional($lbl->solution)->so_name ?? $lbl->or_soln }}
                        <strong>{{ number_format($lbl->or_solvol, 0) }} mL</strong>
                        (ดูดยา {{ number_format($lbl->or_working, 2) }} mL | รวม <strong>{{ number_format($lbl->or_totalvol, 2) }} mL</strong>)
                    </div>
                    <div class="mt-1">
                        <strong>วิธีให้:</strong> {{ optional($lbl->route)->rt_name ?? $lbl->or_medroute }}
                        &bull; <strong>เวลา:</strong> <span class="text-decoration-underline fw-bold">{{ optional($lbl->timing)->tm_time ?? $lbl->or_medtime }}</span>
                    </div>
                </div>

                @if(optional($lbl->medication)->dg_precaution)
                    <div class="precaution-line text-danger my-1">
                        <strong>ระวัง:</strong> {{ optional($lbl->medication)->dg_precaution }}
                    </div>
                @endif

                <div class="d-flex justify-content-between sticker-footer text-secondary">
                    <span>ผู้เตรียม: .......................</span>
                    <span>ผู้ตรวจ: .......................</span>
                    <span>เวลา: .......... น.</span>
                </div>
            </div>
        @endforeach
    @endforeach
</div>

</body>
</html>
