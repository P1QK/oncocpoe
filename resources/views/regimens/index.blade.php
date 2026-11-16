@extends('layouts.app')

@section('title', 'จัดการสูตรยาเคมีบำบัด (Regimen Management) - Admin & Superadmin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <span class="badge bg-dark mb-1">เฉพาะ Admin &amp; Superadmin</span>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-journal-medical text-primary me-2"></i>จัดการฐานข้อมูลสูตรยาเคมีบำบัด (Regimen Master: <code>reg_h</code> &amp; <code>reg_d</code>)
        </h4>
        <small class="text-secondary">กำหนดรหัสสูตรยา รหัสยา ขนาดยาเริ่มต้น สารน้ำ ปริมาตรเริ่มต้น วันที่เริ่มยา และจำนวนวันที่ให้ยา</small>
    </div>
    <button type="button" class="btn btn-primary fw-semibold shadow-sm" data-bs-toggle="collapse" data-bs-target="#addRegimenCollapse">
        <i class="bi bi-plus-circle-fill me-1"></i> เพิ่มสูตรยา Regimen ใหม่
    </button>
</div>

<!-- ฟอร์มเพิ่มสูตรยา Regimen ใหม่ (สำหรับ Admin / Superadmin) -->
<div class="collapse mb-4" id="addRegimenCollapse">
    <div class="card card-medical bg-white border-primary border-2">
        <div class="card-header bg-primary text-white fw-bold py-2 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-plus-square-fill me-2"></i>สร้างสูตรยาเคมีบำบัดใหม่ (Add New Regimen)</span>
            <button type="button" class="btn btn-sm btn-light" onclick="addRegimenRow()">
                <i class="bi bi-plus-lg me-1"></i> เพิ่มรายการยาในสูตร
            </button>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('regimens.store') }}">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">ชื่อ/รหัสสูตรยา (<code>rh_code</code>, ไม่เกิน 20 ตัวอักษร) <span class="text-danger">*</span></label>
                        <input type="text" name="rh_code" class="form-control fw-bold" maxlength="20" placeholder="เช่น CHOP, ABVD, Carbo+Taxol" required>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-3 small">
                        <thead class="table-light text-center">
                            <tr>
                                <th>ยาเคมีบำบัด (<code>rd_medcode</code>)</th>
                                <th style="width: 120px;">ขนาดเริ่มต้น</th>
                                <th style="width: 110px;">หน่วยนับ</th>
                                <th>สารน้ำ (<code>rd_sol</code>)</th>
                                <th style="width: 110px;">ปริมาตร (mL)</th>
                                <th>วิถีทาง (<code>rd_medroute</code>)</th>
                                <th>เวลาให้ยา (<code>rd_medtime</code>)</th>
                                <th style="width: 85px;">เริ่ม Day</th>
                                <th style="width: 85px;">ให้กี่วัน</th>
                                <th style="width: 50px;">ลบ</th>
                            </tr>
                        </thead>
                        <tbody id="regimenRowsBody">
                            <tr>
                                <td>
                                    <select name="items[0][rd_medcode]" class="form-select form-select-sm" required>
                                        @foreach($meds as $m)
                                            <option value="{{ $m->dg_code }}">[{{ $m->dg_code }}] {{ $m->dg_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[0][rd_meddose]" class="form-control form-control-sm text-end" value="100" required>
                                </td>
                                <td>
                                    <select name="items[0][rd_medunit]" class="form-select form-select-sm">
                                        @foreach($units as $u)
                                            <option value="{{ $u->un_code }}" {{ $u->un_code === '03' ? 'selected' : '' }}>{{ $u->un_unit }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][rd_sol]" class="form-select form-select-sm">
                                        @foreach($solutions as $s)
                                            <option value="{{ $s->so_code }}">{{ $s->so_code }} - {{ $s->so_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="1" name="items[0][rd_solvol]" class="form-control form-control-sm text-end" value="250" required>
                                </td>
                                <td>
                                    <select name="items[0][rd_medroute]" class="form-select form-select-sm">
                                        @foreach($routes as $r)
                                            <option value="{{ $r->rt_code }}">{{ $r->rt_name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[0][rd_medtime]" class="form-select form-select-sm">
                                        @foreach($timings as $t)
                                            <option value="{{ $t->tm_code }}">{{ $t->tm_time }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" min="1" name="items[0][rd_start]" class="form-control form-control-sm text-center" value="1" required>
                                </td>
                                <td>
                                    <input type="number" min="1" name="items[0][rd_duration]" class="form-control form-control-sm text-center" value="1" required>
                                </td>
                                <td class="text-center">-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-save me-1"></i> บันทึกสูตรยา Regimen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- รายการสูตรยาเคมีบำบัดทั้งหมด -->
<div class="row g-4">
    @foreach($regimens as $reg)
        <div class="col-md-6">
            <div class="card card-medical bg-white h-100">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2">
                    <span class="fw-bold fs-6"><i class="bi bi-prescription2 me-2"></i>สูตร: {{ $reg->rh_code }}</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary">{{ $reg->details->count() }} รายการยา</span>
                        <form method="POST" action="{{ route('regimens.destroy', $reg->rh_id) }}" onsubmit="return confirm('ยืนยันการลบสูตรยา {{ $reg->rh_code }} ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="ลบสูตรยา">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>รหัสยา / ชื่อยา</th>
                                <th class="text-end">ขนาดเริ่มต้น</th>
                                <th>สารน้ำ / ปริมาตร</th>
                                <th>วิถีทาง / เวลา</th>
                                <th class="text-center">เริ่ม Day</th>
                                <th class="text-center">ให้กี่วัน</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reg->details as $d)
                                <tr>
                                    <td class="fw-semibold text-primary">
                                        [{{ $d->rd_medcode }}] {{ optional($d->medication)->dg_name ?? $d->rd_medcode }}
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($d->rd_meddose, 2) }} {{ optional($d->unit)->un_unit ?? $d->rd_medunit }}
                                    </td>
                                    <td>
                                        {{ $d->rd_sol }} ({{ number_format($d->rd_solvol, 0) }} mL)
                                    </td>
                                    <td>
                                        {{ optional($d->route)->rt_name ?? $d->rd_medroute }}<br>
                                        <span class="text-muted">{{ optional($d->timing)->tm_time ?? $d->rd_medtime }}</span>
                                    </td>
                                    <td class="text-center">Day {{ $d->rd_start }}</td>
                                    <td class="text-center"><span class="badge bg-info text-dark">{{ $d->rd_duration }} วัน</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@section('js_after')
<script>
    let rowIdx = 1;
    function addRegimenRow() {
        const tbody = document.getElementById('regimenRowsBody');
        const firstRow = tbody.querySelector('tr');
        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('select, input').forEach(el => {
            const name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', name.replace(/items\[\d+\]/, `items[${rowIdx}]`));
            }
        });

        const lastCell = newRow.querySelector('td:last-child');
        lastCell.innerHTML = `<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="this.closest('tr').remove()"><i class="bi bi-x-lg"></i></button>`;

        tbody.appendChild(newRow);
        rowIdx++;
    }
</script>
@endsection
