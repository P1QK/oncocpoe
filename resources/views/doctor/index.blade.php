@extends('layouts.app')

@section('title', 'ระบบแพทย์ - รายการคำสั่งใช้ยาเคมีบำบัด')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-primary mb-1">
            <i class="bi bi-heart-pulse-fill me-2"></i>ระบบแพทย์: สั่งใช้ยาเคมีบำบัดชนิดฉีด (Doctor Ordering)
        </h4>
        <small class="text-secondary">
            แสดงเฉพาะคำสั่งใช้ยาประจำวันที่เลือก (<strong class="text-dark">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</strong>)
            &mdash; เลือกสูตร Regimen คำนวณขนาดยาตาม BSA / GFR ปรับลดขนาดยา (%) และส่งคำสั่งไปยังห้องเตรียมยา
        </small>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-2">
            <i class="bi bi-calendar-event me-1"></i>
            วันที่แสดงผล: {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
            @if($selectedDate === today()->format('Y-m-d'))
                (วันนี้)
            @endif
        </span>
        <a href="{{ route('doctor.create') }}" class="btn btn-primary fw-semibold shadow-sm">
            <i class="bi bi-plus-circle-fill me-1"></i> สั่งใช้ยาเคมีบำบัดใหม่ (New Order)
        </a>
    </div>
</div>

<!-- สรุปสถิติ -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-primary">
            <div class="small text-secondary">คำสั่งยาในวันที่เลือก</div>
            <div class="fs-3 fw-bold text-primary">{{ $stats['total'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-warning">
            <div class="small text-secondary">รอเภสัชกรรับคำสั่ง (วันที่เลือก)</div>
            <div class="fs-3 fw-bold text-warning">{{ $stats['pending'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-success">
            <div class="small text-secondary">เภสัชกรรับคำสั่งแล้ว (วันที่เลือก)</div>
            <div class="fs-3 fw-bold text-success">{{ $stats['confirmed'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-info">
            <div class="small text-secondary">สั่งยาวันนี้ ({{ today()->format('d/m/Y') }})</div>
            <div class="fs-3 fw-bold text-info">{{ $stats['today'] }}</div>
        </div>
    </div>
</div>

<!-- ตัวกรองค้นหา -->
<div class="card card-medical bg-white mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('doctor.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">วันที่สั่งใช้ยา (เลือกวันที่แสดง):</label>
                <input type="date" name="date" class="form-control fw-bold border-primary" value="{{ $selectedDate }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-secondary mb-1">ค้นหาผู้ป่วย / ใบสั่งยา:</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="ค้นหา HN, ชื่อผู้ป่วย, เลขที่ใบสั่งยา, สูตร Regimen...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">สถานะคำสั่ง:</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">-- ทุกสถานะคำสั่ง --</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>รอเภสัชกรรับคำสั่ง</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>รับคำสั่งแล้ว</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ยกเลิกแล้ว</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill me-1"></i> ค้นหา</button>
                <a href="{{ route('doctor.index') }}" class="btn btn-outline-secondary" title="กลับสู่วันปัจจุบัน">วันนี้</a>
            </div>
        </form>
    </div>
</div>

<!-- ตารางรายการคำสั่งยา -->
<div class="card card-medical bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>เลขที่ใบสั่งยา / วันที่เริ่ม</th>
                    <th>HN / ชื่อ-สกุลผู้ป่วย</th>
                    <th>หอผู้ป่วย</th>
                    <th>BSA / GFR</th>
                    <th>สูตร Regimen (Cycle)</th>
                    <th>การปรับลดขนาดยา</th>
                    <th>สถานะคำสั่ง</th>
                    <th class="text-end">รายละเอียด</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php $isCancelled = (int) ($order->or_cancel ?? 0) === 1; @endphp
                    <tr class="{{ $isCancelled ? 'table-danger opacity-75' : '' }}">
                        <td>
                            <div class="fw-bold {{ $isCancelled ? 'text-decoration-line-through text-danger' : 'text-primary' }}">{{ $order->or_id }}</div>
                            <small class="text-secondary"><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($order->or_date)->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $order->or_patname }}</div>
                            <a href="{{ route('reports.patients.profile', $order->or_hn) }}" class="badge bg-light text-primary border border-primary-subtle text-decoration-none" title="คลิกเพื่อเปิดดู Profile การรับยาของผู้ป่วยรายนี้">
                                <i class="bi bi-person-vcard me-1"></i>HN: {{ $order->or_hn }}
                            </a>
                            @if($order->or_age)
                                <small class="text-muted ms-1">({{ $order->or_age }} ปี, {{ $order->or_sex === 'F' ? 'หญิง' : 'ชาย' }})</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-dark">
                                {{ optional($order->ward)->wd_name ?? $order->or_ward }}
                            </span>
                        </td>
                        <td>
                            <div><strong>BSA:</strong> {{ number_format($order->or_bsa, 2) }} m&sup2;</div>
                            <small class="text-secondary">
                                Wt: {{ number_format($order->or_wt, 1) }} kg | Ht: {{ number_format($order->or_ht, 0) }} cm
                                @if($order->or_gfr)
                                    | GFR: {{ number_format($order->or_gfr, 1) }}
                                @endif
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1">
                                {{ $order->or_reg_code ?? '-' }}
                            </span>
                            <div class="small text-muted mt-1">
                                Cycle #{{ $order->or_cycle ?? 1 }} ({{ $order->details->count() }} รายการยา)
                            </div>
                        </td>
                        <td>
                            @if($order->or_adjust_percent > 0)
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-arrow-down-circle-fill me-1"></i>ลด {{ number_format($order->or_adjust_percent, 0) }}%
                                </span>
                                <div class="small text-danger mt-1 text-truncate" style="max-width: 180px;" title="{{ $order->or_adjust_reason }}">
                                    {{ $order->or_adjust_reason }}
                                </div>
                            @else
                                <span class="badge bg-light text-secondary border">100% (Full Dose)</span>
                            @endif
                        </td>
                        <td>
                            @if($isCancelled)
                                <span class="badge bg-danger px-2 py-1">
                                    <i class="bi bi-x-octagon-fill me-1"></i>ยกเลิกแล้ว
                                </span>
                                @if($order->or_cancelled_by)
                                    <div class="small text-danger mt-1">{{ $order->or_cancelled_by }}</div>
                                @endif
                            @elseif($order->or_confirm == 1)
                                <span class="badge bg-success px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i>รับคำสั่งแล้ว
                                </span>
                                @if($order->or_pharmacist_name)
                                    <div class="small text-muted mt-1">โดย: {{ $order->or_pharmacist_name }}</div>
                                @endif
                            @else
                                <span class="badge bg-warning text-dark px-2 py-1">
                                    <i class="bi bi-hourglass-split me-1"></i>รอเภสัชกรรับคำสั่ง
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1 flex-wrap">
                                @if(!$isCancelled)
                                    <a href="{{ route('doctor.print', $order->or_id) }}" target="_blank" class="btn btn-sm btn-dark fw-semibold" title="พิมพ์ใบสั่งยาเต็มรูปแบบ (A4)">
                                        <i class="bi bi-printer-fill me-1"></i> พิมพ์ใบสั่งยา
                                    </a>
                                @endif
                                <a href="{{ route('doctor.show', $order->or_id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye-fill me-1"></i> ดูใบสั่งยา
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-secondary">
                            <i class="bi bi-calendar2-x fs-1 d-block mb-2 opacity-50"></i>
                            ไม่มีรายการคำสั่งใช้ยาเคมีบำบัดในวันที่ <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</strong>
                            <div class="small text-muted mt-1">สามารถเลือกวันที่อื่นจากช่องวันที่ด้านบน หรือสร้างคำสั่งใช้ยาใหม่</div>
                            <div class="mt-3">
                                <a href="{{ route('doctor.create') }}" class="btn btn-sm btn-primary">+ สร้างคำสั่งใช้ยาเคมีบำบัด</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
        <div class="card-footer bg-white">
            {{ $orders->links() }}
        </div>
    @endif
</div>
@endsection
