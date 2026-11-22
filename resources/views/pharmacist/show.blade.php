@extends('layouts.app')

@section('title', 'ตรวจสอบคำสั่งและแตกวันให้ยา - ' . $order->or_id)

@section('content')
@php $isCancelled = (int) ($order->or_cancel ?? 0) === 1; @endphp
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold {{ $isCancelled ? 'text-danger text-decoration-line-through' : 'text-success' }} mb-0">
            <i class="bi bi-clipboard2-check-fill me-2"></i>ตรวจสอบคำสั่งใช้ยาเคมีบำบัด: {{ $order->or_id }}
        </h4>
        <small class="text-secondary">
            แพทย์ผู้สั่ง: {{ $order->or_doctor_name ?? '-' }} | วันที่เริ่มให้ยา: {{ \Carbon\Carbon::parse($order->or_date)->format('d/m/Y') }}
        </small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if(!$isCancelled)
            @if($order->or_confirm == 0)
                <form method="POST" action="{{ route('pharmacist.confirm', $order->or_id) }}" id="confirmOrderForm" onsubmit="syncSequencesBeforeConfirm(this)">
                    @csrf
                    <div id="confirmHiddenSequences"></div>
                    <button type="submit" class="btn btn-success fw-bold shadow-sm">
                        <i class="bi bi-check2-all me-1"></i> Confirm รับคำสั่งใช้ยา &amp; แตกคำสั่งรายวัน
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('pharmacist.confirm', $order->or_id) }}" id="confirmOrderForm" onsubmit="if(!confirm('ต้องการคำนวณและแตกคำสั่งรายวันใหม่อีกครั้งหรือไม่?')) return false; syncSequencesBeforeConfirm(this);">
                    @csrf
                    <div id="confirmHiddenSequences"></div>
                    <button type="submit" class="btn btn-outline-success">
                        <i class="bi bi-arrow-repeat me-1"></i> แตกคำสั่งรายวันใหม่
                    </button>
                </form>
            @endif

            <a href="{{ route('pharmacist.edit', $order->or_id) }}" class="btn btn-warning text-dark fw-bold shadow-sm">
                <i class="bi bi-pencil-square me-1"></i> แก้ไขคำสั่งยาเคมีบำบัด
            </a>

            <a href="{{ route('pharmacist.print.order', $order->or_id) }}" target="_blank" class="btn btn-info text-dark fw-semibold shadow-sm">
                <i class="bi bi-file-earmark-medical-fill me-1"></i> พิมพ์ใบสั่งยา (A4)
            </a>

            <a href="{{ route('pharmacist.print.working-formula', $order->or_id) }}" target="_blank" class="btn btn-dark fw-semibold">
                <i class="bi bi-printer-fill me-1"></i> พิมพ์ใบจัดยา Working Formula (A5 แนวนอน)
            </a>

            <a href="{{ route('pharmacist.print.labels', $order->or_id) }}" target="_blank" class="btn btn-primary fw-semibold">
                <i class="bi bi-tags-fill me-1"></i> พิมพ์ฉลากยาทั้งหมด
            </a>

            <button type="button" class="btn btn-outline-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                <i class="bi bi-x-octagon-fill me-1"></i> ยกเลิกคำสั่งใช้ยา
            </button>
        @endif

        <a href="{{ route('pharmacist.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> กลับหน้าหลักเภสัชกร
        </a>
    </div>
</div>

@if($isCancelled)
    <div class="alert alert-danger border-danger d-flex align-items-center mb-4 shadow-sm">
        <i class="bi bi-x-octagon-fill fs-3 me-3 text-danger"></i>
        <div>
            <div class="fw-bold fs-6">คำสั่งใช้ยานี้ถูกยกเลิกแล้ว (CANCELLED) &mdash; งดการเตรียมยาและส่งมอบ</div>
            <div class="small mt-1">
                <strong>ยกเลิกโดย:</strong> {{ $order->or_cancelled_by ?? '-' }} |
                <strong>เวลาที่ยกเลิก:</strong> {{ $order->or_cancelled_at ? \Carbon\Carbon::parse($order->or_cancelled_at)->format('d/m/Y H:i น.') : '-' }}<br>
                <strong>เหตุผลการยกเลิก:</strong> {{ $order->or_cancel_reason ?: '-' }}
            </div>
        </div>
    </div>
