@extends('layouts.app')

@section('title', 'หน้าหลัก - COPS Oncology')

@section('content')
<div class="card card-medical bg-white p-4 mb-4 border-0">
    <div class="row align-items-center">
        <div class="col-md-8">
            <span class="badge {{ auth()->user()->roleBadgeClass() }} mb-2 px-3 py-1">
                {{ auth()->user()->roleTitle() }}
            </span>
            <h3 class="fw-bold mb-1">ยินดีต้อนรับ, {{ auth()->user()->name }}</h3>
            <p class="text-secondary mb-0">
                ระบบบริหารจัดการคำสั่งใช้ยา การเตรียมยา และการบริหารยาเคมีบำบัดชนิดฉีด (ฐานข้อมูล <code>oncospr</code>)
            </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            @if(auth()->user()->canAccessDoctor())
                <a href="{{ route('doctor.create') }}" class="btn btn-primary fw-semibold shadow-sm">
                    <i class="bi bi-plus-circle-fill me-1"></i> สั่งใช้ยาเคมีบำบัดใหม่
                </a>
            @endif
        </div>
    </div>
</div>

<!-- เมนูเข้าถึงระบบตามสิทธิ์ผู้ใช้งาน (Role-based Isolation) -->
<div class="row g-4 justify-content-center">
    @if(auth()->user()->canAccessDoctor())
        <div class="col-md-4">
            <a href="{{ route('doctor.index') }}" class="text-decoration-none text-dark">
                <div class="card card-medical card-hover h-100 bg-white border-top border-4 border-primary">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <img src="{{ asset('images/doctorf.png') }}" alt="Doctor" style="height: 110px; object-fit: contain;">
                        </div>
                        <h4 class="fw-bold text-primary">ระบบแพทย์ (Doctor)</h4>
                        <p class="text-muted small mb-3">
                            เลือกสูตรยา Regimen, คำนวณ BSA &amp; GFR, ปรับลดขนาดยา (% Reduction) พร้อมระบุเหตุผล และส่งคำสั่งใช้ยาเคมีบำบัด
                        </p>
                        <div class="d-flex justify-content-around bg-light rounded-3 p-2 small">
                            <div>
                                <div class="fw-bold text-primary fs-5">{{ $stats['today_orders_total'] }}</div>
                                <span class="text-secondary">คำสั่งยาวันนี้</span>
                            </div>
                            <div class="border-start"></div>
                            <div>
                                <div class="fw-bold text-success fs-5">{{ $stats['today_orders_confirmed'] }}</div>
                                <span class="text-secondary">รับคำสั่งแล้ววันนี้</span>
                            </div>
                        </div>
                        <div class="mt-2 pt-1 text-muted opacity-75" style="font-size: 0.74rem;">
                            สะสมทั้งหมด: {{ $stats['orders_total'] }} คำสั่งยา &bull; สูตร Regimen {{ $stats['regimens_count'] }} สูตร
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endif

    @if(auth()->user()->canAccessPharmacist())
        <div class="col-md-4">
            <a href="{{ route('pharmacist.index') }}" class="text-decoration-none text-dark">
                <div class="card card-medical card-hover h-100 bg-white border-top border-4 border-success">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <img src="{{ asset('images/pharmacist.png') }}" alt="Pharmacist" style="height: 110px; object-fit: contain;">
                        </div>
                        <h4 class="fw-bold text-success">ระบบเภสัชกร (Pharmacist)</h4>
                        <p class="text-muted small mb-3">
                            รับคำสั่งใช้ยาแบบ Real-time, ยืนยันรับคำสั่ง, แตกคำสั่งใช้ยารายวัน (<code>onc_dayrecord</code>), พิมพ์ใบจัดยา (A5 แนวนอน) และพิมพ์ฉลากยา
                        </p>
                        <div class="d-flex justify-content-around bg-light rounded-3 p-2 small">
                            <div>
                                <div class="fw-bold text-danger fs-5">{{ $stats['today_orders_pending'] }}</div>
                                <span class="text-secondary">รอรับคำสั่งวันนี้</span>
                            </div>
                            <div class="border-start"></div>
                            <div>
                                <div class="fw-bold text-success fs-5">{{ $stats['today_orders_confirmed'] }}</div>
                                <span class="text-secondary">รับคำสั่งแล้ววันนี้</span>
                            </div>
                        </div>
                        <div class="mt-2 pt-1 text-muted opacity-75" style="font-size: 0.74rem;">
                            สะสมทั้งหมด: รอรับคำสั่ง {{ $stats['orders_pending'] }} &bull; รับแล้ว {{ $stats['orders_confirmed'] }} (รวม {{ $stats['orders_total'] }})
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endif

    @if(auth()->user()->canAccessNurse())
        <div class="col-md-4">
            <a href="{{ route('nurse.index') }}" class="text-decoration-none text-dark">
                <div class="card card-medical card-hover h-100 bg-white border-top border-4 border-info">
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <img src="{{ asset('images/hospital.png') }}" alt="Nurse" style="height: 110px; object-fit: contain;">
                        </div>
                        <h4 class="fw-bold text-info">ระบบพยาบาล (Nurse)</h4>
                        <p class="text-muted small mb-3">
                            ตรวจสอบตารางการให้ยาเคมีบำบัดรายวันตามหอผู้ป่วย ตรวจสอบข้อควรระวัง (High-Alert) และยืนยันการรับ/บริหารยาข้างเตียง
                        </p>
                        <div class="d-flex justify-content-around bg-light rounded-3 p-2 small">
                            <div>
                                <div class="fw-bold text-info fs-5">{{ $stats['today_doses'] }}</div>
                                <span class="text-secondary">รายการให้ยาวันนี้</span>
                            </div>
                            <div class="border-start"></div>
                            <div>
                                <div class="fw-bold text-primary fs-5">{{ $stats['today_delivered'] }}</div>
                                <span class="text-secondary">บริหารยาแล้ววันนี้</span>
                            </div>
                        </div>
                        <div class="mt-2 pt-1 text-muted opacity-75" style="font-size: 0.74rem;">
                            สะสมทั้งหมด: รายการให้ยา {{ $stats['all_doses'] }} โดส &bull; บริหารยาแล้ว {{ $stats['all_delivered'] }} โดส
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @endif
</div>

@if(auth()->user()->isAdmin())
    <div class="card card-medical bg-white border-start border-4 border-dark mt-4 p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-journal-medical me-1 text-primary"></i> การจัดการข้อมูลหลักสำหรับผู้ดูแลระบบ (Admin &amp; Superadmin)</h6>
                <p class="small text-secondary mb-0">
                    จัดการบัญชียาเคมีบำบัด (<code>medcyto</code>: {{ $stats['meds_count'] ?? 0 }} รายการ)
                    และสูตรยาเคมีบำบัดมาตรฐาน (Regimen: <code>reg_h</code> &amp; <code>reg_d</code>: {{ $stats['regimens_count'] }} สูตรยา)
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('medcyto.index') }}" class="btn btn-danger btn-sm fw-semibold">
                    <i class="bi bi-capsule me-1"></i> จัดการยาเคมีบำบัด (medcyto)
                </a>
                <a href="{{ route('regimens.index') }}" class="btn btn-dark btn-sm fw-semibold">
                    <i class="bi bi-journal-medical me-1"></i> จัดการสูตรยา Regimen
                </a>
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('users.index') }}" class="btn btn-outline-danger btn-sm fw-semibold">
                        <i class="bi bi-people-fill me-1"></i> จัดการผู้ใช้งาน (onc_users: {{ $stats['users_count'] }} บัญชี)
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
@endsection
