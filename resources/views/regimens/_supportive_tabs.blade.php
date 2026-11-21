@php
    $p = $prefix ?? 'create';
@endphp

<div class="border rounded-3 overflow-hidden mb-3">
    <div class="bg-light px-3 py-2 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-bold text-dark small">
            <i class="bi bi-shield-plus text-primary me-1"></i>
            กำหนดยาสนับสนุนเริ่มต้นของสูตรนี้ (Supportive Care Defaults &mdash; ซิงค์ไปยังหน้าสั่งยาแพทย์อัตโนมัติ)
        </span>
        <span class="small text-secondary">ติ๊กเปิด/ปิดและกำหนดขนาดยาเริ่มต้นแยกตามแต่ละหมวด</span>
    </div>

    <div class="p-3 bg-white">
        <ul class="nav nav-pills nav-fill gap-1 mb-3 small" id="{{ $p }}SupportiveTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active py-1 px-2 fw-semibold border" data-bs-toggle="pill" data-bs-target="#{{ $p }}_tab_premed" type="button">
                    <i class="bi bi-shield-plus me-1"></i>1. Pre-med <span class="badge bg-info text-dark ms-1" id="{{ $p }}_cnt_premed">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-1 px-2 fw-semibold border" data-bs-toggle="pill" data-bs-target="#{{ $p }}_tab_prehyd" type="button">
                    <i class="bi bi-droplet-fill me-1"></i>2. Pre-hyd <span class="badge bg-primary ms-1" id="{{ $p }}_cnt_prehyd">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-1 px-2 fw-semibold border" data-bs-toggle="pill" data-bs-target="#{{ $p }}_tab_postmed" type="button">
                    <i class="bi bi-clock-history me-1"></i>3. Post-med (MESNA) <span class="badge bg-secondary ms-1" id="{{ $p }}_cnt_postmed">OFF</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-1 px-2 fw-semibold border" data-bs-toggle="pill" data-bs-target="#{{ $p }}_tab_posthyd" type="button">
                    <i class="bi bi-droplet-half me-1"></i>4. Post-hyd <span class="badge bg-success ms-1" id="{{ $p }}_cnt_posthyd">0</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-1 px-2 fw-semibold border" data-bs-toggle="pill" data-bs-target="#{{ $p }}_tab_homemed" type="button">
                    <i class="bi bi-house-heart-fill me-1"></i>5. Home-med <span class="badge bg-warning text-dark ms-1" id="{{ $p }}_cnt_homemed">0</span>
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- TAB 1: Pre-medication (Antiemetics + Anti-Hypersensitivity) -->
            <div class="tab-pane fade show active" id="{{ $p }}_tab_premed">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="fw-bold small text-primary mb-2"><i class="bi bi-capsule me-1"></i>Antiemetics (ป้องกันคลื่นไส้-อาเจียน)</div>
                        <div class="d-flex flex-column gap-2">
                            @foreach($supportiveByCategory['pre_med_antiemetic'] as $sm)
                                <div class="border rounded-2 p-2 bg-light reg-sup-row" data-prefix="{{ $p }}" data-group="premed" data-code="{{ $sm->sm_code }}" data-def-dose="{{ $sm->default_dose }}" data-def-route="{{ $sm->default_route }}" data-def-inst="{{ $sm->default_instruction }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input reg-sup-chk" type="checkbox" role="switch"
                                                   id="{{ $p }}_chk_{{ $sm->sm_code }}"
                                                   name="supportive[{{ $sm->sm_code }}][enabled]" value="1"
                                                   onchange="onRegSupToggle('{{ $p }}', '{{ $sm->sm_code }}')">
                                            <label class="form-check-label fw-bold small" for="{{ $p }}_chk_{{ $sm->sm_code }}">{{ $sm->sm_name }}</label>
                                        </div>
                                    </div>
                                    <div class="row g-1">
                                        <div class="col-5">
                                            <input type="text" class="form-control form-control-sm reg-sup-input" id="{{ $p }}_dose_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][dose]" value="{{ $sm->default_dose }}" placeholder="ขนาดยา" disabled>
                                        </div>
                                        <div class="col-3">
                                            <input type="text" class="form-control form-control-sm text-center reg-sup-input" id="{{ $p }}_route_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][route]" value="{{ $sm->default_route }}" placeholder="Route" disabled>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" class="form-control form-control-sm reg-sup-input" id="{{ $p }}_inst_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][instruction]" value="{{ $sm->default_instruction }}" placeholder="วิธีใช้" disabled>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="fw-bold small text-warning-emphasis mb-2"><i class="bi bi-exclamation-shield-fill text-warning me-1"></i>Prophylaxis for Hypersensitivity / Infusion Reaction</div>
                        <div class="d-flex flex-column gap-2">
                            @foreach($supportiveByCategory['pre_med_hypersensitivity'] as $sm)
                                <div class="border rounded-2 p-2 bg-light reg-sup-row" data-prefix="{{ $p }}" data-group="premed" data-code="{{ $sm->sm_code }}" data-def-dose="{{ $sm->default_dose }}" data-def-route="{{ $sm->default_route }}" data-def-inst="{{ $sm->default_instruction }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input reg-sup-chk" type="checkbox" role="switch"
                                                   id="{{ $p }}_chk_{{ $sm->sm_code }}"
                                                   name="supportive[{{ $sm->sm_code }}][enabled]" value="1"
                                                   onchange="onRegSupToggle('{{ $p }}', '{{ $sm->sm_code }}')">
                                            <label class="form-check-label fw-bold small" for="{{ $p }}_chk_{{ $sm->sm_code }}">{{ $sm->sm_name }}</label>
                                        </div>
                                    </div>
                                    <div class="row g-1">
                                        <div class="col-5">
                                            <input type="text" class="form-control form-control-sm reg-sup-input" id="{{ $p }}_dose_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][dose]" value="{{ $sm->default_dose }}" placeholder="ขนาดยา" disabled>
                                        </div>
                                        <div class="col-3">
                                            <input type="text" class="form-control form-control-sm text-center reg-sup-input" id="{{ $p }}_route_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][route]" value="{{ $sm->default_route }}" placeholder="Route" disabled>
                                        </div>
                                        <div class="col-4">
                                            <input type="text" class="form-control form-control-sm reg-sup-input" id="{{ $p }}_inst_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][instruction]" value="{{ $sm->default_instruction }}" placeholder="วิธีใช้" disabled>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: Pre-hydration -->
            <div class="tab-pane fade" id="{{ $p }}_tab_prehyd">
                <div class="row g-2">
                    @foreach($supportiveByCategory['pre_hydration'] as $sm)
                        <div class="col-md-6">
                            <div class="border rounded-2 p-2 bg-light reg-sup-row h-100" data-prefix="{{ $p }}" data-group="prehyd" data-code="{{ $sm->sm_code }}" data-def-dose="{{ $sm->default_dose }}" data-def-route="{{ $sm->default_route }}" data-def-inst="{{ $sm->default_instruction }}">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input reg-sup-chk" type="checkbox" role="switch"
                                           id="{{ $p }}_chk_{{ $sm->sm_code }}"
                                           name="supportive[{{ $sm->sm_code }}][enabled]" value="1"
                                           onchange="onRegSupToggle('{{ $p }}', '{{ $sm->sm_code }}')">
                                    <label class="form-check-label fw-bold small" for="{{ $p }}_chk_{{ $sm->sm_code }}">{{ $sm->sm_name }}</label>
                                </div>
                                <div class="row g-1">
                                    <div class="col-4">
                                        <input type="text" class="form-control form-control-sm reg-sup-input" id="{{ $p }}_dose_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][dose]" value="{{ $sm->default_dose }}" disabled>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" class="form-control form-control-sm text-center reg-sup-input" id="{{ $p }}_route_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][route]" value="{{ $sm->default_route }}" disabled>
                                    </div>
                                    <div class="col-5">
                                        <input type="text" class="form-control form-control-sm reg-sup-input" id="{{ $p }}_inst_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][instruction]" value="{{ $sm->default_instruction }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- TAB 3: Post-medication (MESNA Protocol Default) -->
            <div class="tab-pane fade" id="{{ $p }}_tab_postmed">
                <div class="border rounded-3 p-3" style="background-color: #faf5ff; border-color: #d8b4fe !important;">
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="{{ $p }}_mesna_enabled" name="mesna_enabled" value="1" onchange="onRegMesnaToggle('{{ $p }}')">
                            <label class="form-check-label fw-bold" style="color: #5b21b6;" for="{{ $p }}_mesna_enabled">
                                เปิดใช้ MESNA Uroprotection เป็นค่าเริ่มต้นสำหรับสูตรนี้ (แบ่งให้ 3 ครั้ง ที่ 0-3-6 ชั่วโมง)
                            </label>
                        </div>
                    </div>
                    <div class="row g-3" id="{{ $p }}_mesna_fields">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold">สัดส่วน MESNA (% ของขนาดยาหลัก):</label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="20" max="200" step="5" class="form-control text-center fw-bold text-danger" id="{{ $p }}_mesna_pct" name="mesna_pct" value="60" disabled>
                                <span class="input-group-text">%</span>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">วิธีผสม MESNA ที่ชั่วโมงที่ 0 (ครั้งที่ 1/3):</label>
                            <select class="form-select form-select-sm" id="{{ $p }}_mesna_hr0_mode" name="mesna_hr0_mode" disabled>
                                <option value="admix">ผสมลงไปกับขวดยาเคมีบำบัดเลย (Admixed in Chemo Bag)</option>
                                <option value="separate">แยกไปผสมกับน้ำเกลือต่างหาก (Separate NSS Bag)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">ปริมาตรน้ำเกลือ (สำหรับโดสที่แยกผสม):</label>
                            <select class="form-select form-select-sm" id="{{ $p }}_mesna_nss_vol" name="mesna_nss_vol" disabled>
                                <option value="50 mL">NSS 50 mL</option>
                                <option value="100 mL" selected>NSS 100 mL</option>
                                <option value="250 mL">NSS 250 mL</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: Post-hydration -->
            <div class="tab-pane fade" id="{{ $p }}_tab_posthyd">
                <div class="row g-2">
                    @foreach($supportiveByCategory['post_hydration'] as $sm)
                        <div class="col-md-6">
                            <div class="border rounded-2 p-2 bg-light reg-sup-row h-100" data-prefix="{{ $p }}" data-group="posthyd" data-code="{{ $sm->sm_code }}" data-def-dose="{{ $sm->default_dose }}" data-def-route="{{ $sm->default_route }}" data-def-inst="{{ $sm->default_instruction }}">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input reg-sup-chk" type="checkbox" role="switch"
                                           id="{{ $p }}_chk_{{ $sm->sm_code }}"
                                           name="supportive[{{ $sm->sm_code }}][enabled]" value="1"
                                           onchange="onRegSupToggle('{{ $p }}', '{{ $sm->sm_code }}')">
                                    <label class="form-check-label fw-bold small" for="{{ $p }}_chk_{{ $sm->sm_code }}">{{ $sm->sm_name }}</label>
                                </div>
                                <div class="row g-1">
                                    <div class="col-4">
                                        <input type="text" class="form-control form-control-sm reg-sup-input" id="{{ $p }}_dose_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][dose]" value="{{ $sm->default_dose }}" disabled>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" class="form-control form-control-sm text-center reg-sup-input" id="{{ $p }}_route_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][route]" value="{{ $sm->default_route }}" disabled>
                                    </div>
                                    <div class="col-5">
                                        <input type="text" class="form-control form-control-sm reg-sup-input" id="{{ $p }}_inst_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][instruction]" value="{{ $sm->default_instruction }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- TAB 5: Home-medication -->
            <div class="tab-pane fade" id="{{ $p }}_tab_homemed">
                <div class="row g-2">
                    @foreach($supportiveByCategory['home_med'] as $sm)
                        <div class="col-md-6">
                            <div class="border rounded-2 p-2 bg-light reg-sup-row h-100" data-prefix="{{ $p }}" data-group="homemed" data-code="{{ $sm->sm_code }}" data-def-dose="{{ $sm->default_dose }}" data-def-route="{{ $sm->default_route }}" data-def-inst="{{ $sm->default_instruction }}">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input reg-sup-chk" type="checkbox" role="switch"
                                           id="{{ $p }}_chk_{{ $sm->sm_code }}"
                                           name="supportive[{{ $sm->sm_code }}][enabled]" value="1"
                                           onchange="onRegSupToggle('{{ $p }}', '{{ $sm->sm_code }}')">
                                    <label class="form-check-label fw-bold small" for="{{ $p }}_chk_{{ $sm->sm_code }}">{{ $sm->sm_name }}</label>
                                </div>
                                <div class="row g-1">
                                    <div class="col-4">
                                        <input type="text" class="form-control form-control-sm reg-sup-input" id="{{ $p }}_dose_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][dose]" value="{{ $sm->default_dose }}" disabled>
                                    </div>
                                    <div class="col-3">
                                        <input type="text" class="form-control form-control-sm text-center reg-sup-input" id="{{ $p }}_route_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][route]" value="{{ $sm->default_route }}" disabled>
                                    </div>
                                    <div class="col-5">
                                        <input type="text" class="form-control form-control-sm reg-sup-input" id="{{ $p }}_inst_{{ $sm->sm_code }}" name="supportive[{{ $sm->sm_code }}][instruction]" value="{{ $sm->default_instruction }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
