@extends('layouts.app')

@section('title', 'รายละเอียดคำสั่งใช้ยา ' . $order->or_id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-file-medical-fill me-2"></i>ใบคำสั่งใช้ยาเคมีบำบัด เลขที่: {{ $order->or_id }}
        </h4>
        <small class="text-secondary">สั่งใช้โดย: {{ $order->or_doctor_name ?? 'แพทย์ผู้สั่งรักษา' }} | วันที่เริ่มให้ยา: {{ \Carbon\Carbon::parse($order->or_date)->format('d/m/Y') }}</small>
    </div>
    <div class="d-flex gap-2">
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
                    @if($order->or_confirm == 1)
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
                            <div class="fw-bold text-primary">[{{ $d->or_medcode }}] {{ optional($d->medication)->dg_name ?? $d->or_medcode }}</div>
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
@endsection