@endif

<!-- ข้อมูลผู้ป่วยและสถานะ -->
<div class="card card-medical bg-white mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-md-4 border-end">
                <span class="badge bg-dark mb-1">HN: {{ $order->or_hn }}</span>
                <h5 class="fw-bold mb-1">{{ $order->or_patname }}</h5>
                <div class="small text-secondary">
                    อายุ: {{ $order->or_age ?? '-' }} ปี | เพศ: {{ $order->or_sex === 'F' ? 'หญิง' : 'ชาย' }} |
                    หอผู้ป่วย: <strong>{{ optional($order->ward)->wd_name ?? $order->or_ward }}</strong>
                </div>
            </div>
            <div class="col-md-4 border-end">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="small text-muted">น้ำหนัก / ส่วนสูง</div>
                        <div class="fw-bold">{{ number_format($order->or_wt, 1) }} kg / {{ number_format($order->or_ht, 0) }} cm</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">BSA</div>
                        <div class="fw-bold text-primary fs-5">{{ number_format($order->or_bsa, 2) }} m&sup2;</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">GFR (SCr {{ $order->or_scr }})</div>
                        <div class="fw-bold text-success fs-5">{{ number_format($order->or_gfr, 1) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="mb-2">
                    <span class="badge bg-primary-subtle text-primary fs-6">
                        สูตร Regimen: {{ $order->or_reg_code }} (Cycle #{{ $order->or_cycle ?? 1 }})
                    </span>
                </div>
                @if($isCancelled)
                    <span class="badge bg-danger px-3 py-2 fs-6">
                        <i class="bi bi-x-octagon-fill me-1"></i> สถานะ: ยกเลิกแล้ว
                    </span>
                    <div class="small text-danger mt-1">
                        {{ $order->or_cancelled_by ?? '-' }}
                    </div>
                @elseif($order->or_confirm == 1)
                    <span class="badge bg-success px-3 py-2 fs-6">
                        <i class="bi bi-check-circle-fill me-1"></i> สถานะ: รับคำสั่งแล้ว
                    </span>
                    <div class="small text-muted mt-1">
                        ผู้ยืนยัน: {{ $order->or_pharmacist_name ?? '-' }}
                    </div>
                @else
                    <span class="badge bg-danger px-3 py-2 fs-6">
                        <i class="bi bi-exclamation-circle-fill me-1"></i> สถานะ: รอเภสัชกรกด Confirm รับคำสั่ง
                    </span>
                @endif
            </div>
        </div>

        @if($order->or_adjust_percent > 0 || $order->or_adjust_reason)
            <div class="alert alert-warning mt-3 mb-0 border-warning d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
                <div>
                    <strong>แพทย์ปรับลดขนาดยาลง {{ number_format($order->or_adjust_percent, 0) }}%</strong> &mdash;
                    <strong>เหตุผล:</strong> {{ $order->or_adjust_reason ?: '-' }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- รายการ Pre-medication ที่แพทย์สั่ง (ตรวจสอบก่อนให้ยาเคมีบำบัด) -->
@php
    $preMeds = $order->supportiveMeds->whereIn('category', ['pre_med_antiemetic', 'pre_med_hypersensitivity']);
    $antiemetics = $preMeds->where('category', 'pre_med_antiemetic');
    $antiHsr = $preMeds->where('category', 'pre_med_hypersensitivity');
@endphp
@if($preMeds->isNotEmpty())
    <div class="card card-medical bg-white mb-4 border-start border-4 border-info">
        <div class="card-header bg-info-subtle py-2 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="bi bi-shield-plus text-info me-1"></i> รายการ Pre-medication (ยาเตรียมก่อนให้เคมีบำบัด)</span>
            <span class="badge bg-info text-dark">{{ $preMeds->count() }} รายการ</span>
        </div>
        <div class="card-body py-3">
            <div class="row g-3">
                @if($antiemetics->isNotEmpty())
                    <div class="col-md-6">
                        <div class="small fw-bold text-primary mb-2"><i class="bi bi-capsule me-1"></i>Antiemetics (ป้องกันคลื่นไส้-อาเจียน)</div>
                        <ul class="list-group list-group-flush small border rounded-2">
                            @foreach($antiemetics as $pm)
                                <li class="list-group-item py-2">
                                    <span class="fw-bold text-dark">{{ $pm->sm_name }}</span>
                                    <span class="badge bg-primary-subtle text-primary ms-1">{{ $pm->dose }}</span>
                                    @if($pm->route)<span class="badge bg-light text-dark border ms-1">{{ $pm->route }}</span>@endif
                                    @if($pm->instruction)<div class="text-secondary mt-1">{{ $pm->instruction }}</div>@endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if($antiHsr->isNotEmpty())
                    <div class="col-md-6">
                        <div class="small fw-bold text-warning-emphasis mb-2"><i class="bi bi-exclamation-shield-fill text-warning me-1"></i>Prophylaxis for Hypersensitivity / Infusion Reaction</div>
                        <ul class="list-group list-group-flush small border rounded-2">
                            @foreach($antiHsr as $pm)
                                <li class="list-group-item py-2">
                                    <span class="fw-bold text-dark">{{ $pm->sm_name }}</span>
                                    <span class="badge bg-warning-subtle text-dark border border-warning ms-1">{{ $pm->dose }}</span>
                                    @if($pm->route)<span class="badge bg-light text-dark border ms-1">{{ $pm->route }}</span>@endif
                                    @if($pm->instruction)<div class="text-secondary mt-1">{{ $pm->instruction }}</div>@endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

<!-- รายการ Pre-hydration ที่แพทย์สั่ง (สารน้ำและอิเล็กโทรไลต์ก่อนเคมีบำบัด) -->
@php
    $preHydrations = $order->supportiveMeds->where('category', 'pre_hydration');
@endphp
@if($preHydrations->isNotEmpty())
    <div class="card card-medical bg-white mb-4 border-start border-4 border-primary">
        <div class="card-header bg-primary-subtle py-2 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="bi bi-droplet-fill text-primary me-1"></i> รายการ Pre-hydration (สารน้ำและอิเล็กโทรไลต์ก่อนเคมีบำบัด)</span>
            <span class="badge bg-primary text-white">{{ $preHydrations->count() }} รายการ</span>
        </div>
        <div class="card-body py-3">
            <div class="row g-2">
                @foreach($preHydrations as $ph)
                    <div class="col-md-6">
                        <div class="border rounded-2 p-2 bg-light small h-100">
                            <span class="fw-bold text-primary">{{ $ph->sm_name }}</span>
                            <span class="badge bg-primary-subtle text-primary ms-1">{{ $ph->dose }}</span>
                            @if($ph->route)<span class="badge bg-white text-dark border ms-1">{{ $ph->route }}</span>@endif
                            @if($ph->instruction)<div class="text-secondary mt-1">{{ $ph->instruction }}</div>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- ตารางที่ 1: รายการคำสั่งใช้ยาหลัก (order_d) -->
<div class="card card-medical bg-white mb-4">
    <form method="POST" action="{{ route('pharmacist.sequence', $order->or_id) }}">
        @csrf
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <span class="fw-bold"><i class="bi bi-prescription2 text-primary me-1"></i> 1. รายการยาตามคำสั่งแพทย์ (ตาราง <code>order_d</code>)</span>
                @if(!$hasRegimen)
                    <span class="badge bg-warning text-dark ms-2">
                        <i class="bi bi-sort-numeric-down me-1"></i>คำสั่งยาแบบอิสระ (ไม่มี Regimen) &mdash; กรุณาระบุลำดับการให้ยาด้านล่าง
                    </span>
                @else
                    <span class="badge bg-info-subtle text-dark border ms-2">
                        ลำดับการให้ยาอ้างอิงตามสูตร Regimen ({{ $order->or_reg_code }})
                    </span>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-sm {{ !$hasRegimen ? 'btn-warning fw-bold shadow-sm' : 'btn-outline-primary fw-semibold' }}">
                    <i class="bi bi-sort-numeric-down me-1"></i> บันทึกลำดับการให้ยา
                </button>
                <span class="badge bg-secondary">{{ $order->details->count() }} รายการยา</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center bg-warning-subtle" style="width: 110px;">
                            ลำดับการให้ยา<br><small class="text-secondary">(<code>or_orderno</code>)</small>
                        </th>
                        <th>รหัส / ชื่อยาเคมีบำบัด (ความเข้มข้น)</th>
                        <th class="text-end">ขนาดยาสุทธิ (mg)</th>
                        <th class="text-end bg-warning-subtle">ปริมาตรยาที่ดูด (mL)</th>
                        <th>สารน้ำ &amp; ปริมาตรเริ่มต้น</th>
                        <th class="text-end">ปริมาตรรวม (Total Vol)</th>
                        <th>วิถีทาง / เวลาให้ยา</th>
                        <th class="text-center">จำนวนวัน</th>
                        <th class="text-center">ช่วงวันที่ให้ยา (or_start - or_stop)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->details as $idx => $d)
                        @php
                            $days = \Carbon\Carbon::parse($d->or_start)->diffInDays(\Carbon\Carbon::parse($d->or_stop)) + 1;
                        @endphp
                        <tr>
                            <td class="text-center bg-warning-subtle">
                                <input type="number" min="1" max="99"
                                       name="sequences[{{ $idx }}]"
                                       class="form-control form-control-sm text-center fw-bold border-warning pharm-seq-input"
                                       data-idx="{{ $idx }}"
                                       value="{{ $d->or_orderno ?: ($idx + 1) }}" required>
                            </td>
                            <td>
                                <div class="fw-bold text-primary">
                                    [{{ $d->or_medcode }}] {{ optional($d->medication)->dg_name ?? $d->or_medcode }}
                                    @if($d->or_bag_group)
                                        <span class="badge text-white ms-1" style="background-color: #7c3aed;">
                                            <i class="bi bi-layers-fill me-1"></i>ผสมรวม {{ $d->or_bag_group }}
                                        </span>
                                    @endif
                                </div>
                                <small class="text-secondary">
                                    ความเข้มข้น: <strong>{{ number_format(optional($d->medication)->dg_conc ?? 1, 2) }} mg/mL</strong>
                                    @if(($d->or_reduce_pct ?? 0) > 0)
                                        | <span class="text-danger fw-semibold">ลด {{ number_format($d->or_reduce_pct, 0) }}% จาก {{ number_format($d->or_std_dose, 2) }} mg</span>
                                    @endif
                                </small>
                            </td>
                            <td class="text-end fw-bold text-danger fs-5">
                                {{ number_format($d->or_meddose, 2) }} {{ optional($d->unit)->un_unit ?? 'mg' }}
                            </td>
                            <td class="text-end fw-bold bg-warning-subtle fs-5 text-dark">
                                {{ number_format($d->or_working, 2) }} mL
                            </td>
                            <td>
                                {{ optional($d->solution)->so_name ?? $d->or_soln }}
                                <strong>{{ number_format($d->or_solvol, 0) }} mL</strong>
                                @if(!empty($d->or_additives) && is_array($d->or_additives))
                                    <div class="mt-1 d-flex flex-wrap gap-1">
                                        @foreach($d->or_additives as $ad)
                                            <span class="badge bg-info-subtle text-dark border border-info-subtle" style="font-size: 0.72rem;">
                                                + {{ $ad['name'] ?? '' }}
                                                @if(!empty($ad['dose'])) {{ $ad['dose'] }} {{ $ad['unit'] ?? '' }}@endif
                                                @if(!empty($ad['vol_ml']) && (float)$ad['vol_ml'] > 0) ({{ $ad['vol_ml'] }} mL)@endif
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="text-end fw-semibold">
                                {{ number_format($d->or_totalvol, 2) }} mL
                            </td>
                            <td>
                                <div>{{ optional($d->route)->rt_name ?? $d->or_medroute }}</div>
                                <small class="text-primary fw-semibold">{{ optional($d->timing)->tm_time ?? $d->or_medtime }}</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark fs-6">{{ $days }} วัน</span>
                            </td>
                            <td class="text-center fw-semibold">
                                {{ \Carbon\Carbon::parse($d->or_start)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($d->or_stop)->format('d/m/Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
</div>

<!-- ตารางที่ 2: รายการแตกคำสั่งใช้ยาเป็นรายวัน (onc_dayrecord) -->
@php
    $postMeds = $order->supportiveMeds->where('category', 'post_medication');
@endphp
@if($postMeds->isNotEmpty())
    <div class="card card-medical bg-white mb-4 border-start border-4" style="border-left-color: #6f42c1 !important;">
        <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background-color: #f3e8ff;">
            <span class="fw-bold" style="color: #5b21b6;"><i class="bi bi-clock-history me-1"></i> รายการ Post-medication (ตรวจสอบวิธีผสม MESNA ชั่วโมงที่ 0 และโดส +3, +6 ชม.)</span>
            <span class="badge text-white" style="background-color: #6f42c1;">{{ $postMeds->count() }} รายการ</span>
        </div>
        <div class="card-body py-3">
            <div class="row g-2">
                @foreach($postMeds as $pm)
                    <div class="col-md-4">
                        <div class="border rounded-2 p-2 bg-light small h-100">
                            <div class="fw-bold" style="color: #5b21b6;">{{ $pm->sm_name }}</div>
                            <div class="mt-1">
                                <span class="badge bg-danger-subtle text-danger">{{ $pm->dose }}</span>
                                @if($pm->route)<span class="badge bg-white text-dark border ms-1">{{ $pm->route }}</span>@endif
                            </div>
                            @if($pm->instruction)<div class="text-secondary mt-1 fw-medium">{{ $pm->instruction }}</div>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- รายการ Post-hydration (สารน้ำและอิเล็กโทรไลต์หลังเคมีบำบัด) -->
@php
    $postHydrations = $order->supportiveMeds->where('category', 'post_hydration');
@endphp
@if($postHydrations->isNotEmpty())
    <div class="card card-medical bg-white mb-4 border-start border-4 border-success">
        <div class="card-header bg-success-subtle py-2 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-success-emphasis"><i class="bi bi-droplet-half text-success me-1"></i> รายการ Post-hydration (สารน้ำและอิเล็กโทรไลต์หลังเคมีบำบัด)</span>
            <span class="badge bg-success text-white">{{ $postHydrations->count() }} รายการ</span>
        </div>
        <div class="card-body py-3">
            <div class="row g-2">
                @foreach($postHydrations as $poh)
                    <div class="col-md-6">
                        <div class="border rounded-2 p-2 bg-light small h-100">
                            <span class="fw-bold text-success">{{ $poh->sm_name }}</span>
                            <span class="badge bg-success-subtle text-success ms-1">{{ $poh->dose }}</span>
                            @if($poh->route)<span class="badge bg-white text-dark border ms-1">{{ $poh->route }}</span>@endif
                            @if($poh->instruction)<div class="text-secondary mt-1">{{ $poh->instruction }}</div>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<!-- รายการ Home-medication (ยากลับบ้านสำหรับจัดยาและให้คำแนะนำผู้ป่วย) -->
@php
    $homeMeds = $order->supportiveMeds->where('category', 'home_med');
@endphp
@if($homeMeds->isNotEmpty())
    <div class="card card-medical bg-white mb-4 border-start border-4 border-warning">
        <div class="card-header bg-warning-subtle py-2 d-flex justify-content-between align-items-center">
            <span class="fw-bold text-dark"><i class="bi bi-house-heart-fill text-warning me-1"></i> รายการ Home-medication (ยากลับบ้าน)</span>
            <span class="badge bg-warning text-dark">{{ $homeMeds->count() }} รายการ</span>
        </div>
        <div class="card-body py-3">
            <div class="row g-2">
                @foreach($homeMeds as $hm)
                    <div class="col-md-6">
                        <div class="border rounded-2 p-2 bg-light small h-100">
                            <span class="fw-bold text-dark">{{ $hm->sm_name }}</span>
                            <span class="badge bg-warning-subtle text-dark border border-warning ms-1">{{ $hm->dose }}</span>
                            @if($hm->route)<span class="badge bg-white text-dark border ms-1">{{ $hm->route }}</span>@endif
                            @if($hm->instruction)<div class="text-secondary mt-1">{{ $hm->instruction }}</div>@endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="card card-medical bg-white">
    <div class="card-header bg-success text-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-bold">
            <i class="bi bi-calendar2-week-fill me-2"></i> 2. ตารางแตกคำสั่งใช้ยาเป็นรายวัน (ตาราง <code>onc_dayrecord</code>)
        </span>
        <span class="badge bg-white text-success">รวมทั้งหมด {{ $order->dayRecords->count() }} โดสรายวัน</span>
    </div>
    <div class="card-body">
        @if($groupedDayRecords->isEmpty())
            <div class="text-center py-4 text-secondary">
                <i class="bi bi-exclamation-circle fs-2 d-block mb-2 text-warning"></i>
                กรุณากดปุ่ม <strong>"Confirm รับคำสั่งใช้ยา &amp; แตกคำสั่งรายวัน"</strong> ด้านบน เพื่อให้ระบบนำข้อมูลจาก <code>order_d</code> มาแตกเป็นรายการยารายวันตามช่วงวันที่ <code>or_start</code> ถึง <code>or_stop</code>
            </div>
        @else
            @foreach($groupedDayRecords as $dateStr => $records)
                <div class="border rounded-3 mb-3 overflow-hidden">
                    <div class="bg-light px-3 py-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="fw-bold text-dark">
                            <i class="bi bi-calendar-event text-success me-1"></i>
                            วันที่ให้ยา: <span class="text-primary">{{ \Carbon\Carbon::parse($dateStr)->format('d/m/Y') }}</span>
                            <span class="badge bg-secondary ms-2">{{ $records->count() }} รายการยา</span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('pharmacist.print.labels', ['orId' => $order->or_id, 'date' => $dateStr]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-tag-fill me-1"></i> พิมพ์ฉลากยาเฉพาะวันนี้
                            </a>
                            <form method="POST" action="{{ route('pharmacist.day-status', $order->or_id) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="or_daterec" value="{{ $dateStr }}">
                                <input type="hidden" name="action" value="prep">
                                <button type="submit" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-check2-square me-1"></i> ยืนยันเตรียมยาวันนี้เสร็จสิ้น
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light small">
                                <tr>
                                    <th class="ps-3">ลำดับ</th>
                                    <th>วันที่ให้ยา (or_daterec)</th>
                                    <th>รายการยา</th>
                                    <th class="text-end">ขนาดยา</th>
                                    <th class="text-end">ดูดยา (Working)</th>
                                    <th>สารน้ำ / ปริมาตรรวม</th>
                                    <th>วิถีทาง / เวลา</th>
                                    <th class="text-center">วันในคอร์ส</th>
                                    <th class="text-center">สถานะเตรียมยา</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($records as $rec)
                                    <tr>
                                        <td class="ps-3 fw-bold">{{ $rec->or_orderno }}</td>
                                        <td>{{ \Carbon\Carbon::parse($rec->or_daterec)->format('d/m/Y') }}</td>
                                        <td class="fw-semibold text-primary">
                                            [{{ $rec->or_medcode }}] {{ optional($rec->medication)->dg_name ?? $rec->or_medcode }}
                                            @if($rec->or_bag_group)
                                                <span class="badge text-white ms-1" style="background-color: #7c3aed; font-size: 0.7rem;">{{ $rec->or_bag_group }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-danger">{{ number_format($rec->or_meddose, 2) }} mg</td>
                                        <td class="text-end fw-bold">{{ number_format($rec->or_working, 2) }} mL</td>
                                        <td>
                                            {{ optional($rec->solution)->so_name ?? $rec->or_soln }} ({{ number_format($rec->or_solvol, 0) }} mL)
                                            <small class="text-muted">รวม {{ number_format($rec->or_totalvol, 2) }} mL</small>
                                            @if(!empty($rec->or_additives) && is_array($rec->or_additives))
                                                <div class="mt-1 d-flex flex-wrap gap-1">
                                                    @foreach($rec->or_additives as $ad)
                                                        <span class="badge bg-info-subtle text-dark border border-info-subtle" style="font-size: 0.68rem;">
                                                            + {{ $ad['name'] ?? '' }} {{ !empty($ad['dose']) ? ($ad['dose'] . ' ' . ($ad['unit'] ?? '')) : '' }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            {{ optional($rec->route)->rt_name ?? $rec->or_medroute }} /
                                            {{ optional($rec->timing)->tm_time ?? $rec->or_medtime }}
                                        </td>
                                        <td class="text-center"><span class="badge bg-light text-dark border">{{ $rec->or_text }}</span></td>
                                        <td class="text-center">
                                            @if($rec->or_send == 1)
                                                <span class="badge bg-info text-dark">ส่งมอบ/บริหารยาแล้ว</span>
                                            @elseif($rec->or_prep == 1)
                                                <span class="badge bg-success">เตรียมยาแล้ว</span>
                                            @else
                                                <span class="badge bg-secondary">รับคำสั่งแล้ว (รอผสม)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@if(!$isCancelled)
<!-- Modal ยืนยันการยกเลิกคำสั่งใช้ยา (เภสัชกร) -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <form method="POST" action="{{ route('pharmacist.cancel', $order->or_id) }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-x-octagon-fill me-2"></i>ยืนยันการยกเลิกคำสั่งใช้ยา (เภสัชกร)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small mb-3">
                        <strong>เลขที่คำสั่ง:</strong> {{ $order->or_id }} | <strong>ผู้ป่วย:</strong> {{ $order->or_patname }} (HN: {{ $order->or_hn }})<br>
                        ระบบจะ <strong>ไม่ลบข้อมูล</strong> แต่จะเปลี่ยนสถานะเป็น <strong>"ยกเลิกแล้ว"</strong> และระงับรายการจากสติกเกอร์รายวันและหน้าพยาบาลอัตโนมัติ
                    </div>
                    <label class="form-label fw-bold text-danger">เหตุผลการยกเลิกคำสั่งใช้ยา (เช่น หลังปรึกษาแพทย์) <span class="text-danger">*</span></label>
                    <textarea name="cancel_reason" class="form-control" rows="3" required
                              placeholder="ระบุเหตุผล เช่น ปรึกษาแพทย์แล้วขอยกเลิกคำสั่ง เนื่องจากผู้ป่วยเลื่อนรับยา / สั่งซ้ำ..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-danger fw-bold">
                        <i class="bi bi-x-octagon-fill me-1"></i> ยืนยันยกเลิกคำสั่งใช้ยา
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('js_after')
<script>
    function syncSequencesBeforeConfirm(form) {
        const container = form.querySelector('#confirmHiddenSequences');
        if (!container) return;
        container.innerHTML = '';

        document.querySelectorAll('.pharm-seq-input').forEach(input => {
            const idx = input.getAttribute('data-idx');
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `sequences[${idx}]`;
            hidden.value = input.value;
            container.appendChild(hidden);
        });
    }
</script>
@endsection
