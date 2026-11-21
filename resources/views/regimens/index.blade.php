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

                <!-- แท็บกำหนดยาสนับสนุนเริ่มต้น 5 หมวด (Pre-med, Pre-hyd, Post-med MESNA, Post-hyd, Home-med) -->
                @include('regimens._supportive_tabs', ['prefix' => 'create'])

                <div class="text-end">
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-save me-1"></i> บันทึกสูตรยา Regimen พร้อมยาสนับสนุน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- แถบค้นหาสูตรยา Regimen แบบ Dropdown ที่มี Textbox พิมพ์ค้นหาในตัว -->
<div class="card card-medical bg-white mb-4 border-start border-4 border-primary">
    <div class="card-body py-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-3">
                <label class="fw-bold text-dark small mb-0">
                    <i class="bi bi-search text-primary me-1"></i>ค้นหาและเลือกดูสูตรยา Regimen:
                </label>
                <div class="text-secondary" style="font-size: 0.78rem;">
                    แสดงอยู่ <strong class="text-primary" id="visibleRegimenCount">{{ $regimens->count() }}</strong> / {{ $regimens->count() }} สูตรยา
                </div>
            </div>
            <div class="col-md-7">
                <div class="dropdown w-100" id="regimenFilterDropdownWrapper">
                    <button class="btn btn-outline-primary bg-white text-dark fw-semibold w-100 d-flex justify-content-between align-items-center dropdown-toggle"
                            type="button" id="regimenFilterDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                        <span id="regimenFilterSelectedLabel">
                            <i class="bi bi-funnel-fill text-primary me-1"></i>-- แสดงสูตรยาทั้งหมด ({{ $regimens->count() }} สูตร) --
                        </span>
                    </button>
                    <div class="dropdown-menu w-100 p-2 shadow-lg border-primary" aria-labelledby="regimenFilterDropdownBtn" style="max-height: 360px; overflow-y: auto;">
                        <!-- ช่อง Textbox พิมพ์ค้นหาภายใน Dropdown -->
                        <div class="input-group input-group-sm mb-2 sticky-top bg-white pb-1">
                            <span class="input-group-text bg-primary text-white"><i class="bi bi-search"></i></span>
                            <input type="text" id="regimenDropdownSearchInput" class="form-control border-primary"
                                   placeholder="พิมพ์ชื่อสูตรยา (เช่น FOLFOX, AC) หรือชื่อยาในสูตร (เช่น 5FU, Carbo)..."
                                   autocomplete="off" oninput="filterRegimenDropdown(this.value)">
                            <button type="button" class="btn btn-outline-secondary" onclick="clearRegimenDropdownSearch()" title="ล้างคำค้นหา">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div class="list-group list-group-flush small" id="regimenDropdownList">
                            <button type="button" class="list-group-item list-group-item-action fw-bold text-primary active"
                                    data-rh-id="ALL" onclick="selectRegimenFilter('ALL', '-- แสดงสูตรยาทั้งหมด ({{ $regimens->count() }} สูตร) --')">
                                <i class="bi bi-grid-fill me-1"></i> แสดงสูตรยาทั้งหมด ({{ $regimens->count() }} สูตร)
                            </button>
                            @foreach($regimens as $regOpt)
                                @php
                                    $optDrugCodes = $regOpt->details->pluck('rd_medcode')->unique()->implode(', ');
                                    $optDrugNames = $regOpt->details->map(fn ($d) => optional($d->medication)->dg_name)->filter()->unique()->implode(' ');
                                    $optSearchText = strtolower($regOpt->rh_code . ' ' . $optDrugCodes . ' ' . $optDrugNames);
                                @endphp
                                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center regimen-dropdown-option"
                                        data-rh-id="{{ $regOpt->rh_id }}"
                                        data-search="{{ $optSearchText }}"
                                        onclick="selectRegimenFilter('{{ $regOpt->rh_id }}', '{{ addslashes($regOpt->rh_code) }} ({{ $regOpt->details->count() }} รายการยา)')">
                                    <div>
                                        <span class="fw-bold text-dark">{{ $regOpt->rh_code }}</span>
                                        <small class="text-secondary d-block">ตัวยา: {{ $optDrugCodes ?: '-' }}</small>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary">{{ $regOpt->details->count() }} ยา</span>
                                </button>
                            @endforeach
                        </div>
                        <div id="regimenDropdownNoMatch" class="text-center text-danger small py-3" style="display: none;">
                            <i class="bi bi-exclamation-circle me-1"></i>ไม่พบสูตรยาที่ตรงกับคำค้นหา
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-secondary w-100 fw-semibold" onclick="selectRegimenFilter('ALL', '-- แสดงสูตรยาทั้งหมด ({{ $regimens->count() }} สูตร) --')">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> แสดงทั้งหมด
                </button>
            </div>
        </div>
    </div>
