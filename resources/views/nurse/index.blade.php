@extends('layouts.app')

@section('title', 'ระบบพยาบาล - บริหารยาเคมีบำบัด')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold text-info mb-1">
            <i class="bi bi-clipboard2-pulse-fill me-2"></i>ระบบพยาบาล: ตรวจสอบและบริหารยาเคมีบำบัดรายวัน (Nurse Station)
        </h4>
        <small class="text-secondary">แสดงตารางการให้ยาเคมีบำบัดรายวัน (<code>onc_dayrecord</code>) ที่ผ่านการยืนยันจากเภสัชกรแล้ว</small>
    </div>
</div>

<!-- สรุปสถิติ -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-info">
            <div class="small text-secondary">รายการให้ยาวันนี้</div>
            <div class="fs-3 fw-bold text-info">{{ $stats['today_doses'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-success">
            <div class="small text-secondary">เภสัชกรเตรียมยาพร้อมแล้ว</div>
            <div class="fs-3 fw-bold text-success">{{ $stats['ready_to_admin'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-primary">
            <div class="small text-secondary">บริหารยา/รับยาแล้ว</div>
            <div class="fs-3 fw-bold text-primary">{{ $stats['delivered'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-dark">
            <div class="small text-secondary">คำสั่งที่ยืนยันทั้งหมด</div>
            <div class="fs-3 fw-bold text-dark">{{ $stats['confirmed_orders'] }}</div>
        </div>
    </div>
</div>

<!-- ตัวกรอง -->
<div class="card card-medical bg-white mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('nurse.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="ค้นหา HN หรือ ชื่อผู้ป่วย...">
            </div>
            <div class="col-md-3">
                <select name="ward" class="form-select">
                    <option value="">-- ทุกหอผู้ป่วย --</option>
                    @foreach($wards as $w)
                        <option value="{{ $w->wd_code }}" {{ $selectedWard == $w->wd_code ? 'selected' : '' }}>
                            [{{ $w->wd_code }}] {{ $w->wd_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" value="{{ $selectedDate }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-info text-dark fw-semibold w-100">กรองข้อมูล</button>
                <a href="{{ route('nurse.index') }}" class="btn btn-outline-secondary">ล้าง</a>
            </div>
        </form>
    </div>
</div>

<!-- ตารางรายการบริหารยารายวัน -->
<div class="card card-medical bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>วันที่ให้ยา</th>
                    <th>HN / ชื่อผู้ป่วย / หอผู้ป่วย</th>
                    <th>รายการยาเคมีบำบัด &amp; ขนาดยา</th>
                    <th>สารน้ำ &amp; ปริมาตรรวม</th>
                    <th>วิธีบริหารยา / เวลา</th>
                    <th>ข้อควรระวัง (High-Alert)</th>
                    <th>สถานะ</th>
                    <th class="text-end">ยืนยันรับ/ให้ยา</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dayRecords as $rec)
                    <tr>
                        <td>
                            <div class="fw-bold text-primary">{{ \Carbon\Carbon::parse($rec->or_daterec)->format('d/m/Y') }}</div>
                            <span class="badge bg-light text-dark border">{{ $rec->or_text }}</span>
                        </td>
                        <td>
                            <div class="fw-bold">{{ optional($rec->orderHeader)->or_patname ?? '-' }}</div>
                            <small class="text-secondary">
                                HN: {{ optional($rec->orderHeader)->or_hn ?? '-' }} |
                                {{ optional(optional($rec->orderHeader)->ward)->wd_name ?? optional($rec->orderHeader)->or_ward }}
                            </small>
                        </td>
                        <td>
                            <div class="fw-bold text-danger">[{{ $rec->or_medcode }}] {{ optional($rec->medication)->dg_name ?? $rec->or_medcode }}</div>
                            <span class="badge bg-danger-subtle text-danger fs-6">{{ number_format($rec->or_meddose, 2) }} mg</span>
                        </td>
                        <td>
                            <div>{{ optional($rec->solution)->so_name ?? $rec->or_soln }} ({{ number_format($rec->or_solvol, 0) }} mL)</div>
                            <small class="text-muted">ปริมาตรรวม: {{ number_format($rec->or_totalvol, 2) }} mL</small>
                        </td>
                        <td>
                            <div>{{ optional($rec->route)->rt_name ?? $rec->or_medroute }}</div>
                            <small class="fw-bold text-primary">{{ optional($rec->timing)->tm_time ?? $rec->or_medtime }}</small>
                        </td>
                        <td>
                            <small class="text-danger">{{ optional($rec->medication)->dg_precaution ?? '-' }}</small>
                        </td>
                        <td>
                            @if($rec->or_send == 1)
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>บริหารยาแล้ว</span>
                            @elseif($rec->or_prep == 1)
                                <span class="badge bg-info text-dark">เตรียมยาพร้อมใช้</span>
                            @else
                                <span class="badge bg-warning text-dark">กำลังเตรียมยา</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($rec->or_send == 0)
                                <form method="POST" action="{{ route('nurse.acknowledge') }}">
                                    @csrf
                                    <input type="hidden" name="or_id" value="{{ $rec->or_id }}">
                                    <input type="hidden" name="or_daterec" value="{{ $rec->or_daterec }}">
                                    <input type="hidden" name="or_orderno" value="{{ $rec->or_orderno }}">
                                    <button type="submit" class="btn btn-sm btn-outline-success fw-semibold">
                                        <i class="bi bi-check2-circle me-1"></i> ยืนยันให้ยา
                                    </button>
                                </form>
                            @else
                                <span class="text-success small fw-semibold"><i class="bi bi-check-all"></i> เรียบร้อย</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-secondary">
                            ยังไม่มีรายการตารางให้ยารายวัน (รายการจะแสดงเมื่อเภสัชกรกด Confirm รับคำสั่งใช้ยาแล้ว)
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
