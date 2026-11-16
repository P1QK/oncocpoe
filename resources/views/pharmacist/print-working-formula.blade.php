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

        <!-- ตารางรายการยาที่ผู้ป่วยได้รับ จำนวนวัน และช่วงวันที่รับยา -->
        <table class="table table-wf mb-2">
            <thead>
                <tr>
                    <th style="width: 28px;">#</th>
                    <th>รายการยาเคมีบำบัด (ความเข้มข้น)</th>
                    <th style="width: 85px;">ขนาดยาที่ได้รับ<br>(Dose)</th>
                    <th style="width: 78px;">ปริมาตรดูดยา<br>(Working mL)</th>
                    <th style="width: 120px;">สารน้ำเจือจาง &amp; ปริมาตร</th>
                    <th style="width: 72px;">ปริมาตรรวม<br>(Total mL)</th>
                    <th style="width: 110px;">วิถีทาง / เวลาให้ยา</th>
                    <th style="width: 55px;">จำนวนวัน<br>ที่รับยา</th>
                    <th style="width: 125px;">วันที่เริ่มยา &ndash; สิ้นสุด<br>(Start &ndash; Stop)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->details as $d)
                    @php
                        $daysCount = \Carbon\Carbon::parse($d->or_start)->diffInDays(\Carbon\Carbon::parse($d->or_stop)) + 1;
                        $conc = optional($d->medication)->dg_conc ?? 1;
                    @endphp
                    <tr>
                        <td class="text-center fw-bold">{{ $d->or_orderno }}</td>
                        <td>
                            <div class="fw-bold">[{{ $d->or_medcode }}] {{ optional($d->medication)->dg_name ?? $d->or_medcode }}</div>
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
                        <td>
                            {{ optional($d->solution)->so_name ?? $d->or_soln }}<br>
                            <strong>{{ number_format($d->or_solvol, 0) }} mL</strong>
                        </td>
                        <td class="text-end fw-semibold">
                            {{ number_format($d->or_totalvol, 2) }} mL
                        </td>
                        <td>
                            {{ optional($d->route)->rt_name ?? $d->or_medroute }}<br>
                            <small class="fw-semibold">{{ optional($d->timing)->tm_time ?? $d->or_medtime }}</small>
                        </td>
                        <td class="text-center fw-bold fs-6">
                            {{ $daysCount }} วัน
                        </td>
                        <td class="text-center fw-semibold">
                            {{ \Carbon\Carbon::parse($d->or_start)->format('d/m/Y') }}<br>
                            ถึง {{ \Carbon\Carbon::parse($d->or_stop)->format('d/m/Y') }}
                        </td>
                    </tr>
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
