@extends('layouts.app')

@section('title', 'จัดการผู้ใช้งาน (onc_users) - Superadmin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0"><i class="bi bi-people-fill text-danger me-2"></i>จัดการผู้ใช้งานระบบ (ตาราง <code>onc_users</code>)</h4>
        <small class="text-secondary">เฉพาะสิทธิ์ Superadmin สามารถเพิ่มและลบผู้ใช้งานในแต่ละกลุ่มวิชาชีพได้</small>
    </div>
</div>

<div class="row g-4">
    <!-- ฟอร์มเพิ่มผู้ใช้งานใหม่ -->
    <div class="col-lg-4">
        <div class="card card-medical bg-white">
            <div class="card-header bg-dark text-white fw-bold py-3">
                <i class="bi bi-person-plus-fill me-1"></i> เพิ่มผู้ใช้งานใหม่
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Username (สำหรับ Login) <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" value="{{ old('username') }}" required placeholder="เช่น doc01, pharm01, nurse01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required placeholder="เช่น นพ. ใจดี รักษาเก่ง">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">กลุ่มผู้ใช้งาน (Role) <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="doctor" {{ old('role') === 'doctor' ? 'selected' : '' }}>Doctor (แพทย์ - สั่งยาเคมีบำบัด)</option>
                            <option value="pharmacist" {{ old('role') === 'pharmacist' ? 'selected' : '' }}>Pharmacist (เภสัชกร - รับคำสั่ง/เตรียมยา)</option>
                            <option value="nurse" {{ old('role') === 'nurse' ? 'selected' : '' }}>Nurse (พยาบาล - บริหารยาเคมีบำบัด)</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (ดูแลระบบ แพทย์/เภสัชกร/พยาบาล)</option>
                            <option value="superadmin" {{ old('role') === 'superadmin' ? 'selected' : '' }}>Superadmin (เข้าได้ทุกระดับ + เพิ่ม/ลบผู้ใช้)</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">เลขใบประกอบวิชาชีพ</label>
                            <input type="text" name="license_no" class="form-control" value="{{ old('license_no') }}" placeholder="ว. / ภ. / พ.">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">แผนก / หน่วยงาน</label>
                            <input type="text" name="department" class="form-control" value="{{ old('department') }}" placeholder="หน่วยมะเร็งวิทยา">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">อีเมล (ถ้ามี)</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="user@cops.local">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">รหัสผ่าน <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required placeholder="อย่างน้อย 4 ตัวอักษร">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="bi bi-save me-1"></i> บันทึกผู้ใช้งานใหม่
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ตารางรายชื่อผู้ใช้งาน -->
    <div class="col-lg-8">
        <div class="card card-medical bg-white">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold">รายชื่อผู้ใช้งานทั้งหมด ({{ $users->count() }} บัญชี)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Username</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>กลุ่มสิทธิ์ (Role)</th>
                            <th>เลขใบประกอบฯ / แผนก</th>
                            <th class="text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td class="fw-bold text-primary">{{ $u->username }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $u->name }}</div>
                                    @if($u->email)
                                        <small class="text-muted">{{ $u->email }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $u->roleBadgeClass() }}">{{ strtoupper($u->role) }}</span>
                                </td>
                                <td>
                                    @if($u->license_no)
                                        <span class="badge bg-light text-dark border">{{ $u->license_no }}</span>
                                    @endif
                                    <div class="small text-secondary">{{ $u->department ?? '-' }}</div>
                                </td>
                                <td class="text-end">
                                    @if($u->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $u->id) }}" onsubmit="return confirm('ยืนยันการลบผู้ใช้งาน {{ $u->name }} ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i> ลบ
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge bg-secondary">บัญชีของคุณ</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
