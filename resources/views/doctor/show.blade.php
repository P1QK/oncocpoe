@extends('layouts.app')

@section('title', 'รายละเอียดคำสั่งใช้ยา ' . $order->or_id)

@section('content')
@php $isCancelled = (int) ($order->or_cancel ?? 0) === 1; @endphp
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold {{ $isCancelled ? 'text-danger text-decoration-line-through' : 'text-primary' }} mb-0">
            <i class="bi bi-file-medical-fill me-2"></i>ใบคำสั่งใช้ยาเคมีบำบัด เลขที่: {{ $order->or_id }}
        </h4>
        <small class="text-secondary">สั่งใช้โดย: {{ $order->or_doctor_name ?? 'แพทย์ผู้สั่งรักษา' }} | วันที่เริ่มให้ยา: {{ \Carbon\Carbon::parse($order->or_date)->format('d/m/Y') }}</small>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(!$isCancelled)
            <a href="{{ route('doctor.print', $order->or_id) }}" target="_blank" class="btn btn-dark fw-semibold shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> พิมพ์ใบสั่งยาเต็มรูปแบบ (A4)
            </a>
            <button type="button" class="btn btn-outline-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">
                <i class="bi bi-x-octagon-fill me-1"></i> ยกเลิกคำสั่งใช้ยา
            </button>
        @endif
        @if(auth()->user()->canAccessPharmacist())
            <a href="{{ route('pharmacist.show', $order->or_id) }}" class="btn btn-success fw-semibold">
                <i class="bi bi-capsule me-1"></i> ไปที่หน้าตรวจสอบของเภสัชกร
            </a>
        @endif
        <a href="{{ route('doctor.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
        </a>
    </div>
</div>

