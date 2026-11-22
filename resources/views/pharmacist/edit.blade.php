@extends('layouts.app')

@section('title', 'แก้ไขคำสั่งใช้ยาเคมีบำบัด (เภสัชกร) - ' . $order->or_id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold text-warning-emphasis mb-0">
            <i class="bi bi-pencil-square text-warning me-2"></i>แก้ไขคำสั่งใช้ยาเคมีบำบัด (สำหรับเภสัชกรหลังปรึกษาแพทย์): {{ $order->or_id }}
        </h4>
        <small class="text-secondary">
            แก้ไขเฉพาะรายการยาเคมีบำบัดหลัก (<code>order_d</code>) &mdash; รายการ Pre-med / Pre-hydration / Post-med / Post-hydration / Home-med จะยังคงเดิมไม่ถูกเปลี่ยนแปลง
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pharmacist.show', $order->or_id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> ยกเลิกและกลับหน้าคำสั่งยา
        </a>
    </div>
</div>

<!-- ข้อมูลผู้ป่วยและค่าทางคลินิก (อ้างอิงระหว่างแก้ไข) -->
<div class="card card-medical bg-white mb-4 border-start border-4 border-warning">
    <div class="card-body py-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-4 border-end">
                <span class="badge bg-dark mb-1">HN: {{ $order->or_hn }}</span>
                <h5 class="fw-bold mb-1">{{ $order->or_patname }}</h5>
                <div class="small text-secondary">
                    อายุ: {{ $order->or_age ?? '-' }} ปี | เพศ: {{ $order->or_sex === 'F' ? 'หญิง' : 'ชาย' }} |
                    หอผู้ป่วย: <strong>{{ optional($order->ward)->wd_name ?? $order->or_ward }}</strong>
                </div>
            </div>
            <div class="col-md-4 border-end">
                <div class="row text-center">
                    <div class="col-4">
                        <div class="small text-muted">น้ำหนัก / ส่วนสูง</div>
                        <div class="fw-bold">{{ number_format($order->or_wt, 1) }} kg / {{ number_format($order->or_ht, 0) }} cm</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">BSA</div>
                        <div class="fw-bold text-primary fs-5">{{ number_format($order->or_bsa, 2) }} m&sup2;</div>
                    </div>
                    <div class="col-4">
                        <div class="small text-muted">GFR (SCr {{ $order->or_scr ?? '-' }})</div>
                        <div class="fw-bold text-success fs-5">{{ $order->or_gfr ? number_format($order->or_gfr, 1) : '-' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <div>
                    <span class="badge bg-primary-subtle text-primary fs-6">
                        สูตร: {{ $order->or_reg_code ?? 'CUSTOM' }} (Cycle #{{ $order->or_cycle ?? 1 }})
                    </span>
                </div>
                <div class="small text-secondary mt-1">
                    แพทย์ผู้สั่งเดิม: <strong>{{ $order->or_doctor_name ?? '-' }}</strong>
                </div>
                <div class="small mt-1">
                    สถานะปัจจุบัน:
                    @if($order->or_confirm == 1)
                        <span class="badge bg-success">รับคำสั่งแล้ว (มีตารางแตกวัน)</span>
                    @else
                        <span class="badge bg-warning text-dark">รอเภสัชกร Confirm</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('pharmacist.update', $order->or_id) }}" id="pharmEditOrderForm">
    @csrf
    @method('PUT')

    <!-- หมายเหตุการปรึกษาแพทย์ (Clinical Intervention Note) -->
    <div class="card card-medical bg-white mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-8">
                    <label for="edit_reason" class="form-label fw-bold text-dark mb-1">
                        <i class="bi bi-chat-left-text-fill text-primary me-1"></i>
                        บันทึกการปรึกษาแพทย์ / เหตุผลการแก้ไขคำสั่งยา (Intervention Note):
                    </label>
                    <input type="text" name="edit_reason" id="edit_reason" class="form-control"
                           value="{{ old('edit_reason') }}"
                           placeholder="เช่น ปรึกษา นพ./พญ. ... เรียบร้อยแล้ว ปรับลดขนาดยา / เปลี่ยนสารน้ำ / ผสมรวมถุง...">
                    @if($order->or_adjust_reason)
                        <small class="text-secondary d-block mt-1">
                            <strong>หมายเหตุเดิม:</strong> {{ $order->or_adjust_reason }}
                        </small>
                    @endif
                </div>
                <div class="col-md-4">
                    @if($order->or_confirm == 1)
                        <div class="form-check form-switch bg-success-subtle border border-success-subtle rounded-3 p-2 ps-5">
                            <input class="form-check-input" type="checkbox" role="switch" id="resync_day_records"
                                   name="resync_day_records" value="1" checked>
                            <label class="form-check-label small fw-bold text-success-emphasis" for="resync_day_records">
                                แตกคำสั่งรายวัน (<code>onc_dayrecord</code>) ใหม่อัตโนมัติหลังบันทึก
                            </label>
                            <div class="text-secondary" style="font-size: 0.72rem;">
                                เพื่อให้สติกเกอร์และหน้าพยาบาลตรงกับคำสั่งที่แก้ทันที
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info py-2 px-3 mb-0 small">
                            <i class="bi bi-info-circle me-1"></i> คำสั่งนี้ยังไม่ได้กด Confirm หลังบันทึกการแก้ไขแล้ว สามารถกด Confirm แตกวันได้ในหน้าคำสั่งยา
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ตารางแก้ไขรายการยาเคมีบำบัด -->
    <div class="card card-medical bg-white">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-prescription2 text-danger me-1"></i> รายการยาเคมีบำบัดที่แก้ไขได้ (<code>order_d</code>)
                </h6>
                <small class="text-secondary">
                    สามารถเปลี่ยนตัวยา, ปรับขนาดยา / % ลดขนาด, เปลี่ยนสารน้ำ, เลือกผสมรวมถุงเดียวกัน, เพิ่มสารผสมในขวด, และปรับช่วงวันที่ได้ครบถ้วน
                </small>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="addNewChemoRow()">
                    <i class="bi bi-plus-circle-fill me-1"></i> เพิ่มตัวยาเคมีบำบัด
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" style="font-size: 0.85rem;">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 115px;">ลำดับ / ถุงรวม</th>
                        <th style="min-width: 210px;">ตัวยาเคมีบำบัด</th>
                        <th style="width: 115px;">ขนาดตั้งต้น<br>(Std Dose)</th>
                        <th style="width: 145px;" class="bg-warning-subtle">ปรับลด (%)</th>
                        <th style="min-width: 175px;">ขนาดยาสุทธิ (Final)<br>&amp; ดูดยา (mL)</th>
                        <th style="min-width: 220px;">สารน้ำเจือจาง &amp; สารผสมในขวด</th>
                        <th style="width: 120px;">ปริมาตรสารน้ำ<br>(mL)</th>
                        <th style="min-width: 155px;">วิถีทาง / เวลาให้ยา</th>
                        <th style="min-width: 165px;">วันที่เริ่ม &ndash; วันที่สิ้นสุด</th>
                    </tr>
                </thead>
                <tbody id="editMedItemsBody"></tbody>
            </table>
        </div>

        <div class="card-footer bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="small text-secondary">
                <i class="bi bi-shield-check text-success me-1"></i>
                ระบบจะคำนวณ <strong>ปริมาตรยาที่ดูด (Working mL)</strong> และ <strong>ปริมาตรรวมสุทธิ (Total Vol)</strong> ให้อัตโนมัติเมื่อบันทึก
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('pharmacist.show', $order->or_id) }}" class="btn btn-outline-secondary">
                    ยกเลิก
                </a>
                <button type="submit" id="saveEditBtn" class="btn btn-success btn-lg fw-bold shadow-sm">
                    <i class="bi bi-check-circle-fill me-1"></i> บันทึกการแก้ไขคำสั่งยาเคมีบำบัด
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js_after')
<script>
    const MEDS_LIST      = @json($meds->values());
    const UNITS_LIST     = @json($units->values());
    const SOLUTIONS_LIST = @json($solutions->values());
    const ROUTES_LIST    = @json($routes->values());
    const TIMINGS_LIST   = @json($timings->values());
    const ADDITIVES_LIST = @json($additives->values());
    const DEFAULT_DATE   = @json(\Carbon\Carbon::parse($order->or_date)->format('Y-m-d'));

    const BAG_GROUP_META = {
        'BAG-A': { label: 'ถุงรวม A', color: '#7c3aed', bg: '#f5f3ff' },
        'BAG-B': { label: 'ถุงรวม B', color: '#0d9488', bg: '#f0fdfa' },
        'BAG-C': { label: 'ถุงรวม C', color: '#ea580c', bg: '#fff7ed' },
        'BAG-D': { label: 'ถุงรวม D', color: '#db2777', bg: '#fdf2f8' },
    };

    let editItems = @json($initialItems);

    function escapeAttr(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function syncEditStateFromDom() {
        editItems.forEach((item, idx) => {
            const ordEl  = document.getElementById(`orderno_${idx}`);
            const bagEl  = document.getElementById(`bag_group_${idx}`);
            const medEl  = document.getElementById(`medcode_${idx}`);
            const stdEl  = document.getElementById(`std_dose_${idx}`);
            const redEl  = document.getElementById(`reduce_pct_${idx}`);
            const doseEl = document.getElementById(`meddose_${idx}`);
            const unitEl = document.getElementById(`medunit_${idx}`);
            const solEl  = document.getElementById(`soln_${idx}`);
            const volEl  = document.getElementById(`solvol_${idx}`);
            const rtEl   = document.getElementById(`medroute_${idx}`);
            const tmEl   = document.getElementById(`medtime_${idx}`);
            const stEl   = document.getElementById(`or_start_${idx}`);
            const spEl   = document.getElementById(`or_stop_${idx}`);

            if (ordEl)  item.orderno    = parseInt(ordEl.value) || (idx + 1);
            if (bagEl)  item.bag_group  = bagEl.value || '';
            if (medEl)  item.medcode    = medEl.value;
            if (stdEl)  item.std_dose   = parseFloat(stdEl.value) || 0;
            if (redEl)  item.reduce_pct = parseFloat(redEl.value) || 0;
            if (doseEl) item.meddose    = parseFloat(doseEl.value) || 0;
            if (unitEl) item.medunit    = unitEl.value;
            if (solEl)  item.soln       = solEl.value;
            if (volEl)  item.solvol     = parseFloat(volEl.value) || 0;
            if (rtEl)   item.medroute   = rtEl.value;
            if (tmEl)   item.medtime    = tmEl.value;
            if (stEl)   item.or_start   = stEl.value;
            if (spEl)   item.or_stop    = spEl.value;
            if (!Array.isArray(item.additives)) item.additives = [];
        });
    }

    function addNewChemoRow() {
        syncEditStateFromDom();
        const defMed = MEDS_LIST.length > 0 ? MEDS_LIST[0] : { dg_code: '5FU', dg_name: 'Fluorouracil', dg_conc: 50 };
        editItems.push({
            orderno: editItems.length + 1,
            bag_group: '',
            additives: [],
            medcode: defMed.dg_code,
            dg_name: defMed.dg_name,
            dg_conc: parseFloat(defMed.dg_conc) || 1,
            std_dose: 100,
            reduce_pct: 0,
            meddose: 100,
            medunit: '01',
            soln: 'NSS',
            solvol: 250,
            medroute: '01',
            medtime: '03',
            or_start: DEFAULT_DATE,
            or_stop: DEFAULT_DATE,
        });
        renderEditTable();
    }

    function removeChemoRow(idx) {
        if (editItems.length <= 1) {
            alert('ต้องมีรายการยาเคมีบำบัดอย่างน้อย 1 รายการ');
            return;
        }
        syncEditStateFromDom();
        editItems.splice(idx, 1);
        renderEditTable();
    }

    function onMedChange(idx, medcode) {
        syncEditStateFromDom();
        const found = MEDS_LIST.find(m => m.dg_code === medcode);
        if (found && editItems[idx]) {
            editItems[idx].medcode = found.dg_code;
            editItems[idx].dg_name = found.dg_name;
            editItems[idx].dg_conc = parseFloat(found.dg_conc) || 1;
        }
        renderEditTable();
    }

    function onBagGroupChange(idx, group) {
        syncEditStateFromDom();
        if (!editItems[idx]) return;
        editItems[idx].bag_group = group || '';
        if (group) {
            const peer = editItems.find((it, i) => i !== idx && it.bag_group === group);
            if (peer) {
                editItems[idx].soln = peer.soln;
                editItems[idx].solvol = peer.solvol;
                editItems[idx].medroute = peer.medroute;
                editItems[idx].medtime = peer.medtime;
            }
        }
        renderEditTable();
    }

    function onBagSharedFieldChange(idx, field, val) {
        if (!editItems[idx]) return;
        editItems[idx][field] = (field === 'solvol') ? (parseFloat(val) || 0) : val;
        const group = editItems[idx].bag_group;
        if (group) {
            editItems.forEach((it, i) => {
                if (i !== idx && it.bag_group === group) {
                    it[field] = editItems[idx][field];
                    const el = document.getElementById(`${field}_${i}`);
                    if (el) el.value = val;
                }
            });
        }
        updateRowCalculations(idx);
    }

    function onStdOrReduceChange(idx) {
        const stdEl  = document.getElementById(`std_dose_${idx}`);
        const redEl  = document.getElementById(`reduce_pct_${idx}`);
        const doseEl = document.getElementById(`meddose_${idx}`);
        if (!editItems[idx]) return;

        const std = parseFloat(stdEl?.value) || 0;
        const red = Math.max(0, Math.min(90, parseFloat(redEl?.value) || 0));
        const finalDose = Math.round(std * (1 - red / 100) * 100) / 100;

        editItems[idx].std_dose = std;
        editItems[idx].reduce_pct = red;
        editItems[idx].meddose = finalDose;

        if (doseEl) doseEl.value = finalDose.toFixed(2);
        updateRowCalculations(idx);
    }

    function setQuickReduce(idx, pct) {
        const redEl = document.getElementById(`reduce_pct_${idx}`);
        if (redEl) redEl.value = pct;
        onStdOrReduceChange(idx);
    }

    function onFinalDoseManual(idx, val) {
        if (!editItems[idx]) return;
        editItems[idx].meddose = parseFloat(val) || 0;
        updateRowCalculations(idx);
    }

    function updateRowCalculations(idx) {
        const item = editItems[idx];
        if (!item) return;
        const conc = item.dg_conc > 0 ? item.dg_conc : 1;
        const workVol = item.meddose / conc;
        const workEl = document.getElementById(`work_vol_${idx}`);
        if (workEl) workEl.innerText = `ดูดยา: ${workVol.toFixed(2)} mL`;

        const addVol = (item.additives || []).reduce((acc, a) => acc + (parseFloat(a.vol_ml) || 0), 0);
        const totalVol = (parseFloat(item.solvol) || 0) + workVol + addVol;
        const totEl = document.getElementById(`total_vol_${idx}`);
        if (totEl) totEl.innerText = `รวม ~${totalVol.toFixed(1)} mL`;

        updateDaysLabel(idx);
    }

    function updateDaysLabel(idx) {
        const stEl = document.getElementById(`or_start_${idx}`);
        const spEl = document.getElementById(`or_stop_${idx}`);
        const lbl  = document.getElementById(`days_lbl_${idx}`);
        if (!stEl || !spEl || !lbl) return;
        if (stEl.value && spEl.value) {
            const d1 = new Date(stEl.value);
            const d2 = new Date(spEl.value);
            const diff = Math.floor((d2 - d1) / (1000 * 60 * 60 * 24)) + 1;
            lbl.innerText = diff >= 1 ? `รวม ${diff} วัน` : 'วันที่สิ้นสุดน้อยกว่าเริ่ม!';
            lbl.className = diff >= 1 ? 'badge bg-info-subtle text-dark border mt-1' : 'badge bg-danger text-white mt-1';
        }
    }

    function addAdditive(idx) {
        syncEditStateFromDom();
        if (!editItems[idx]) return;
        if (!Array.isArray(editItems[idx].additives)) editItems[idx].additives = [];
        const firstAd = ADDITIVES_LIST.length > 0 ? ADDITIVES_LIST[0] : { ad_code: 'CUSTOM', ad_name: '', default_dose: '', default_unit: '', default_vol_ml: 0 };
        editItems[idx].additives.push({
            code: firstAd.ad_code,
            name: firstAd.ad_name,
            dose: firstAd.default_dose || '',
            unit: firstAd.default_unit || '',
            vol_ml: parseFloat(firstAd.default_vol_ml) || 0,
        });
        renderEditTable();
    }

    function onAdditivePresetChange(idx, adIdx, code) {
        if (!editItems[idx]?.additives?.[adIdx]) return;
        if (code === 'CUSTOM') {
            editItems[idx].additives[adIdx] = { code: 'CUSTOM', name: '', dose: '', unit: 'mEq', vol_ml: 0 };
        } else {
            const found = ADDITIVES_LIST.find(a => a.ad_code === code);
            if (found) {
                editItems[idx].additives[adIdx] = {
                    code: found.ad_code,
                    name: found.ad_name,
                    dose: found.default_dose || '',
                    unit: found.default_unit || '',
                    vol_ml: parseFloat(found.default_vol_ml) || 0,
                };
            }
        }
        renderEditTable();
    }

    function onAdditiveFieldChange(idx, adIdx, field, val) {
        if (!editItems[idx]?.additives?.[adIdx]) return;
        editItems[idx].additives[adIdx][field] = (field === 'vol_ml') ? (parseFloat(val) || 0) : val;
        const hiddenEl = document.getElementById(`additives_json_${idx}`);
        if (hiddenEl) hiddenEl.value = JSON.stringify(editItems[idx].additives);
        updateRowCalculations(idx);
    }

    function removeAdditive(idx, adIdx) {
        syncEditStateFromDom();
        if (!editItems[idx]?.additives) return;
        editItems[idx].additives.splice(adIdx, 1);
        renderEditTable();
    }

    function renderEditTable() {
        const tbody = document.getElementById('editMedItemsBody');
        const bagCounts = {};
        editItems.forEach(it => {
            if (it.bag_group) bagCounts[it.bag_group] = (bagCounts[it.bag_group] || 0) + 1;
        });

        let html = '';
        editItems.forEach((item, idx) => {
            const bagMeta = (item.bag_group && BAG_GROUP_META[item.bag_group]) ? BAG_GROUP_META[item.bag_group] : null;
            const rowStyle = bagMeta ? `border-left: 5px solid ${bagMeta.color}; background-color: ${bagMeta.bg};` : '';

            const medOpts = MEDS_LIST.map(m =>
                `<option value="${m.dg_code}" ${m.dg_code === item.medcode ? 'selected' : ''}>[${m.dg_code}] ${m.dg_name} (${m.dg_conc} mg/mL)</option>`
            ).join('');

            const unitOpts = UNITS_LIST.map(u =>
                `<option value="${u.un_code}" ${u.un_code === item.medunit ? 'selected' : ''}>${u.un_unit}</option>`
            ).join('');

            const solOpts = SOLUTIONS_LIST.map(s =>
                `<option value="${s.so_code}" ${s.so_code === item.soln ? 'selected' : ''}>${s.so_code} - ${s.so_name}</option>`
            ).join('');

            const routeOpts = ROUTES_LIST.map(r =>
                `<option value="${r.rt_code}" ${r.rt_code === item.medroute ? 'selected' : ''}>${r.rt_name}</option>`
            ).join('');

            const timeOpts = TIMINGS_LIST.map(t =>
                `<option value="${t.tm_code}" ${t.tm_code === item.medtime ? 'selected' : ''}>${t.tm_time}</option>`
            ).join('');

            const bagOpts = [
                { code: '', label: 'แยกขวดเดี่ยว' },
                { code: 'BAG-A', label: 'ผสมรวมถุง A' },
                { code: 'BAG-B', label: 'ผสมรวมถุง B' },
                { code: 'BAG-C', label: 'ผสมรวมถุง C' },
                { code: 'BAG-D', label: 'ผสมรวมถุง D' },
            ].map(bg => `<option value="${bg.code}" ${bg.code === (item.bag_group || '') ? 'selected' : ''}>${bg.label}</option>`).join('');

            const additivesArr = Array.isArray(item.additives) ? item.additives : [];
            const additivesHtml = additivesArr.map((ad, adIdx) => {
                const isCustom = ad.code === 'CUSTOM';
                const presetOpts = ADDITIVES_LIST.map(a =>
                    `<option value="${a.ad_code}" ${a.ad_code === ad.code ? 'selected' : ''}>${a.ad_name}</option>`
                ).join('') + `<option value="CUSTOM" ${isCustom ? 'selected' : ''}>อื่นๆ (พิมพ์เอง)...</option>`;

                return `
                    <div class="border rounded-2 p-1 bg-white mt-1 shadow-sm" style="font-size: 0.72rem;">
                        <div class="d-flex gap-1 align-items-center mb-1">
                            <span class="badge bg-info text-dark px-1">+สาร</span>
                            <select class="form-select form-select-sm py-0 px-1" style="font-size: 0.72rem;"
                                onchange="onAdditivePresetChange(${idx}, ${adIdx}, this.value)">
                                ${presetOpts}
                            </select>
                            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1" style="font-size: 0.68rem;"
                                onclick="removeAdditive(${idx}, ${adIdx})">&times;</button>
                        </div>
                        ${isCustom ? `
                            <input type="text" class="form-control form-control-sm py-0 px-1 mb-1" style="font-size: 0.72rem;"
                                placeholder="ชื่อสารผสม..." value="${escapeAttr(ad.name)}"
                                oninput="onAdditiveFieldChange(${idx}, ${adIdx}, 'name', this.value)">
                        ` : ''}
                        <div class="d-flex gap-1">
                            <input type="text" class="form-control form-control-sm py-0 px-1 text-end" style="font-size: 0.72rem; width: 42%;"
                                placeholder="ขนาด" value="${escapeAttr(ad.dose)}"
                                oninput="onAdditiveFieldChange(${idx}, ${adIdx}, 'dose', this.value)">
                            <input type="text" class="form-control form-control-sm py-0 px-1" style="font-size: 0.72rem; width: 28%;"
                                placeholder="หน่วย" value="${escapeAttr(ad.unit)}"
                                oninput="onAdditiveFieldChange(${idx}, ${adIdx}, 'unit', this.value)">
                            <input type="number" step="0.5" min="0" class="form-control form-control-sm py-0 px-1 text-end" style="font-size: 0.7rem; width: 30%;"
                                placeholder="mL" title="ปริมาตรสารผสม (mL)" value="${ad.vol_ml || 0}"
                                oninput="onAdditiveFieldChange(${idx}, ${adIdx}, 'vol_ml', this.value)">
                        </div>
                    </div>
                `;
            }).join('');

            html += `
                <tr style="${rowStyle}">
                    <td class="text-center">
                        <div class="d-flex flex-column gap-1">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text px-1">#</span>
                                <input type="number" min="1" max="99" name="items[${idx}][orderno]" id="orderno_${idx}"
                                    class="form-control text-center fw-bold" value="${item.orderno}" required>
                            </div>
                            <select name="items[${idx}][bag_group]" id="bag_group_${idx}"
                                class="form-select form-select-sm py-0 px-1 fw-semibold"
                                style="font-size: 0.72rem; ${bagMeta ? 'border-color:' + bagMeta.color + '; color:' + bagMeta.color + ';' : ''}"
                                onchange="onBagGroupChange(${idx}, this.value)">
                                ${bagOpts}
                            </select>
                            <button type="button" class="btn btn-outline-danger btn-sm py-0" style="font-size: 0.7rem;"
                                onclick="removeChemoRow(${idx})">
                                <i class="bi bi-trash"></i> ลบแถว
                            </button>
                        </div>
                    </td>
                    <td>
                        <select name="items[${idx}][medcode]" id="medcode_${idx}"
                            class="form-select form-select-sm fw-bold text-primary mb-1"
                            onchange="onMedChange(${idx}, this.value)" required>
                            ${medOpts}
                        </select>
                        <small class="text-secondary">Conc: <strong>${item.dg_conc} mg/mL</strong></small>
                        ${bagMeta ? `<div class="mt-1"><span class="badge text-white" style="background-color:${bagMeta.color}; font-size:0.7rem;"><i class="bi bi-layers-fill me-1"></i>${bagMeta.label} (${bagCounts[item.bag_group]} ตัว)</span></div>` : ''}
                    </td>
                    <td>
                        <input type="number" step="0.01" min="0" name="items[${idx}][std_dose]" id="std_dose_${idx}"
                            class="form-control form-control-sm text-end fw-semibold"
                            value="${Number(item.std_dose || 0).toFixed(2)}"
                            oninput="onStdOrReduceChange(${idx})">
                        <small class="text-muted d-block text-end mt-1">ขนาดเดิม</small>
                    </td>
                    <td class="bg-warning-subtle">
                        <div class="input-group input-group-sm mb-1">
                            <span class="input-group-text px-1 bg-white">ลด</span>
                            <input type="number" step="1" min="0" max="90" name="items[${idx}][reduce_pct]" id="reduce_pct_${idx}"
                                class="form-control text-center fw-bold text-danger px-1"
                                value="${item.reduce_pct || 0}"
                                oninput="onStdOrReduceChange(${idx})">
                            <span class="input-group-text px-1 bg-white">%</span>
                        </div>
                        <div class="d-flex flex-wrap gap-1 justify-content-center">
                            ${[0, 10, 20, 25, 30, 50].map(p =>
                                `<button type="button" class="btn btn-outline-danger py-0 px-1" style="font-size:0.65rem;" onclick="setQuickReduce(${idx}, ${p})">${p === 0 ? '0%' : '-' + p + '%'}</button>`
                            ).join('')}
                        </div>
                    </td>
                    <td>
                        <div class="input-group input-group-sm mb-1">
                            <input type="number" step="0.01" min="0.01" name="items[${idx}][meddose]" id="meddose_${idx}"
                                class="form-control text-end fw-bold text-danger fs-6"
                                value="${Number(item.meddose || 0).toFixed(2)}"
                                oninput="onFinalDoseManual(${idx}, this.value)" required>
                            <select name="items[${idx}][medunit]" id="medunit_${idx}" class="form-select form-select-sm" style="max-width: 72px;">
                                ${unitOpts}
                            </select>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="fw-semibold text-primary" id="work_vol_${idx}">ดูดยา: 0.00 mL</span>
                            <span class="text-secondary" id="total_vol_${idx}">รวม 0 mL</span>
                        </div>
                    </td>
                    <td>
                        <select name="items[${idx}][soln]" id="soln_${idx}" class="form-select form-select-sm"
                            onchange="onBagSharedFieldChange(${idx}, 'soln', this.value)">
                            ${solOpts}
                        </select>
                        <input type="hidden" name="items[${idx}][additives_json]" id="additives_json_${idx}"
                            value="${escapeAttr(JSON.stringify(additivesArr))}">
                        ${additivesHtml}
                        <button type="button" class="btn btn-outline-info btn-sm py-0 px-2 mt-1 w-100 fw-semibold" style="font-size: 0.7rem;"
                            onclick="addAdditive(${idx})">
                            <i class="bi bi-plus-circle me-1"></i>ผสมสารอื่นในขวดนี้
                        </button>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="number" step="1" min="0" name="items[${idx}][solvol]" id="solvol_${idx}"
                                class="form-control text-end fw-bold"
                                value="${item.solvol || 0}"
                                oninput="onBagSharedFieldChange(${idx}, 'solvol', this.value)" required>
                            <span class="input-group-text px-1">mL</span>
                        </div>
                    </td>
                    <td>
                        <select name="items[${idx}][medroute]" id="medroute_${idx}" class="form-select form-select-sm mb-1"
                            onchange="onBagSharedFieldChange(${idx}, 'medroute', this.value)">
                            ${routeOpts}
                        </select>
                        <select name="items[${idx}][medtime]" id="medtime_${idx}" class="form-select form-select-sm"
                            onchange="onBagSharedFieldChange(${idx}, 'medtime', this.value)">
                            ${timeOpts}
                        </select>
                    </td>
                    <td>
                        <div class="mb-1">
                            <label class="small text-muted d-block" style="font-size: 0.7rem;">เริ่มให้ยา:</label>
                            <input type="date" name="items[${idx}][or_start]" id="or_start_${idx}"
                                class="form-control form-control-sm" value="${item.or_start}"
                                onchange="updateDaysLabel(${idx})" required>
                        </div>
                        <div>
                            <label class="small text-muted d-block" style="font-size: 0.7rem;">สิ้นสุด:</label>
                            <input type="date" name="items[${idx}][or_stop]" id="or_stop_${idx}"
                                class="form-control form-control-sm" value="${item.or_stop}"
                                onchange="updateDaysLabel(${idx})" required>
                        </div>
                        <div class="text-center">
                            <span id="days_lbl_${idx}" class="badge bg-info-subtle text-dark border mt-1">-</span>
                        </div>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        editItems.forEach((_, idx) => updateRowCalculations(idx));
    }

    document.addEventListener('DOMContentLoaded', renderEditTable);
</script>
@endsection
