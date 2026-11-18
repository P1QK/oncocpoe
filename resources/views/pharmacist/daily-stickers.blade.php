@extends('layouts.app')

@section('title', 'พิมพ์สติกเกอร์ยาเคมีบำบัดตามวันที่ให้ยา (รวมผู้ป่วยทุกราย)')

@section('css_before')
<style>
    .labels-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        justify-content: flex-start;
    }
    .sticker-unit {
        width: 9cm;
        transition: opacity 0.2s ease;
        page-break-inside: avoid;
        break-inside: avoid;
    }
    .sticker-unit.excluded-from-print {
        opacity: 0.38;
    }
    .sticker-unit.excluded-from-print .chemo-label-card {
        border-style: dashed;
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
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
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
            background: #fff !important;
            margin: 0 !important;
        }
        nav.navbar, footer, .no-print {
            display: none !important;
        }
        main.container {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        .labels-grid {
            gap: 4mm;
        }
        .sticker-unit {
            width: 9cm !important;
            height: 7cm !important;
        }
        .chemo-label-card {
            width: 9cm !important;
            height: 7cm !important;
            min-width: 9cm !important;
            max-width: 9cm !important;
            min-height: 7cm !important;
            max-height: 7cm !important;
            box-shadow: none !important;
        }
        .sticker-unit.excluded-from-print {
            display: none !important;
        }
    }
</style>
@endsection

@section('content')
@php
    $displayPatientsGroup = $selectedOrId
        ? $patientsGroup->where('or_id', $selectedOrId)
        : $patientsGroup;

    $totalAllStickersCount = $patientsGroup->sum('stickers_count');
    $visibleStickersCount  = $displayPatientsGroup->sum('stickers_count');
@endphp

