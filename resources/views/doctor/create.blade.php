@extends('layouts.app')

@section('title', 'สั่งใช้ยาเคมีบำบัดใหม่ - ระบบแพทย์')

@section('css_before')
<style>
    .calc-panel {
        background: linear-gradient(135deg, #eff6ff 0%, #e0f2fe 100%);
        border: 2px solid #93c5fd;
        border-radius: 12px;
    }
    .reduce-panel {
        background: #fffbeb;
        border: 2px solid #fcd34d;
        border-radius: 12px;
    }
    .dose-highlight {
        font-size: 1.1rem;
        font-weight: 700;
        color: #b91c1c;
        background-color: #fef2f2;
        border: 2px solid #fca5a5;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold text-primary mb-0">
            <i class="bi bi-file-earmark-medical-fill me-2"></i>สร้างคำสั่งใช้ยาเคมีบำบัดชนิดฉีด (Chemotherapy Order)
        </h4>
        <small class="text-secondary">ขั้นตอน: 1. กรอกข้อมูลผู้ป่วยและผล Lab &rarr; 2. เลือกสูตร Regimen &rarr; 3. ตรวจสอบ/ปรับลดขนาดยา (%) และระบุเหตุผล</small>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-info text-dark fw-semibold" onclick="fillDemoPatient()">
            <i class="bi bi-magic me-1"></i> กรอกข้อมูลผู้ป่วยตัวอย่างอัตโนมัติ
        </button>
        <a href="{{ route('doctor.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> กลับหน้ารายการ
        </a>
    </div>
</div>

<form method="POST" action="{{ route('doctor.store') }}" id="chemoOrderForm">
    @csrf

    <!-- STEP 1: ข้อมูลผู้ป่วย และ การคำนวณ BSA / GFR -->
    <div class="card card-medical bg-white mb-4">
        <div class="card-header bg-primary text-white py-2 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-person-vcard-fill me-2"></i>ส่วนที่ 1: ข้อมูลผู้ป่วยและผลตรวจทางห้องปฏิบัติการ (คำนวณ BSA &amp; GFR)</span>
            <span class="badge bg-white text-primary">กรอกข้อมูลผู้ป่วยโดยตรง</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">HN <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" name="or_hn" id="or_hn" class="form-control fw-bold border-primary"
                               value="{{ old('or_hn') }}" placeholder="เช่น 6701122" maxlength="10"
                               oninput="onHnInputChange(this.value)"
                               onblur="checkPatientCycleHistory(false)" required>
                        <button type="button" class="btn btn-outline-primary px-2" onclick="checkPatientCycleHistory(true)" title="ค้นหาประวัติและเช็ค Cycle ล่าสุดของผู้ป่วย">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold small">ชื่อ-นามสกุลผู้ป่วย <span class="text-danger">*</span></label>
                    <input type="text" name="or_patname" id="or_patname" class="form-control" value="{{ old('or_patname') }}" placeholder="ระบุคำนำหน้า ชื่อ-นามสกุล" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold small">อายุ (ปี) <span class="text-danger">*</span></label>
                    <input type="number" name="or_age" id="or_age" class="form-control calc-trigger" value="{{ old('or_age', 55) }}" min="1" max="120" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">เพศ <span class="text-danger">*</span></label>
                    <select name="or_sex" id="or_sex" class="form-select calc-trigger" required>
                        <option value="M" {{ old('or_sex') === 'M' ? 'selected' : '' }}>ชาย (Male)</option>
                        <option value="F" {{ old('or_sex') === 'F' ? 'selected' : '' }}>หญิง (Female)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">หอผู้ป่วย / หน่วยงาน <span class="text-danger">*</span></label>
                    <select name="or_ward" id="or_ward" class="form-select" required>
                        @foreach($wards as $w)
                            <option value="{{ $w->wd_code }}" {{ old('or_ward') == $w->wd_code ? 'selected' : '' }}>
                                [{{ $w->wd_code }}] {{ $w->wd_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- แถวที่ 2: น้ำหนัก ส่วนสูง Serum Creatinine วันเริ่มยา และผลลัพธ์ BSA / GFR -->
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">น้ำหนัก (Weight, kg) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.1" name="or_wt" id="or_wt" class="form-control fw-bold text-primary calc-trigger" value="{{ old('or_wt', 60.0) }}" min="1" max="300" required>
                        <span class="input-group-text small">kg</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">ส่วนสูง (Height, cm) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.1" name="or_ht" id="or_ht" class="form-control fw-bold text-primary calc-trigger" value="{{ old('or_ht', 165.0) }}" min="30" max="250" required>
                        <span class="input-group-text small">cm</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">Serum Cr ล่าสุด <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="or_scr" id="or_scr" class="form-control fw-bold text-danger calc-trigger" value="{{ old('or_scr', 0.95) }}" min="0.1" max="30" required>
                        <span class="input-group-text small">mg/dL</span>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small">วันที่เริ่มให้ยา (Day 1) <span class="text-danger">*</span></label>
                    <input type="date" name="or_date" id="or_date" class="form-control calc-trigger" value="{{ old('or_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold small d-flex justify-content-between align-items-center">
                        <span>Cycle ที่</span>
                        <span id="cycleAutoBadge" class="badge bg-info text-dark" style="font-size: 0.62rem; display: none;">Auto</span>
                    </label>
                    <input type="number" name="or_cycle" id="or_cycle"
                           class="form-control fw-bold text-primary text-center border-primary"
                           value="{{ old('or_cycle', 1) }}" min="1" max="99"
                           data-manual="false"
                           oninput="onCycleManualEdit(this)" required>
                </div>

                <!-- กล่องแสดงผลคำนวณ BSA และ GFR อัตโนมัติ -->
                <div class="col-md-3">
                    <div class="calc-panel p-2 px-3 h-100 d-flex align-items-center justify-content-around">
                        <div class="text-center">
                            <div class="small fw-semibold text-primary">BSA (Mosteller)</div>
                            <div class="fs-4 fw-bold text-dark"><span id="bsaDisplay">1.66</span> <small class="fs-6">m&sup2;</small></div>
                            <input type="hidden" name="or_bsa" id="or_bsa" value="{{ old('or_bsa', 1.66) }}">
                        </div>
                        <div class="border-start border-primary-subtle h-75"></div>
                        <div class="text-center">
                            <div class="small fw-semibold text-primary">GFR (Cockcroft)</div>
                            <div class="fs-4 fw-bold text-success"><span id="gfrDisplay">74.6</span> <small class="fs-6">mL/m</small></div>
                            <input type="hidden" name="or_gfr" id="or_gfr" value="{{ old('or_gfr', 74.6) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- แถบแสดงประวัติการให้ยาและ Cycle อัตโนมัติจากฐานข้อมูล -->
            <div id="cycleHistoryBanner" class="mt-3 p-2 px-3 rounded-3 border border-info bg-info-subtle small" style="display: none;"></div>
        </div>
    </div>

    <!-- STEP 2: เลือก Regimen และ ปรับลดขนาดยา (%) -->
    <div class="card card-medical bg-white mb-4">
        <div class="card-header bg-dark text-white py-2 d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-prescription2 me-2"></i>ส่วนที่ 2: เลือกสูตรยาเคมีบำบัด (Regimen) และปรับลดขนาดยา (% Dose Reduction)</span>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end mb-4">
                <div class="col-md-5">
                    <label class="form-label fw-bold text-primary mb-1">
                        1. ค้นหาและเลือกสูตรยาเคมีบำบัด (Regimen) <span class="text-danger">*</span>
                    </label>

                    <!-- ช่องข้อความสำหรับพิมพ์ค้นหาสูตรยา หรือ ชื่อยาในสูตร -->
                    <div class="position-relative mb-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-primary text-white"><i class="bi bi-search"></i></span>
                            <input type="text" id="regimenSearchInput" class="form-control border-primary"
                                placeholder="พิมพ์ค้นหาชื่อสูตรยา (เช่น FOLFOX, AC) หรือชื่อยา (เช่น 5FU, Carbo)..."
                                autocomplete="off"
                                oninput="filterRegimenOptions(this.value)"
                                onfocus="filterRegimenOptions(this.value)">
                            <button type="button" class="btn btn-outline-secondary" onclick="clearRegimenSearch()" title="ล้างคำค้นหา">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <!-- กล่องแสดงผลการค้นหาด่วน (Autocomplete Dropdown) -->
                        <div id="regimenSearchResults" class="list-group position-absolute w-100 shadow-lg z-3 mt-1" style="display: none; max-height: 240px; overflow-y: auto;"></div>
                    </div>

                    <!-- Dropdown เลือกสูตรยา (ถูกกรองตามคำค้นหาอัตโนมัติ) -->
                    <select id="regimenSelect" class="form-select border-primary fw-bold" onchange="onRegimenChange()">
                        <option value="">-- กรุณาเลือกสูตรยา Regimen หรือกดปุ่มสั่งยาแบบอิสระ --</option>
                        <option value="CUSTOM" data-code="CUSTOM" data-drugs="กำหนดตัวยาเองอย่างอิสระ" data-search="custom free สั่งยาอิสระ กำหนดเอง">
                            ★ [CUSTOM] สั่งใช้ยาเคมีบำบัดแบบอิสระ (เลือกตัวยาเองโดยไม่ใช้สูตร)
                        </option>
                        @foreach($regimens as $reg)
                            @php
                                $drugCodes = $reg->details->pluck('rd_medcode')->unique()->implode(', ');
                                $drugNames = $reg->details->map(fn($d) => optional($d->medication)->dg_name)->filter()->unique()->implode(' ');
                            @endphp
                            <option value="{{ $reg->rh_id }}"
                                data-code="{{ $reg->rh_code }}"
                                data-drugs="{{ $drugCodes }}"
                                data-search="{{ strtolower($reg->rh_code . ' ' . $drugCodes . ' ' . $drugNames) }}">
                                {{ $reg->rh_code }} ({{ $reg->details->count() }} รายการยา: {{ $drugCodes }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- แผงควบคุมการปรับลดขนาดยา (%) และ Dose Banding / Rounding -->
                <div class="col-md-7">
                    <div class="reduce-panel p-3">
                        <div class="row g-2 align-items-center mb-2 pb-2 border-bottom border-warning-subtle">
                            <div class="col-md-5">
                                <label class="form-label fw-bold text-dark mb-1">
                                    <i class="bi bi-sliders me-1 text-warning"></i>2. ปรับลดขนาดยาทั้งหมด (% ทั้งสูตร):
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-warning-subtle fw-bold">ลดทั้งหมด</span>
                                    <input type="number" step="1" min="0" max="90" name="or_adjust_percent" id="globalReducePct" class="form-control fw-bold text-danger fs-6 text-center" value="{{ old('or_adjust_percent', 0) }}" oninput="applyGlobalReduction(this.value)">
                                    <span class="input-group-text bg-warning-subtle fw-bold">%</span>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <div class="small fw-semibold text-secondary mb-1">ปุ่มลัดปรับลดทั้งสูตร (หรือปรับลดแยกเฉพาะตัวในตารางด้านล่าง):</div>
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary fw-bold py-0 px-2" onclick="setGlobalReduction(0)">0% (Full)</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold py-0 px-2" onclick="setGlobalReduction(10)">-10%</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold py-0 px-2" onclick="setGlobalReduction(15)">-15%</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold py-0 px-2" onclick="setGlobalReduction(20)">-20%</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold py-0 px-2" onclick="setGlobalReduction(25)">-25%</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold py-0 px-2" onclick="setGlobalReduction(30)">-30%</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger fw-bold py-0 px-2" onclick="setGlobalReduction(50)">-50%</button>
                                </div>
                            </div>
                        </div>

                        <!-- แผงควบคุม Dose Banding / Dose Rounding -->
                        <div class="row g-2 align-items-center">
                            <div class="col-md-7">
                                <label class="form-label fw-bold text-primary mb-1 small">
                                    <i class="bi bi-Bullseye me-1"></i>3. รูปแบบการปัดขนาดยา (Dose Banding / Rounding):
                                </label>
                                <select id="roundingModeSelect" class="form-select form-select-sm border-primary fw-semibold" onchange="recalculateAllRows()">
                                    <option value="clinical_band">Clinical Dose Banding (ปัดตามมาตรฐานตัวยา ภายใน &plusmn;5%)</option>
                                    <option value="syringe_05">Syringe Volume 0.5 mL (ปัดให้ดูดยาลงตัวทุก 0.5 mL)</option>
                                    <option value="syringe_02">Syringe Volume 0.2 mL (ปัดให้ดูดยาลงตัวทุก 0.2 mL)</option>
                                    <option value="int_5">Round to 5 mg (ปัดเป็นจำนวนเต็มทีละ 5 mg)</option>
                                    <option value="int_1">Round to 1 mg (ปัดเป็นจำนวนเต็ม 1 mg)</option>
                                    <option value="exact" selected>Exact Dose (ไม่ปัดเศษ ใช้ค่าทศนิยมตามจริง)</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold text-secondary mb-1 small">ทิศทางการปัด (Direction):</label>
                                <select id="roundingDirSelect" class="form-select form-select-sm" onchange="recalculateAllRows()">
                                    <option value="nearest" selected>ปัดใกล้เคียงที่สุด (&plusmn; Nearest Band)</option>
                                    <option value="down">ปัดลงเท่านั้น (Round Down / Cap)</option>
                                    <option value="up">ปัดขึ้น (Round Up)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- กล่องระบุเหตุผลในการปรับลดขนาดยา (แสดงเด่นชัดและบังคับกรอกเมื่อมีการลดขนาดยา) -->
            <div id="reasonBox" class="mb-4 p-3 rounded-3 border border-2 border-danger bg-danger-subtle" style="display: none;">
                <div class="row g-2 align-items-center">
                    <div class="col-md-12">
                        <label class="form-label fw-bold text-danger mb-1">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>เหตุผลที่แพทย์ปรับลดขนาดยา (บันทึกลงตาราง <code>order_h.or_adjust_reason</code>) <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="or_adjust_reason" id="or_adjust_reason" class="form-control border-danger fw-semibold" value="{{ old('or_adjust_reason') }}" placeholder="ระบุเหตุผลทางคลินิกที่ปรับลดขนาดยา เช่น Neutropenia Gr.3, ค่าการทำงานของไตลดลง (CrCl ต่ำ), Poor ECOG Performance Status...">
                        <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
                            <span class="small text-secondary me-1">เลือกเหตุผลด่วน:</span>
                            <button type="button" class="btn btn-xs btn-outline-danger btn-sm py-0" onclick="setReason('ภาวะเม็ดเลือดขาวต่ำ (Febrile / Grade 3-4 Neutropenia)')">เม็ดเลือดขาวต่ำ (Neutropenia)</button>
                            <button type="button" class="btn btn-xs btn-outline-danger btn-sm py-0" onclick="setReason('การทำงานของไตลดลง (Renal Impairment / Low GFR)')">ค่าไตลดลง (Low GFR)</button>
                            <button type="button" class="btn btn-xs btn-outline-danger btn-sm py-0" onclick="setReason('ผู้ป่วยสูงอายุ / สภาพร่างกายอ่อนเพลีย (ECOG >= 2)')">ผู้ป่วยสูงอายุ / ECOG &ge; 2</button>
                            <button type="button" class="btn btn-xs btn-outline-danger btn-sm py-0" onclick="setReason('ภาวะเกล็ดเลือดต่ำ (Thrombocytopenia)')">เกล็ดเลือดต่ำ</button>
                            <button type="button" class="btn btn-xs btn-outline-danger btn-sm py-0" onclick="setReason('ภาวะพิษต่อระบบประสาทส่วนปลาย (Peripheral Neuropathy)')">ปลายประสาทอักเสบ (Neuropathy)</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- แถบเครื่องมือจัดการรายการยา: ชื่อสูตรยา + ปุ่มสั่งใช้ยาแบบอิสระ + คำแนะนำการ Off ยา -->
            <div class="d-flex flex-wrap justify-content-between align-items-center bg-light p-2 px-3 rounded-top border border-bottom-0 gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="fw-bold text-dark small"><i class="bi bi-tag-fill text-primary me-1"></i>ชื่อสูตรยา / รูปแบบคำสั่ง:</span>
                    <input type="text" name="or_reg_code" id="or_reg_code" class="form-control form-control-sm fw-bold text-primary border-primary"
                        style="width: 210px;" value="{{ old('or_reg_code', 'CUSTOM') }}" placeholder="เช่น FOLFOX-4 หรือ CUSTOM" required>
                    <span class="badge bg-secondary-subtle text-dark border" id="activeItemsCounter">สั่งใช้จริง: 0 รายการ</span>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="small text-secondary d-none d-md-inline">
                        <i class="bi bi-lightbulb text-warning me-1"></i>สามารถกดปุ่ม <strong>ON/OFF</strong> เพื่อยกเว้นยาบางตัวในสูตร หรือกดเพิ่มยาแบบอิสระได้:
                    </span>
                    <button type="button" class="btn btn-sm btn-success fw-bold shadow-sm" onclick="addCustomMedicationRow()">
                        <i class="bi bi-plus-circle-fill me-1"></i> + สั่งใช้ยาแบบอิสระ / เพิ่มตัวยา (Add Drug)
                    </button>
                </div>
            </div>

            <!-- ตารางรายการยาในสูตร Regimen และการคำนวณขนาดยา -->
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0" id="medItemsTable">
                    <thead class="table-dark text-center small">
                        <tr>
                            <th style="width: 95px;">สถานะสั่งยา<br>(ON / OFF)</th>
                            <th style="min-width: 210px;">รหัสยา / ชื่อยาเคมีบำบัด</th>
                            <th style="min-width: 145px;">ขนาดยาเริ่มต้น<br>(ตามสูตร / กำหนดเอง)</th>
                            <th style="width: 130px;">ขนาดยาคำนวณ<br>(คูณ BSA/GFR)</th>
                            <th style="min-width: 165px;" class="bg-warning text-dark">ปรับลดเฉพาะตัว (%)<br><small class="fw-normal">ปรับรายตัวยาได้</small></th>
                            <th style="width: 195px;" class="bg-danger">ขนาดยาใหม่ที่ให้จริง<br>(Final Dose, mg)</th>
                            <th style="min-width: 145px;">สารน้ำ (Diluent)</th>
                            <th style="min-width: 145px;">ปริมาตรสารน้ำ<br>(mL)</th>
                            <th style="min-width: 140px;">วิถีทาง / เวลาให้ยา</th>
                            <th style="width: 80px;">เริ่ม Day</th>
                            <th style="width: 85px;">ให้กี่วัน<br>(reg_d)</th>
                            <th style="width: 120px;">ช่วงวันที่ให้ยา<br>(Start - Stop)</th>
                        </tr>
                    </thead>
                    <tbody id="medItemsBody">
                        <tr id="emptyRow">
                            <td colspan="12" class="text-center py-5 text-secondary">
                                <i class="bi bi-arrow-up-circle fs-2 d-block mb-2 text-primary"></i>
                                เลือก <strong>สูตรยาเคมีบำบัด (Regimen)</strong> ด้านบน หรือคลิกปุ่ม <strong>"+ สั่งใช้ยาแบบอิสระ / เพิ่มตัวยา"</strong> เพื่อเลือกยาสั่งเองโดยอิสระ
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- STEP 3: PRE-MEDICATION (Antiemetics & Prophylaxis for Hypersensitivity / Infusion Reaction) -->
    <div class="card card-medical bg-white mb-4 border-top border-4 border-info">
        <div class="card-header bg-info-subtle text-dark py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold">
                <i class="bi bi-shield-plus text-info me-2"></i>ส่วนที่ 3: Pre-medication (ยาเตรียมก่อนให้เคมีบำบัด &mdash; ปรับอัตโนมัติตาม Regimen และเลือกเปิด/ปิดได้)
            </span>
            <span class="badge bg-info text-dark" id="preMedSelectedCounter">เลือกใช้: 0 รายการ</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <!-- 3.1 Antiemetics -->
                <div class="col-lg-6">
                    <div class="border rounded-3 h-100 overflow-hidden">
                        <div class="bg-primary-subtle px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-primary small">
                                <i class="bi bi-capsule me-1"></i>1. Antiemetics (ยาป้องกันคลื่นไส้-อาเจียน)
                            </span>
                            <span class="badge bg-white text-primary border">{{ $preMedAntiemetics->count() }} รายการ</span>
                        </div>
                        <div class="p-2 d-flex flex-column gap-2">
                            @foreach($preMedAntiemetics as $pm)
                                @php $pmKey = $pm->sm_code; @endphp
                                <div class="border rounded-2 p-2 pre-med-row bg-light" id="premed_row_{{ $pmKey }}" data-sm-code="{{ $pmKey }}" data-default-dose="{{ $pm->default_dose }}" data-default-route="{{ $pm->default_route }}" data-default-instruction="{{ $pm->default_instruction }}">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input pre-med-checkbox" type="checkbox" role="switch"
                                                   id="premed_chk_{{ $pmKey }}"
                                                   name="pre_meds[{{ $pmKey }}][enabled]" value="1"
                                                   onchange="togglePreMedRow('{{ $pmKey }}')">
                                            <label class="form-check-label fw-bold text-dark" for="premed_chk_{{ $pmKey }}">
                                                {{ $pm->sm_name }}
                                            </label>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary pre-med-status-badge" id="premed_badge_{{ $pmKey }}">ไม่ใช้</span>
                                    </div>
                                    <input type="hidden" name="pre_meds[{{ $pmKey }}][sm_code]" value="{{ $pm->sm_code }}">
                                    <input type="hidden" name="pre_meds[{{ $pmKey }}][sm_name]" value="{{ $pm->sm_name }}">
                                    <input type="hidden" name="pre_meds[{{ $pmKey }}][category]" value="{{ $pm->category }}">

                                    <div class="row g-1 align-items-center mt-1 pre-med-fields" id="premed_fields_{{ $pmKey }}">
                                        <div class="col-5">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white px-1 small">ขนาด</span>
                                                <input type="text" name="pre_meds[{{ $pmKey }}][dose]" id="premed_dose_{{ $pmKey }}"
                                                       class="form-control fw-bold text-primary" value="{{ $pm->default_dose }}">
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <input type="text" name="pre_meds[{{ $pmKey }}][route]" id="premed_route_{{ $pmKey }}"
                                                   class="form-control form-control-sm text-center" value="{{ $pm->default_route }}" placeholder="Route">
                                        </div>
                                        <div class="col-4">
                                            @if(!empty($pm->dose_options) && is_array($pm->dose_options))
                                                <div class="d-flex flex-wrap gap-1 justify-content-end">
                                                    @foreach($pm->dose_options as $opt)
                                                        <button type="button" class="btn btn-outline-primary py-0 px-1" style="font-size: 0.67rem;"
                                                                onclick="setPreMedDose('{{ $pmKey }}', '{{ $opt }}')">{{ $opt }}</button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-12 mt-1">
                                            <input type="text" name="pre_meds[{{ $pmKey }}][instruction]" id="premed_inst_{{ $pmKey }}"
                                                   class="form-control form-control-sm text-secondary" value="{{ $pm->default_instruction }}" placeholder="วิธีบริหารยา / เวลาให้ยา">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- 3.2 Prophylaxis for Hypersensitivity / Infusion Reaction -->
                <div class="col-lg-6">
                    <div class="border rounded-3 h-100 overflow-hidden">
                        <div class="bg-warning-subtle px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small">
                                <i class="bi bi-exclamation-shield-fill text-warning me-1"></i>2. Prophylaxis for Hypersensitivity / Infusion Reaction
                            </span>
                            <span class="badge bg-white text-dark border">{{ $preMedHypersensitivity->count() }} รายการ</span>
                        </div>
                        <div class="p-2 d-flex flex-column gap-2">
                            @foreach($preMedHypersensitivity as $pm)
                                @php $pmKey = $pm->sm_code; @endphp
                                <div class="border rounded-2 p-2 pre-med-row bg-light" id="premed_row_{{ $pmKey }}" data-sm-code="{{ $pmKey }}" data-default-dose="{{ $pm->default_dose }}" data-default-route="{{ $pm->default_route }}" data-default-instruction="{{ $pm->default_instruction }}">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input pre-med-checkbox" type="checkbox" role="switch"
                                                   id="premed_chk_{{ $pmKey }}"
                                                   name="pre_meds[{{ $pmKey }}][enabled]" value="1"
                                                   onchange="togglePreMedRow('{{ $pmKey }}')">
                                            <label class="form-check-label fw-bold text-dark" for="premed_chk_{{ $pmKey }}">
                                                {{ $pm->sm_name }}
                                            </label>
                                        </div>
                                        <span class="badge bg-secondary-subtle text-secondary pre-med-status-badge" id="premed_badge_{{ $pmKey }}">ไม่ใช้</span>
                                    </div>
                                    <input type="hidden" name="pre_meds[{{ $pmKey }}][sm_code]" value="{{ $pm->sm_code }}">
                                    <input type="hidden" name="pre_meds[{{ $pmKey }}][sm_name]" value="{{ $pm->sm_name }}">
                                    <input type="hidden" name="pre_meds[{{ $pmKey }}][category]" value="{{ $pm->category }}">

                                    <div class="row g-1 align-items-center mt-1 pre-med-fields" id="premed_fields_{{ $pmKey }}">
                                        <div class="col-5">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white px-1 small">ขนาด</span>
                                                <input type="text" name="pre_meds[{{ $pmKey }}][dose]" id="premed_dose_{{ $pmKey }}"
                                                       class="form-control fw-bold text-dark" value="{{ $pm->default_dose }}">
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <input type="text" name="pre_meds[{{ $pmKey }}][route]" id="premed_route_{{ $pmKey }}"
                                                   class="form-control form-control-sm text-center" value="{{ $pm->default_route }}" placeholder="Route">
                                        </div>
                                        <div class="col-4">
                                            @if(!empty($pm->dose_options) && is_array($pm->dose_options))
                                                <div class="d-flex flex-wrap gap-1 justify-content-end">
                                                    @foreach($pm->dose_options as $opt)
                                                        <button type="button" class="btn btn-outline-secondary py-0 px-1" style="font-size: 0.67rem;"
                                                                onclick="setPreMedDose('{{ $pmKey }}', '{{ $opt }}')">{{ $opt }}</button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-12 mt-1">
                                            <input type="text" name="pre_meds[{{ $pmKey }}][instruction]" id="premed_inst_{{ $pmKey }}"
                                                   class="form-control form-control-sm text-secondary" value="{{ $pm->default_instruction }}" placeholder="วิธีบริหารยา / เวลาให้ยา">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 4: PRE-HYDRATION (การให้สารน้ำและอิเล็กโทรไลต์ก่อนเคมีบำบัด) -->
    <div class="card card-medical bg-white mb-4 border-top border-4 border-primary">
        <div class="card-header bg-primary-subtle text-dark py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold">
                <i class="bi bi-droplet-fill text-primary me-2"></i>ส่วนที่ 4: Pre-hydration (สารน้ำและอิเล็กโทรไลต์ก่อนให้เคมีบำบัด &mdash; ปรับอัตโนมัติตาม Regimen เช่น Cisplatin)
            </span>
            <span class="badge bg-primary text-white" id="preHydSelectedCounter">เลือกใช้: 0 รายการ</span>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @foreach($preHydrations as $ph)
                    @php $phKey = $ph->sm_code; @endphp
                    <div class="col-lg-6">
                        <div class="border rounded-2 p-2 pre-med-row bg-light h-100" id="premed_row_{{ $phKey }}" data-sm-code="{{ $phKey }}" data-category="pre_hydration" data-default-dose="{{ $ph->default_dose }}" data-default-route="{{ $ph->default_route }}" data-default-instruction="{{ $ph->default_instruction }}">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input pre-med-checkbox pre-hyd-checkbox" type="checkbox" role="switch"
                                           id="premed_chk_{{ $phKey }}"
                                           name="pre_meds[{{ $phKey }}][enabled]" value="1"
                                           onchange="togglePreMedRow('{{ $phKey }}')">
                                    <label class="form-check-label fw-bold text-dark" for="premed_chk_{{ $phKey }}">
                                        {{ $ph->sm_name }}
                                    </label>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary pre-med-status-badge" id="premed_badge_{{ $phKey }}">ไม่ใช้</span>
                            </div>
                            <input type="hidden" name="pre_meds[{{ $phKey }}][sm_code]" value="{{ $ph->sm_code }}">
                            <input type="hidden" name="pre_meds[{{ $phKey }}][sm_name]" value="{{ $ph->sm_name }}">
                            <input type="hidden" name="pre_meds[{{ $phKey }}][category]" value="{{ $ph->category }}">

                            <div class="row g-1 align-items-center mt-1 pre-med-fields" id="premed_fields_{{ $phKey }}">
                                <div class="col-5">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white px-1 small">ขนาด</span>
                                        <input type="text" name="pre_meds[{{ $phKey }}][dose]" id="premed_dose_{{ $phKey }}"
                                               class="form-control fw-bold text-primary" value="{{ $ph->default_dose }}">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <input type="text" name="pre_meds[{{ $phKey }}][route]" id="premed_route_{{ $phKey }}"
                                           class="form-control form-control-sm text-center" value="{{ $ph->default_route }}" placeholder="Route">
                                </div>
                                <div class="col-4">
                                    @if(!empty($ph->dose_options) && is_array($ph->dose_options))
                                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                                            @foreach($ph->dose_options as $opt)
                                                <button type="button" class="btn btn-outline-primary py-0 px-1" style="font-size: 0.67rem;"
                                                        onclick="setPreMedDose('{{ $phKey }}', '{{ $opt }}')">{{ $opt }}</button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="col-12 mt-1">
                                    <input type="text" name="pre_meds[{{ $phKey }}][instruction]" id="premed_inst_{{ $phKey }}"
                                           class="form-control form-control-sm text-secondary" value="{{ $ph->default_instruction }}" placeholder="อัตราเร็ว / ระยะเวลาหยด">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- STEP 5: POST-MEDICATION (MESNA Auto-Calculator สำหรับ Ifosfamide / High-dose Cyclophosphamide) -->
    <div class="card card-medical bg-white mb-4 border-top border-4" style="border-top-color: #6f42c1 !important;">
        <div class="card-header py-2 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: #f3e8ff;">
            <span class="fw-bold" style="color: #5b21b6;">
                <i class="bi bi-clock-history me-2"></i>ส่วนที่ 5: Post-medication &mdash; MESNA Uroprotection (สำหรับ Ifosfamide / High-dose Cyclophosphamide)
            </span>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-white text-dark border" id="mesnaRefDrugBadge">ยังไม่ได้เปิดใช้ MESNA</span>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="mesnaEnabledChk" onchange="toggleMesnaSection()">
                    <label class="form-check-label fw-bold small" style="color: #5b21b6;" for="mesnaEnabledChk">เปิดใช้ MESNA</label>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- แถบแจ้งเตือนอัตโนมัติเมื่อตรวจพบ Ifosfamide / Cyclophosphamide -->
            <div id="mesnaAutoAlert" class="alert alert-warning py-2 px-3 small mb-3" style="display: none;"></div>

            <div id="mesnaCalculatorBody" class="opacity-50">
                <div class="row g-3 align-items-end mb-3 pb-3 border-bottom">
                    <!-- 1. สัดส่วน % MESNA -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold small mb-1" style="color: #5b21b6;">
                            1. สัดส่วนขนาด MESNA รวม (% ของขนาดยาหลัก: 60&ndash;120%):
                        </label>
                        <div class="input-group input-group-sm mb-1">
                            <span class="input-group-text bg-white fw-semibold">สัดส่วน</span>
                            <input type="number" id="mesnaPercentInput" class="form-control text-center fw-bold text-danger"
                                   value="60" min="20" max="200" step="5" oninput="updateMesnaCalculation(true)">
                            <span class="input-group-text bg-white fw-bold">%</span>
                            <span class="input-group-text bg-light fw-bold" id="mesnaTotalDisplay">รวม 0.00 mg</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" style="font-size: 0.72rem;" onclick="setMesnaPercent(60)">60% (20% &times; 3)</button>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" style="font-size: 0.72rem;" onclick="setMesnaPercent(80)">80%</button>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" style="font-size: 0.72rem;" onclick="setMesnaPercent(100)">100%</button>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 fw-semibold" style="font-size: 0.72rem;" onclick="setMesnaPercent(120)">120%</button>
                        </div>
                    </div>

                    <!-- 2. ตัวเลือกวิธีผสม MESNA ที่ชั่วโมงที่ 0 (ครั้งที่ 1/3) -->
                    <div class="col-md-5">
                        <label class="form-label fw-bold small mb-1 text-dark">
                            2. วิธีบริหารยา MESNA ที่ชั่วโมงที่ 0 (ครั้งที่ 1/3):
                        </label>
                        <div class="d-flex flex-column gap-1 bg-light p-2 rounded-2 border">
                            <div class="form-check mb-0 small">
                                <input class="form-check-input" type="radio" name="mesna_hr0_mode" id="mesnaHr0Admix" value="admix" checked onchange="updateMesnaCalculation(true)">
                                <label class="form-check-label fw-semibold text-primary" for="mesnaHr0Admix">
                                    <i class="bi bi-flask me-1"></i>ผสมลงไปกับขวดยาเคมีบำบัดเลย (Admixed in Chemo Bag)
                                </label>
                            </div>
                            <div class="form-check mb-0 small">
                                <input class="form-check-input" type="radio" name="mesna_hr0_mode" id="mesnaHr0Separate" value="separate" onchange="updateMesnaCalculation(true)">
                                <label class="form-check-label fw-semibold text-dark" for="mesnaHr0Separate">
                                    <i class="bi bi-droplet me-1"></i>แยกไปผสมกับน้ำเกลือต่างหาก (Separate NSS Bag)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- 3. ปริมาตร NSS สำหรับโดสที่แยกผสม -->
                    <div class="col-md-3">
                        <label class="form-label fw-bold small mb-1 text-secondary">
                            3. ปริมาตรน้ำเกลือ (สำหรับโดสที่แยกผสม):
                        </label>
                        <select id="mesnaNssVolSelect" class="form-select form-select-sm" onchange="updateMesnaCalculation(true)">
                            <option value="50 mL">NSS 50 mL (IV drip in 15 mins)</option>
                            <option value="100 mL" selected>NSS 100 mL (IV drip in 15-30 mins)</option>
                            <option value="250 mL">NSS 250 mL (IV drip in 30 mins)</option>
                        </select>
                    </div>
                </div>

                <!-- ตารางแบ่งให้ MESNA 3 ครั้ง (0, +3, +6 ชั่วโมง) -->
                <div class="row g-2">
                    @foreach([
                        ['key' => 'POST_MESNA_1', 'no' => 1, 'hr' => '0 ชม. (เริ่มให้ยา)', 'badge' => 'ครั้งที่ 1/3 (0 hr)'],
                        ['key' => 'POST_MESNA_2', 'no' => 2, 'hr' => '+3 ชม. หลังเริ่มยา', 'badge' => 'ครั้งที่ 2/3 (+3 hrs)'],
                        ['key' => 'POST_MESNA_3', 'no' => 3, 'hr' => '+6 ชม. หลังเริ่มยา', 'badge' => 'ครั้งที่ 3/3 (+6 hrs)'],
                    ] as $mDose)
                        <div class="col-md-4">
                            <div class="border rounded-2 p-2 bg-light h-100 mesna-dose-card">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge text-white" style="background-color: #6f42c1;">{{ $mDose['badge'] }}</span>
                                    <small class="text-secondary fw-semibold">{{ $mDose['hr'] }}</small>
                                </div>
                                <input type="hidden" class="mesna-input" name="pre_meds[{{ $mDose['key'] }}][enabled]" id="mesna_enabled_{{ $mDose['no'] }}" value="1" disabled>
                                <input type="hidden" class="mesna-input" name="pre_meds[{{ $mDose['key'] }}][sm_code]" value="{{ $mDose['key'] }}" disabled>
                                <input type="hidden" class="mesna-input" name="pre_meds[{{ $mDose['key'] }}][sm_name]" value="MESNA inj. ({{ $mDose['badge'] }})" disabled>
                                <input type="hidden" class="mesna-input" name="pre_meds[{{ $mDose['key'] }}][category]" value="post_medication" disabled>

                                <div class="row g-1 mb-1">
                                    <div class="col-7">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white px-1 small">ขนาด</span>
                                            <input type="text" class="form-control fw-bold text-danger mesna-input"
                                                   name="pre_meds[{{ $mDose['key'] }}][dose]" id="mesna_dose_{{ $mDose['no'] }}"
                                                   value="0 mg" disabled>
                                        </div>
                                    </div>
                                    <div class="col-5">
                                        <input type="text" class="form-control form-control-sm text-center mesna-input"
                                               name="pre_meds[{{ $mDose['key'] }}][route]" id="mesna_route_{{ $mDose['no'] }}"
                                               value="IV" disabled>
                                    </div>
                                </div>
                                <input type="text" class="form-control form-control-sm text-secondary mesna-input"
                                       name="pre_meds[{{ $mDose['key'] }}][instruction]" id="mesna_inst_{{ $mDose['no'] }}"
                                       value="" placeholder="วิธีผสมและเวลาให้ยา" disabled>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 6: POST-HYDRATION (การให้สารน้ำและอิเล็กโทรไลต์หลังเคมีบำบัด) -->
    <div class="card card-medical bg-white mb-4 border-top border-4 border-success">
        <div class="card-header bg-success-subtle text-dark py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold text-success-emphasis">
                <i class="bi bi-droplet-half text-success me-2"></i>ส่วนที่ 6: Post-hydration (สารน้ำและอิเล็กโทรไลต์หลังให้เคมีบำบัด &mdash; ปรับอัตโนมัติตาม Regimen)
            </span>
            <span class="badge bg-success text-white" id="postHydSelectedCounter">เลือกใช้: 0 รายการ</span>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @foreach($postHydrations as $poh)
                    @php $pohKey = $poh->sm_code; @endphp
                    <div class="col-lg-6">
                        <div class="border rounded-2 p-2 pre-med-row bg-light h-100" id="premed_row_{{ $pohKey }}" data-sm-code="{{ $pohKey }}" data-category="post_hydration" data-default-dose="{{ $poh->default_dose }}" data-default-route="{{ $poh->default_route }}" data-default-instruction="{{ $poh->default_instruction }}">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input pre-med-checkbox post-hyd-checkbox" type="checkbox" role="switch"
                                           id="premed_chk_{{ $pohKey }}"
                                           name="pre_meds[{{ $pohKey }}][enabled]" value="1"
                                           onchange="togglePreMedRow('{{ $pohKey }}')">
                                    <label class="form-check-label fw-bold text-dark" for="premed_chk_{{ $pohKey }}">
                                        {{ $poh->sm_name }}
                                    </label>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary pre-med-status-badge" id="premed_badge_{{ $pohKey }}">ไม่ใช้</span>
                            </div>
                            <input type="hidden" name="pre_meds[{{ $pohKey }}][sm_code]" value="{{ $poh->sm_code }}">
                            <input type="hidden" name="pre_meds[{{ $pohKey }}][sm_name]" value="{{ $poh->sm_name }}">
                            <input type="hidden" name="pre_meds[{{ $pohKey }}][category]" value="{{ $poh->category }}">

                            <div class="row g-1 align-items-center mt-1 pre-med-fields" id="premed_fields_{{ $pohKey }}">
                                <div class="col-5">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white px-1 small">ขนาด</span>
                                        <input type="text" name="pre_meds[{{ $pohKey }}][dose]" id="premed_dose_{{ $pohKey }}"
                                               class="form-control fw-bold text-success" value="{{ $poh->default_dose }}">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <input type="text" name="pre_meds[{{ $pohKey }}][route]" id="premed_route_{{ $pohKey }}"
                                           class="form-control form-control-sm text-center" value="{{ $poh->default_route }}" placeholder="Route">
                                </div>
                                <div class="col-4">
                                    @if(!empty($poh->dose_options) && is_array($poh->dose_options))
                                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                                            @foreach($poh->dose_options as $opt)
                                                <button type="button" class="btn btn-outline-success py-0 px-1" style="font-size: 0.67rem;"
                                                        onclick="setPreMedDose('{{ $pohKey }}', '{{ $opt }}')">{{ $opt }}</button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="col-12 mt-1">
                                    <input type="text" name="pre_meds[{{ $pohKey }}][instruction]" id="premed_inst_{{ $pohKey }}"
                                           class="form-control form-control-sm text-secondary" value="{{ $poh->default_instruction }}" placeholder="อัตราเร็ว / ระยะเวลาหยด / คำแนะนำ">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- STEP 7: HOME-MEDICATION (ยากลับบ้านหลังรับยาเคมีบำบัด) -->
    <div class="card card-medical bg-white mb-4 border-top border-4 border-warning">
        <div class="card-header bg-warning-subtle text-dark py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="fw-bold">
                <i class="bi bi-house-heart-fill text-warning me-2"></i>ส่วนที่ 7: Home-medication (ยากลับบ้านหลังรับยาเคมีบำบัด &mdash; ปรับอัตโนมัติตาม Regimen)
            </span>
            <span class="badge bg-warning text-dark" id="homeMedSelectedCounter">เลือกใช้: 0 รายการ</span>
        </div>
        <div class="card-body">
            <div class="row g-2">
                @foreach($homeMeds as $hm)
                    @php $hmKey = $hm->sm_code; @endphp
                    <div class="col-lg-6">
                        <div class="border rounded-2 p-2 pre-med-row bg-light h-100" id="premed_row_{{ $hmKey }}" data-sm-code="{{ $hmKey }}" data-category="home_med" data-default-dose="{{ $hm->default_dose }}" data-default-route="{{ $hm->default_route }}" data-default-instruction="{{ $hm->default_instruction }}">
                            <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input pre-med-checkbox home-med-checkbox" type="checkbox" role="switch"
                                           id="premed_chk_{{ $hmKey }}"
                                           name="pre_meds[{{ $hmKey }}][enabled]" value="1"
                                           onchange="togglePreMedRow('{{ $hmKey }}')">
                                    <label class="form-check-label fw-bold text-dark" for="premed_chk_{{ $hmKey }}">
                                        {{ $hm->sm_name }}
                                    </label>
                                </div>
                                <span class="badge bg-secondary-subtle text-secondary pre-med-status-badge" id="premed_badge_{{ $hmKey }}">ไม่ใช้</span>
                            </div>
                            <input type="hidden" name="pre_meds[{{ $hmKey }}][sm_code]" value="{{ $hm->sm_code }}">
                            <input type="hidden" name="pre_meds[{{ $hmKey }}][sm_name]" value="{{ $hm->sm_name }}">
                            <input type="hidden" name="pre_meds[{{ $hmKey }}][category]" value="{{ $hm->category }}">

                            <div class="row g-1 align-items-center mt-1 pre-med-fields" id="premed_fields_{{ $hmKey }}">
                                <div class="col-5">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white px-1 small">ขนาด</span>
                                        <input type="text" name="pre_meds[{{ $hmKey }}][dose]" id="premed_dose_{{ $hmKey }}"
                                               class="form-control fw-bold text-dark" value="{{ $hm->default_dose }}">
                                    </div>
                                </div>
                                <div class="col-3">
                                    <input type="text" name="pre_meds[{{ $hmKey }}][route]" id="premed_route_{{ $hmKey }}"
                                           class="form-control form-control-sm text-center" value="{{ $hm->default_route }}" placeholder="Route">
                                </div>
                                <div class="col-4">
                                    @if(!empty($hm->dose_options) && is_array($hm->dose_options))
                                        <div class="d-flex flex-wrap gap-1 justify-content-end">
                                            @foreach($hm->dose_options as $opt)
                                                <button type="button" class="btn btn-outline-dark py-0 px-1" style="font-size: 0.67rem;"
                                                        onclick="setPreMedDose('{{ $hmKey }}', '{{ $opt }}')">{{ $opt }}</button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="col-12 mt-1">
                                    <input type="text" name="pre_meds[{{ $hmKey }}][instruction]" id="premed_inst_{{ $hmKey }}"
                                           class="form-control form-control-sm text-secondary" value="{{ $hm->default_instruction }}" placeholder="วิธีรับประทาน / คำแนะนำผู้ป่วย">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="small text-secondary">
                <i class="bi bi-info-circle me-1"></i> สูตรการคำนวณ: ยาหน่วย <code>mg/m2</code> = ขนาดยาเริ่มต้น &times; BSA | ยาหน่วย <code>AUC</code> (Carboplatin) = AUC &times; (GFR + 25)
            </div>
            <button type="submit" id="submitOrderBtn" class="btn btn-primary btn-lg fw-bold shadow" disabled>
                <i class="bi bi-send-check-fill me-1"></i> ยืนยันและส่งคำสั่งใช้ยาไปยังเภสัชกร
            </button>
        </div>
    </div>
</form>
@endsection

@section('js_after')
<script>
    const REGIMENS_DATA = @json($regimensJson);
    const MEDS_LIST = @json($meds->values());
    const UNITS_LIST = @json($units);
    const SOLUTIONS_LIST = @json($solutions);
    const ROUTES_LIST = @json($routes);
    const TIMINGS_LIST = @json($timings);
    const ADDITIVES_LIST = @json($additives);

    const BAG_GROUP_META = {
        'BAG-A': { label: 'ถุงรวม A', color: '#7c3aed', bg: '#f5f3ff' },
        'BAG-B': { label: 'ถุงรวม B', color: '#0d9488', bg: '#f0fdfa' },
        'BAG-C': { label: 'ถุงรวม C', color: '#ea580c', bg: '#fff7ed' },
        'BAG-D': { label: 'ถุงรวม D', color: '#db2777', bg: '#fdf2f8' },
    };

    let currentItems = [];

    function escapeHtmlAttr(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function syncInputsToState() {
        currentItems.forEach((item, idx) => {
            const ordEl = document.getElementById(`orderno_${idx}`);
            const bagEl = document.getElementById(`bag_group_${idx}`);
            const solEl = document.querySelector(`[name="items[${idx}][soln]"]`);
            const volEl = document.getElementById(`solvol_${idx}`);
            const rtEl  = document.querySelector(`[name="items[${idx}][medroute]"]`);
            const tmEl  = document.querySelector(`[name="items[${idx}][medtime]"]`);
            const sdEl  = document.getElementById(`start_day_${idx}`);
            const durEl = document.getElementById(`duration_${idx}`);
            const redEl = document.getElementById(`reduce_pct_${idx}`);

            if (ordEl) item.orderno = parseInt(ordEl.value) || (idx + 1);
            if (bagEl) item.bag_group = bagEl.value || '';
            if (solEl) item.soln = solEl.value;
            if (volEl) item.solvol = parseFloat(volEl.value) || 0;
            if (rtEl)  item.medroute = rtEl.value;
            if (tmEl)  item.medtime = tmEl.value;
            if (sdEl)  item.start_day = parseInt(sdEl.value) || 1;
            if (durEl) item.duration = parseInt(durEl.value) || 1;
            if (redEl) item.reduce_pct = parseFloat(redEl.value) || 0;
            if (!Array.isArray(item.additives)) item.additives = [];
        });
    }

    function calculateBsaAndGfr() {
        const wt = parseFloat(document.getElementById('or_wt').value) || 0;
        const ht = parseFloat(document.getElementById('or_ht').value) || 0;
        const age = parseFloat(document.getElementById('or_age').value) || 0;
        const sex = document.getElementById('or_sex').value;
        const scr = parseFloat(document.getElementById('or_scr').value) || 0;

        // 1. Mosteller BSA = sqrt((Ht * Wt) / 3600)
        let bsa = 0;
        if (wt > 0 && ht > 0) {
            bsa = Math.sqrt((ht * wt) / 3600);
        }
        bsa = Math.round(bsa * 100) / 100;

        // 2. Cockcroft-Gault GFR = (((140 - Age) * Wt) / (72 * SCr)) * (0.85 if Female)
        let gfr = 0;
        if (wt > 0 && age > 0 && scr > 0) {
            gfr = ((140 - age) * wt) / (72 * scr);
            if (sex === 'F') {
                gfr = gfr * 0.85;
            }
        }
        gfr = Math.round(gfr * 10) / 10;

        document.getElementById('bsaDisplay').innerText = bsa.toFixed(2);
        document.getElementById('or_bsa').value = bsa.toFixed(2);

        document.getElementById('gfrDisplay').innerText = gfr.toFixed(1);
        document.getElementById('or_gfr').value = gfr.toFixed(1);

        recalculateAllRows();
    }

    function onRegimenChange() {
        const select = document.getElementById('regimenSelect');
        const rhId = select.value;
        const selectedOption = select.options[select.selectedIndex];
        const rhCode = selectedOption ? (selectedOption.getAttribute('data-code') || '') : '';

        if (rhId === 'CUSTOM') {
            document.getElementById('or_reg_code').value = 'CUSTOM';
            currentItems = [];
            applyRegimenPreMeds({});
            addCustomMedicationRow();
            return;
        }

        if (!rhId || !REGIMENS_DATA[rhId]) {
            currentItems = [];
            applyRegimenPreMeds({});
            renderMedTable();
            updateSubmitState();
            return;
        }

        document.getElementById('or_reg_code').value = rhCode;

        const globalReduce = parseFloat(document.getElementById('globalReducePct').value) || 0;
        const reg = REGIMENS_DATA[rhId];
        applyRegimenPreMeds(reg.pre_meds || {});

        currentItems = reg.items.map((item, idx) => ({
            orderno: item.rd_orderno || (idx + 1),
            bag_group: item.rd_bag_group || '',
            additives: Array.isArray(item.rd_additives) ? JSON.parse(JSON.stringify(item.rd_additives)) : [],
            medcode: item.rd_medcode,
            dg_name: item.dg_name,
            dg_conc: item.dg_conc,
            dg_precaution: item.dg_precaution,
            base_dose: item.rd_meddose,
            medunit: item.rd_medunit,
            un_unit: item.un_unit,
            soln: item.rd_sol,
            solvol: item.rd_solvol,
            medroute: item.rd_medroute,
            medtime: item.rd_medtime,
            start_day: item.rd_start,
            duration: item.rd_duration,
            reduce_pct: globalReduce,
            std_dose: 0,
            final_dose: 0,
            is_off: false,
            is_custom: false,
        }));

        renderMedTable();
        recalculateAllRows();
        checkPatientCycleHistory(false);
    }

    function addCustomMedicationRow() {
        syncInputsToState();

        const regCodeInput = document.getElementById('or_reg_code');
        if (!regCodeInput.value || regCodeInput.value.trim() === '') {
            regCodeInput.value = 'CUSTOM';
        }

        const defaultMed = MEDS_LIST.length > 0 ? MEDS_LIST[0] : { dg_code: '5FU', dg_name: 'Fluorouracil (5-FU)', dg_conc: 50, dg_precaution: '' };
        const globalReduce = parseFloat(document.getElementById('globalReducePct').value) || 0;

        currentItems.push({
            orderno: currentItems.length + 1,
            bag_group: '',
            additives: [],
            medcode: defaultMed.dg_code,
            dg_name: defaultMed.dg_name,
            dg_conc: parseFloat(defaultMed.dg_conc) || 1,
            dg_precaution: defaultMed.dg_precaution || '',
            base_dose: 100,
            medunit: '03', // default mg/m2 (สามารถสลับเป็น 01 = mg คงที่ หรือ 04 = AUC ได้)
            un_unit: 'mg/m2',
            soln: 'NSS',
            solvol: 250,
            medroute: '01',
            medtime: '03',
            start_day: 1,
            duration: 1,
            reduce_pct: globalReduce,
            std_dose: 0,
            final_dose: 0,
            is_off: false,
            is_custom: true,
        });

        renderMedTable();
        recalculateAllRows();
        checkPatientCycleHistory(false);
    }

    function onBagGroupChange(idx, group) {
        syncInputsToState();
        if (!currentItems[idx]) return;
        currentItems[idx].bag_group = group || '';

        // หากเลือกกลุ่มถุงรวมที่มีตัวยาอื่นอยู่ก่อนหน้า ให้คัดลอกสารน้ำ/ปริมาตร/Route/Timing จากตัวยาแรกในถุงเดียวกันโดยอัตโนมัติ
        if (group) {
            const peer = currentItems.find((it, i) => i !== idx && !it.is_off && it.bag_group === group);
            if (peer) {
                currentItems[idx].soln = peer.soln;
                currentItems[idx].solvol = peer.solvol;
                currentItems[idx].medroute = peer.medroute;
                currentItems[idx].medtime = peer.medtime;
            }
        }
        renderMedTable();
        recalculateAllRows();
    }

    function onBagSharedFieldChange(idx, field, val) {
        if (!currentItems[idx]) return;
        currentItems[idx][field] = (field === 'solvol') ? (parseFloat(val) || 0) : val;
        const group = currentItems[idx].bag_group;
        if (!group) return;

        // ซิงก์ค่าสารน้ำ/ปริมาตร/วิถีทาง/เวลา ให้ตรงกันทุกตัวที่อยู่ในถุงรวมเดียวกัน
        currentItems.forEach((it, i) => {
            if (i !== idx && !it.is_off && it.bag_group === group) {
                it[field] = currentItems[idx][field];
                if (field === 'soln') {
                    const el = document.querySelector(`[name="items[${i}][soln]"]`);
                    if (el) el.value = val;
                } else if (field === 'solvol') {
                    const el = document.getElementById(`solvol_${i}`);
                    if (el) el.value = val;
                } else if (field === 'medroute') {
                    const el = document.querySelector(`[name="items[${i}][medroute]"]`);
                    if (el) el.value = val;
                } else if (field === 'medtime') {
                    const el = document.querySelector(`[name="items[${i}][medtime]"]`);
                    if (el) el.value = val;
                }
            }
        });
    }

    function addAdditiveToItem(idx) {
        syncInputsToState();
        if (!currentItems[idx]) return;
        if (!Array.isArray(currentItems[idx].additives)) currentItems[idx].additives = [];
        const firstAd = ADDITIVES_LIST.length > 0 ? ADDITIVES_LIST[0] : { ad_code: 'CUSTOM', ad_name: '', default_dose: '', default_unit: '', default_vol_ml: 0 };
        currentItems[idx].additives.push({
            code: firstAd.ad_code,
            name: firstAd.ad_name,
            dose: firstAd.default_dose || '',
            unit: firstAd.default_unit || '',
            vol_ml: parseFloat(firstAd.default_vol_ml) || 0,
        });
        renderMedTable();
        recalculateAllRows();
    }

    function onAdditivePresetChange(idx, adIdx, code) {
        if (!currentItems[idx] || !currentItems[idx].additives || !currentItems[idx].additives[adIdx]) return;
        if (code === 'CUSTOM') {
            currentItems[idx].additives[adIdx] = { code: 'CUSTOM', name: '', dose: '', unit: 'mEq', vol_ml: 0 };
        } else {
            const found = ADDITIVES_LIST.find(a => a.ad_code === code);
            if (found) {
                currentItems[idx].additives[adIdx] = {
                    code: found.ad_code,
                    name: found.ad_name,
                    dose: found.default_dose || '',
                    unit: found.default_unit || '',
                    vol_ml: parseFloat(found.default_vol_ml) || 0,
                };
            }
        }
        renderMedTable();
        recalculateAllRows();
    }

    function onAdditiveFieldChange(idx, adIdx, field, val) {
        if (!currentItems[idx] || !currentItems[idx].additives || !currentItems[idx].additives[adIdx]) return;
        currentItems[idx].additives[adIdx][field] = (field === 'vol_ml') ? (parseFloat(val) || 0) : val;
        const hiddenEl = document.getElementById(`additives_json_${idx}`);
        if (hiddenEl) {
            hiddenEl.value = JSON.stringify(currentItems[idx].additives);
        }
    }

    function removeAdditiveFromItem(idx, adIdx) {
        syncInputsToState();
        if (!currentItems[idx] || !currentItems[idx].additives) return;
        currentItems[idx].additives.splice(adIdx, 1);
        renderMedTable();
        recalculateAllRows();
    }

    function toggleItemOff(idx) {
        syncInputsToState();
        if (!currentItems[idx]) return;
        currentItems[idx].is_off = !currentItems[idx].is_off;
        renderMedTable();
        recalculateAllRows();
    }

    function removeCustomItem(idx) {
        syncInputsToState();
        currentItems.splice(idx, 1);
        renderMedTable();
        recalculateAllRows();
    }

    function onCustomMedSelectChange(idx, medcode) {
        syncInputsToState();
        const found = MEDS_LIST.find(m => m.dg_code === medcode);
        if (found && currentItems[idx]) {
            currentItems[idx].medcode = found.dg_code;
            currentItems[idx].dg_name = found.dg_name;
            currentItems[idx].dg_conc = parseFloat(found.dg_conc) || 1;
            currentItems[idx].dg_precaution = found.dg_precaution || '';
        }
        renderMedTable();
        recalculateAllRows();
    }

    function onBaseDoseOrUnitChange(idx) {
        const doseEl = document.getElementById(`base_dose_${idx}`);
        const unitEl = document.getElementById(`medunit_${idx}`);
        if (currentItems[idx]) {
            if (doseEl) currentItems[idx].base_dose = parseFloat(doseEl.value) || 0;
            if (unitEl) {
                currentItems[idx].medunit = unitEl.value;
                const uObj = UNITS_LIST.find(u => u.un_code === unitEl.value);
                currentItems[idx].un_unit = uObj ? uObj.un_unit : unitEl.value;
            }
        }
        recalculateAllRows();
    }

    function renderMedTable() {
        const tbody = document.getElementById('medItemsBody');
        if (!currentItems.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center py-5 text-secondary">
                        <i class="bi bi-arrow-up-circle fs-2 d-block mb-2 text-primary"></i>
                        เลือก <strong>สูตรยาเคมีบำบัด (Regimen)</strong> ด้านบน หรือคลิกปุ่ม <strong>"+ สั่งใช้ยาแบบอิสระ / เพิ่มตัวยา"</strong> เพื่อเลือกยาสั่งเองโดยอิสระ
                    </td>
                </tr>`;
            updateSubmitState();
            return;
        }

        // นับจำนวนตัวยาที่เปิดสั่งในแต่ละ Bag Group เพื่อแสดงสถานะถุงรวม
        const bagCounts = {};
        currentItems.forEach(it => {
            if (!it.is_off && it.bag_group) {
                bagCounts[it.bag_group] = (bagCounts[it.bag_group] || 0) + 1;
            }
        });

        let html = '';
        currentItems.forEach((item, idx) => {
            const isOff = !!item.is_off;
            const disabledAttr = isOff ? 'disabled' : '';
            const bagMeta = (!isOff && item.bag_group && BAG_GROUP_META[item.bag_group]) ? BAG_GROUP_META[item.bag_group] : null;
            const rowClass = isOff ? 'table-secondary opacity-50' : '';
            const rowStyle = bagMeta ? `border-left: 5px solid ${bagMeta.color}; background-color: ${bagMeta.bg};` : '';

            const solOptions = SOLUTIONS_LIST.map(s =>
                `<option value="${s.so_code}" ${s.so_code === item.soln ? 'selected' : ''}>${s.so_code} - ${s.so_name}</option>`
            ).join('');

            const routeOptions = ROUTES_LIST.map(r =>
                `<option value="${r.rt_code}" ${r.rt_code === item.medroute ? 'selected' : ''}>${r.rt_name}</option>`
            ).join('');

            const timeOptions = TIMINGS_LIST.map(t =>
                `<option value="${t.tm_code}" ${t.tm_code === item.medtime ? 'selected' : ''}>${t.tm_time}</option>`
            ).join('');

            const unitOptions = [
                { code: '03', label: 'mg/m2 (xBSA)' },
                { code: '01', label: 'mg (Fixed)' },
                { code: '04', label: 'AUC (Calvert)' },
            ].map(u => `<option value="${u.code}" ${u.code === item.medunit ? 'selected' : ''}>${u.label}</option>`).join('');

            const bagGroupOptions = [
                { code: '', label: 'แยกขวดเดี่ยว' },
                { code: 'BAG-A', label: 'ผสมรวมถุง A' },
                { code: 'BAG-B', label: 'ผสมรวมถุง B' },
                { code: 'BAG-C', label: 'ผสมรวมถุง C' },
                { code: 'BAG-D', label: 'ผสมรวมถุง D' },
            ].map(bg => `<option value="${bg.code}" ${bg.code === (item.bag_group || '') ? 'selected' : ''}>${bg.label}</option>`).join('');

            const bagBadgeHtml = bagMeta
                ? `<div class="mt-1"><span class="badge text-white" style="background-color: ${bagMeta.color}; font-size: 0.7rem;"><i class="bi bi-layers-fill me-1"></i>${bagMeta.label} (${bagCounts[item.bag_group] || 1} ตัวยา)</span></div>`
                : '';

            // ส่วนแสดงสารผสมเพิ่มเติมในขวดเดียวกัน (Additives)
            const additivesArr = Array.isArray(item.additives) ? item.additives : [];
            const additivesRowsHtml = additivesArr.map((ad, adIdx) => {
                const isCustomAd = ad.code === 'CUSTOM';
                const presetOpts = ADDITIVES_LIST.map(a =>
                    `<option value="${a.ad_code}" ${a.ad_code === ad.code ? 'selected' : ''}>${a.ad_name}</option>`
                ).join('') + `<option value="CUSTOM" ${isCustomAd ? 'selected' : ''}>อื่นๆ (พิมพ์ชื่อเอง)...</option>`;

                return `
                    <div class="border rounded-2 p-1 bg-white mt-1 shadow-sm" style="font-size: 0.72rem;">
                        <div class="d-flex gap-1 align-items-center mb-1">
                            <span class="badge bg-info text-dark px-1">+สาร</span>
                            <select class="form-select form-select-sm py-0 px-1" style="font-size: 0.72rem;"
                                onchange="onAdditivePresetChange(${idx}, ${adIdx}, this.value)" ${disabledAttr}>
                                ${presetOpts}
                            </select>
                            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1" style="font-size: 0.68rem;"
                                onclick="removeAdditiveFromItem(${idx}, ${adIdx})" title="ลบสารผสมนี้" ${disabledAttr}>&times;</button>
                        </div>
                        ${isCustomAd ? `
                            <input type="text" class="form-control form-control-sm py-0 px-1 mb-1" style="font-size: 0.72rem;"
                                placeholder="ระบุชื่อสารผสม เช่น 10% CaGluconate..."
                                value="${escapeHtmlAttr(ad.name)}"
                                oninput="onAdditiveFieldChange(${idx}, ${adIdx}, 'name', this.value)" ${disabledAttr}>
                        ` : ''}
                        <div class="d-flex gap-1">
                            <input type="text" class="form-control form-control-sm py-0 px-1 text-end" style="font-size: 0.72rem; width: 42%;"
                                placeholder="ขนาด" value="${escapeHtmlAttr(ad.dose)}"
                                oninput="onAdditiveFieldChange(${idx}, ${adIdx}, 'dose', this.value)" ${disabledAttr}>
                            <input type="text" class="form-control form-control-sm py-0 px-1" style="font-size: 0.72rem; width: 28%;"
                                placeholder="หน่วย" value="${escapeHtmlAttr(ad.unit)}"
                                oninput="onAdditiveFieldChange(${idx}, ${adIdx}, 'unit', this.value)" ${disabledAttr}>
                            <div class="input-group input-group-sm" style="width: 30%;">
                                <input type="number" step="0.5" min="0" class="form-control form-control-sm py-0 px-1 text-end" style="font-size: 0.7rem;"
                                    placeholder="mL" title="ปริมาตรสารผสม (mL)" value="${ad.vol_ml || 0}"
                                    oninput="onAdditiveFieldChange(${idx}, ${adIdx}, 'vol_ml', this.value)" ${disabledAttr}>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            // ส่วนแสดงชื่อยา (หากเป็นรายการสั่งอิสระ ให้เลือกยาจาก Dropdown ได้ทันที)
            let medCellHtml = '';
            if (item.is_custom) {
                const medOptions = MEDS_LIST.map(m =>
                    `<option value="${m.dg_code}" ${m.dg_code === item.medcode ? 'selected' : ''}>[${m.dg_code}] ${m.dg_name} (${m.dg_conc} mg/mL)</option>`
                ).join('');
                medCellHtml = `
                    <select name="items[${idx}][medcode]" class="form-select form-select-sm fw-bold text-primary border-primary mb-1"
                        onchange="onCustomMedSelectChange(${idx}, this.value)" ${disabledAttr}>
                        ${medOptions}
                    </select>
                    <small class="text-muted">ความเข้มข้น: ${item.dg_conc} mg/mL <span class="badge bg-info-subtle text-dark border ms-1">สั่งอิสระ</span></small>
                    ${bagBadgeHtml}
                `;
            } else {
                medCellHtml = `
                    <div class="fw-bold ${isOff ? 'text-decoration-line-through text-secondary' : 'text-primary'}">
                        [${item.medcode}] ${item.dg_name}
                        ${isOff ? '<span class="badge bg-danger ms-1">OFF (งดสั่ง)</span>' : ''}
                    </div>
                    <small class="text-muted">ความเข้มข้น: ${item.dg_conc} mg/mL</small>
                    ${bagBadgeHtml}
                    <input type="hidden" name="items[${idx}][medcode]" value="${item.medcode}" ${disabledAttr}>
                `;
            }

            html += `
                <tr class="${rowClass}" style="${rowStyle}" id="med_row_${idx}">
                    <td class="text-center">
                        <div class="d-flex flex-column gap-1 align-items-center">
                            <button type="button" class="btn btn-sm ${isOff ? 'btn-secondary' : 'btn-success'} fw-bold w-100 py-1"
                                onclick="toggleItemOff(${idx})" title="คลิกเพื่อเปิด/ปิด (ON/OFF) การสั่งใช้ยาตัวนี้">
                                <i class="bi ${isOff ? 'bi-toggle-off' : 'bi-toggle-on'} me-1"></i>${isOff ? 'OFF (งด)' : 'ON (สั่ง)'}
                            </button>
                            <select name="items[${idx}][bag_group]" id="bag_group_${idx}"
                                class="form-select form-select-sm py-0 px-1 fw-semibold"
                                style="font-size: 0.7rem; ${bagMeta ? 'border-color: ' + bagMeta.color + '; color: ' + bagMeta.color + ';' : ''}"
                                onchange="onBagGroupChange(${idx}, this.value)"
                                title="เลือกกลุ่มถุงรวม หากต้องการผสมยาเคมีบำบัดหลายตัวลงในขวด/ถุงเดียวกัน" ${disabledAttr}>
                                ${bagGroupOptions}
                            </select>
                            ${item.is_custom ? `
                                <input type="hidden" name="items[${idx}][orderno]" id="orderno_${idx}" value="${item.orderno || (idx + 1)}" ${disabledAttr}>
                                <button type="button" class="btn btn-xs btn-outline-danger btn-sm py-0 px-2 w-100" style="font-size: 0.7rem;"
                                    onclick="removeCustomItem(${idx})">
                                    <i class="bi bi-trash"></i> ลบแถว
                                </button>
                            ` : `
                                <input type="hidden" name="items[${idx}][orderno]" id="orderno_${idx}" value="${item.orderno || (idx + 1)}" ${disabledAttr}>
                                <span class="badge bg-warning-subtle text-dark border border-warning" style="font-size: 0.7rem;">ลำดับให้ยา #${item.orderno || (idx + 1)}</span>
                            `}
                        </div>
                    </td>
                    <td>
                        ${medCellHtml}
                    </td>
                    <td>
                        <div class="input-group input-group-sm mb-1">
                            <input type="number" step="0.01" min="0.01"
                                id="base_dose_${idx}"
                                class="form-control text-end fw-bold"
                                value="${item.base_dose}"
                                oninput="onBaseDoseOrUnitChange(${idx})" ${disabledAttr}>
                        </div>
                        <select name="items[${idx}][medunit]" id="medunit_${idx}"
                            class="form-select form-select-sm text-secondary"
                            onchange="onBaseDoseOrUnitChange(${idx})" ${disabledAttr}>
                            ${unitOptions}
                        </select>
                    </td>
                    <td class="text-center">
                        <div class="fw-bold text-dark fs-6" id="std_dose_text_${idx}">0.00 mg</div>
                        <small class="text-secondary d-block" id="raw_reduced_text_${idx}"></small>
                        <input type="hidden" name="items[${idx}][std_dose]" id="std_dose_input_${idx}" value="0" ${disabledAttr}>
                    </td>
                    <td class="bg-warning-subtle" style="min-width: 165px;">
                        <div class="input-group input-group-sm mb-1">
                            <span class="input-group-text px-1 bg-white text-secondary" style="font-size: 0.72rem;">ลด</span>
                            <input type="number" step="1" min="0" max="90"
                                name="items[${idx}][reduce_pct]"
                                id="reduce_pct_${idx}"
                                class="form-control text-center fw-bold text-danger px-1"
                                value="${item.reduce_pct}"
                                oninput="onItemReduceChange(${idx}, this.value)" ${disabledAttr}>
                            <span class="input-group-text px-1 bg-white fw-bold">%</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1 justify-content-center" id="item_reduce_btns_${idx}">
                            ${[0, 10, 20, 25, 30, 50].map(p => {
                                const isActive = Math.round(item.reduce_pct) === p;
                                const cls = isActive
                                    ? (p === 0 ? 'btn-secondary' : 'btn-danger')
                                    : (p === 0 ? 'btn-outline-secondary' : 'btn-outline-danger');
                                return `<button type="button" class="btn ${cls} py-0 px-1 fw-semibold" style="font-size: 0.67rem;" onclick="setItemReduction(${idx}, ${p})" ${disabledAttr}>${p === 0 ? '0%' : '-' + p + '%'}</button>`;
                            }).join('')}
                        </div>
                        <div class="text-center mt-1" id="item_reduce_status_${idx}">
                            ${item.reduce_pct > 0
                                ? `<span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.67rem;">ลดเฉพาะตัว -${item.reduce_pct}%</span>`
                                : `<span class="text-muted" style="font-size: 0.67rem;">100% (Full Dose)</span>`}
                        </div>
                    </td>
                    <td style="min-width: 195px;">
                        <div class="input-group input-group-sm">
                            <input type="number" step="0.01" min="0.01"
                                name="items[${idx}][meddose]"
                                id="final_dose_${idx}"
                                class="form-control text-end dose-highlight"
                                value="0.00"
                                oninput="onFinalDoseManualChange(${idx}, this.value)" ${disabledAttr} required>
                            <span class="input-group-text fw-bold">mg</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <span id="band_badge_${idx}" class="badge bg-success-subtle text-success border" style="font-size: 0.7rem;">Exact</span>
                            <small class="fw-semibold text-primary" id="draw_vol_text_${idx}">ดูดยา: 0.00 mL</small>
                        </div>
                        <div id="band_chips_${idx}" class="d-flex justify-content-end gap-1 mt-1 flex-wrap"></div>
                    </td>
                    <td style="min-width: 210px;">
                        <select name="items[${idx}][soln]" class="form-select form-select-sm"
                            onchange="onBagSharedFieldChange(${idx}, 'soln', this.value)" ${disabledAttr}>
                            ${solOptions}
                        </select>
                        <input type="hidden" name="items[${idx}][additives_json]" id="additives_json_${idx}"
                            value="${escapeHtmlAttr(JSON.stringify(additivesArr))}" ${disabledAttr}>
                        ${additivesRowsHtml}
                        <button type="button" class="btn btn-outline-info btn-sm py-0 px-2 mt-1 w-100 fw-semibold" style="font-size: 0.7rem;"
                            onclick="addAdditiveToItem(${idx})" ${disabledAttr}>
                            <i class="bi bi-plus-circle me-1"></i>ผสมสารอื่นในขวดนี้ (NaHCO3 / Mannitol / KCl...)
                        </button>
                    </td>
                    <td style="min-width: 145px;">
                        <div class="input-group input-group-sm">
                            <input type="number" step="1" min="0"
                                name="items[${idx}][solvol]"
                                id="solvol_${idx}"
                                class="form-control text-end fw-bold fs-6 px-2"
                                style="min-width: 80px;"
                                value="${item.solvol}"
                                oninput="onBagSharedFieldChange(${idx}, 'solvol', this.value)" ${disabledAttr}>
                            <span class="input-group-text px-2 fw-semibold">mL</span>
                        </div>
                    </td>
                    <td>
                        <select name="items[${idx}][medroute]" class="form-select form-select-sm mb-1"
                            onchange="onBagSharedFieldChange(${idx}, 'medroute', this.value)" ${disabledAttr}>
                            ${routeOptions}
                        </select>
                        <select name="items[${idx}][medtime]" class="form-select form-select-sm"
                            onchange="onBagSharedFieldChange(${idx}, 'medtime', this.value)" ${disabledAttr}>
                            ${timeOptions}
                        </select>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">D</span>
                            <input type="number" min="1" max="30"
                                name="items[${idx}][start_day]"
                                id="start_day_${idx}"
                                class="form-control text-center"
                                value="${item.start_day}"
                                oninput="updateDateRangeDisplay(${idx})" ${disabledAttr}>
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="number" min="1" max="30"
                                name="items[${idx}][duration]"
                                id="duration_${idx}"
                                class="form-control text-center fw-bold text-primary"
                                value="${item.duration}"
                                oninput="updateDateRangeDisplay(${idx})" ${disabledAttr}>
                            <span class="input-group-text">วัน</span>
                        </div>
                    </td>
                    <td class="text-center small">
                        <div class="fw-semibold text-dark" id="date_range_${idx}">-</div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        updateSubmitState();
    }

    function updateSubmitState() {
        const activeItems = currentItems.filter(i => !i.is_off);
        const offItems = currentItems.filter(i => i.is_off);
        const counterEl = document.getElementById('activeItemsCounter');
        const btn = document.getElementById('submitOrderBtn');

        if (counterEl) {
            counterEl.innerText = offItems.length > 0
                ? `สั่งใช้จริง: ${activeItems.length} รายการ (งด/OFF: ${offItems.length} รายการ)`
                : `สั่งใช้จริง: ${activeItems.length} รายการ`;
        }
        if (btn) {
            btn.disabled = activeItems.length === 0;
        }
    }

    // ตารางกำหนด Clinical Dose Banding Step มาตรฐานตามรหัสยา (mg)
    const CLINICAL_BAND_STEPS = {
        '5FU':   25,  // 50 mg/mL -> 25 mg = 0.5 mL
        'LV':    10,  // 10 mg/mL -> 10 mg = 1.0 mL
        'OXAL':  5,   // 5 mg/mL  -> 5 mg  = 1.0 mL
        'DOXO':  2,   // 2 mg/mL  -> 2 mg  = 1.0 mL
        'CYCLO': 10,  // 20 mg/mL -> 10 mg = 0.5 mL
        'PACLI': 3,   // 6 mg/mL  -> 3 mg  = 0.5 mL
        'CARBO': 10,  // 10 mg/mL -> 10 mg = 1.0 mL
        'CISPL': 5,   // 1 mg/mL  -> 5 mg  = 5.0 mL
        'GEMCI': 20,  // 38 mg/mL -> 20 mg
        'IRIN':  10,  // 20 mg/mL -> 10 mg = 0.5 mL
    };

    function getBandStepForItem(item, rawDose) {
        const modeEl = document.getElementById('roundingModeSelect');
        const mode = modeEl ? modeEl.value : 'clinical_band';
        const conc = item.dg_conc > 0 ? item.dg_conc : 1;

        if (mode === 'exact' || rawDose <= 0) {
            return 0;
        }
        if (mode === 'syringe_05') {
            return Math.round(conc * 0.5 * 100) / 100;
        }
        if (mode === 'syringe_02') {
            return Math.round(conc * 0.2 * 100) / 100;
        }
        if (mode === 'int_5') {
            return 5;
        }
        if (mode === 'int_1') {
            return 1;
        }

        // Default: clinical_band (ควบคุมความคลาดเคลื่อนไม่ให้เกิน ±5% ของขนาดยา)
        let step = CLINICAL_BAND_STEPS[item.medcode] || (rawDose >= 500 ? 25 : (rawDose >= 100 ? 5 : 1));
        const fallbackSteps = [25, 10, 5, 2, 1, 0.5];
        if ((step / 2) > (rawDose * 0.05)) {
            for (const s of fallbackSteps) {
                if ((s / 2) <= (rawDose * 0.05)) {
                    step = s;
                    break;
                }
            }
        }
        return step;
    }

    function applyDoseBanding(rawDose, step) {
        if (!step || step <= 0 || rawDose <= 0) {
            return Math.round(rawDose * 100) / 100;
        }
        const dirEl = document.getElementById('roundingDirSelect');
        const dir = dirEl ? dirEl.value : 'nearest';

        let banded = rawDose;
        if (dir === 'down') {
            banded = Math.floor((rawDose + 1e-9) / step) * step;
        } else if (dir === 'up') {
            banded = Math.ceil((rawDose - 1e-9) / step) * step;
        } else {
            banded = Math.round(rawDose / step) * step;
        }
        if (banded <= 0) banded = step;
        return Math.round(banded * 100) / 100;
    }

    function recalculateAllRows() {
        const bsa = parseFloat(document.getElementById('or_bsa').value) || 1.0;
        const gfr = parseFloat(document.getElementById('or_gfr').value) || 60.0;

        currentItems.forEach((item, idx) => {
            let stdDose = 0;
            // Unit 04 = AUC (Calvert Formula: AUC * (GFR + 25))
            if (item.medunit === '04' || (item.un_unit && item.un_unit.toUpperCase() === 'AUC')) {
                stdDose = item.base_dose * (gfr + 25);
            } else if (item.medunit === '01') {
                // Unit 01 = mg (Fixed Dose ไม่คูณ BSA)
                stdDose = item.base_dose;
            } else {
                // Unit 03 = mg/m2 (คูณ BSA)
                stdDose = item.base_dose * bsa;
            }

            stdDose = Math.round(stdDose * 100) / 100;
            item.std_dose = stdDose;

            const reduceInput = document.getElementById(`reduce_pct_${idx}`);
            const reducePct = reduceInput ? (parseFloat(reduceInput.value) || 0) : item.reduce_pct;

            const rawReducedDose = Math.round(stdDose * (1 - reducePct / 100) * 100) / 100;
            item.raw_reduced_dose = rawReducedDose;

            const step = getBandStepForItem(item, rawReducedDose);
            item.band_step = step;
            const finalDose = applyDoseBanding(rawReducedDose, step);
            item.final_dose = finalDose;

            const stdTextEl = document.getElementById(`std_dose_text_${idx}`);
            const rawReducedEl = document.getElementById(`raw_reduced_text_${idx}`);
            const stdInputEl = document.getElementById(`std_dose_input_${idx}`);
            const finalInputEl = document.getElementById(`final_dose_${idx}`);

            if (stdTextEl) stdTextEl.innerText = stdDose.toFixed(2) + ' mg';
            if (rawReducedEl) {
                rawReducedEl.innerText = reducePct > 0
                    ? `หลังลด ${reducePct}%: ${rawReducedDose.toFixed(2)} mg`
                    : `ก่อนปัด: ${rawReducedDose.toFixed(2)} mg`;
            }
            if (stdInputEl) stdInputEl.value = stdDose.toFixed(2);
            if (finalInputEl) finalInputEl.value = finalDose.toFixed(2);

            updateBandingUI(idx, rawReducedDose, finalDose, step, item.dg_conc);
            updateDateRangeDisplay(idx);
        });

        toggleReasonBoxVisibility();
        updateMesnaCalculation(false);
    }

    function updateBandingUI(idx, rawDose, finalDose, step, conc) {
        updateDrawVolumeDisplay(idx, finalDose, conc);

        const badgeEl = document.getElementById(`band_badge_${idx}`);
        const chipsEl = document.getElementById(`band_chips_${idx}`);

        if (badgeEl) {
            if (rawDose > 0) {
                const diffPct = ((finalDose - rawDose) / rawDose) * 100;
                const sign = diffPct > 0 ? '+' : '';
                const absDiff = Math.abs(diffPct);

                if (absDiff < 0.01) {
                    badgeEl.className = 'badge bg-secondary-subtle text-secondary border';
                    badgeEl.innerText = 'Exact (0.0%)';
                } else if (absDiff <= 5.0) {
                    badgeEl.className = 'badge bg-success-subtle text-success border border-success';
                    badgeEl.innerText = `Band: ${sign}${diffPct.toFixed(1)}% (ในเกณฑ์ ±5%)`;
                } else {
                    badgeEl.className = 'badge bg-warning-subtle text-danger border border-danger';
                    badgeEl.innerText = `ต่าง ${sign}${diffPct.toFixed(1)}% (>5%)`;
                }
            }
        }

        if (chipsEl) {
            if (!step || step <= 0 || rawDose <= 0) {
                chipsEl.innerHTML = '';
                return;
            }
            const lower = Math.max(step, Math.floor(rawDose / step) * step);
            const nearest = Math.max(step, Math.round(rawDose / step) * step);
            const upper = Math.ceil(rawDose / step) * step;
            const candidates = Array.from(new Set([
                Math.round((nearest - step) * 100) / 100,
                Math.round(lower * 100) / 100,
                Math.round(nearest * 100) / 100,
                Math.round(upper * 100) / 100,
            ])).filter(v => v > 0).slice(0, 3);

            chipsEl.innerHTML = `<span class="text-muted" style="font-size:0.68rem;">Band:</span>` + candidates.map(val => {
                const isSelected = Math.abs(val - finalDose) < 0.01;
                const btnClass = isSelected ? 'btn-danger' : 'btn-outline-secondary';
                return `<button type="button" class="btn ${btnClass} py-0 px-1" style="font-size: 0.68rem;" onclick="selectBandValue(${idx}, ${val})">${val}</button>`;
            }).join('');
        }
    }

    function selectBandValue(idx, val) {
        const finalInputEl = document.getElementById(`final_dose_${idx}`);
        if (finalInputEl) {
            finalInputEl.value = parseFloat(val).toFixed(2);
            onFinalDoseManualChange(idx, val);
        }
    }

    function updateDrawVolumeDisplay(idx, finalDose, conc) {
        const drawEl = document.getElementById(`draw_vol_text_${idx}`);
        if (!drawEl) return;
        const drawVol = conc > 0 ? (finalDose / conc) : 0;
        drawEl.innerText = `ดูดยา: ${drawVol.toFixed(2)} mL`;
    }

    function updateDateRangeDisplay(idx) {
        const baseDateStr = document.getElementById('or_date').value;
        const startDayEl = document.getElementById(`start_day_${idx}`);
        const durationEl = document.getElementById(`duration_${idx}`);
        const rangeEl = document.getElementById(`date_range_${idx}`);

        if (!baseDateStr || !startDayEl || !durationEl || !rangeEl) return;

        const startDay = Math.max(1, parseInt(startDayEl.value) || 1);
        const duration = Math.max(1, parseInt(durationEl.value) || 1);

        const startDate = new Date(baseDateStr);
        startDate.setDate(startDate.getDate() + (startDay - 1));

        const stopDate = new Date(startDate);
        stopDate.setDate(stopDate.getDate() + (duration - 1));

        const fmt = (d) => d.toLocaleDateString('th-TH', { day: '2-digit', month: 'short' });
        rangeEl.innerHTML = `<span class="badge bg-info-subtle text-dark border">${fmt(startDate)} - ${fmt(stopDate)}</span>`;
    }

    function setGlobalReduction(pct) {
        document.getElementById('globalReducePct').value = pct;
        applyGlobalReduction(pct);
    }

    function applyGlobalReduction(pct) {
        const cleanPct = Math.min(90, Math.max(0, parseFloat(pct) || 0));
        currentItems.forEach((item, idx) => {
            item.reduce_pct = cleanPct;
            const el = document.getElementById(`reduce_pct_${idx}`);
            if (el) el.value = cleanPct;
            updateItemReduceUI(idx, cleanPct);
        });
        recalculateAllRows();
    }

    function setItemReduction(idx, pct) {
        const el = document.getElementById(`reduce_pct_${idx}`);
        if (el) el.value = pct;
        onItemReduceChange(idx, pct);
    }

    function updateItemReduceUI(idx, pct) {
        const btnsEl = document.getElementById(`item_reduce_btns_${idx}`);
        const statusEl = document.getElementById(`item_reduce_status_${idx}`);
        const isOff = currentItems[idx] ? !!currentItems[idx].is_off : false;
        const disabledAttr = isOff ? 'disabled' : '';

        if (btnsEl) {
            btnsEl.innerHTML = [0, 10, 20, 25, 30, 50].map(p => {
                const isActive = Math.round(pct) === p;
                const cls = isActive
                    ? (p === 0 ? 'btn-secondary' : 'btn-danger')
                    : (p === 0 ? 'btn-outline-secondary' : 'btn-outline-danger');
                return `<button type="button" class="btn ${cls} py-0 px-1 fw-semibold" style="font-size: 0.67rem;" onclick="setItemReduction(${idx}, ${p})" ${disabledAttr}>${p === 0 ? '0%' : '-' + p + '%'}</button>`;
            }).join('');
        }

        if (statusEl) {
            statusEl.innerHTML = pct > 0
                ? `<span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 0.67rem;">ลดเฉพาะตัว -${pct}%</span>`
                : `<span class="text-muted" style="font-size: 0.67rem;">100% (Full Dose)</span>`;
        }
    }

    function onItemReduceChange(idx, val) {
        const cleanPct = Math.min(90, Math.max(0, parseFloat(val) || 0));
        if (currentItems[idx]) {
            currentItems[idx].reduce_pct = cleanPct;
            const stdDose = currentItems[idx].std_dose;
            const rawReducedDose = Math.round(stdDose * (1 - cleanPct / 100) * 100) / 100;
            currentItems[idx].raw_reduced_dose = rawReducedDose;

            const step = getBandStepForItem(currentItems[idx], rawReducedDose);
            currentItems[idx].band_step = step;
            const finalDose = applyDoseBanding(rawReducedDose, step);
            currentItems[idx].final_dose = finalDose;

            const rawReducedEl = document.getElementById(`raw_reduced_text_${idx}`);
            if (rawReducedEl) {
                rawReducedEl.innerText = cleanPct > 0
                    ? `หลังลด ${cleanPct}%: ${rawReducedDose.toFixed(2)} mg`
                    : `ก่อนปัด: ${rawReducedDose.toFixed(2)} mg`;
            }

            document.getElementById(`final_dose_${idx}`).value = finalDose.toFixed(2);
            updateItemReduceUI(idx, cleanPct);
            updateBandingUI(idx, rawReducedDose, finalDose, step, currentItems[idx].dg_conc);
        }
        toggleReasonBoxVisibility();
        updateMesnaCalculation(false);
    }

    function onFinalDoseManualChange(idx, val) {
        const finalDose = parseFloat(val) || 0;
        if (currentItems[idx]) {
            currentItems[idx].final_dose = finalDose;
            const rawDose = currentItems[idx].raw_reduced_dose || currentItems[idx].std_dose || finalDose;
            const step = currentItems[idx].band_step || 0;
            updateBandingUI(idx, rawDose, finalDose, step, currentItems[idx].dg_conc);
        }
    }

    function toggleReasonBoxVisibility() {
        const globalPct = parseFloat(document.getElementById('globalReducePct').value) || 0;
        let maxItemPct = 0;
        const offDrugs = [];

        currentItems.forEach((item, idx) => {
            if (item.is_off && !item.is_custom) {
                offDrugs.push(item.medcode);
            }
            if (!item.is_off) {
                const el = document.getElementById(`reduce_pct_${idx}`);
                const p = el ? (parseFloat(el.value) || 0) : 0;
                if (p > maxItemPct) maxItemPct = p;
            }
        });

        const hasReductionOrOff = (globalPct > 0 || maxItemPct > 0 || offDrugs.length > 0);
        const box = document.getElementById('reasonBox');
        const reasonInput = document.getElementById('or_adjust_reason');

        if (hasReductionOrOff) {
            box.style.display = 'block';
            reasonInput.required = (globalPct > 0 || maxItemPct > 0);
            if (offDrugs.length > 0 && !reasonInput.value.trim()) {
                reasonInput.placeholder = `ระบุเหตุผลที่งดสั่งยา [${offDrugs.join(', ')}] หรือปรับลดขนาดยา...`;
            }
        } else {
            box.style.display = 'none';
            reasonInput.required = false;
        }
    }

    function setReason(text) {
        const input = document.getElementById('or_adjust_reason');
        input.value = text;
        input.focus();
    }

    const PATIENT_HISTORY_URL = @json(route('doctor.patient-history'));
    let hnLookupTimer = null;

    function onHnInputChange(val) {
        clearTimeout(hnLookupTimer);
        const hn = (val || '').trim();
        if (hn.length < 3) {
            const banner = document.getElementById('cycleHistoryBanner');
            if (banner) banner.style.display = 'none';
            return;
        }
        hnLookupTimer = setTimeout(() => {
            checkPatientCycleHistory(false);
        }, 400);
    }

    function onCycleManualEdit(input) {
        input.setAttribute('data-manual', 'true');
        const badge = document.getElementById('cycleAutoBadge');
        if (badge) {
            badge.style.display = 'inline-block';
            badge.className = 'badge bg-warning text-dark';
            badge.innerText = 'แก้ไขเอง';
        }
    }

    function applySuggestedCycle(cycleNum, autoSelectRegCode) {
        const cycleEl = document.getElementById('or_cycle');
        if (cycleEl) {
            cycleEl.value = cycleNum;
            cycleEl.setAttribute('data-manual', 'false');
        }
        const badge = document.getElementById('cycleAutoBadge');
        if (badge) {
            badge.style.display = 'inline-block';
            badge.className = 'badge bg-info text-dark';
            badge.innerText = 'Auto';
        }

        if (autoSelectRegCode) {
            const matchedOpt = allRegimenOptions.find(o => o.code.toUpperCase() === autoSelectRegCode.toUpperCase());
            if (matchedOpt) {
                selectRegimenFromSearch(matchedOpt.value);
            }
        }
    }

    function checkPatientCycleHistory(forceFillPatient) {
        const hnEl = document.getElementById('or_hn');
        const hn = hnEl ? hnEl.value.trim() : '';
        const regCodeEl = document.getElementById('or_reg_code');
        const regCode = regCodeEl ? regCodeEl.value.trim() : '';
        const banner = document.getElementById('cycleHistoryBanner');
        const cycleEl = document.getElementById('or_cycle');
        const badge = document.getElementById('cycleAutoBadge');

        if (!hn) {
            if (banner) banner.style.display = 'none';
            return;
        }

        const url = `${PATIENT_HISTORY_URL}?hn=${encodeURIComponent(hn)}&reg_code=${encodeURIComponent(regCode)}`;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.found) {
                if (cycleEl && cycleEl.getAttribute('data-manual') !== 'true') {
                    cycleEl.value = 1;
                }
                if (badge) {
                    badge.style.display = 'inline-block';
                    badge.className = 'badge bg-secondary text-white';
                    badge.innerText = 'New';
                }
                if (banner) {
                    banner.className = 'mt-3 p-2 px-3 rounded-3 border border-secondary-subtle bg-light small text-secondary';
                    banner.innerHTML = `<i class="bi bi-info-circle me-1"></i> ไม่พบประวัติการรับยาเคมีบำบัดเดิมของ HN <strong>${hn}</strong> &mdash; ระบบตั้งค่าเริ่มต้นเป็น <strong>Cycle #1</strong> (แพทย์สามารถแก้ไขตัวเลข Cycle ได้)`;
                    banner.style.display = 'block';
                }
                return;
            }

            // เติมข้อมูลผู้ป่วยอัตโนมัติหากยังว่างอยู่ หรือกดปุ่มค้นหาโดยตรง
            const patNameEl = document.getElementById('or_patname');
            if (data.patient && (forceFillPatient || !patNameEl.value.trim())) {
                patNameEl.value = data.patient.or_patname || '';
                if (data.patient.or_age) document.getElementById('or_age').value = data.patient.or_age;
                if (data.patient.or_sex) document.getElementById('or_sex').value = data.patient.or_sex;
                if (data.patient.or_wt)  document.getElementById('or_wt').value = data.patient.or_wt;
                if (data.patient.or_ht)  document.getElementById('or_ht').value = data.patient.or_ht;
                if (data.patient.or_scr) document.getElementById('or_scr').value = data.patient.or_scr;
                if (data.patient.or_ward) document.getElementById('or_ward').value = data.patient.or_ward;
                calculateBsaAndGfr();
            }

            // อัปเดตช่อง Cycle อัตโนมัติ (หากแพทย์ยังไม่ได้ล็อกค่าที่แก้ไขเอง หรือกดปุ่มค้นหา)
            if (cycleEl && (forceFillPatient || cycleEl.getAttribute('data-manual') !== 'true')) {
                cycleEl.value = data.next_cycle || 1;
                cycleEl.setAttribute('data-manual', 'false');
                if (badge) {
                    badge.style.display = 'inline-block';
                    badge.className = 'badge bg-info text-dark';
                    badge.innerText = 'Auto';
                }
            }

            if (banner) {
                const histTags = (data.history || []).map(h =>
                    `<span class="badge bg-white text-dark border me-1">${h.or_reg_code} Cycle #${h.or_cycle} (${h.or_date})</span>`
                ).join('');

                const regSummary = data.last_cycle > 0
                    ? `เคยได้รับสูตร <strong>${data.matched_reg_code}</strong> ล่าสุด <strong>Cycle #${data.last_cycle}</strong> (เมื่อ ${data.last_date}) &rarr; ระบบกำหนดรอบนี้เป็น <span class="badge bg-primary fs-6">Cycle #${data.next_cycle}</span>`
                    : `ผู้ป่วยมีประวัติเดิม (${data.patient.last_reg} Cycle #${data.patient.last_cycle}) แต่เริ่มสูตรใหม่ <strong>${data.matched_reg_code}</strong> เป็นครั้งแรก &rarr; เริ่ม <span class="badge bg-primary fs-6">Cycle #1</span>`;

                banner.className = 'mt-3 p-2 px-3 rounded-3 border border-info bg-info-subtle small text-dark';
                banner.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <i class="bi bi-clock-history text-primary me-1"></i>
                            <strong>ตรวจสอบประวัติผู้ป่วย [${data.hn}] ${data.patient.or_patname}:</strong>
                            ${regSummary}
                            <span class="text-secondary ms-1">(สามารถแก้ไขช่อง Cycle ที่ ได้ตามต้องการ)</span>
                        </div>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-xs btn-primary btn-sm py-0 px-2"
                                onclick="applySuggestedCycle(${data.next_cycle}, '${data.matched_reg_code || ''}')">
                                <i class="bi bi-magic me-1"></i> ใช้สูตร ${data.matched_reg_code} (Cycle #${data.next_cycle})
                            </button>
                        </div>
                    </div>
                    <div class="mt-1 pt-1 border-top border-info-subtle text-secondary">
                        <strong class="me-1">ประวัติย้อนหลัง:</strong> ${histTags}
                    </div>
                `;
                banner.style.display = 'block';
            }
        })
        .catch(() => {});
    }

    function fillDemoPatient() {
        const demoPatients = [
            { hn: '6701122', name: 'นาย กิตติพงศ์ ใจดี', age: 58, sex: 'M', wt: 62.0, ht: 168, scr: 0.95, ward: '000' },
            { hn: '6708412', name: 'นาย วิชัย ใจสู้ดี', age: 62, sex: 'M', wt: 64.5, ht: 168, scr: 1.05, ward: '000' },
            { hn: '6709105', name: 'นาง มาลี ศรีสุข', age: 54, sex: 'F', wt: 56.0, ht: 158, scr: 0.85, ward: '002' },
        ];
        const p = demoPatients[0];
        document.getElementById('or_hn').value = p.hn;
        document.getElementById('or_patname').value = p.name;
        document.getElementById('or_age').value = p.age;
        document.getElementById('or_sex').value = p.sex;
        document.getElementById('or_wt').value = p.wt;
        document.getElementById('or_ht').value = p.ht;
        document.getElementById('or_scr').value = p.scr;
        document.getElementById('or_ward').value = p.ward;
        document.getElementById('or_cycle').setAttribute('data-manual', 'false');

        calculateBsaAndGfr();

        const regSelect = document.getElementById('regimenSelect');
        if (!regSelect.value && regSelect.options.length > 2) {
            regSelect.selectedIndex = 2;
            onRegimenChange();
        } else {
            checkPatientCycleHistory(true);
        }
    }

    // เก็บรายการตัวเลือก Regimen ทั้งหมดไว้ใช้ค้นหาและกรอง
    let allRegimenOptions = [];

    function initRegimenSearch() {
        const select = document.getElementById('regimenSelect');
        allRegimenOptions = Array.from(select.options)
            .filter(opt => opt.value !== '')
            .map(opt => ({
                value: opt.value,
                text: opt.text,
                code: opt.getAttribute('data-code') || '',
                drugs: opt.getAttribute('data-drugs') || '',
                search: (opt.getAttribute('data-search') || opt.text).toLowerCase(),
            }));

        const searchInput = document.getElementById('regimenSearchInput');
        if (searchInput) {
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const kw = this.value.trim().toLowerCase();
                    const matched = allRegimenOptions.filter(o => o.search.includes(kw));
                    if (matched.length > 0) {
                        selectRegimenFromSearch(matched[0].value);
                    }
                }
            });
        }

        document.addEventListener('click', function (e) {
            const box = document.getElementById('regimenSearchResults');
            const input = document.getElementById('regimenSearchInput');
            if (box && input && !box.contains(e.target) && e.target !== input) {
                box.style.display = 'none';
            }
        });
    }

    function filterRegimenOptions(keyword) {
        const kw = (keyword || '').trim().toLowerCase();
        const select = document.getElementById('regimenSelect');
        const resultsBox = document.getElementById('regimenSearchResults');
        const currentVal = select.value;

        const matched = kw === ''
            ? allRegimenOptions
            : allRegimenOptions.filter(o => o.search.includes(kw));

        // 1. กรองตัวเลือกใน <select> ให้เหลือเฉพาะที่ตรงกับคำค้นหา
        select.innerHTML = `<option value="">-- กรุณาเลือกสูตรยาเคมีบำบัด (พบ ${matched.length} สูตร) --</option>`;
        matched.forEach(o => {
            const opt = document.createElement('option');
            opt.value = o.value;
            opt.text = o.text;
            opt.setAttribute('data-code', o.code);
            opt.setAttribute('data-drugs', o.drugs);
            opt.setAttribute('data-search', o.search);
            if (o.value === currentVal) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });

        // 2. แสดงรายการผลลัพธ์แบบ Autocomplete ให้คลิกเลือกได้ทันทีเมื่อพิมพ์ค้นหา
        if (kw === '') {
            resultsBox.style.display = 'none';
            return;
        }

        if (matched.length === 0) {
            resultsBox.innerHTML = `
                <div class="list-group-item text-danger small py-2">
                    <i class="bi bi-exclamation-circle me-1"></i> ไม่พบสูตรยาที่ตรงกับ "${keyword}"
                </div>`;
            resultsBox.style.display = 'block';
            return;
        }

        resultsBox.innerHTML = matched.map(o => `
            <button type="button" class="list-group-item list-group-item-action py-2 d-flex justify-content-between align-items-center"
                onclick="selectRegimenFromSearch('${o.value}')">
                <div>
                    <div class="fw-bold text-primary">${o.code}</div>
                    <small class="text-secondary"><i class="bi bi-capsule me-1"></i>ตัวยาในสูตร: ${o.drugs}</small>
                </div>
                <span class="badge bg-primary-subtle text-primary">คลิกเลือก</span>
            </button>
        `).join('');
        resultsBox.style.display = 'block';
    }

    function selectRegimenFromSearch(rhId) {
        const select = document.getElementById('regimenSelect');
        const resultsBox = document.getElementById('regimenSearchResults');
        const searchInput = document.getElementById('regimenSearchInput');

        // คืนค่ารายการทั้งหมดใน select ก่อนแล้วเลือกค่าที่คลิก
        filterRegimenOptions('');
        select.value = rhId;

        const chosen = allRegimenOptions.find(o => o.value === String(rhId));
        if (chosen && searchInput) {
            searchInput.value = chosen.code;
        }
        if (resultsBox) {
            resultsBox.style.display = 'none';
        }

        onRegimenChange();
    }

    function clearRegimenSearch() {
        const searchInput = document.getElementById('regimenSearchInput');
        const resultsBox = document.getElementById('regimenSearchResults');
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
        filterRegimenOptions('');
        if (resultsBox) {
            resultsBox.style.display = 'none';
        }
    }

    function applyRegimenPreMeds(preMedsMap) {
        document.querySelectorAll('.pre-med-row').forEach(row => {
            const code = row.getAttribute('data-sm-code');
            const chk = document.getElementById(`premed_chk_${code}`);
            const doseEl = document.getElementById(`premed_dose_${code}`);
            const routeEl = document.getElementById(`premed_route_${code}`);
            const instEl = document.getElementById(`premed_inst_${code}`);

            const defDose = row.getAttribute('data-default-dose') || '';
            const defRoute = row.getAttribute('data-default-route') || '';
            const defInst = row.getAttribute('data-default-instruction') || '';

            const cfg = preMedsMap && preMedsMap[code] ? preMedsMap[code] : null;
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
            togglePreMedRow(code);
        });

        // ซิงค์ค่าตั้งต้นของ Post-medication (MESNA) จากสูตร Regimen หากมีการกำหนดไว้
        const mesnaCfg = preMedsMap && preMedsMap['POST_MESNA'] ? preMedsMap['POST_MESNA'] : null;
        const mesnaChk = document.getElementById('mesnaEnabledChk');
        const mesnaPctEl = document.getElementById('mesnaPercentInput');
        const mesnaNssEl = document.getElementById('mesnaNssVolSelect');
        if (mesnaCfg && mesnaCfg.is_default && mesnaChk) {
            mesnaManualToggle = true;
            mesnaChk.checked = true;
            if (mesnaPctEl && mesnaCfg.override_dose) {
                mesnaPctEl.value = parseInt(mesnaCfg.override_dose, 10) || 60;
            }
            if (mesnaCfg.override_route) {
                const parts = String(mesnaCfg.override_route).split('|');
                const mode = parts[0] === 'separate' ? 'separate' : 'admix';
                const nssVol = parts[1] || '100 mL';
                const radio = document.getElementById(mode === 'separate' ? 'mesnaHr0Separate' : 'mesnaHr0Admix');
                if (radio) radio.checked = true;
                if (mesnaNssEl) mesnaNssEl.value = nssVol;
            }
            const body = document.getElementById('mesnaCalculatorBody');
            if (body) body.classList.remove('opacity-50');
            document.querySelectorAll('.mesna-input').forEach(inp => { inp.disabled = false; });
        } else {
            mesnaManualToggle = false;
        }
    }

    function togglePreMedRow(code) {
        const row = document.getElementById(`premed_row_${code}`);
        const chk = document.getElementById(`premed_chk_${code}`);
        const badge = document.getElementById(`premed_badge_${code}`);
        const fields = document.getElementById(`premed_fields_${code}`);
        const isChecked = chk && chk.checked;

        if (row) {
            row.classList.toggle('bg-white', isChecked);
            row.classList.toggle('border-info', isChecked);
            row.classList.toggle('shadow-sm', isChecked);
            row.classList.toggle('bg-light', !isChecked);
            row.classList.toggle('opacity-75', !isChecked);
        }
        if (badge) {
            badge.className = isChecked
                ? 'badge bg-success pre-med-status-badge'
                : 'badge bg-secondary-subtle text-secondary pre-med-status-badge';
            badge.innerText = isChecked ? 'สั่งใช้ (ON)' : 'ไม่ใช้';
        }
        if (fields) {
            fields.querySelectorAll('input').forEach(inp => {
                inp.disabled = !isChecked;
            });
        }
        updatePreMedCounter();
    }

    function setPreMedDose(code, doseVal) {
        const chk = document.getElementById(`premed_chk_${code}`);
        const doseEl = document.getElementById(`premed_dose_${code}`);
        if (chk && !chk.checked) {
            chk.checked = true;
            togglePreMedRow(code);
        }
        if (doseEl) {
            doseEl.value = doseVal;
        }
    }

    function updatePreMedCounter() {
        const preMedTotal = document.querySelectorAll('.pre-med-checkbox:not(.pre-hyd-checkbox):not(.post-hyd-checkbox):not(.home-med-checkbox):checked').length;
        const preHydTotal = document.querySelectorAll('.pre-hyd-checkbox:checked').length;
        const postHydTotal = document.querySelectorAll('.post-hyd-checkbox:checked').length;
        const homeMedTotal = document.querySelectorAll('.home-med-checkbox:checked').length;
        const counterEl = document.getElementById('preMedSelectedCounter');
        const hydCounterEl = document.getElementById('preHydSelectedCounter');
        const postHydCounterEl = document.getElementById('postHydSelectedCounter');
        const homeMedCounterEl = document.getElementById('homeMedSelectedCounter');
        if (counterEl) counterEl.innerText = `เลือกใช้: ${preMedTotal} รายการ`;
        if (hydCounterEl) hydCounterEl.innerText = `เลือกใช้: ${preHydTotal} รายการ`;
        if (postHydCounterEl) postHydCounterEl.innerText = `เลือกใช้: ${postHydTotal} รายการ`;
        if (homeMedCounterEl) homeMedCounterEl.innerText = `เลือกใช้: ${homeMedTotal} รายการ`;
    }

    let mesnaManualToggle = false;

    function toggleMesnaSection() {
        mesnaManualToggle = true;
        const chk = document.getElementById('mesnaEnabledChk');
        const isEnabled = chk && chk.checked;
        const body = document.getElementById('mesnaCalculatorBody');

        if (body) {
            body.classList.toggle('opacity-50', !isEnabled);
        }
        document.querySelectorAll('.mesna-input').forEach(inp => {
            inp.disabled = !isEnabled;
        });
        if (isEnabled) {
            updateMesnaCalculation(true);
        }
    }

    function setMesnaPercent(pct) {
        const pctEl = document.getElementById('mesnaPercentInput');
        if (pctEl) pctEl.value = pct;
        const chk = document.getElementById('mesnaEnabledChk');
        if (chk && !chk.checked) {
            chk.checked = true;
            toggleMesnaSection();
        } else {
            updateMesnaCalculation(true);
        }
    }

    function updateMesnaCalculation(forceUpdateValues) {
        const chk = document.getElementById('mesnaEnabledChk');
        const alertEl = document.getElementById('mesnaAutoAlert');
        const refBadge = document.getElementById('mesnaRefDrugBadge');
        const totalEl = document.getElementById('mesnaTotalDisplay');
        const pctEl = document.getElementById('mesnaPercentInput');
        const nssVolEl = document.getElementById('mesnaNssVolSelect');
        const hr0ModeEl = document.querySelector('input[name="mesna_hr0_mode"]:checked');

        if (!chk) return;

        // ตรวจหายา Ifosfamide (IFOS) หรือ Cyclophosphamide (CYCLO) ที่เปิดสั่งใช้อยู่ในตาราง
        const activeItems = currentItems.filter(i => !i.is_off);
        const ifosItem = activeItems.find(i => (i.medcode || '').toUpperCase().startsWith('IFOS'));
        const cycloItem = activeItems.find(i => (i.medcode || '').toUpperCase().startsWith('CYCLO'));
        const refItem = ifosItem || cycloItem || null;

        const isIfos = !!ifosItem;
        const isHighDoseCyclo = !!(cycloItem && (cycloItem.base_dose >= 1000 || cycloItem.final_dose >= 1500));

        // เปิด MESNA อัตโนมัติเมื่อมี Ifosfamide หรือ High-dose Cyclophosphamide (หากแพทย์ยังไม่ได้ปิดเอง)
        if (!mesnaManualToggle) {
            const shouldAutoEnable = isIfos || isHighDoseCyclo;
            chk.checked = shouldAutoEnable;
            const body = document.getElementById('mesnaCalculatorBody');
            if (body) body.classList.toggle('opacity-50', !shouldAutoEnable);
            document.querySelectorAll('.mesna-input').forEach(inp => {
                inp.disabled = !shouldAutoEnable;
            });
        }

        if (alertEl) {
            if (isIfos) {
                alertEl.className = 'alert alert-danger py-2 px-3 small mb-3';
                alertEl.innerHTML = `<i class="bi bi-exclamation-octagon-fill me-1"></i> <strong>ตรวจพบ Ifosfamide (${ifosItem.final_dose.toFixed(2)} mg):</strong> ระบบเปิดใช้งาน <strong>MESNA Uroprotection</strong> และคำนวณขนาดแบ่งให้ 3 ครั้ง (0-3-6 ชม.) อัตโนมัติ`;
                alertEl.style.display = 'block';
            } else if (isHighDoseCyclo) {
                alertEl.className = 'alert alert-warning py-2 px-3 small mb-3';
                alertEl.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>ตรวจพบ High-dose Cyclophosphamide (${cycloItem.final_dose.toFixed(2)} mg):</strong> แนะนำให้ใช้ <strong>MESNA Uroprotection</strong> เพื่อป้องกัน Hemorrhagic Cystitis`;
                alertEl.style.display = 'block';
            } else if (cycloItem) {
                alertEl.className = 'alert alert-info py-2 px-3 small mb-3';
                alertEl.innerHTML = `<i class="bi bi-info-circle-fill me-1"></i> ตรวจพบ Cyclophosphamide (${cycloItem.final_dose.toFixed(2)} mg) &mdash; หากต้องการใช้ MESNA สามารถเปิดสวิตช์ด้านขวาบนเพื่อให้ระบบคำนวณอัตโนมัติได้`;
                alertEl.style.display = 'block';
            } else {
                alertEl.style.display = 'none';
            }
        }

        const baseChemoDose = refItem ? (parseFloat(refItem.final_dose) || 0) : 0;
        const pct = Math.min(200, Math.max(0, parseFloat(pctEl ? pctEl.value : 60) || 60));
        const totalMesna = Math.round(baseChemoDose * (pct / 100) * 100) / 100;
        const singleDose = Math.round((totalMesna / 3) * 100) / 100;
        const nssVol = nssVolEl ? nssVolEl.value : '100 mL';
        const hr0Mode = hr0ModeEl ? hr0ModeEl.value : 'admix';

        if (refBadge) {
            refBadge.innerText = refItem
                ? `อ้างอิง: ${refItem.dg_name} (${baseChemoDose.toFixed(2)} mg)`
                : (chk.checked ? 'MESNA (กำหนดขนาดเอง)' : 'ยังไม่ได้เปิดใช้ MESNA');
        }
        if (totalEl) {
            totalEl.innerText = `รวม ${totalMesna.toFixed(0)} mg`;
        }

        const refDrugLabel = refItem ? refItem.dg_name : 'ยาเคมีบำบัด';
        const hr0Route = hr0Mode === 'admix' ? 'IV (ผสมในขวดยา)' : `IV drip (${nssVol})`;
        const hr0Inst = hr0Mode === 'admix'
            ? `ชั่วโมงที่ 0 (ครั้งที่ 1/3): ผสม MESNA ลงในขวดยา ${refDrugLabel} เลย หยดพร้อมกัน (${pct}% รวม ${totalMesna.toFixed(0)} mg)`
            : `ชั่วโมงที่ 0 (ครั้งที่ 1/3): แยกผสม MESNA ใน NSS ${nssVol} ต่างหาก IV drip in 15-30 mins ก่อน/พร้อมเริ่ม ${refDrugLabel}`;

        const hr3Inst = `ชั่วโมงที่ +3 (ครั้งที่ 2/3): แยกผสม MESNA ใน NSS ${nssVol} IV drip in 15-30 mins หลังเริ่มยา 3 ชั่วโมง`;
        const hr6Inst = `ชั่วโมงที่ +6 (ครั้งที่ 3/3): แยกผสม MESNA ใน NSS ${nssVol} IV drip in 15-30 mins หลังเริ่มยา 6 ชั่วโมง`;

        if (forceUpdateValues || refItem) {
            const d1 = document.getElementById('mesna_dose_1');
            const d2 = document.getElementById('mesna_dose_2');
            const d3 = document.getElementById('mesna_dose_3');
            if (d1) d1.value = `${singleDose.toFixed(0)} mg`;
            if (d2) d2.value = `${singleDose.toFixed(0)} mg`;
            if (d3) d3.value = `${singleDose.toFixed(0)} mg`;

            const r1 = document.getElementById('mesna_route_1');
            const r2 = document.getElementById('mesna_route_2');
            const r3 = document.getElementById('mesna_route_3');
            if (r1) r1.value = hr0Route;
            if (r2) r2.value = `IV drip (${nssVol})`;
            if (r3) r3.value = `IV drip (${nssVol})`;

            const i1 = document.getElementById('mesna_inst_1');
            const i2 = document.getElementById('mesna_inst_2');
            const i3 = document.getElementById('mesna_inst_3');
            if (i1) i1.value = hr0Inst;
            if (i2) i2.value = hr3Inst;
            if (i3) i3.value = hr6Inst;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initRegimenSearch();
        document.querySelectorAll('.pre-med-row').forEach(row => {
            togglePreMedRow(row.getAttribute('data-sm-code'));
        });
        updateMesnaCalculation(false);
        document.querySelectorAll('.calc-trigger').forEach(el => {
            el.addEventListener('input', calculateBsaAndGfr);
            el.addEventListener('change', calculateBsaAndGfr);
        });
        calculateBsaAndGfr();
    });
</script>
@endsection
