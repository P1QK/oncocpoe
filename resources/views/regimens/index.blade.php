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
        <div class="card-header bg-primary text-white fw-bold py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="bi bi-plus-square-fill me-2"></i>สร้างสูตรยาเคมีบำบัดใหม่ (Add New Regimen)</span>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold" onclick="resetAutoSequence()" title="รีเซ็ตตัวเลขลำดับให้รัน 1, 2, 3... ตามแถวอัตโนมัติ">
                    <i class="bi bi-sort-numeric-down me-1"></i> เรียงลำดับ 1..N อัตโนมัติ
                </button>
                <button type="button" class="btn btn-sm btn-light fw-semibold" onclick="addRegimenRow()">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มรายการยาในสูตร
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('regimens.store') }}">
                @csrf
                <div class="row g-3 mb-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">ชื่อ/รหัสสูตรยา (<code>rh_code</code>, ไม่เกิน 20 ตัวอักษร) <span class="text-danger">*</span></label>
                        <input type="text" name="rh_code" class="form-control fw-bold" maxlength="20" placeholder="เช่น CHOP, ABVD, Carbo+Taxol" required>
                    </div>
                    <div class="col-md-8">
                        <div class="alert alert-warning py-1 px-3 mb-0 small border-warning">
                            <i class="bi bi-magic me-1"></i>
                            <strong>ระบบรันลำดับการให้ยา (1, 2, 3...) ให้อัตโนมัติเมื่อเพิ่มหรือลบแถว</strong>
                            &mdash; สามารถกดปุ่ม <strong>▲ / ▼</strong> เพื่อสลับลำดับแถว หรือพิมพ์แก้ไขตัวเลขลำดับเองได้โดยตรง
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-3 small">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 120px;" class="bg-warning-subtle">ลำดับให้ยาอัตโนมัติ<br><small>(แก้ไขได้ / เลื่อนแถว)</small></th>
                                <th>ยาเคมีบำบัด (<code>rd_medcode</code>)</th>
                                <th style="width: 115px;">ขนาดเริ่มต้น</th>
                                <th style="width: 105px;">หน่วยนับ</th>
                                <th>สารน้ำ (<code>rd_sol</code>)</th>
                                <th style="width: 105px;">ปริมาตร (mL)</th>
                                <th>วิถีทาง (<code>rd_medroute</code>)</th>
                                <th>เวลาให้ยา (<code>rd_medtime</code>)</th>
                                <th style="width: 80px;">เริ่ม Day</th>
                                <th style="width: 80px;">ให้กี่วัน</th>
                                <th style="width: 55px;">ลบ</th>
                            </tr>
                        </thead>
                        <tbody id="regimenRowsBody">
                            <tr>
                                <td class="bg-warning-subtle">
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="number" min="1" max="99" name="items[0][rd_orderno]"
                                               class="form-control form-control-sm text-center fw-bold text-dark border-warning seq-input"
                                               value="1" data-manual="false" oninput="markManualSequence(this)" required>
                                        <div class="btn-group-vertical btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary py-0 px-1" style="font-size: 0.6rem; line-height: 1;" onclick="moveRegimenRow(this, -1)" title="เลื่อนขึ้น">&#9650;</button>
                                            <button type="button" class="btn btn-outline-secondary py-0 px-1" style="font-size: 0.6rem; line-height: 1;" onclick="moveRegimenRow(this, 1)" title="เลื่อนลง">&#9660;</button>
                                        </div>
                                    </div>
                                </td>
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

<!-- รายการสูตรยาเคมีบำบัดทั้งหมด พร้อมกำหนด/แก้ไขลำดับการให้ยา -->
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
                <form method="POST" action="{{ route('regimens.sequence', $reg->rh_id) }}">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center bg-warning-subtle" style="width: 82px;">ลำดับให้ยา</th>
                                    <th>รหัสยา / ชื่อยา</th>
                                    <th class="text-end">ขนาดเริ่มต้น</th>
                                    <th>สารน้ำ / ปริมาตร</th>
                                    <th>วิถีทาง / เวลา</th>
                                    <th class="text-center">เริ่ม Day</th>
                                    <th class="text-center">ให้กี่วัน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reg->details as $idx => $d)
                                    <tr>
                                        <td class="text-center bg-warning-subtle">
                                            <input type="number" min="1" max="99"
                                                   name="sequences[{{ $idx }}]"
                                                   class="form-control form-control-sm text-center fw-bold border-warning"
                                                   value="{{ $d->rd_orderno ?: ($idx + 1) }}" required>
                                        </td>
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
                    <div class="card-footer bg-light py-2 d-flex justify-content-between align-items-center">
                        <small class="text-secondary">
                            <i class="bi bi-sort-numeric-down text-primary me-1"></i>ลำดับให้ยาอัตโนมัติ (สามารถแก้ไขตัวเลขแล้วกดบันทึกได้)
                        </small>
                        <button type="submit" class="btn btn-sm btn-outline-primary fw-semibold py-0 px-2">
                            <i class="bi bi-check2-circle me-1"></i> บันทึกลำดับการให้ยา
                        </button>
                    </div>
                </form>
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
        const nextSeq = tbody.querySelectorAll('tr').length + 1;

        newRow.querySelectorAll('select, input').forEach(el => {
            const name = el.getAttribute('name');
            if (name) {
                el.setAttribute('name', name.replace(/items\[\d+\]/, `items[${rowIdx}]`));
                if (name.includes('[rd_orderno]')) {
                    el.value = nextSeq;
                    el.setAttribute('data-manual', 'false');
                    el.classList.remove('bg-info-subtle');
                }
            }
        });

        const lastCell = newRow.querySelector('td:last-child');
        lastCell.innerHTML = `<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeRegimenRow(this)" title="ลบแถว"><i class="bi bi-x-lg"></i></button>`;

        tbody.appendChild(newRow);
        rowIdx++;
        autoNumberRegimenRows(false);
    }

    function removeRegimenRow(btn) {
        const tbody = document.getElementById('regimenRowsBody');
        if (tbody.querySelectorAll('tr').length <= 1) return;
        btn.closest('tr').remove();
        autoNumberRegimenRows(false);
    }

    function moveRegimenRow(btn, dir) {
        const row = btn.closest('tr');
        const tbody = document.getElementById('regimenRowsBody');
        if (dir === -1 && row.previousElementSibling) {
            tbody.insertBefore(row, row.previousElementSibling);
            autoNumberRegimenRows(true);
        } else if (dir === 1 && row.nextElementSibling) {
            tbody.insertBefore(row.nextElementSibling, row);
            autoNumberRegimenRows(true);
        }
    }

    function markManualSequence(input) {
        input.setAttribute('data-manual', 'true');
        input.classList.add('bg-info-subtle');
    }

    function autoNumberRegimenRows(forceAll) {
        const rows = document.querySelectorAll('#regimenRowsBody tr');
        rows.forEach((tr, idx) => {
            const seqInput = tr.querySelector('.seq-input');
            if (!seqInput) return;
            if (forceAll || seqInput.getAttribute('data-manual') !== 'true') {
                seqInput.value = idx + 1;
                if (forceAll) {
                    seqInput.setAttribute('data-manual', 'false');
                    seqInput.classList.remove('bg-info-subtle');
                }
            }
        });
    }

    function resetAutoSequence() {
        autoNumberRegimenRows(true);
    }
</script>
@endsection