<div class="no-print">
    <!-- หัวข้อหน้า -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h4 class="fw-bold text-success mb-1">
                <i class="bi bi-tags-fill me-2"></i>พิมพ์สติกเกอร์ยาเคมีบำบัดประจำวัน (รวมคนไข้ทุกรายตามวันที่ให้ยา)
            </h4>
            <small class="text-secondary">
                พิมพ์สติกเกอร์ยารายขวด พร้อม <strong>สติกเกอร์สรุปรายการยาประจำวันของคนไข้ (+1 ดวงต่อคนไข้)</strong> สามารถเลือกสั่งพิมพ์ <strong>ทั้งหมดทุกคน</strong> หรือ <strong>รายคนไข้</strong> ได้ทันที
            </small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pharmacist.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> กลับหน้าหลักเภสัชกร
            </a>
        </div>
    </div>

    <!-- แผงเลือกวันที่ และ เลือกคนไข้ -->
    <div class="card card-medical bg-white mb-4 border-top border-4 border-success">
        <div class="card-body">
            <form method="GET" action="{{ route('pharmacist.stickers') }}" id="filterStickerForm" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold text-dark small">
                        <i class="bi bi-calendar-date text-success me-1"></i>1. เลือกวันที่ให้ยา (Administration Date):
                    </label>
                    <input type="date" name="date" class="form-control form-control-lg border-success fw-bold"
                        value="{{ $selectedDate }}" onchange="this.form.submit()">
                </div>

                <div class="col-md-5">
                    <label class="form-label fw-bold text-dark small">
                        <i class="bi bi-person-lines-fill text-primary me-1"></i>2. เลือกโหมดแสดงผล (คนไข้ทั้งหมด / รายคนไข้):
                    </label>
                    <select name="or_id" class="form-select form-select-lg border-primary fw-semibold" onchange="this.form.submit()">
                        <option value="">
                            -- แสดงคนไข้ทุกรายในวันที่เลือก ({{ $patientsGroup->count() }} ราย / รวม {{ $totalAllStickersCount }} ดวง) --
                        </option>
                        @foreach($patientsGroup as $pat)
                            <option value="{{ $pat->or_id }}" {{ $selectedOrId === $pat->or_id ? 'selected' : '' }}>
                                [HN: {{ $pat->or_hn }}] {{ $pat->or_patname }} &mdash; ยา {{ $pat->doses_count }} ขวด + สรุป 1 ดวง (รวม {{ $pat->stickers_count }} ดวง)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="button" onclick="printAllVisible()" class="btn btn-primary btn-lg fw-bold w-100 shadow-sm" {{ $displayPatientsGroup->isEmpty() ? 'disabled' : '' }}>
                        <i class="bi bi-printer-fill me-1"></i>
                        {{ $selectedOrId ? 'พิมพ์สติกเกอร์คนไข้ที่เลือก' : 'พิมพ์สติกเกอร์ทั้งหมดทุกคน' }}
                        (<span id="selectedStickerCount">{{ $visibleStickersCount }}</span> ดวง)
                    </button>
                </div>
            </form>

            <!-- ปุ่มลัดเลือกวันที่ที่มีคิวให้ยา -->
            @if($activeDates->isNotEmpty())
                <div class="mt-3 pt-2 border-top d-flex flex-wrap align-items-center gap-1">
                    <span class="small fw-semibold text-secondary me-2">
                        <i class="bi bi-calendar2-check me-1"></i>วันที่ที่มีรายการให้ยาเคมีบำบัด:
                    </span>
                    @foreach($activeDates as $dStr)
                        <a href="{{ route('pharmacist.stickers', ['date' => $dStr]) }}"
                           class="btn btn-sm {{ $selectedDate === $dStr ? 'btn-success fw-bold' : 'btn-outline-secondary' }}">
                            {{ \Carbon\Carbon::parse($dStr)->format('d/m/Y') }}
                            @if($dStr === today()->format('Y-m-d'))
                                <span class="badge bg-warning text-dark ms-1">วันนี้</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- ตารางรายชื่อคนไข้ที่ได้รับยาในวันที่เลือก พร้อมปุ่มสั่งพิมพ์รายคนไข้ทันที -->
    @if($patientsGroup->isNotEmpty())
        <div class="card card-medical bg-white mb-4">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="fw-bold text-dark">
                    <i class="bi bi-people-fill text-primary me-1"></i>
                    รายชื่อคนไข้ที่มีรายการให้ยาในวันที่ <span class="text-success">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</span>
                    (ทั้งหมด {{ $patientsGroup->count() }} ราย / รวมทั้งหมด {{ $totalAllStickersCount }} ดวง)
                </span>
                <div class="d-flex gap-2">
                    @if($selectedOrId)
                        <a href="{{ route('pharmacist.stickers', ['date' => $selectedDate]) }}" class="btn btn-sm btn-outline-dark fw-semibold">
                            <i class="bi bi-eye-fill me-1"></i> แสดงคนไข้ทุกรายรวมกัน
                        </a>
                    @endif
                    <button type="button" class="btn btn-sm btn-success fw-bold" onclick="printAllPatientsNow()">
                        <i class="bi bi-printer-fill me-1"></i> พิมพ์สติกเกอร์ทั้งหมดทุกคน ({{ $totalAllStickersCount }} ดวง)
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-3">เลขที่คำสั่ง (Order ID)</th>
                            <th>HN / ชื่อ-สกุลผู้ป่วย</th>
                            <th>หอผู้ป่วย</th>
                            <th>สูตร Regimen</th>
                            <th class="text-center">สถานะคำสั่ง</th>
                            <th class="text-center">จำนวนสติกเกอร์วันนี้</th>
                            <th class="text-end pe-3">สั่งพิมพ์รายคนไข้ / จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patientsGroup as $pat)
                            <tr class="{{ $selectedOrId === $pat->or_id ? 'table-primary' : '' }}">
                                <td class="ps-3 fw-bold">{{ $pat->or_id }}</td>
                                <td>
                                    <span class="badge bg-dark me-1">HN: {{ $pat->or_hn }}</span>
                                    <strong>{{ $pat->or_patname }}</strong>
                                </td>
                                <td>{{ $pat->ward_name }}</td>
                                <td><span class="badge bg-primary-subtle text-primary">{{ $pat->or_reg_code }}</span></td>
                                <td class="text-center">
                                    @if($pat->or_confirm == 1)
                                        <span class="badge bg-success">รับคำสั่งแล้ว</span>
                                    @else
                                        <span class="badge bg-warning text-dark">รอ Confirm</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $pat->doses_count }} ขวดยา</span>
                                    <span class="badge bg-info text-dark">+ 1 ดวงสรุป</span>
                                    <span class="badge bg-danger ms-1">รวม {{ $pat->stickers_count }} ดวง</span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-flex justify-content-end gap-1">
                                        <button type="button" class="btn btn-sm btn-primary fw-semibold"
                                            onclick="printSinglePatientNow('{{ $pat->or_id }}')">
                                            <i class="bi bi-printer-fill me-1"></i> พิมพ์เฉพาะรายนี้ ({{ $pat->stickers_count }} ดวง)
                                        </button>
                                        <a href="{{ route('pharmacist.stickers', ['date' => $selectedDate, 'or_id' => $pat->or_id]) }}"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-funnel me-1"></i> กรองดูรายนี้
                                        </a>
                                        <a href="{{ route('pharmacist.show', $pat->or_id) }}" class="btn btn-sm btn-outline-dark" title="ดูรายละเอียดคำสั่ง">
                                            <i class="bi bi-folder2-open"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<!-- ส่วนแสดงสติกเกอร์ยาเคมีบำบัด (พร้อมพิมพ์) -->
