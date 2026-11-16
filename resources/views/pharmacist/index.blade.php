@extends('layouts.app')

@section('title', 'ระบบเภสัชกร - รับคำสั่งและเตรียมยาเคมีบำบัด')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-success mb-1">
            <i class="bi bi-capsule-pill me-2"></i>ระบบเภสัชกร: รับคำสั่งใช้ยาและเตรียมยาเคมีบำบัด (Pharmacist Station)
        </h4>
        <small class="text-secondary">
            ระบบตรวจสอบคำสั่งใหม่แบบ Real-time อัตโนมัติ ยืนยันรับคำสั่ง แตกคำสั่งรายวัน และพิมพ์ใบจัดยา (A5 แนวนอน) / ฉลากยา
        </small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-success-subtle text-success border border-success px-3 py-2 d-flex align-items-center gap-2">
            <span class="spinner-grow spinner-grow-sm text-success" role="status"></span>
            <span>Auto-Refresh Real-time (<span id="lastCheckTime">{{ now()->format('H:i:s') }}</span>)</span>
        </span>
        <a href="{{ route('pharmacist.index') }}" class="btn btn-outline-success btn-sm">
            <i class="bi bi-arrow-clockwise me-1"></i> รีเฟรชทันที
        </a>
    </div>
</div>

<!-- สรุปสถิติ Real-time -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-danger">
            <div class="small text-secondary fw-semibold">รอรับคำสั่ง (New Orders)</div>
            <div class="fs-3 fw-bold text-danger" id="statPending">{{ $stats['pending'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-success">
            <div class="small text-secondary fw-semibold">รับคำสั่งแล้ว (Confirmed)</div>
            <div class="fs-3 fw-bold text-success" id="statConfirmed">{{ $stats['confirmed'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-primary">
            <div class="small text-secondary fw-semibold">คำสั่งทั้งหมด</div>
            <div class="fs-3 fw-bold text-primary" id="statTotal">{{ $stats['total'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-info">
            <div class="small text-secondary fw-semibold">รายการยารายวันวันนี้</div>
            <div class="fs-3 fw-bold text-info" id="statTodayDoses">{{ $stats['today_doses'] }}</div>
        </div>
    </div>
</div>

<!-- ตัวกรองค้นหา -->
<div class="card card-medical bg-white mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('pharmacist.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="ค้นหา HN, ชื่อผู้ป่วย, เลขที่ใบสั่งยา, สูตร Regimen...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- ทุกสถานะคำสั่ง --</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>รอรับคำสั่ง (Pending)</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>รับคำสั่งแล้ว (Confirmed)</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-success"><i class="bi bi-funnel-fill me-1"></i> กรองข้อมูล</button>
                <a href="{{ route('pharmacist.index') }}" class="btn btn-outline-secondary">ล้างค่า</a>
            </div>
        </form>
    </div>
</div>

<!-- ตารางรายการคำสั่งใช้ยา -->
<div class="card card-medical bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>เลขที่ใบสั่งยา / วันที่</th>
                    <th>HN / ชื่อ-สกุลผู้ป่วย</th>
                    <th>หอผู้ป่วย</th>
                    <th>BSA / GFR</th>
                    <th>สูตร Regimen</th>
                    <th>การปรับลดขนาดยา</th>
                    <th>สถานะคำสั่ง</th>
                    <th class="text-end">การจัดการ (Confirm / พิมพ์)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="{{ $order->or_confirm == 0 ? 'table-warning' : '' }}">
                        <td>
                            <div class="fw-bold text-dark">{{ $order->or_id }}</div>
                            <small class="text-secondary"><i class="bi bi-calendar3 me-1"></i>เริ่ม: {{ \Carbon\Carbon::parse($order->or_date)->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $order->or_patname }}</div>
                            <span class="badge bg-white text-dark border">HN: {{ $order->or_hn }}</span>
                            @if($order->or_doctor_name)
                                <small class="text-muted d-block mt-1">แพทย์: {{ $order->or_doctor_name }}</small>
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
                                GFR: {{ $order->or_gfr ? number_format($order->or_gfr, 1) : '-' }} | SCr: {{ $order->or_scr ?? '-' }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1">{{ $order->or_reg_code }}</span>
                            <div class="small text-secondary mt-1">Cycle #{{ $order->or_cycle ?? 1 }} ({{ $order->details->count() }} ตัวยา)</div>
                        </td>
                        <td>
                            @if($order->or_adjust_percent > 0)
                                <span class="badge bg-danger">ลด {{ number_format($order->or_adjust_percent, 0) }}%</span>
                                <div class="small text-danger mt-1 text-truncate" style="max-width: 170px;" title="{{ $order->or_adjust_reason }}">
                                    {{ $order->or_adjust_reason }}
                                </div>
                            @else
                                <span class="badge bg-light text-secondary border">100% (Full Dose)</span>
                            @endif
                        </td>
                        <td>
                            @if($order->or_confirm == 1)
                                <span class="badge bg-success px-2 py-1">
                                    <i class="bi bi-check-circle-fill me-1"></i>รับคำสั่งแล้ว
                                </span>
                                @if($order->or_pharmacist_name)
                                    <small class="d-block text-muted mt-1">{{ $order->or_pharmacist_name }}</small>
                                @endif
                            @else
                                <span class="badge bg-danger px-2 py-1">
                                    <i class="bi bi-bell-fill me-1"></i>รอรับคำสั่งใหม่
                                </span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1 flex-wrap">
                                @if($order->or_confirm == 0)
                                    <form method="POST" action="{{ route('pharmacist.confirm', $order->or_id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success fw-semibold">
                                            <i class="bi bi-check2-circle me-1"></i> Confirm รับคำสั่ง
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('pharmacist.show', $order->or_id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-folder2-open me-1"></i> รายละเอียด
                                </a>

                                <a href="{{ route('pharmacist.print.working-formula', $order->or_id) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="พิมพ์ใบจัดยา A5 แนวนอน">
                                    <i class="bi bi-printer-fill me-1"></i> ใบจัดยา (A5)
                                </a>

                                <a href="{{ route('pharmacist.print.labels', $order->or_id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="พิมพ์ฉลากยาเคมีบำบัด">
                                    <i class="bi bi-tag-fill me-1"></i> ฉลากยา
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-secondary">
                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                            ยังไม่มีรายการคำสั่งใช้ยาเคมีบำบัดที่ส่งเข้ามา
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

@section('js_after')
<script>
    let currentStateToken = @json($latestToken);

    // ตรวจสอบคำสั่งใหม่จากแพทย์ทุกๆ 5 วินาที หากมีคำสั่งใหม่หรือมีการเปลี่ยนแปลงสถานะ ให้รีเฟรชหน้าทันที
    setInterval(function () {
        fetch(@json(route('pharmacist.poll')), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.time) {
                document.getElementById('lastCheckTime').innerText = data.time;
            }
            if (data.token && data.token !== currentStateToken) {
                window.location.reload();
            }
        })
        .catch(() => {});
    }, 5000);
</script>
@endsection