</div>

<!-- รายการสูตรยาเคมีบำบัดทั้งหมด พร้อมกำหนด/แก้ไขลำดับการให้ยา -->
<div class="row g-4" id="regimensCardsContainer">
    @foreach($regimens as $reg)
        @php
            $cardDrugCodes = $reg->details->pluck('rd_medcode')->unique()->implode(' ');
            $cardDrugNames = $reg->details->map(fn ($d) => optional($d->medication)->dg_name)->filter()->unique()->implode(' ');
            $cardSearchText = strtolower($reg->rh_code . ' ' . $cardDrugCodes . ' ' . $cardDrugNames);
        @endphp
        <div class="col-md-6 regimen-card-col" data-rh-id="{{ $reg->rh_id }}" data-search="{{ $cardSearchText }}">
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
                    <div class="card-footer bg-light py-2">
                        @php
                            $supList = $reg->supportiveMeds;
                            $cntPreMed = $supList->filter(fn ($s) => in_array(optional($s->supportiveMed)->category, ['pre_med_antiemetic', 'pre_med_hypersensitivity']))->count();
                            $cntPreHyd = $supList->filter(fn ($s) => optional($s->supportiveMed)->category === 'pre_hydration')->count();
                            $mesnaItem = $supList->firstWhere('sm_code', 'POST_MESNA');
                            $cntPostHyd = $supList->filter(fn ($s) => optional($s->supportiveMed)->category === 'post_hydration')->count();
                            $cntHomeMed = $supList->filter(fn ($s) => optional($s->supportiveMed)->category === 'home_med')->count();
                        @endphp
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2 pb-2 border-bottom">
                            <div class="d-flex flex-wrap gap-1 align-items-center" style="font-size: 0.72rem;">
                                <span class="badge bg-info-subtle text-dark border border-info">Pre-med: {{ $cntPreMed }}</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Pre-hyd: {{ $cntPreHyd }}</span>
                                @if($mesnaItem)
                                    <span class="badge text-white" style="background-color: #6f42c1;">MESNA {{ $mesnaItem->override_dose ?? '60' }}%</span>
                                @endif
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Post-hyd: {{ $cntPostHyd }}</span>
                                <span class="badge bg-warning-subtle text-dark border border-warning">Home-med: {{ $cntHomeMed }}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-dark fw-semibold py-0 px-2"
                                    onclick="openEditSupportiveModal({{ $reg->rh_id }})">
                                <i class="bi bi-sliders me-1"></i> ตั้งค่ายาสนับสนุน (Pre/Post/Home)
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-secondary">
                                <i class="bi bi-sort-numeric-down text-primary me-1"></i>ลำดับให้ยาอัตโนมัติ (สามารถแก้ไขตัวเลขแล้วกดบันทึกได้)
                            </small>
                            <button type="submit" class="btn btn-sm btn-outline-primary fw-semibold py-0 px-2">
                                <i class="bi bi-check2-circle me-1"></i> บันทึกลำดับการให้ยา
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</div>