@if($displayPatientsGroup->isEmpty())
    <div class="card card-medical bg-white p-5 text-center text-secondary no-print">
        <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
        <h5 class="fw-bold">ไม่พบรายการสติกเกอร์ยาเคมีบำบัดในวันที่ {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</h5>
        <p class="small mb-0">กรุณาเลือกวันที่อื่นจากปฏิทินด้านบน หรือคลิกปุ่มวันที่ที่มีรายการให้ยา</p>
    </div>
@else
    <div class="d-flex justify-content-between align-items-center mb-2 no-print">
        <div class="fw-bold text-secondary small">
            <i class="bi bi-grid-fill me-1"></i>ตัวอย่างสติกเกอร์สำหรับวันที่
            <span class="text-dark">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</span>
            &mdash; <span class="badge bg-dark">ขนาดมาตรฐาน: กว้าง 9 cm &times; สูง 7 cm ทั้งหมด</span>
            (ประกอบด้วย <strong>สติกเกอร์สรุปรายการยาของคนไข้ 1 ดวง</strong> + <strong>สติกเกอร์ยารายขวด</strong>):
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="toggleAllCheckboxes(true)">เลือกทั้งหมด</button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="toggleAllCheckboxes(false)">ยกเลิกทั้งหมด</button>
        </div>
    </div>

    @php $cardGlobalIdx = 0; @endphp
    <div class="labels-grid" id="stickersContainer">
        @foreach($displayPatientsGroup as $pat)
            @php
                $order = $pat->orderHeader;
                $summaryCardId = 'sticker_card_' . (++$cardGlobalIdx);
            @endphp

            <!-- 1. สติกเกอร์สรุปรายการยาประจำวันของคนไข้ (ขนาด 9 cm x 7 cm) -->
            <div class="sticker-unit" data-or-id="{{ $pat->or_id }}" id="{{ $summaryCardId }}">
                <div class="d-flex justify-content-between align-items-center mb-1 px-1 no-print">
                    <div class="form-check mb-0 text-truncate" style="max-width: 72%;">
                        <input class="form-check-input sticker-checkbox" type="checkbox" id="chk_{{ $cardGlobalIdx }}"
                            data-card-id="{{ $summaryCardId }}" checked onchange="onStickerCheckChange()">
                        <label class="form-check-label small fw-bold text-primary" for="chk_{{ $cardGlobalIdx }}" style="font-size: 0.72rem;">
                            [สรุปรายการยา] {{ $pat->or_patname }}
                        </label>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.7rem;"
                        onclick="printSingleCard('{{ $summaryCardId }}')">
                        <i class="bi bi-printer"></i> พิมพ์ดวงนี้
                    </button>
                </div>

                <div class="chemo-label-card summary-label-card">
                    <!-- หัวฉลากสรุปรายการยาประจำวัน -->
                    <div class="d-flex justify-content-between align-items-center summary-banner mb-1">
                        <span><i class="bi bi-card-checklist me-1"></i> สรุปรายการยาประจำวัน</span>
                        <span>วันที่: {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</span>
                    </div>

                    <!-- ข้อมูลผู้ป่วย -->
                    <div class="d-flex justify-content-between sticker-patient-box">
                        <div class="text-truncate pe-1">
                            <div class="sticker-patname text-truncate">{{ $pat->or_patname }}</div>
                            <div>
                                <strong>HN:</strong> {{ $pat->or_hn }} |
                                <strong>Ward:</strong> {{ $pat->ward_name }} |
                                <strong>BSA:</strong> {{ optional($order)->or_bsa ? number_format($order->or_bsa, 2) : '-' }}
                            </div>
                        </div>
                        <div class="text-end text-nowrap">
                            <div><strong>ID:</strong> {{ $pat->or_id }}</div>
                            <div><strong>Reg:</strong> <span class="fw-bold">{{ $pat->or_reg_code }}</span></div>
                        </div>
                    </div>

                    <!-- ตารางสรุปรายการยาทั้งหมดที่คนไข้ได้รับในวันนี้ -->
                    <div class="bg-white border border-primary rounded-1 p-1 summary-body-box">
                        <div class="fw-bold text-primary mb-1 d-flex justify-content-between align-items-center" style="font-size: 8.5px;">
                            <span>รายการยาที่ได้รับวันนี้:</span>
                            <span class="badge bg-primary" style="font-size: 7.5px;">รวม {{ $pat->doses_count }} รายการ</span>
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
                                @foreach($pat->items->sortBy('or_orderno')->values() as $dIdx => $dItem)
                                    <tr>
                                        <td class="text-center fw-bold text-primary text-nowrap">{{ $dIdx + 1 }}/{{ $pat->doses_count }}</td>
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
            </div>

            <!-- 2. สติกเกอร์ยารายขวด/ถุง ของคนไข้รายนี้ (ขนาด 9 cm x 7 cm) -->
            @foreach($pat->items->sortBy('or_orderno')->values() as $itemIdx => $lbl)
                @php
                    $medCardId = 'sticker_card_' . (++$cardGlobalIdx);
                    $seqText = ($itemIdx + 1) . '/' . $pat->doses_count;
                @endphp
                <div class="sticker-unit" data-or-id="{{ $lbl->or_id }}" id="{{ $medCardId }}">
                    <div class="d-flex justify-content-between align-items-center mb-1 px-1 no-print">
                        <div class="form-check mb-0 text-truncate" style="max-width: 72%;">
                            <input class="form-check-input sticker-checkbox" type="checkbox" id="chk_{{ $cardGlobalIdx }}"
                                data-card-id="{{ $medCardId }}" checked onchange="onStickerCheckChange()">
                            <label class="form-check-label small fw-bold text-danger" for="chk_{{ $cardGlobalIdx }}" style="font-size: 0.72rem;">
                                [ลำดับ {{ $seqText }}] {{ optional($lbl->medication)->dg_name ?? $lbl->or_medcode }}
                            </label>
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.7rem;"
                            onclick="printSingleCard('{{ $medCardId }}')">
                            <i class="bi bi-printer"></i> พิมพ์ดวงนี้
                        </button>
                    </div>

                    <div class="chemo-label-card">
                        <!-- หัวฉลากยาเคมีบำบัด -->
                        <div class="d-flex justify-content-between align-items-center chemo-banner mb-1">
                            <span><i class="bi bi-radioactive me-1"></i> ลำดับการให้ยา: {{ $seqText }}</span>
                            <span>{{ \Carbon\Carbon::parse($lbl->or_daterec)->format('d/m/Y') }} ({{ $lbl->or_text }})</span>
                        </div>

                        <!-- ข้อมูลผู้ป่วย -->
                        <div class="d-flex justify-content-between sticker-patient-box">
                            <div class="text-truncate pe-1">
                                <div class="sticker-patname text-truncate">{{ optional($order)->or_patname ?? '-' }}</div>
                                <div>
                                    <strong>HN:</strong> {{ optional($order)->or_hn ?? '-' }} |
                                    <strong>หอผู้ป่วย:</strong> {{ $pat->ward_name }}
                                </div>
                            </div>
                            <div class="text-end text-nowrap">
                                <div><strong>ID:</strong> {{ $lbl->or_id }}</div>
                                <div><strong>Reg:</strong> {{ optional($order)->or_reg_code ?? '-' }}</div>
                            </div>
                        </div>

                        <!-- รายละเอียดตัวยา ขนาดยา สารน้ำ และวิธีให้ -->
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
                                &bull; <strong>เวลา:</strong>
                                <span class="text-decoration-underline fw-bold">{{ optional($lbl->timing)->tm_time ?? $lbl->or_medtime }}</span>
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
                </div>
            @endforeach
        @endforeach
    </div>
@endif
@endsection

@section('js_after')
<script>
    function onStickerCheckChange() {
        let count = 0;
        document.querySelectorAll('.sticker-checkbox').forEach(chk => {
            const card = document.getElementById(chk.getAttribute('data-card-id'));
            if (chk.checked) {
                card.classList.remove('excluded-from-print');
                count++;
            } else {
                card.classList.add('excluded-from-print');
            }
        });
        const countEl = document.getElementById('selectedStickerCount');
        if (countEl) countEl.innerText = count;
    }

    function toggleAllCheckboxes(checked) {
        document.querySelectorAll('.sticker-checkbox').forEach(chk => {
            chk.checked = checked;
        });
        onStickerCheckChange();
    }

    function printAllVisible() {
        window.print();
    }

    function printAllPatientsNow() {
        toggleAllCheckboxes(true);
        window.print();
    }

    // พิมพ์เฉพาะคนไข้รายที่คลิกทันที (ทั้งสติกเกอร์สรุปรายการยา + สติกเกอร์ยารายขวดของคนไข้รายนั้น)
    function printSinglePatientNow(targetOrId) {
        const checkboxes = document.querySelectorAll('.sticker-checkbox');
        const prevStates = [];

        checkboxes.forEach(chk => {
            prevStates.push(chk.checked);
            const card = document.getElementById(chk.getAttribute('data-card-id'));
            const cardOrId = card ? card.getAttribute('data-or-id') : '';
            chk.checked = (cardOrId === targetOrId);
        });
        onStickerCheckChange();

        window.print();

        checkboxes.forEach((chk, i) => {
            chk.checked = prevStates[i];
        });
        onStickerCheckChange();
    }

    // พิมพ์เฉพาะสติกเกอร์ดวงเดียว
    function printSingleCard(targetCardId) {
        const checkboxes = document.querySelectorAll('.sticker-checkbox');
        const prevStates = [];

        checkboxes.forEach(chk => {
            prevStates.push(chk.checked);
            chk.checked = (chk.getAttribute('data-card-id') === targetCardId);
        });
        onStickerCheckChange();

        window.print();

        checkboxes.forEach((chk, i) => {
            chk.checked = prevStates[i];
        });
        onStickerCheckChange();
    }
</script>
@endsection
