@extends('layouts.app')

@section('title', 'ระบบพยาบาล - บริหารยาเคมีบำบัด')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold text-info mb-1">
            <i class="bi bi-clipboard2-pulse-fill me-2"></i>ระบบพยาบาล: ตรวจสอบและบริหารยาเคมีบำบัดรายวัน (Nurse Station)
        </h4>
        <small class="text-secondary">
            แสดง <strong>เฉพาะรายการยาที่ให้ตรงวัน</strong>
            (ประจำวันที่ <strong class="text-dark">{{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</strong> &mdash; ไม่แสดงรายการยาของวันถัดไป)
            จัดกลุ่มตามผู้ป่วยพร้อมรายการยาย่อยด้านใต้
        </small>
    </div>
    <div>
        <span class="badge bg-info text-dark fs-6 px-3 py-2 shadow-sm">
            <i class="bi bi-calendar-check-fill me-1"></i>
            วันที่ให้ยา: {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}
            @if($selectedDate === today()->format('Y-m-d'))
                (วันนี้)
            @endif
        </span>
    </div>
</div>

<!-- สรุปสถิติประจำวันที่เลือก -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-dark">
            <div class="small text-secondary">ผู้ป่วยที่ได้รับยาในวันนี้ (ราย)</div>
            <div class="fs-3 fw-bold text-dark">{{ $stats['patients_today'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-info">
            <div class="small text-secondary">รายการยารวมในวันนี้ (ขวด/โดส)</div>
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
</div>

<!-- ตัวกรอง -->
<div class="card card-medical bg-white mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('nurse.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-secondary mb-1">ค้นหาผู้ป่วย (HN / ชื่อ-นามสกุล):</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="ระบุ HN หรือ ชื่อ-นามสกุลผู้ป่วย...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">หอผู้ป่วย (Ward):</label>
                <select name="ward" class="form-select" onchange="this.form.submit()">
                    <option value="">-- ทุกหอผู้ป่วย --</option>
                    @foreach($wards as $w)
                        <option value="{{ $w->wd_code }}" {{ $selectedWard == $w->wd_code ? 'selected' : '' }}>
                            [{{ $w->wd_code }}] {{ $w->wd_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">วันที่ให้ยา (แสดงเฉพาะตรงวัน):</label>
                <input type="date" name="date" class="form-control fw-bold border-info" value="{{ $selectedDate }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-info text-dark fw-semibold w-100">
                    <i class="bi bi-funnel-fill me-1"></i> กรองข้อมูล
                </button>
                <a href="{{ route('nurse.index') }}" class="btn btn-outline-secondary" title="กลับสู่วันนี้">วันนี้</a>
            </div>
        </form>
    </div>
</div>

<!-- รายการผู้ป่วยและรายการยาย่อยใต้ชื่อผู้ป่วย (เฉพาะยาที่ให้ตรงวัน) -->
@forelse($patientsGroup as $patIndex => $patient)
    <div class="card card-medical bg-white mb-4 border-top border-4 {{ $patient->all_sent ? 'border-success' : 'border-info' }}">
        <!-- ส่วนหัวข้อมูลผู้ป่วย: HN และ ชื่อ-นามสกุลผู้ป่วย -->
        <div class="card-header bg-light py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="badge bg-dark fs-6 px-3 py-2">
                        <i class="bi bi-person-vcard me-1"></i> HN: {{ $patient->or_hn }}
                    </span>
                    <h5 class="fw-bold text-dark mb-0 me-2">
                        {{ $patient->or_patname }}
                    </h5>
                    <span class="badge bg-secondary-subtle text-dark border px-2 py-1">
                        <i class="bi bi-hospital me-1"></i> หอผู้ป่วย: {{ $patient->ward_name }}
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                        Regimen: {{ $patient->or_reg_code }} (Cycle #{{ $patient->or_cycle }})
                    </span>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-info-subtle text-dark border border-info px-3 py-2">
                        รายการยาวันนี้: <strong>{{ $patient->doses_count }}</strong> รายการ
                        (บริหารแล้ว {{ $patient->send_count }}/{{ $patient->doses_count }})
                    </span>

                    @if(!$patient->all_sent)
                        <form method="POST" action="{{ route('nurse.acknowledge') }}" class="d-inline"
                              onsubmit="return confirm('ยืนยันการรับยาและบริหารยาครบทุกรายการของ {{ $patient->or_patname }} (HN: {{ $patient->or_hn }}) ประจำวันที่ {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }} ใช่หรือไม่?');">
                            @csrf
                            <input type="hidden" name="or_id" value="{{ $patient->or_id }}">
                            <input type="hidden" name="or_daterec" value="{{ $selectedDate }}">
                            <button type="submit" class="btn btn-sm btn-success fw-semibold shadow-sm">
                                <i class="bi bi-check2-all me-1"></i> ยืนยันให้ยาครบทุกรายการวันนี้
                            </button>
                        </form>
                    @else
                        <span class="badge bg-success px-3 py-2">
                            <i class="bi bi-check-circle-fill me-1"></i> บริหารยาวันนี้ครบแล้ว
                        </span>
                    @endif
                </div>
            </div>

            <!-- ข้อมูลทางคลินิกเสริมของผู้ป่วย -->
            <div class="mt-2 pt-2 border-top d-flex flex-wrap gap-3 small text-secondary">
                <span><strong>เลขที่คำสั่ง (Order ID):</strong> {{ $patient->or_id }}</span>
                @if($patient->or_age)
                    <span><strong>อายุ/เพศ:</strong> {{ $patient->or_age }} ปี ({{ $patient->or_sex === 'F' ? 'หญิง' : 'ชาย' }})</span>
                @endif
                @if($patient->or_wt && $patient->or_ht)
                    <span><strong>น้ำหนัก/ส่วนสูง:</strong> {{ number_format($patient->or_wt, 1) }} kg / {{ number_format($patient->or_ht, 0) }} cm</span>
                @endif
                @if($patient->or_bsa)
                    <span><strong>BSA:</strong> <span class="text-primary fw-bold">{{ number_format($patient->or_bsa, 2) }} m&sup2;</span></span>
                @endif
                @if($patient->or_gfr)
                    <span><strong>GFR:</strong> <span class="text-success fw-bold">{{ number_format($patient->or_gfr, 1) }} mL/min</span></span>
                @endif
                @if($patient->or_doctor_name)
                    <span><strong>แพทย์ผู้สั่ง:</strong> {{ $patient->or_doctor_name }}</span>
                @endif
            </div>
        </div>

        <!-- แถบแสดงรายการ Pre-medication ที่ต้องให้ก่อนเริ่มยาเคมีบำบัด -->
        @if(isset($patient->pre_meds) && $patient->pre_meds->isNotEmpty())
            <div class="px-4 py-2 bg-info-subtle border-bottom">
                <div class="d-flex align-items-start flex-wrap gap-2 small">
                    <span class="fw-bold text-dark text-nowrap mt-1">
                        <i class="bi bi-shield-plus text-primary me-1"></i>Pre-medication (ให้ก่อนเริ่มยาเคมีบำบัด):
                    </span>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($patient->pre_meds as $pm)
                            <span class="badge bg-white text-dark border {{ $pm->category === 'pre_med_hypersensitivity' ? 'border-warning' : 'border-primary' }} px-2 py-1 text-start" title="{{ $pm->instruction }}">
                                <strong class="{{ $pm->category === 'pre_med_hypersensitivity' ? 'text-warning-emphasis' : 'text-primary' }}">{{ $pm->sm_name }}</strong>
                                {{ $pm->dose }}
                                @if($pm->route)({{ $pm->route }})@endif
                                @if($pm->instruction)
                                    <span class="text-secondary fw-normal">&mdash; {{ $pm->instruction }}</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- แถบแสดงรายการ Pre-hydration (สารน้ำก่อนเริ่มยาเคมีบำบัด) -->
        @if(isset($patient->pre_hydrations) && $patient->pre_hydrations->isNotEmpty())
            <div class="px-4 py-2 bg-primary-subtle border-bottom">
                <div class="d-flex align-items-start flex-wrap gap-2 small">
                    <span class="fw-bold text-primary text-nowrap mt-1">
                        <i class="bi bi-droplet-fill me-1"></i>Pre-hydration (ให้สารน้ำก่อนเริ่มยาเคมีบำบัด):
                    </span>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($patient->pre_hydrations as $ph)
                            <span class="badge bg-white text-dark border border-primary px-2 py-1 text-start" title="{{ $ph->instruction }}">
                                <strong class="text-primary">{{ $ph->sm_name }}</strong>
                                {{ $ph->dose }}
                                @if($ph->route)({{ $ph->route }})@endif
                                @if($ph->instruction)
                                    <span class="text-secondary fw-normal">&mdash; {{ $ph->instruction }}</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- รายการยาย่อยใต้ชื่อผู้ป่วย (เฉพาะยาที่ให้ตรงวันที่เลือก) -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th class="ps-4" style="width: 70px;">ลำดับ</th>
                            <th>รายการยาย่อย &amp; ขนาดยา</th>
                            <th>สารน้ำ &amp; ปริมาตรรวม</th>
                            <th>วิธีบริหารยา / เวลา</th>
                            <th class="text-center">วันในคอร์ส (ตรงวัน)</th>
                            <th>ข้อควรระวัง (High-Alert)</th>
                            <th class="text-center">สถานะยา</th>
                            <th class="text-end pe-4">ยืนยันรับ/ให้ยา</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patient->items as $idx => $rec)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-secondary rounded-pill">#{{ $rec->or_orderno }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-danger">
                                        [{{ $rec->or_medcode }}] {{ optional($rec->medication)->dg_name ?? $rec->or_medcode }}
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger fs-6 mt-1">
                                        {{ number_format($rec->or_meddose, 2) }} {{ optional($rec->unit)->un_unit ?? 'mg' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ optional($rec->solution)->so_name ?? $rec->or_soln }} ({{ number_format($rec->or_solvol, 0) }} mL)</div>
                                    <small class="text-muted">
                                        ดูดยา: {{ number_format($rec->or_working, 2) }} mL |
                                        ปริมาตรรวม: <strong>{{ number_format($rec->or_totalvol, 2) }} mL</strong>
                                    </small>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ optional($rec->route)->rt_name ?? $rec->or_medroute }}</div>
                                    <small class="fw-bold text-primary">
                                        <i class="bi bi-clock me-1"></i>{{ optional($rec->timing)->tm_time ?? $rec->or_medtime }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-primary">{{ \Carbon\Carbon::parse($rec->or_daterec)->format('d/m/Y') }}</div>
                                    <span class="badge bg-light text-dark border">{{ $rec->or_text }}</span>
                                </td>
                                <td>
                                    <small class="text-danger">{{ optional($rec->medication)->dg_precaution ?? '-' }}</small>
                                </td>
                                <td class="text-center">
                                    @if($rec->or_send == 1)
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>บริหารยาแล้ว</span>
                                    @elseif($rec->or_prep == 1)
                                        <span class="badge bg-info text-dark">เตรียมยาพร้อมใช้</span>
                                    @else
                                        <span class="badge bg-warning text-dark">กำลังเตรียมยา</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@empty
    <div class="card card-medical bg-white">
        <div class="card-body text-center py-5 text-secondary">
            <i class="bi bi-calendar2-x fs-1 d-block mb-2 opacity-50"></i>
            <h6 class="fw-bold text-dark">ไม่มีรายการยาเคมีบำบัดที่ต้องให้ในวันที่ {{ \Carbon\Carbon::parse($selectedDate)->format('d/m/Y') }}</h6>
            <div class="small">
                ระบบแสดงเฉพาะยาที่ให้ตรงกับวันที่เลือกเท่านั้น (ไม่แสดงรายการยาของวันถัดไป)
                หากต้องการตรวจสอบวันอื่น สามารถเลือกวันที่จากตัวกรองด้านบนได้
            </div>
        </div>
    </div>
@endforelse
@endsection