<!-- Modal แก้ไขชุดยาสนับสนุน (Pre-med / Pre-hyd / Post-med MESNA / Post-hyd / Home-med) ของสูตรยาเดิม -->
<div class="modal fade" id="editSupportiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="editSupportiveForm">
                @csrf
                <div class="modal-header bg-dark text-white py-2">
                    <h6 class="modal-title fw-bold">
                        <i class="bi bi-sliders text-info me-2"></i>ตั้งค่ายาสนับสนุนเริ่มต้นของสูตร:
                        <span id="editSupportiveRegCode" class="badge bg-warning text-dark ms-1"></span>
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
                    @include('regimens._supportive_tabs', ['prefix' => 'edit'])
                </div>
                <div class="modal-footer bg-white py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold px-4">
                        <i class="bi bi-save me-1"></i> บันทึกชุดยาสนับสนุนของสูตรนี้
                    </button>
                </div>
            </form>
        </div>
    </div>
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

    // --- จัดการแท็บยาสนับสนุน (Create & Edit Modal) ---
    const REGIMENS_SUPPORTIVE_DATA = @json($regimensSupportiveJson);
    const UPDATE_SUPPORTIVE_BASE = @json(url('/regimens'));

    function onRegSupToggle(prefix, code) {
        const chk = document.getElementById(`${prefix}_chk_${code}`);
        const row = chk ? chk.closest('.reg-sup-row') : null;
        const isChecked = !!(chk && chk.checked);

        if (row) {
            row.classList.toggle('bg-white', isChecked);
            row.classList.toggle('border-primary', isChecked);
            row.classList.toggle('bg-light', !isChecked);
            row.querySelectorAll('.reg-sup-input').forEach(inp => {
                inp.disabled = !isChecked;
            });
        }
        updateRegSupCounters(prefix);
    }

    function onRegMesnaToggle(prefix) {
        const chk = document.getElementById(`${prefix}_mesna_enabled`);
        const isChecked = !!(chk && chk.checked);
        const fields = document.getElementById(`${prefix}_mesna_fields`);
        if (fields) {
            fields.querySelectorAll('input, select').forEach(el => {
                el.disabled = !isChecked;
            });
        }
        const badge = document.getElementById(`${prefix}_cnt_postmed`);
        if (badge) {
            badge.className = isChecked ? 'badge text-white ms-1' : 'badge bg-secondary ms-1';
            badge.style.backgroundColor = isChecked ? '#6f42c1' : '';
            badge.innerText = isChecked ? 'ON' : 'OFF';
        }
    }

    function updateRegSupCounters(prefix) {
        ['premed', 'prehyd', 'posthyd', 'homemed'].forEach(group => {
            const cnt = document.querySelectorAll(`.reg-sup-row[data-prefix="${prefix}"][data-group="${group}"] .reg-sup-chk:checked`).length;
            const el = document.getElementById(`${prefix}_cnt_${group}`);
            if (el) el.innerText = cnt;
        });
    }

    function openEditSupportiveModal(rhId) {
        const reg = REGIMENS_SUPPORTIVE_DATA[rhId];
        if (!reg) return;

        const form = document.getElementById('editSupportiveForm');
        form.action = `${UPDATE_SUPPORTIVE_BASE}/${rhId}/supportive`;
        document.getElementById('editSupportiveRegCode').innerText = reg.rh_code;

        const itemsMap = reg.items || {};

        document.querySelectorAll('.reg-sup-row[data-prefix="edit"]').forEach(row => {
            const code = row.getAttribute('data-code');
            const chk = document.getElementById(`edit_chk_${code}`);
            const doseEl = document.getElementById(`edit_dose_${code}`);
            const routeEl = document.getElementById(`edit_route_${code}`);
            const instEl = document.getElementById(`edit_inst_${code}`);

            const defDose = row.getAttribute('data-def-dose') || '';
            const defRoute = row.getAttribute('data-def-route') || '';
            const defInst = row.getAttribute('data-def-inst') || '';

            const cfg = itemsMap[code];
            if (cfg && cfg.is_default) {
                if (chk) chk.checked = true;
                if (doseEl) doseEl.value = cfg.override_dose || defDose;
                if (routeEl) routeEl.value = cfg.override_route || defRoute;
                if (instEl) instEl.value = cfg.override_instruction || defInst;
            } else {
                if (chk) chk.checked = false;
                if (doseEl) doseEl.value = defDose;
                if (routeEl) routeEl.value = defRoute;
                if (instEl) instEl.value = defInst;
            }
            onRegSupToggle('edit', code);
        });

        // โหลดค่า MESNA ของสูตรนี้
        const mesnaCfg = itemsMap['POST_MESNA'];
        const mesnaChk = document.getElementById('edit_mesna_enabled');
        const mesnaPct = document.getElementById('edit_mesna_pct');
        const mesnaMode = document.getElementById('edit_mesna_hr0_mode');
        const mesnaVol = document.getElementById('edit_mesna_nss_vol');

        if (mesnaCfg && mesnaCfg.is_default) {
            if (mesnaChk) mesnaChk.checked = true;
            if (mesnaPct) mesnaPct.value = parseInt(mesnaCfg.override_dose, 10) || 60;
            const parts = String(mesnaCfg.override_route || 'admix|100 mL').split('|');
            if (mesnaMode) mesnaMode.value = parts[0] === 'separate' ? 'separate' : 'admix';
            if (mesnaVol) mesnaVol.value = parts[1] || '100 mL';
        } else {
            if (mesnaChk) mesnaChk.checked = false;
            if (mesnaPct) mesnaPct.value = 60;
            if (mesnaMode) mesnaMode.value = 'admix';
            if (mesnaVol) mesnaVol.value = '100 mL';
        }
        onRegMesnaToggle('edit');

        const modal = new bootstrap.Modal(document.getElementById('editSupportiveModal'));
        modal.show();
    }

    // --- ระบบค้นหาสูตรยาแบบ Dropdown ที่มี Textbox พิมพ์ค้นหาในตัว ---
    const regimenDropdownBtn = document.getElementById('regimenFilterDropdownBtn');
    if (regimenDropdownBtn) {
        regimenDropdownBtn.addEventListener('shown.bs.dropdown', function () {
            const input = document.getElementById('regimenDropdownSearchInput');
            if (input) {
                input.focus();
                input.select();
            }
        });
    }

    function filterRegimenDropdown(keyword) {
        const kw = (keyword || '').trim().toLowerCase();
        const options = document.querySelectorAll('.regimen-dropdown-option');
        let matchedOptCount = 0;

        options.forEach(opt => {
            const text = opt.getAttribute('data-search') || '';
            const match = kw === '' || text.includes(kw);
            opt.classList.toggle('d-none', !match);
            if (match) matchedOptCount++;
        });

        const noMatchEl = document.getElementById('regimenDropdownNoMatch');
        if (noMatchEl) {
            noMatchEl.style.display = matchedOptCount === 0 ? 'block' : 'none';
        }

        // กรองการ์ดสูตรยาด้านล่างแบบ Real-time ตามคำที่พิมพ์ใน Textbox ด้วย
        let visibleCards = 0;
        document.querySelectorAll('.regimen-card-col').forEach(card => {
            const text = card.getAttribute('data-search') || '';
            const match = kw === '' || text.includes(kw);
            card.classList.toggle('d-none', !match);
            if (match) visibleCards++;
        });
        const countEl = document.getElementById('visibleRegimenCount');
        if (countEl) countEl.innerText = visibleCards;
    }

    function selectRegimenFilter(rhId, labelText) {
        const labelEl = document.getElementById('regimenFilterSelectedLabel');
        if (labelEl) {
            labelEl.innerHTML = `<i class="bi bi-funnel-fill text-primary me-1"></i>${labelText}`;
        }

        document.querySelectorAll('#regimenDropdownList .list-group-item').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-rh-id') === String(rhId));
        });

        let visibleCards = 0;
        document.querySelectorAll('.regimen-card-col').forEach(card => {
            const show = (String(rhId) === 'ALL') || (card.getAttribute('data-rh-id') === String(rhId));
            card.classList.toggle('d-none', !show);
            if (show) visibleCards++;
        });

        const countEl = document.getElementById('visibleRegimenCount');
        if (countEl) countEl.innerText = visibleCards;

        if (String(rhId) === 'ALL') {
            const input = document.getElementById('regimenDropdownSearchInput');
            if (input) input.value = '';
            document.querySelectorAll('.regimen-dropdown-option').forEach(opt => opt.classList.remove('d-none'));
            const noMatchEl = document.getElementById('regimenDropdownNoMatch');
            if (noMatchEl) noMatchEl.style.display = 'none';
        }

        const ddInstance = bootstrap.Dropdown.getInstance(document.getElementById('regimenFilterDropdownBtn'));
        if (ddInstance) ddInstance.hide();
    }

    function clearRegimenDropdownSearch() {
        const input = document.getElementById('regimenDropdownSearchInput');
        if (input) {
            input.value = '';
            input.focus();
        }
        filterRegimenDropdown('');
    }
</script>
@endsection
