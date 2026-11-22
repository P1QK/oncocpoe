@extends('layouts.app')

@section('title', 'Profile การรับยาเคมีบำบัดของผู้ป่วย & ระบบรายงาน')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-primary mb-1">
            <i class="bi bi-person-lines-fill me-2"></i>Profile การรับยาเคมีบำบัดของผู้ป่วย &amp; รายงานการรักษา (Patient Profile &amp; Reports)
        </h4>
        <small class="text-secondary">
            ค้นหาผู้ป่วยจาก HN หรือชื่อ-สกุล เพื่อดูประวัติการรับยาทุก Cycle, ขนาดยาสะสม (Cumulative Dose) และรายงานสรุปเวลาให้ยาต่อครั้ง (แพทย์ → เภสัชกร → พยาบาล)
        </small>
    </div>
    <div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-2">
            <i class="bi bi-people-fill me-1"></i> ผู้ป่วยทั้งหมด {{ $patients->count() }} ราย
        </span>
    </div>
</div>

<!-- ช่องค้นหาผู้ป่วย -->
<div class="card card-medical bg-white mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('reports.patients.index') }}" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-primary"></i></span>
                    <input type="text" name="search" class="form-control" value="{{ $search }}"
                           placeholder="พิมพ์ค้นหา HN, ชื่อ-นามสกุลผู้ป่วย, สูตร Regimen หรือเลขที่ใบสั่งยา...">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-lg fw-semibold w-100">
                    <i class="bi bi-search me-1"></i> ค้นหาผู้ป่วย
                </button>
                @if($search !== '')
                    <a href="{{ route('reports.patients.index') }}" class="btn btn-outline-secondary btn-lg">ล้าง</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- ตารางรายชื่อผู้ป่วย -->
<div class="card card-medical bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">HN / ชื่อ-นามสกุลผู้ป่วย</th>
                    <th>หอผู้ป่วยล่าสุด</th>
                    <th>ค่าทางคลินิกล่าสุด (BSA / GFR)</th>
                    <th>สูตรยาที่เคยได้รับ</th>
                    <th class="text-center">จำนวนครั้งที่รับยา</th>
                    <th class="text-center">วันที่รับยาล่าสุด</th>
                    <th class="text-end pe-3">เปิดดู Profile</th>
                </tr>
            </thead>
            <tbody>
                @forelse($patients as $pat)
                    <tr>
                        <td class="ps-3">
                            <div class="fw-bold text-dark fs-6">{{ $pat->or_patname }}</div>
                            <span class="badge bg-dark">HN: {{ $pat->or_hn }}</span>
                            @if($pat->or_age)
                                <small class="text-muted ms-1">({{ $pat->or_age }} ปี, {{ $pat->or_sex === 'F' ? 'หญิง' : 'ชาย' }})</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-dark">{{ $pat->ward_name }}</span>
                        </td>
                        <td>
                            <div>
                                <strong>BSA:</strong> <span class="text-primary fw-bold">{{ number_format($pat->or_bsa, 2) }} m&sup2;</span>
                                @if($pat->or_gfr)
                                    | <strong>GFR:</strong> <span class="text-success fw-bold">{{ number_format($pat->or_gfr, 1) }}</span>
                                @endif
                            </div>
                            <small class="text-secondary">Wt: {{ number_format($pat->or_wt, 1) }} kg | Ht: {{ number_format($pat->or_ht, 0) }} cm</small>
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($pat->regimens_used as $rc)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $rc }}</span>
                                @endforeach
                            </div>
                            <small class="text-muted">ล่าสุด: Cycle #{{ $pat->latest_cycle }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success fs-6">{{ $pat->active_count }} ครั้ง</span>
                            @if($pat->cancelled_count > 0)
                                <div class="small text-danger mt-1">(ยกเลิก {{ $pat->cancelled_count }})</div>
                            @endif
                        </td>
                        <td class="text-center fw-semibold">
                            {{ \Carbon\Carbon::parse($pat->latest_date)->format('d/m/Y') }}
                        </td>
                        <td class="text-end pe-3">
                            <a href="{{ route('reports.patients.profile', $pat->or_hn) }}" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                                <i class="bi bi-person-vcard-fill me-1"></i> เปิด Profile ผู้ป่วย
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-secondary">
                            <i class="bi bi-person-x fs-1 d-block mb-2 opacity-50"></i>
                            ไม่พบข้อมูลผู้ป่วยที่ตรงกับคำค้นหา
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
