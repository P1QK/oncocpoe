@extends('layouts.app')

@section('title', 'Patient Chemotherapy Profile - HN ' . $hn)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-person-vcard-fill me-2"></i>Profile การรับยาเคมีบำบัดของผู้ป่วย (Patient Chemotherapy Profile)
        </h4>
        <small class="text-secondary">
            สรุปประวัติการรักษาทุก Cycle, ยอดขนาดยาสะสม (Cumulative Dose) และรายงานสรุปเวลาให้ยาต่อครั้ง
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.patients.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายชื่อผู้ป่วย
        </a>
    </div>
</div>

<!-- การ์ดข้อมูลผู้ป่วยและสถิติสรุป -->
<div class="card card-medical bg-white mb-4 border-top border-4 border-primary">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-md-4 border-end">
                <span class="badge bg-dark fs-6 mb-1">HN: {{ $hn }}</span>
                <h4 class="fw-bold text-dark mb-1">{{ $latest->or_patname }}</h4>
                <div class="small text-secondary">
                    อายุ: <strong>{{ $latest->or_age ?? '-' }} ปี</strong> |
                    เพศ: <strong>{{ $latest->or_sex === 'F' ? 'หญิง' : 'ชาย' }}</strong> |
                    หอผู้ป่วยล่าสุด: <strong>{{ optional($latest->ward)->wd_name ?? $latest->or_ward }}</strong>
                </div>
            </div>
            <div class="col-md-4 border-end">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="small text-muted">น้ำหนัก / ส่วนสูง</div>
                        <div class="fw-bold">{{ number_format($latest->or_wt, 1) }} kg</div>
                        <small class="text-muted">{{ number_format($latest->or_ht, 0) }} cm</small>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">BSA ล่าสุด</div>
                        <div class="fw-bold text-primary fs-4">{{ number_format($latest->or_bsa, 2) }}</div>
                        <small class="text-muted">m&sup2;</small>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">GFR ล่าสุด</div>
                        <div class="fw-bold text-success fs-4">{{ $latest->or_gfr ? number_format($latest->or_gfr, 1) : '-' }}</div>
                        <small class="text-muted">SCr {{ $latest->or_scr ?? '-' }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="row g-2 text-center">
                    <div class="col-6">
                        <div class="border rounded-3 p-2 bg-success-subtle">
                            <div class="small text-success-emphasis">รับยาจริงทั้งหมด</div>
                            <div class="fs-4 fw-bold text-success">{{ $activeOrders->count() }} ครั้ง</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded-3 p-2 bg-danger-subtle">
                            <div class="small text-danger">คำสั่งที่ยกเลิก</div>
                            <div class="fs-4 fw-bold text-danger">{{ $cancelledOrders->count() }} ครั้ง</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 1. ตารางขนาดยาสะสม (Cumulative Dose Tracker) -->
<div class="card card-medical bg-white mb-4 border-start border-4 border-danger">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="fw-bold text-danger mb-0">
                <i class="bi bi-graph-up-arrow me-1"></i> 1. สรุปขนาดยาสะสมตลอดการรักษา (Cumulative Dose Tracker)
            </h6>
            <small class="text-secondary">คำนวณเฉพาะคำสั่งใช้ยาที่ไม่ถูกยกเลิก เพื่อช่วยเฝ้าระวังพิษสะสม (เช่น Doxorubicin Cardiotoxicity / Cisplatin Nephrotoxicity)</small>
        </div>
        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $cumulativeDoses->count() }} ชนิดยา</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">รหัส / ชื่อยาเคมีบำบัด</th>
                    <th class="text-end">ขนาดยาสะสมรวม (Total Dose)</th>
                    <th class="text-end">ขนาดยาสะสมต่อพื้นที่ผิวกาย (mg/m&sup2;)</th>
                    <th class="text-center">จำนวนรอบ (Cycles)</th>
                    <th class="text-center">รวมจำนวนวันให้ยา</th>
                    <th class="text-center">ช่วงวันที่รับยา (ครั้งแรก &ndash; ล่าสุด)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cumulativeDoses as $cd)
                    <tr>
                        <td class="ps-3 fw-bold text-primary">
                            [{{ $cd['medcode'] }}] {{ $cd['dg_name'] }}
                        </td>
                        <td class="text-end fw-bold text-danger fs-6">
                            {{ number_format($cd['total_mg'], 2) }} {{ $cd['unit'] }}
                        </td>
                        <td class="text-end fw-bold text-dark">
                            {{ $cd['total_mg_m2'] > 0 ? number_format($cd['total_mg_m2'], 1) . ' mg/m²' : '-' }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary-subtle text-primary">{{ $cd['cycles_count'] }} รอบ</span>
                        </td>
                        <td class="text-center">{{ $cd['total_days'] }} วัน</td>
                        <td class="text-center small text-secondary">
                            {{ \Carbon\Carbon::parse($cd['first_date'])->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($cd['last_date'])->format('d/m/Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-3 text-muted">ยังไม่มีรายการยาสะสมที่ใช้งานจริง</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- 2. ประวัติการรับการรักษาทั้งหมดทุก Cycle -->
<div class="card card-medical bg-white">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-journal-medical text-primary me-1"></i> 2. ประวัติการรับยาเคมีบำบัดทั้งหมด (เรียงจากล่าสุด &rarr; เก่าสุด)
            </h6>
            <small class="text-secondary">คลิกปุ่ม <strong>"รายงานการรักษา &amp; เวลา"</strong> เพื่อดูรายละเอียดครบวงจรตั้งแต่แพทย์สั่งจนถึงพยาบาลบริหารยาพร้อมสรุปเวลา</small>
        </div>
        <span class="badge bg-dark">รวม {{ $orders->count() }} ใบสั่งยา</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">วันที่เริ่ม / Order ID</th>
                    <th>สูตร Regimen (Cycle)</th>
                    <th>BSA / GFR ณ วันนั้น</th>
                    <th>รายการยาเคมีบำบัดที่สั่ง</th>
                    <th>สถานะคำสั่ง</th>
                    <th class="text-end pe-3">รายงาน / เอกสาร</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $ord)
                    @php
                        $isCancelled = (int) ($ord->or_cancel ?? 0) === 1;
                        $totalDayRecs = $ord->dayRecords->count();
                        $sentDayRecs  = $ord->dayRecords->where('or_send', 1)->count();
                    @endphp
                    <tr class="{{ $isCancelled ? 'table-danger opacity-75' : '' }}">
                        <td class="ps-3">
                            <div class="fw-bold {{ $isCancelled ? 'text-decoration-line-through text-danger' : 'text-dark' }}">
                                {{ \Carbon\Carbon::parse($ord->or_date)->format('d/m/Y') }}
                            </div>
                            <small class="text-primary fw-semibold">{{ $ord->or_id }}</small>
                            <div class="small text-muted">แพทย์: {{ $ord->or_doctor_name ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-primary fs-6">{{ $ord->or_reg_code ?? 'CUSTOM' }}</span>
                            <div class="fw-bold text-dark mt-1">Cycle #{{ $ord->or_cycle ?? 1 }}</div>
                            @if(($ord->or_adjust_percent ?? 0) > 0)
                                <span class="badge bg-warning text-dark mt-1">ลด {{ number_format($ord->or_adjust_percent, 0) }}%</span>
                            @endif
                        </td>
                        <td class="small">
                            <div><strong>BSA:</strong> {{ number_format($ord->or_bsa, 2) }} m&sup2;</div>
                            <div><strong>Wt:</strong> {{ number_format($ord->or_wt, 1) }} kg | <strong>SCr:</strong> {{ $ord->or_scr ?? '-' }}</div>
                            <div><strong>GFR:</strong> {{ $ord->or_gfr ? number_format($ord->or_gfr, 1) : '-' }}</div>
                        </td>
                        <td class="small">
                            @foreach($ord->details as $d)
                                <div class="mb-1">
                                    @if($d->or_bag_group)
                                        <span class="badge text-white" style="background-color:#7c3aed; font-size:0.68rem;">{{ $d->or_bag_group }}</span>
                                    @endif
                                    <strong class="text-dark">{{ optional($d->medication)->dg_name ?? $d->or_medcode }}</strong>
                                    <span class="text-danger fw-bold">{{ number_format($d->or_meddose, 2) }} {{ optional($d->unit)->un_unit ?? 'mg' }}</span>
                                    @if(($d->or_reduce_pct ?? 0) > 0)
                                        <span class="text-danger">(-{{ number_format($d->or_reduce_pct, 0) }}%)</span>
                                    @endif
                                    @if(!empty($d->or_additives) && is_array($d->or_additives))
                                        <span class="text-info-emphasis">
                                            + {{ collect($d->or_additives)->pluck('name')->filter()->implode(', ') }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </td>
                        <td>
                            @if($isCancelled)
                                <span class="badge bg-danger"><i class="bi bi-x-octagon-fill me-1"></i>ยกเลิกแล้ว</span>
                                <div class="small text-danger mt-1">{{ $ord->or_cancelled_by }}</div>
                            @elseif($ord->or_confirm == 1)
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>รับคำสั่งแล้ว</span>
                                @if($totalDayRecs > 0)
                                    <div class="small mt-1 {{ $sentDayRecs === $totalDayRecs ? 'text-success fw-bold' : 'text-secondary' }}">
                                        บริหารยาแล้ว {{ $sentDayRecs }}/{{ $totalDayRecs }} โดส
                                    </div>
                                @endif
                            @else
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>รอรับคำสั่ง</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex flex-column gap-1 align-items-end">
                                <a href="{{ route('reports.visit', $ord->or_id) }}" class="btn btn-sm btn-primary fw-semibold shadow-sm">
                                    <i class="bi bi-clock-history me-1"></i> รายงานการรักษา &amp; เวลา
                                </a>
                                <div class="d-flex gap-1">
                                    @if(auth()->user()->canAccessPharmacist())
                                        <a href="{{ route('pharmacist.show', $ord->or_id) }}" class="btn btn-xs btn-sm btn-outline-success py-0 px-2">หน้าเภสัชฯ</a>
                                    @endif
                                    @if(auth()->user()->canAccessDoctor())
                                        <a href="{{ route('doctor.show', $ord->or_id) }}" class="btn btn-xs btn-sm btn-outline-secondary py-0 px-2">หน้าแพทย์</a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
