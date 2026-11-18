@extends('layouts.app')

@section('title', 'จัดการฐานข้อมูลยาเคมีบำบัด (medcyto) - Admin & Superadmin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <span class="badge bg-dark mb-1">เฉพาะ Admin &amp; Superadmin</span>
        <h4 class="fw-bold text-dark mb-1">
            <i class="bi bi-capsule text-danger me-2"></i>จัดการฐานข้อมูลยาเคมีบำบัด (ตาราง <code>medcyto</code>)
        </h4>
        <small class="text-secondary">
            เพิ่ม แก้ไข ค้นหา และลบข้อมูลบัญชียาเคมีบำบัด ความเข้มข้น (<code>dg_conc</code>) ปริมาตรขวด วิธีผสมความคงตัว และข้อควรระวังพิเศษ (High-Alert)
        </small>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-danger fw-semibold shadow-sm" data-bs-toggle="collapse" data-bs-target="#createMedcytoCollapse" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
            <i class="bi bi-plus-circle-fill me-1"></i> เพิ่มยาเคมีบำบัดใหม่
        </button>
    </div>
</div>

<!-- สรุปสถิติข้อมูลยาเคมีบำบัด -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-danger">
            <div class="small text-secondary fw-semibold">รายการยาเคมีบำบัดทั้งหมด</div>
            <div class="fs-3 fw-bold text-danger">{{ $stats['total_meds'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-warning">
            <div class="small text-secondary fw-semibold">ระบุข้อควรระวัง (High-Alert)</div>
            <div class="fs-3 fw-bold text-dark">{{ $stats['with_precaution'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-primary">
            <div class="small text-secondary fw-semibold">ผูกในสูตรยา (Regimen)</div>
            <div class="fs-3 fw-bold text-primary">{{ $stats['in_regimens'] }}</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card card-medical bg-white p-3 border-start border-4 border-success">
            <div class="small text-secondary fw-semibold">มีประวัติสั่งใช้ยาแล้ว</div>
            <div class="fs-3 fw-bold text-success">{{ $stats['in_orders'] }}</div>
        </div>
    </div>
</div>

<!-- ฟอร์มเพิ่มยาเคมีบำบัดใหม่ (Create) -->
<div class="collapse {{ $errors->any() ? 'show' : '' }} mb-4" id="createMedcytoCollapse">
    <div class="card card-medical bg-white border-danger border-2">
        <div class="card-header bg-danger text-white fw-bold py-2 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-plus-square-fill me-2"></i>เพิ่มรายการยาเคมีบำบัดใหม่ลงตาราง <code>medcyto</code></span>
            <button type="button" class="btn-close btn-close-white" data-bs-toggle="collapse" data-bs-target="#createMedcytoCollapse"></button>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('medcyto.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">รหัสยา (<code>dg_code</code>) <span class="text-danger">*</span></label>
                        <input type="text" name="dg_code" class="form-control fw-bold text-uppercase" maxlength="8"
                               value="{{ old('dg_code') }}" placeholder="เช่น 5FU, GEMCI" required>
                        <small class="text-muted" style="font-size: 0.72rem;">A-Z/0-9 ไม่เกิน 8 ตัว</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold small">ชื่อสามัญทางยา (<code>dg_name</code>) <span class="text-danger">*</span></label>
                        <input type="text" name="dg_name" class="form-control" maxlength="150"
                               value="{{ old('dg_name') }}" placeholder="เช่น Gemcitabine HCl" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">ชื่อการค้า (<code>dg_brand</code>)</label>
                        <input type="text" name="dg_brand" class="form-control" maxlength="150"
                               value="{{ old('dg_brand') }}" placeholder="เช่น Gemzar">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">ความเข้มข้นสุทธิ (<code>dg_conc</code>: mg/mL) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0.01" name="dg_conc" class="form-control fw-bold text-danger text-end"
                                   value="{{ old('dg_conc', '10.00') }}" required>
                            <span class="input-group-text">mg/mL</span>
                        </div>
                        <small class="text-muted" style="font-size: 0.72rem;">ใช้คำนวณปริมาตรดูดยาอัตโนมัติ</small>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-bold small">ปริมาตรขวด (<code>dg_vol</code>: mL)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" name="dg_vol" class="form-control text-end"
                                   value="{{ old('dg_vol', '10.00') }}">
                            <span class="input-group-text">mL</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">ราคาทุน (<code>dg_price</code>)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" name="dg_price" class="form-control text-end"
                                   value="{{ old('dg_price', '0.00') }}">
                            <span class="input-group-text">฿</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">ราคาเบิก/ขาย (<code>dg_saleprc</code>)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" name="dg_saleprc" class="form-control text-end"
                                   value="{{ old('dg_saleprc', '0.00') }}">
                            <span class="input-group-text">฿</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">ความคงตัวหลังละลาย</label>
                        <input type="number" min="0" name="dg_exprecon" class="form-control text-center"
                               value="{{ old('dg_exprecon', '24') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">ความคงตัวหลังเจือจาง</label>
                        <input type="number" min="0" name="dg_expdilute" class="form-control text-center"
                               value="{{ old('dg_expdilute', '24') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">หน่วยเวลา (<code>dg_time</code>)</label>
                        <input type="text" name="dg_time" class="form-control text-center" maxlength="50"
                               value="{{ old('dg_time', 'ชั่วโมง') }}">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold small">ชื่อเต็มรูปแบบและความแรง (<code>dg_fullname</code>)</label>
                        <input type="text" name="dg_fullname" class="form-control" maxlength="250"
                               value="{{ old('dg_fullname') }}" placeholder="เช่น Gemcitabine 1,000 mg Vial (38 mg/mL after reconstitution)">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small">วิธีละลายผงยา / เตรียมสารละลายเข้มข้น (<code>dg_recon</code>)</label>
                        <textarea name="dg_recon" rows="2" class="form-control" maxlength="500"
                                  placeholder="ระบุวิธีละลายยาหรือรูปแบบสารละลายพร้อมใช้...">{{ old('dg_recon') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-danger">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>ข้อควรระวังพิเศษ / High-Alert (<code>dg_precaution</code>)
                        </label>
                        <textarea name="dg_precaution" rows="2" class="form-control border-danger-subtle" maxlength="500"
                                  placeholder="เช่น เก็บพ้นแสง ห้ามผสมใน NSS ระวัง Extravasation...">{{ old('dg_precaution') }}</textarea>
                    </div>
                </div>

                <div class="text-end mt-3 pt-3 border-top">
                    <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="collapse" data-bs-target="#createMedcytoCollapse">
                        ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">
                        <i class="bi bi-save me-1"></i> บันทึกยาเคมีบำบัดใหม่
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- แถบค้นหา -->
<div class="card card-medical bg-white mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('medcyto.index') }}" class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                           placeholder="ค้นหารหัสยา (dg_code), ชื่อสามัญ, ชื่อการค้า, ชื่อเต็ม หรือข้อควรระวัง...">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-dark fw-semibold w-100">
                    <i class="bi bi-search me-1"></i> ค้นหาข้อมูลยา
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('medcyto.index') }}" class="btn btn-outline-secondary">ล้างค่า</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- ตารางแสดงรายการยาเคมีบำบัด (Read / Update / Delete) -->
<div class="card card-medical bg-white">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-bold text-dark">
            <i class="bi bi-table text-danger me-1"></i> บัญชีรายการยาเคมีบำบัดทั้งหมด ({{ $meds->count() }} รายการ)
        </span>
        <small class="text-muted">คลิกปุ่ม "แก้ไข" เพื่อปรับปรุงข้อมูลยา หรือ "ลบ" หากยังไม่ถูกอ้างอิงในสูตรยา/ใบสั่งยา</small>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3" style="width: 95px;">รหัสยา</th>
                    <th>ชื่อสามัญ / ชื่อการค้า / ชื่อเต็ม</th>
                    <th class="text-end">ความเข้มข้น</th>
                    <th class="text-end">ปริมาตร/ขวด</th>
                    <th class="text-end">ราคาทุน / ขาย</th>
                    <th>วิธีเตรียมยา &amp; ความคงตัว</th>
                    <th>ข้อควรระวัง (High-Alert)</th>
                    <th class="text-center">การใช้งาน</th>
                    <th class="text-end pe-3" style="width: 140px;">จัดการ (CRUD)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($meds as $med)
                    @php
                        $regCount = $usedInRegimens[$med->dg_code] ?? 0;
                        $ordCount = $usedInOrders[$med->dg_code] ?? 0;
                        $isUsed = ($regCount > 0 || $ordCount > 0);
                    @endphp
                    <tr>
                        <td class="ps-3">
                            <span class="badge bg-danger fs-6 px-2 py-1">{{ $med->dg_code }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $med->dg_name }}</div>
                            @if($med->dg_brand)
                                <span class="badge bg-light text-secondary border">Brand: {{ $med->dg_brand }}</span>
                            @endif
                            @if($med->dg_fullname)
                                <div class="small text-muted mt-1">{{ $med->dg_fullname }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-danger fs-6">{{ number_format((float) $med->dg_conc, 2) }}</span>
                            <small class="text-secondary d-block">mg/mL</small>
                        </td>
                        <td class="text-end">
                            <span class="fw-semibold">{{ number_format((float) $med->dg_vol, 1) }}</span>
                            <small class="text-secondary d-block">mL</small>
                        </td>
                        <td class="text-end small">
                            <div>ทุน: <strong>{{ number_format((float) $med->dg_price, 2) }}</strong> ฿</div>
                            <div class="text-success">ขาย: <strong>{{ number_format((float) $med->dg_saleprc, 2) }}</strong> ฿</div>
                        </td>
                        <td style="max-width: 240px;">
                            <div class="small text-dark">{{ $med->dg_recon ?: '-' }}</div>
                            <span class="badge bg-info-subtle text-dark border mt-1">
                                คงตัว: {{ $med->dg_exprecon ?? 24 }}/{{ $med->dg_expdilute ?? 24 }} {{ $med->dg_time ?: 'ชั่วโมง' }}
                            </span>
                        </td>
                        <td style="max-width: 240px;">
                            @if($med->dg_precaution)
                                <small class="text-danger fw-medium">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>{{ $med->dg_precaution }}
                                </small>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($regCount > 0)
                                <span class="badge bg-primary-subtle text-primary border d-block mb-1" title="ถูกใช้ในสูตร Regimen">
                                    {{ $regCount }} สูตรยา
                                </span>
                            @endif
                            @if($ordCount > 0)
                                <span class="badge bg-success-subtle text-success border d-block" title="ถูกใช้ในใบสั่งยา">
                                    {{ $ordCount }} ใบสั่งยา
                                </span>
                            @endif
                            @if(!$isUsed)
                                <span class="badge bg-light text-muted border">ยังไม่ถูกอ้างอิง</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex justify-content-end gap-1">
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        onclick='openEditMedModal(@json($med))'
                                        title="แก้ไขข้อมูลยา">
                                    <i class="bi bi-pencil-square"></i> แก้ไข
                                </button>

                                <form method="POST"
                                      action="{{ route('medcyto.destroy', $med->dg_code) }}"
                                      onsubmit="return confirm('ยืนยันการลบรายการยา [{{ $med->dg_code }}] {{ $med->dg_name }} ออกจากระบบใช่หรือไม่?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm {{ $isUsed ? 'btn-outline-secondary' : 'btn-outline-danger' }}"
                                            {{ $isUsed ? 'disabled' : '' }}
                                            title="{{ $isUsed ? 'ไม่สามารถลบได้เนื่องจากถูกใช้งานในสูตรยาหรือใบสั่งยาแล้ว' : 'ลบรายการยา' }}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-secondary">
                            <i class="bi bi-capsule fs-1 d-block mb-2 opacity-50"></i>
                            ไม่พบรายการยาเคมีบำบัดในตาราง <code>medcyto</code>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal แก้ไขข้อมูลยาเคมีบำบัด (Update) -->
<div class="modal fade" id="editMedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="editMedForm">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-pencil-square me-2"></i>แก้ไขข้อมูลยาเคมีบำบัด: <span id="editMedCodeBadge" class="badge bg-warning text-dark"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">รหัสยา (<code>dg_code</code>)</label>
                            <input type="text" id="edit_dg_code" class="form-control bg-light fw-bold" readonly>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold small">ชื่อสามัญทางยา (<code>dg_name</code>) <span class="text-danger">*</span></label>
                            <input type="text" name="dg_name" id="edit_dg_name" class="form-control" maxlength="150" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">ชื่อการค้า (<code>dg_brand</code>)</label>
                            <input type="text" name="dg_brand" id="edit_dg_brand" class="form-control" maxlength="150">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small">ความเข้มข้นสุทธิ (<code>dg_conc</code>: mg/mL) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0.01" name="dg_conc" id="edit_dg_conc" class="form-control fw-bold text-danger text-end" required>
                                <span class="input-group-text">mg/mL</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">ปริมาตรต่อขวด (<code>dg_vol</code>: mL)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" name="dg_vol" id="edit_dg_vol" class="form-control text-end">
                                <span class="input-group-text">mL</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">หน่วยเวลาความคงตัว (<code>dg_time</code>)</label>
                            <input type="text" name="dg_time" id="edit_dg_time" class="form-control text-center" maxlength="50">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small">ราคาทุน (<code>dg_price</code>)</label>
                            <input type="number" step="0.01" min="0" name="dg_price" id="edit_dg_price" class="form-control text-end">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">ราคาขาย (<code>dg_saleprc</code>)</label>
                            <input type="number" step="0.01" min="0" name="dg_saleprc" id="edit_dg_saleprc" class="form-control text-end">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">ความคงตัวหลังละลาย</label>
                            <input type="number" min="0" name="dg_exprecon" id="edit_dg_exprecon" class="form-control text-center">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">ความคงตัวหลังเจือจาง</label>
                            <input type="number" min="0" name="dg_expdilute" id="edit_dg_expdilute" class="form-control text-center">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold small">ชื่อเต็มรูปแบบและความแรง (<code>dg_fullname</code>)</label>
                            <input type="text" name="dg_fullname" id="edit_dg_fullname" class="form-control" maxlength="250">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small">วิธีละลายผงยา / เตรียมสารละลายเข้มข้น (<code>dg_recon</code>)</label>
                            <textarea name="dg_recon" id="edit_dg_recon" rows="3" class="form-control" maxlength="500"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-danger">ข้อควรระวังพิเศษ / High-Alert (<code>dg_precaution</code>)</label>
                            <textarea name="dg_precaution" id="edit_dg_precaution" rows="3" class="form-control border-danger-subtle" maxlength="500"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        <i class="bi bi-check2-circle me-1"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js_after')
<script>
    const updateBaseUrl = @json(url('/medcyto'));

    function openEditMedModal(med) {
        const form = document.getElementById('editMedForm');
        form.action = `${updateBaseUrl}/${encodeURIComponent(med.dg_code)}`;

        document.getElementById('editMedCodeBadge').innerText = med.dg_code;
        document.getElementById('edit_dg_code').value = med.dg_code ?? '';
        document.getElementById('edit_dg_name').value = med.dg_name ?? '';
        document.getElementById('edit_dg_brand').value = med.dg_brand ?? '';
        document.getElementById('edit_dg_conc').value = med.dg_conc ?? 0;
        document.getElementById('edit_dg_vol').value = med.dg_vol ?? 0;
        document.getElementById('edit_dg_price').value = med.dg_price ?? 0;
        document.getElementById('edit_dg_saleprc').value = med.dg_saleprc ?? 0;
        document.getElementById('edit_dg_exprecon').value = med.dg_exprecon ?? 24;
        document.getElementById('edit_dg_expdilute').value = med.dg_expdilute ?? 24;
        document.getElementById('edit_dg_time').value = med.dg_time ?? 'ชั่วโมง';
        document.getElementById('edit_dg_fullname').value = med.dg_fullname ?? '';
        document.getElementById('edit_dg_recon').value = med.dg_recon ?? '';
        document.getElementById('edit_dg_precaution').value = med.dg_precaution ?? '';

        const modal = new bootstrap.Modal(document.getElementById('editMedModal'));
        modal.show();
    }
</script>
@endsection