@if($isCancelled)
    <div class="alert alert-danger border-danger d-flex align-items-center mb-4 shadow-sm">
        <i class="bi bi-x-octagon-fill fs-3 me-3 text-danger"></i>
        <div>
            <div class="fw-bold fs-6">คำสั่งใช้ยานี้ถูกยกเลิกแล้ว (CANCELLED) &mdash; ไม่มีการจัด/เตรียม/บริหารยา</div>
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
                <span class="badge bg-primary-subtle text-primary mb-1">HN: {{ $order->or_hn }}</span>
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
                <div class="mb-1">
                    <span class="fw-semibold">สูตร Regimen:</span>
                    <span class="badge bg-dark fs-6">{{ $order->or_reg_code }} (Cycle #{{ $order->or_cycle ?? 1 }})</span>
                </div>
                <div>
                    @if($isCancelled)
                        <span class="badge bg-danger px-3 py-2 fs-6">
                            <i class="bi bi-x-octagon-fill me-1"></i> ยกเลิกคำสั่งใช้ยาแล้ว
                        </span>
                    @elseif($order->or_confirm == 1)
                        <span class="badge bg-success px-3 py-2 fs-6">
                            <i class="bi bi-check-circle-fill me-1"></i> เภสัชกรรับคำสั่งแล้ว
                        </span>
                    @else
                        <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                            <i class="bi bi-hourglass-split me-1"></i> รอเภสัชกรรับคำสั่ง
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if($order->or_adjust_percent > 0 || $order->or_adjust_reason)
            <div class="alert alert-warning mt-3 mb-0 border-warning d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
                <div>
                    <strong>มีการปรับลดขนาดยาลง {{ number_format($order->or_adjust_percent, 0) }}%</strong> &mdash;
                    <strong>เหตุผลของแพทย์:</strong> {{ $order->or_adjust_reason ?: '-' }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- รายการ Pre-medication (ถ้าแพทย์สั่งใช้) -->
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
                                <li class="list-group-item d-flex justify-content-between align-items-start py-2">
                                    <div>
                                        <span class="fw-bold text-dark">{{ $pm->sm_name }}</span>
                                        <span class="badge bg-primary-subtle text-primary ms-1">{{ $pm->dose }}</span>
                                        @if($pm->route)<span class="badge bg-light text-dark border ms-1">{{ $pm->route }}</span>@endif
                                        @if($pm->instruction)<div class="text-secondary mt-1">{{ $pm->instruction }}</div>@endif
                                    </div>
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
                                <li class="list-group-item d-flex justify-content-between align-items-start py-2">
                                    <div>
                                        <span class="fw-bold text-dark">{{ $pm->sm_name }}</span>
                                        <span class="badge bg-warning-subtle text-dark border border-warning ms-1">{{ $pm->dose }}</span>
                                        @if($pm->route)<span class="badge bg-light text-dark border ms-1">{{ $pm->route }}</span>@endif
                                        @if($pm->instruction)<div class="text-secondary mt-1">{{ $pm->instruction }}</div>@endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

<!-- รายการ Pre-hydration (สารน้ำและอิเล็กโทรไลต์ก่อนเคมีบำบัด) -->
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

<!-- รายการยาเคมีบำบัดที่สั่ง (order_d) -->
<div class="card card-medical bg-white">
    <div class="card-header bg-white py-3 fw-bold">
        <i class="bi bi-list-check text-primary me-1"></i> รายการยาเคมีบำบัดในคำสั่งใช้ยา ({{ $order->details->count() }} รายการ)
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center">#</th>
                    <th>รหัส / ชื่อยาเคมีบำบัด</th>
                    <th class="text-end">ขนาดยาคำนวณเริ่มต้น</th>
                    <th class="text-center">ปรับลด (%)</th>
                    <th class="text-end">ขนาดยาสุทธิ (Final Dose)</th>
                    <th>สารน้ำ &amp; ปริมาตร</th>
                    <th>วิธีบริหารยา / เวลา</th>
                    <th class="text-center">จำนวนวัน</th>
                    <th class="text-center">วันที่เริ่ม - วันที่สิ้นสุด</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->details as $d)
                    @php
                        $days = \Carbon\Carbon::parse($d->or_start)->diffInDays(\Carbon\Carbon::parse($d->or_stop)) + 1;
                    @endphp
                    <tr>
                        <td class="text-center fw-bold">{{ $d->or_orderno }}</td>
                        <td>
                            <div class="fw-bold text-primary">
                                [{{ $d->or_medcode }}] {{ optional($d->medication)->dg_name ?? $d->or_medcode }}
                                @if($d->or_bag_group)
                                    <span class="badge text-white ms-1" style="background-color: #7c3aed;">
                                        <i class="bi bi-layers-fill me-1"></i>ผสมรวม {{ $d->or_bag_group }}
                                    </span>
                                @endif
                            </div>
                            <small class="text-muted">{{ optional($d->medication)->dg_fullname }}</small>
                        </td>
                        <td class="text-end">{{ number_format($d->or_std_dose ?? $d->or_meddose, 2) }} mg</td>
                        <td class="text-center">
                            @if(($d->or_reduce_pct ?? 0) > 0)
                                <span class="badge bg-danger">-{{ number_format($d->or_reduce_pct, 0) }}%</span>
                            @else
                                <span class="text-muted">0%</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-danger fs-5">
                            {{ number_format($d->or_meddose, 2) }} {{ optional($d->unit)->un_unit ?? 'mg' }}
                        </td>
                        <td>
                            <div>{{ optional($d->solution)->so_name ?? $d->or_soln }} <strong>{{ number_format($d->or_solvol, 0) }} mL</strong></div>
                            <small class="text-secondary">ดูดยา: {{ number_format($d->or_working, 2) }} mL | รวม: {{ number_format($d->or_totalvol, 2) }} mL</small>
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
                        <td>
                            <div>{{ optional($d->route)->rt_name ?? $d->or_medroute }}</div>
                            <small class="text-primary fw-semibold">{{ optional($d->timing)->tm_time ?? $d->or_medtime }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-info text-dark fs-6">{{ $days }} วัน</span>
                        </td>
                        <td class="text-center">
                            <span class="fw-semibold">
                                {{ \Carbon\Carbon::parse($d->or_start)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($d->or_stop)->format('d/m/Y') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- รายการ Post-medication (เช่น MESNA 0-3-6 ชม.) -->
@php
    $postMeds = $order->supportiveMeds->where('category', 'post_medication');
@endphp
@if($postMeds->isNotEmpty())
    <div class="card card-medical bg-white mt-4 border-start border-4" style="border-left-color: #6f42c1 !important;">
        <div class="card-header py-2 d-flex justify-content-between align-items-center" style="background-color: #f3e8ff;">
            <span class="fw-bold" style="color: #5b21b6;"><i class="bi bi-clock-history me-1"></i> รายการ Post-medication (ยาหลัง/ระหว่างให้เคมีบำบัด เช่น MESNA)</span>
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
                            @if($pm->instruction)<div class="text-secondary mt-1">{{ $pm->instruction }}</div>@endif
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
    <div class="card card-medical bg-white mt-4 border-start border-4 border-success">
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

<!-- รายการ Home-medication (ยากลับบ้านหลังรับยาเคมีบำบัด) -->
@php
    $homeMeds = $order->supportiveMeds->where('category', 'home_med');
@endphp
@if($homeMeds->isNotEmpty())
    <div class="card card-medical bg-white mt-4 border-start border-4 border-warning">
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
@if(!$isCancelled)
<!-- Modal ยืนยันการยกเลิกคำสั่งใช้ยา (แพทย์) -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <form method="POST" action="{{ route('doctor.cancel', $order->or_id) }}">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-x-octagon-fill me-2"></i>ยืนยันการยกเลิกคำสั่งใช้ยา
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small mb-3">
                        <strong>เลขที่คำสั่ง:</strong> {{ $order->or_id }} | <strong>ผู้ป่วย:</strong> {{ $order->or_patname }} (HN: {{ $order->or_hn }})<br>
                        ระบบจะ <strong>ไม่ลบข้อมูล</strong> แต่จะเปลี่ยนสถานะเป็น <strong>"ยกเลิกแล้ว"</strong> และระงับรายการจากหน้าเตรียมยา/พยาบาลอัตโนมัติ
                    </div>
                    <label class="form-label fw-bold text-danger">เหตุผลการยกเลิกคำสั่งใช้ยา <span class="text-danger">*</span></label>
                    <textarea name="cancel_reason" class="form-control" rows="3" required
                              placeholder="ระบุเหตุผล เช่น ผู้ป่วยมีไข้/ANC ต่ำ, เปลี่ยนแผนการรักษา, สั่งผิดสูตรยา..."></textarea>
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
