@extends('layouts.app')

@section('title', 'เข้าสู่ระบบ - COPS Oncology')

@section('content')
<div class="row justify-content-center align-items-center my-4">
    <div class="col-lg-5 col-md-7">
        <div class="card card-medical border-0 shadow">
            <div class="card-header text-white text-center py-4 border-0" style="background: linear-gradient(135deg, #0f172a, #1e3a8a);">
                <img src="{{ asset('images/spr.jpg') }}" alt="SPR" width="60" height="60" class="rounded-circle border border-3 border-white mb-2 shadow-sm">
                <h4 class="fw-bold mb-1">ระบบสั่งและเตรียมยาเคมีบำบัด (COPS)</h4>
                <p class="small mb-0 opacity-75">สำหรับ แพทย์ &bull; เภสัชกร &bull; พยาบาล (ตารางผู้ใช้: <code>onc_users</code>)</p>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ชื่อผู้ใช้งาน (Username หรือ Email)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="username" id="usernameInput" class="form-control" value="{{ old('username') }}" placeholder="เช่น doctor, pharmacist, nurse, admin, superadmin" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">รหัสผ่าน (Password)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key-fill"></i></span>
                            <input type="password" name="password" id="passwordInput" class="form-control" placeholder="รหัสผ่าน" required>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="bg-light rounded-3 p-3 border">
                    <div class="small fw-bold text-secondary mb-2">
                        <i class="bi bi-lightning-charge-fill text-warning me-1"></i> ทดสอบเข้าสู่ระบบด่วนตามสิทธิ์ (คลิกเพื่อเข้าสู่ระบบทันที):
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="quickLogin('doctor')">
                            <i class="bi bi-heart-pulse"></i> แพทย์ (doctor)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="quickLogin('pharmacist')">
                            <i class="bi bi-capsule"></i> เภสัชกร (pharmacist)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info text-dark" onclick="quickLogin('nurse')">
                            <i class="bi bi-clipboard2-pulse"></i> พยาบาล (nurse)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="quickLogin('admin')">
                            <i class="bi bi-shield-check"></i> Admin (admin)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="quickLogin('superadmin')">
                            <i class="bi bi-stars"></i> Superadmin
                        </button>
                    </div>
                    <div class="text-muted mt-2" style="font-size: 0.78rem;">
                        * รหัสผ่านเริ่มต้นของทุกบัญชีคือ <code>password</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js_after')
<script>
    function quickLogin(username) {
        document.getElementById('usernameInput').value = username;
        document.getElementById('passwordInput').value = 'password';
        document.getElementById('loginForm').submit();
    }
</script>
@endsection
